<?php

namespace Swedbank\SPP;

/**
 * A template interface for gateway operation
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
interface Operation
{

	/**
	 * Which payload template to use
	 */
	public function getTemplate();

	/**
	 * Return members that will pass data to payload template
	 */
	public function getDataSourceMembers();

	/**
	 * @param array $response Data received
	 * @return Response
	 */
	public function parseResponse($response);

	/**
	 * Custom fields to be passed for payload template
	 */
	public function getCustomFields();
}