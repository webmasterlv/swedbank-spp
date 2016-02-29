<?php

namespace Swedbank\SPP\Transport;

/**
 * Provides data exchanging with remote server.
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class Agent
{
	/**
	 * Network statuses
	 */
	const STATUS_WAITING	 = 1;
	const STATUS_SUCCESS	 = 2;
	const STATUS_ERROR	 = 3;

	/**
	 * Payload to send
	 *
	 * @var string
	 */
	protected $payload = '';

	/**
	 * List of URL where to send
	 * 
	 * @var array
	 */
	protected $endpoints = [];

	/**
	 * Received response
	 *
	 * @var string
	 */
	protected $response = '';

	/**
	 * Request result
	 *
	 * @var int
	 */
	protected $result = self::STATUS_WAITING;

	/**
	 * Error message returned by transport procol
	 *
	 * @var string
	 */
	protected $errorMessage = '';

	/**
	 * Error code returned by transport protocol
	 *
	 * @var int
	 */
	protected $errorCode = 1;

	/**
	 * Create new request
	 *
	 * @param string $payload Data to send
	 * @param array $endpoints URLs to send
	 * @throws \InvalidArgumentException
	 */
	public function __construct($payload, array $endpoints)
	{
		if (empty($endpoints)) {
			throw new \InvalidArgumentException('At least one endpoint must be provided');
		}

		$this -> payload	 = $payload;
		$this -> endpoints	 = $endpoints;
	}

	/**
	 * Execute network request
	 *
	 * @return string Data fetched
	 */
	public function dispatch()
	{
		$success = self::STATUS_ERROR;
		foreach ($this -> endpoints as $endpointUrl) {
			$result = $this -> callUrl($endpointUrl);

			if ($result === false) {
				continue;
			}

			$this -> response	 = $result;
			$success			 = self::STATUS_SUCCESS;
			break;
		}

		$this -> result = $success;
		return $this -> response;
	}

	/**
	 * Get status of request
	 *
	 * @return array
	 */
	public function getStatus()
	{
		return [
			'code' => $this -> result,
			'errcode' => $this -> errorCode,
			'message' => $this -> errorMessage
		];
	}

	/**
	 * Was request successful
	 *
	 * @return int
	 */
	public function isSuccess()
	{
		return $this -> result == self::STATUS_SUCCESS;
	}

	/**
	 * Returns response from request
	 *
	 * @return string
	 */
	public function getResponse()
	{
		return $this -> response;
	}

	/**
	 * Internal method that perfoms network request
	 *
	 * @param string $url Where to send
	 * @return string
	 */
	private function callUrl($url)
	{

		$ch		 = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this -> payload);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		$data	 = curl_exec($ch);
		$err	 = curl_errno($ch);

		if ($err > 0) {
			$this -> errorMessage = curl_error($ch);
		}

		$this -> errorCode = $err;

		curl_close($ch);

		return $err == 0 ? $data : false;
	}
}