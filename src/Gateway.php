<?php

namespace Swedbank\SPP;

use Swedbank\SPP\Accreditation;
use Swedbank\SPP\Exception\GatewayException;
use Swedbank\SPP\Merchant;
use Swedbank\SPP\Response\NetworkError;
use Swedbank\SPP\Response\InternalError;
use Swedbank\SPP\Operation\Query;
use Swedbank\SPP\Operation\Setup;
use Swedbank\SPP\Operation\SetPassword;
use Swedbank\SPP\Order;
use Swedbank\SPP\Operation\Attempt;
use Swedbank\SPP\Payment\Method;
use Swedbank\SPP\Transport\Agent;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;

/**
 * Payment gateway interface
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class Gateway implements DataSource, LoggerAwareInterface
{
	use LoggerAwareTrait;
	
	/**
	 * Environment settings
	 */
	const ENV_DEV	 = 'dev';
	const ENV_PROD = 'prod';

	/**
	 * Merchant regions
	 */
	const REGION_LAT	 = 'LTV_BANK';
	const REGION_LIT	 = 'LIT_BANK';
	const REGION_EST	 = 'EST_BANK';

	/**
	 * Language constants
	 */
	const LANG_ENG = 'en';
	const LANG_EST = 'et';
	const LANG_LIT = 'lt';
	const LANG_LAT = 'lv';
	const LANG_RUS = 'ru';

	/**
	 * Response codes
	 */
	const STATUS_SUCCESS		 = 1;
	const STATUS_PENDING		 = 2;
	const STATUS_ERROR			 = 3;
	const STATUS_CANCELED		 = 4;
	const STATUS_INVESTIGATE	 = 5;
	const STATUS_REFUSED		 = 7;
	const STATUS_REFUNDED		 = 8;
	const STATUS_TIMEOUT		 = 9;
	const STATUS_STARTED		 = 10;
	const STATUS_UNKNOWN		 = 11;

	/**
	 * Currency codes
	 */
	const CURRENCY_EUR = 978;

	/**
	 * Access credentials
	 * 
	 * @var Accreditation
	 */
	protected $accreditation;

	/**
	 * Merchant data
	 * 
	 * @var Merchant
	 */
	protected $merchant;

	/**
	 * Operation environment
	 * 
	 * @var string
	 */
	protected $mode;

	/**
	 * Initialize the gateway
	 * 
	 * @param Accreditation $accreditation Access credentials
	 * @param Merchant $merchant Merchant data
	 * @param string $mode Environment
	 * @throws GatewayException
	 */
	public function __construct(Accreditation $accreditation, Merchant $merchant, $mode)
	{
		if (!in_array($mode, [self::ENV_DEV, self::ENV_PROD])) {
			throw new GatewayException(sprintf('Invalid mode supplied (\'%s\')', $mode));
		}

		if ($accreditation -> getCredentials($mode) === false) {
			throw new GatewayException('No configuration defined for current mode');
		}

		$this -> accreditation	 = $accreditation;
		$this -> merchant		 = $merchant;
		$this -> mode			 = $mode;
	}

	/**
	 * Is development mode
	 * 
	 * @return boolean
	 */
	public function isDev()
	{
		return $this -> mode == self::ENV_DEV;
	}
	

	/**
	 * Returns API endpoints
	 *
	 * @return array
	 */
	private function getEndpointUrls()
	{
		if ($this -> isDev()) {
			return [
				'https://accreditation.datacash.com/Transaction/acq_a'
			];
		}

		return [
			'https://mars.transaction.datacash.com/Transaction',
			'https://venus.transaction.datacash.com/Transaction'
		];
	}

	/**
	 * Parses response URL
	 *
	 * @param string $url URL to parse
	 * @return array List of URLs
	 */
	private function parseUrl($url)
	{
		$url			 = parse_url($url);
		$url['query']	 = isset($url['query']) ? $url['query'] : '';

		parse_str($url['query'], $get);

		return [
			'absolute' => $url['scheme'].'://'.$url['host'],
			'path' => $url['scheme'].'://'.$url['host'].$url['path'],
			'params' => $get
		];
	}

	/**
	 * Returns merchant URLs
	 *
	 * @param array $add_data Additional data pass in query
	 * @return array
	 */
	public function getMerchantUrls(array $add_data = [])
	{
		$key = $this -> accreditation -> getCredentials($this -> mode);

		$successUrl								 = $this -> parseUrl($key['responseUrl']);
		$successUrl['params']['_banklinkStatus'] = 'success';
		$successUrl['params'] += $add_data;

		$errorUrl								 = $this -> parseUrl($key['responseUrl']);
		$errorUrl['params']['_banklinkStatus']	 = 'fail';
		$errorUrl['params'] += $add_data;

		return [
			'success' => $successUrl['path'].'?'.http_build_query($successUrl['params']),
			'error' => $errorUrl['path'].'?'.http_build_query($errorUrl['params']),
			'store' => $successUrl['absolute'].'/'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getFields()
	{
		$urls	 = $this -> getMerchantUrls();
		$key	 = $this -> accreditation -> getCredentials($this -> mode);
		$ip		 = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

		return [
			'client' => $key['client'],
			'password' => $key['password'],
			'successurl' => $urls['success'],
			'errorurl' => $urls['error'],
			'storeurl' => $urls['store'],
			'date' => date('Ymd H:i:s'),
			'ip' => $ip
		];
	}

	/**
	 * Preparse data for payload
	 *
	 * @param Operation $operation
	 * @return array Data for payload
	 */
	private function prepareData(Operation $operation)
	{
		$data		 = [];
		$datasources = $operation -> getDataSourceMembers();
		$members	 = array_merge(is_array($datasources) ? $datasources : [], [$this, $this -> merchant]);

		foreach ($members as $member) {
			if ($member instanceof DataSource) {
				$fields = $member -> getFields();

				if (!is_array($fields)) {
					continue;
				}

				$ref	 = new \ReflectionClass($member);
				$prefix	 = strtolower($ref -> getShortName());
				foreach ($fields as $k => $v) {
					$data[$prefix.'.'.$k] = $v;
				}
			}
		}

		$custom = $operation -> getCustomFields($this);
		if (is_array($custom) && !empty($custom)) {
			$data = $custom + $data;
		}

		return $data;
	}

	/**
	 * Raw operation processing
	 *
	 * @param Operation $operation Operation to dispatch
	 * @return InternalError|NetworkError|Response
	 */
	public function processOperation(Operation $operation)
	{
		$template	 = $operation -> getTemplate();
		$data		 = $this -> prepareData($operation);

		$parser	 = new Template($template, $data);
		$payload = $parser -> parse();

		$this -> logEvent(LogLevel::DEBUG, $payload);

		$agent = new Agent($payload, $this -> getEndpointUrls());

		$result = $agent -> dispatch();

		$this -> logEvent(LogLevel::DEBUG, $result);

		if (!$agent -> isSuccess()) {
			$status = $agent -> getStatus();
			$this -> logEvent(LogLevel::ALERT, 'Network error ('.$status['errcode'].'): '.$status['message']);
			return new NetworkError('Local error: '.$status['message'], ['code' => $status['errcode']]);
		}

		$response = $parser -> decode($result);

		$return = $operation -> parseResponse($response, $this -> mode);

		if (!$return instanceof Response) {
			$return = new InternalError('Local error: invalid data received from operation');
		}

		return $return;
	}

	/**
	 * Creates new payment transaction.
	 * 
	 * @param Order $order Information about merchant order
	 * @param Customer $customer Information about customer
	 * @param Method $method Payment method
	 * @return Response
	 */
	public function createTransaction(Order $order, Customer $customer, Method $method)
	{
		return $this -> processOperation(new Setup($order, $customer, $method));
	}

	/**
	 * Changes gateway password
	 *
	 * @param string $newPassword
	 * @return Response
	 */
	public function setPassword($newPassword)
	{
		return $this -> processOperation(new SetPassword($newPassword));
	}

	/**
	 * Returns transaction status
	 *
	 * @param Order $order Customer order
	 * @param Customer $customer
	 * @param Method $method Payment method
	 * @param string $reference Order reference
	 * @param boolean $extended Always query extended data
	 * @return Response
	 * @throws GatewayException
	 */
	public function getStatus(Order $order, Customer $customer, Method $method, $reference = '', $extended = false)
	{
		// Try to get reference from URL
		if (!$reference) {
			$reference = filter_input(INPUT_GET, 'dts_reference');
		}

		if (!$reference) {
			throw new GatewayException('Reference required.');
		}

		// Perform initial query
		$query	 = new Query(Query::TYPE_INFO, $reference, $method, $order);
		$result	 = $this -> processOperation($query);

		if ($method instanceof Payment\PayPal) {
			if ($result -> isWaitingSubmission()) {
				$attempt_ref = $result -> getReference();
				$attempt	 = new Attempt($attempt_ref, $reference, $order, $customer);
				$submission	 = $this -> processOperation($attempt);

				if (!$submission -> isSuccess()) {
					$this -> logEvent(LogLevel::ERROR, 'PayPal order submission failed - '.$submission -> getMessage());
					return $submission;
				}

				$order -> setRetry(uniqid());

				return $this -> getStatus($order, $customer, $method, $reference);
			}

			return $result;
		}

		$status = $result -> getStatus();

		if ($status !== false && !$extended) {
			return $result;
		}

		$extRef = $result -> getExtendedReference();

		// No reference available, return what we got already
		if (!$extRef) {
			return $result;
		}

		return $this -> processOperation(new Query(Query::TYPE_EXTENDED, $extRef, $method, $order));
	}

	/**
	 * Returns extended order information
	 *
	 * @param string $reference Order reference
	 * @param Method $method Payment method
	 * @param Order $order Customer order
	 * @return array
	 * @throws GatewayException
	 */
	public function getPaymentInfo($reference, Method $method, Order $order = null)
	{
		if ($method instanceof Payment\PayPal && is_null($order)) {
			throw new GatewayException('Order required when querying PayPal transaction.');
		}

		$query	 = new Query(Query::TYPE_INFO, $reference, $method, $order);
		$result	 = $this -> processOperation($query);

		$extRef = $result -> getExtendedReference();

		if (!$extRef) {
			return [];
		}

		$ext = new Query(Query::TYPE_EXTENDED, $extRef, $method, $order);
		return $this -> processOperation($ext);
	}

	/**
	 * Wrapper for log() function
	 * 
	 * @param string $level Message level
	 * @param string $message Message
	 * @return boolean
	 */
	public function logEvent($level, $message)
	{
		if ($this -> logger) {
			return $this -> logger -> log($level, $message);
		}

		return false;
	}
}