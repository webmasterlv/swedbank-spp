<?php

namespace Swedbank\SPP\Response;

use Swedbank\SPP\Gateway;

/**
 * Response for 'Query' operation
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
abstract class QueryResponse extends GenericResponse
{
	/**
	 * Query type
	 * 
	 * @var string
	 */
	protected $type;

	/**
	 * Returns extended reference for second stage request
	 * @return string
	 */
	abstract protected function getExtendedReference();

	/**
	 * Returns transaction status information
	 * @return array
	 */
	abstract protected function getTransactionStatus();

	/**
	 * Returns extended data
	 * @return array
	 */
	abstract public function getExtendedData();

	/**
	 * New query response
	 *
	 * @param string $type Query type
	 * @param array $data
	 */
	public function __construct($type, array $data = [])
	{
		$this -> type	 = $type;
		$this -> data	 = $data;
		parent::__construct($data);
	}

	/**
	 * @inheritDoc
	 */
	public function getStatus()
	{
		$status = $this -> getTransactionStatus();

		if ($status === false) {
			return Gateway::STATUS_PENDING;
		}

		return $status['code'];
	}

	/**
	 * @inheritDoc
	 */
	public function getReference()
	{
		return isset($this -> data['datacash_reference']) ? $this -> data['datacash_reference'] : '';
	}

	/**
	 * @inheritDoc
	 */
	public function isSuccess()
	{
		return $this -> getStatus() == Gateway::STATUS_SUCCESS;
	}

	/**
	 * @inheritDoc
	 */
	public function getMessage()
	{
		$status = $this -> getTransactionStatus();

		if ($status === false) {
			return '';
		}

		return $status['reason'];
	}

	/**
	 * @inheritDoc
	 */
	public function getSource()
	{
		return 'spp';
	}
}
