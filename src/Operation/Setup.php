<?php

namespace Swedbank\SPP\Operation;

use Swedbank\SPP\Operation;
use Swedbank\SPP\Order;
use Swedbank\SPP\Customer;
use Swedbank\SPP\Payment\Method;
use Swedbank\SPP\Payment\CreditCard;
use Swedbank\SPP\Payment\InternetBank;
use Swedbank\SPP\Payment\PayPal;
use Swedbank\SPP\Response\SetupResponse;
use Swedbank\SPP\Gateway;

/**
 * Requests new transaction
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class Setup implements Operation
{
	/**
	 * Customer order
	 *
	 * @var Order
	 */
	protected $order;

	/**
	 * Customer object
	 *
	 * @var Customer
	 */
	protected $customer;

	/**
	 * Payment method
	 *
	 * @var Method
	 */
	protected $payment;

	/**
	 * Request new transaction
	 *
	 * @param Order $order Customer order
	 * @param Customer $customer Customer
	 * @param Method $method Payment method
	 */
	public function __construct(Order $order, Customer $customer, Method $method)
	{
		$this -> order		 = $order;
		$this -> customer	 = $customer;
		$this -> payment	 = $method;
	}

	/**
	 * @inheritDoc
	 */
	public function getCustomFields(Gateway $gateway = null)
	{
		// Override merchant urls - append order data to it
		if (!is_null($gateway)) {
			$urls = $gateway -> getMerchantUrls(['_merchantRef' => $this -> order -> getId()]);

			$data = [
				'gateway.successurl' => $urls['success'],
				'gateway.errorurl' => $urls['error']
			];
		}

		if ($this -> payment instanceof InternetBank) {
			$data['bank_type'] = $this -> payment -> getBank();
		}

		return $data;
	}

	/**
	 * @inheritDoc
	 */
	public function getDataSourceMembers()
	{
		return [
			$this -> order,
			$this -> customer
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplate()
	{
		if ($this -> payment instanceof CreditCard) {
			return 'payment_create_cc';
		}

		if ($this -> payment instanceof InternetBank) {
			return 'payment_create_ib';
		}

		if ($this -> payment instanceof PayPal) {
			return 'payment_create_paypal';
		}
	}

	/**
	 * @inheritDoc
	 * @return SetupResponse
	 */
	public function parseResponse($response, $mode = '')
	{
		return new SetupResponse($this -> payment, $mode, $response);
	}
}