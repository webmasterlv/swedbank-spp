<?php

namespace Swedbank\SPP;

/**
 * General response interface.
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
interface Response
{

	/**
	 * Indicates whether response is successfull
	 */
	public function isSuccess();

	/**
	 * Returns message associated with response
	 */
	public function getMessage();

	/**
	 * Returns internal status code
	 */
	public function getStatus();

	/**
	 * Return gateway status code
	 */
	public function getRemoteStatus();

	/**
	 * Returns reference id, if possible
	 */
	public function getReference();

	/**
	 * Returns associated data
	 */
	public function getData();

	/**
	 * Error source
	 */
	public function getSource();
}
