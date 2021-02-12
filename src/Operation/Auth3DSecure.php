<?php

namespace Swedbank\SPP\Operation;

use Swedbank\SPP\Operation;
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
class Auth3DSecure implements Operation
{
	private $reference;

	/**
	 * Send 3-D Secure auth request
	 *
	 * @param string $reference Reference ID
	 */
	public function __construct($reference)
	{
		$this -> reference = $reference;
	}

	/**
	 * @inheritDoc
	 */
	public function getCustomFields(Gateway $gateway = null)
	{
		return [
			'reference' => $this -> reference
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getDataSourceMembers()
	{
		return [
			
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplate()
	{
		return '3dsecure_auth';
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
