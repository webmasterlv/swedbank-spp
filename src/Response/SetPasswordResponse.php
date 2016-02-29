<?php

namespace Swedbank\SPP\Response;

use Swedbank\SPP\Gateway;

/**
 * Response for 'SetPassword' operation
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class SetPasswordResponse extends GenericResponse
{

	/**
	 * @inheritDoc
	 */
	public function isSuccess()
	{
		return $this -> data['status'] == 1;
	}

	/**
	 * @inheritDoc
	 */
	public function getReference()
	{
		return isset($this -> data['datacash_reference']) ?
			$this -> data['datacash_reference'] : '';
	}

	/**
	 * @inheritDoc
	 */
	public function getMessage()
	{
		return $this -> data['reason'];
	}

	/**
	 * @inheritDoc
	 */
	public function getStatus()
	{
		return $this -> data['status'] == 1 ? Gateway::STATUS_SUCCESS : Gateway::STATUS_ERROR;
	}

	/**
	 * @inheritDoc
	 */
	public function getNewPassword()
	{
		return $this -> data['new_password'];
	}

	/**
	 * @inheritDoc
	 */
	public function getRemoteStatus()
	{
		return $this -> data['status'];
	}

	/**
	 * @inheritDoc
	 */
	public function getSource()
	{
		return 'spp';
	}
}