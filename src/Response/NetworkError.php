<?php

namespace Swedbank\SPP\Response;

use Swedbank\SPP\Gateway;

/**
 * Response for network-related errors
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class NetworkError extends GenericResponse
{
	/**
	 * Customer error message
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * New network error response
	 *
	 * @param string $message
	 * @param array $data
	 */
	public function __construct($message, $data = array())
	{
		$this -> message = $message;
		parent::__construct($data);
	}

	/**
	 * @inheritDoc
	 */
	public function isSuccess()
	{
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getMessage()
	{
		return $this -> message;
	}

	/**
	 * @inheritDoc
	 */
	public function getReference()
	{
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function getStatus()
	{
		return Gateway::STATUS_ERROR;
	}

	/**
	 * @inheritDoc
	 */
	public function getRemoteStatus()
	{
		return isset($this -> data['code']) ? (int) $this -> data['code'] : 0;
	}

	/**
	 * @inheritDoc
	 */
	public function getSource()
	{
		return 'network';
	}
}
