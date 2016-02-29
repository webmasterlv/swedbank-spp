<?php

namespace Swedbank\SPP\Operation;

use Swedbank\SPP\Operation;
use Swedbank\SPP\Order;
use Swedbank\SPP\Payment\Method;
use Swedbank\SPP\Payment\PayPal;

/**
 * Queries transaction status
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class Query implements Operation
{
	/**
	 * Query types
	 */
	const TYPE_INFO		 = 'info';
	const TYPE_EXTENDED	 = 'extended';

	/**
	 * Order reference
	 *
	 * @var string
	 */
	protected $refId;

	/**
	 * Payment method
	 * @var Method
	 */
	protected $method;

	/**
	 * Customer order
	 * 
	 * @var Order
	 */
	protected $order;

	/**
	 * Query type
	 * 
	 * @var string
	 */
	protected $type;

	/**
	 * Query transaction status
	 *
	 * @param string $type Query type (Query::TYPE_INFO, Query::TYPE_EXTENDED)
	 * @param string $refId Order reference
	 * @param Method $method Payment method
	 * @param Order $order Customer order
	 */
	public function __construct($type, $refId, Method $method, Order $order)
	{
		$this -> type	 = $type;
		$this -> refId	 = $refId;
		$this -> method	 = $method;
		$this -> order	 = $order;
	}

	/**
	 * @inheritDoc
	 */
	public function getCustomFields()
	{
		return [
			'reference_id' => $this -> refId
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getDataSourceMembers()
	{
		return [
			$this -> method,
			$this -> order
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplate()
	{
		if ($this -> method instanceof PayPal && $this -> type == self::TYPE_INFO) {
			return 'query_paypal';
		}

		if ($this -> method instanceof PayPal && $this -> type == self::TYPE_EXTENDED) {
			return 'query_paypal';
		}

		return 'query';
	}

	/**
	 * @inheritDoc
	 * @return \Swedbank\SPP\Response\QueryResponse
	 */
	public function parseResponse($response)
	{
		$responseClass = '\Swedbank\SPP\Response\QueryResponse'.$this -> method -> getCodeName();
		return new $responseClass($this -> type, $response);
	}
}