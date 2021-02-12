<?php

namespace Swedbank\SPP\Operation;

use Swedbank\SPP\Operation;
use Swedbank\SPP\Order;
use Swedbank\SPP\Customer;
use Swedbank\SPP\Payment\Method;
use Swedbank\SPP\Payment\CreditCard;
use Swedbank\SPP\Response\SetupResponse;
use Swedbank\SPP\Gateway;
use Swedbank\SPP\Response\RecurringSetupResponse;

/**
 * Stores customer credit card information in bank for recurring or instant payments
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class RecurringSetup implements Operation
{
	/**
	 * Customer order
	 *
	 * @var Order
	 */
	private $order;

	/**
	 * Customer
	 *
	 * @var Customer
	 */
	private $customer;

	/**
	 * Setup reference
	 *
	 * @var string
	 */
	private $reference;

	/**
	 * Request new transaction
	 *
	 * @param Order $order Customer order
	 * @param Customer $customer Customer
	 * @param Method $method Payment method
	 */
	public function __construct(Order $order, Customer $customer, $reference)
	{
		$this -> order		 = $order;
		$this -> customer	 = $customer;
		$this -> payment	 = new CreditCard();
		$this -> reference 	 = $reference;
	}

	/**
	 * @inheritDoc
	 */
	public function getCustomFields(Gateway $gateway = null)
	{
		return [
			'card.reference' => $this -> reference
		];
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
		return $this -> order -> getTotal() > 0 ? 'recurring_setup_pay' : 'recurring_setup';
	}

	/**
	 * @inheritDoc
	 * @return SetupResponse
	 */
	public function parseResponse($response, $mode = '')
	{
		return new RecurringSetupResponse($response);
	}
}
