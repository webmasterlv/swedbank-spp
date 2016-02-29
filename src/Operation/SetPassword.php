<?php

namespace Swedbank\SPP\Operation;

use Swedbank\SPP\Operation;
use Swedbank\SPP\Order;
use Swedbank\SPP\Response\SetPasswordResponse;

/**
 * Performs change password operation
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class SetPassword implements Operation
{
	/**
	 * New password
	 * 
	 * @var string
	 */
	protected $password;

	/**
	 * Merchant reference
	 * @var string
	 */
	protected $orderid;

	/**
	 * Password change operation
	 *
	 * @param string $password New password
	 */
	public function __construct($password)
	{
		$order = new Order('ServiceRequest', '', 0);

		$this -> password	 = $password;
		$this -> orderid	 = $order -> getReference();
	}

	/**
	 * @inheritDoc
	 */
	public function getCustomFields()
	{
		return [
			'password' => $this -> password,
			'reference' => $this -> orderid
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getDataSourceMembers()
	{
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplate()
	{
		return 'set_password';
	}

	/**
	 * @inheritDoc
	 * @return SetPasswordResponse
	 */
	public function parseResponse($response)
	{
		return new SetPasswordResponse($response);
	}
}