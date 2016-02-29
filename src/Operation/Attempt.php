<?php

namespace Swedbank\SPP\Operation;

use Swedbank\SPP\Customer;
use Swedbank\SPP\Operation;
use Swedbank\SPP\Order;
use Swedbank\SPP\Response\AttemptResponsePayPal;

/**
 * Performs PayPal transaction submission
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class Attempt implements Operation
{
	/**
	 * Reference ID
	 * @var string
	 */
	protected $refId;

	/**
	 * Master reference
	 *
	 * @var string
	 */
	protected $refIdMaster;

	/**
	 * Customer data
	 * 
	 * @var Customer
	 */
	protected $customer;

	/**
	 * Merchant order
	 *
	 * @var Order
	 */
	protected $order;

	/**
	 * Create new submission operation
	 *
	 * @param string $refId
	 * @param string $refIdMaster
	 * @param Order $order
	 * @param Customer $customer
	 */
	public function __construct($refId, $refIdMaster, Order $order, Customer $customer)
	{
		$this -> refId		 = $refId;
		$this -> refIdMaster = $refIdMaster;
		$this -> customer	 = $customer;
		$this -> order		 = $order;
	}

	/**
	 * @inheritDoc
	 */
	public function getCustomFields()
	{
		return [
			'reference_id' => $this -> refId,
			'reference_id_master' => $this -> refIdMaster
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getDataSourceMembers()
	{
		return [
			$this -> customer,
			$this -> order
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplate()
	{
		return 'attempt_paypal';
	}

	/**
	 * @inheritDoc
	 * @return AttemptResponsePayPal
	 */
	public function parseResponse($response)
	{
		return new AttemptResponsePayPal($response);
	}
}