<?php

namespace Swedbank\SPP;

use Behat\Transliterator\Transliterator;

/**
 * Describes merchant transaction order.
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class Order implements DataSource
{
	/**
	 * Minimum merchant reference ID length
	 */
	const MIN_REF_LENGTH = 10;

	/**
	 * Merchant order ID
	 * @var mixed
	 */
	protected $id;

	/**
	 * Reference ID
	 * 
	 * @var string
	 */
	protected $refId = false;

	/**
	 * Order description
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Order amount
	 *
	 * @var int
	 */
	protected $amount;

	/**
	 * Order retry number
	 *
	 * @var string
	 */
	protected $retry = '';

	/**
	 * Should we automatically generate reference field
	 *
	 * @var boolean
	 */
	protected $autoRetry = true;

	/**
	 * Create new order
	 *
	 * @param mixed $id
	 * @param string $description
	 * @param int|float $amount
	 * throws \InvalidArgumentException
	 */
	public function __construct($id, $description, $amount)
	{
		$this -> id			 = $id;
		$this -> description = $description;
		$this -> amount		 = (int) ($amount * 100);

		if ($this -> amount < 0) {
			throw new \InvalidArgumentException('Invalid order or delivery amount');
		}
	}

	/**
	 * Set retry count if the same order is submitted multiple times
	 *
	 * @param int $retry
	 */
	public function setRetry($retry)
	{
		$this -> refId		 = false;
		$this -> retry		 = $retry;
		$this -> autoRetry	 = false;
	}

	/**
	 * Merchant Order ID
	 *
	 * @return mixed
	 */
	public function getId()
	{
		return $this -> id;
	}

	/**
	 * Order ID for use in transaction processing
	 *
	 * @return string
	 */
	public function getReference()
	{
		if ($this -> refId !== false) {
			return $this -> refId;
		}

		$rand = '';
		for ($i = 0; $i < 6; $i++) {
			$rand .= mt_rand(0, 9);
		}

		$retry	 = $this -> autoRetry ? $rand : $this -> retry;
		$ref	 = $this -> getId().($retry ? '/'.$retry : '');

		if (strlen($ref) < self::MIN_REF_LENGTH) {
			$ref = str_repeat('0', self::MIN_REF_LENGTH - strlen($ref)).$ref;
		}

		$this -> refId = $ref;
		return $ref;
	}

	/**
	 * Return order sum
	 *
	 * @return int
	 */
	public function getTotal()
	{
		return $this -> amount;
	}

	/**
	 * Returns order description
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return Transliterator::utf8ToAscii($this -> description);
	}

	/**
	 * @inheritDoc
	 */
	public function getFields()
	{
		return [
			'id' => $this -> getId(),
			'reference' => $this -> getReference(),
			'description' => $this -> getDescription(),
			'total' => $this -> getTotal(),
			'total_base' => $this -> getTotal() / 100
		];
	}
}