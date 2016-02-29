<?php

namespace Swedbank\SPP\Response;

use Swedbank\SPP\Response;

/**
 * Generic response template.
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
abstract class GenericResponse implements Response
{
	/**
	 * Payload data
	 * 
	 * @var array
	 */
	protected $data;

	/**
	 * Creates new response
	 *
	 * @param string $code Response code
	 * @param int $message Textual description of response
	 */
	public function __construct(array $data = [])
	{
		$this -> data = $data;
	}

	/**
	 * Returns payload data
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this -> data;
	}
}