<?php

namespace Swedbank\SPP\Operation;

use Swedbank\SPP\Operation;
use Swedbank\SPP\Order;
use Swedbank\SPP\Payment\CreditCard;
use Swedbank\SPP\Response\SetupResponse;
use Swedbank\SPP\Gateway;

/**
 * Creates a card capture session request
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class CaptureCardSetup implements Operation
{
	public function __construct(Order $order)
	{
		$this -> order		 = $order;
	}

	/**
	 * @inheritDoc
	 */
	public function getCustomFields(Gateway $gateway = null)
	{
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function getDataSourceMembers()
	{
		return [
			$this -> order
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplate()
	{
		return $this -> order -> getTotal() > 0 ? 'card_capture_setup_with_pay' : 'card_capture_setup';
	}

	/**
	 * @inheritDoc
	 * @return SetupResponse
	 */
	public function parseResponse($response, $mode = '')
	{
		return new SetupResponse(new CreditCard(), $mode, $response);
	}
}
