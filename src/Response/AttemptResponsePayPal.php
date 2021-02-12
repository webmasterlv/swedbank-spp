<?php

namespace Swedbank\SPP\Response;

/**
 * Response for 'Attempt' operation
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class AttemptResponsePayPal extends GenericResponse
{

	/**
	 * @inheritDoc
	 */
	public function getStatus()
	{
		return $this -> data['status'];
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
	public function getReference()
	{
		return '';
	}

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
