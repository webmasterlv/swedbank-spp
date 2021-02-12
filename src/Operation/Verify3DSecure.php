<?php

namespace Swedbank\SPP\Operation;

use Swedbank\SPP\Operation;
use Swedbank\SPP\Order;
use Swedbank\SPP\Customer;
use Swedbank\SPP\Payment\Method;
use Swedbank\SPP\Response\SetupResponse;
use Swedbank\SPP\Gateway;
use Swedbank\SPP\Response\RecurringSetupResponse;

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
class Verify3DSecure implements Operation
{
	private $pares_message;

	/**
	 * Request new transaction
	 *
	 * @param Order $order Customer order
	 * @param Customer $customer Customer
	 * @param Method $method Payment method
	 */
	public function __construct(Order $order, $reference, $pares_message = '')
	{
		$this -> order		 = $order;
		$this -> pares_message = $pares_message ? : (isset($_POST['PaRes']) ? $_POST['PaRes'] : '');
		$this -> reference = $reference;
	}

	/**
	 * @inheritDoc
	 */
	public function getCustomFields(Gateway $gateway = null)
	{
		return [
			'pares_message' => $this -> pares_message,
			'3ds.reference' => $this -> reference
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getDataSourceMembers()
	{
		return [
			$this -> order,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplate()
	{
		return '3dsecure_verify';
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
