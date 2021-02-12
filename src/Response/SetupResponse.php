<?php

namespace Swedbank\SPP\Response;

use Swedbank\SPP\Payment\Method;
use Swedbank\SPP\Payment\PayPal;
use Swedbank\SPP\Gateway;

/**
 * Response for 'Setup' operation
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class SetupResponse extends GenericResponse
{
	/**
	 * Payment method
	 *
	 * @var Method
	 */
	protected $method;

	/**
	 * Query type
	 * @var string
	 */
	protected $mode;

	/**
	 * Creates new transaction
	 *
	 * @param Method $method Payment method
	 * @param type $mode Query type
	 * @param array $data
	 */
	public function __construct(Method $method, $mode, array $data = [])
	{
		$this -> method	 = $method;
		$this -> mode	 = $mode;

		parent::__construct($data);
	}

	/**
	 * Returns customer redirect URL
	 * @return string|boolean
	 */
	public function getRedirectUrl()
	{
		if (!$this -> isSuccess()) {
			return false;
		}

		if ($this -> method instanceof PayPal) {
			$token = $this -> data['PayPalTxn']['token'];
			if ($this -> mode == Gateway::ENV_DEV) {
				return 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$token;
			} else {
				return 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$token;
			}
		} else {
			$hps = $this -> data['HpsTxn'];
			return $hps['hps_url'].'?HPS_SessionID='.$hps['session_id'];
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getReference()
	{
		if (!isset($this -> data['datacash_reference'])) {
			return '';
		}

		return $this -> data['datacash_reference'];
	}

	/**
	 * @inheritDoc
	 */
	public function isSuccess()
	{
		return $this -> data['status'] == 1 && isset($this -> data['datacash_reference']);
	}

	/**
	 * @inheritDoc
	 */
	public function getStatus()
	{
		return $this -> data['status'] == 1 ? Gateway::STATUS_SUCCESS : Gateway::STATUS_ERROR;
	}

	/**
	 * @inheritDoc
	 */
	public function getMessage()
	{
		return $this -> data['reason'];
	}

	/**
	 * @inheritDoc
	 */
	public function getRemoteStatus()
	{
		return $this -> data['status'];
	}

	/**
	 * @inheritDoc
	 */
	public function getSource()
	{
		return 'spp';
	}
}
