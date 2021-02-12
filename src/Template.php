<?php

namespace Swedbank\SPP;

use Swedbank\SPP\Exception\GatewayException;

/**
 * Prepares payload for gateway and parses responses from it.
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class Template
{
	/**
	 * Template name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Payload data
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * Creates new data parser
	 *
	 * @param string $name Template to use
	 * @param array $data Payload data passed to template
	 */
	public function __construct($name, array $data = [])
	{
		$this -> name	 = $name;
		$this -> data	 = $data;
	}

	/**
	 * Gets template file data
	 *
	 * @return string
	 */
	private function getFile()
	{
		$dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'templates/';
		return file_get_contents($dir.$this -> name.'.xml');
	}

	/**
	 * Create payload string from template and passed data
	 *
	 * @return string
	 * @throws GatewayException When not enough data to generate payload
	 */
	public function parse()
	{
		$template = $this -> getFile();

		$matches = [];
		preg_match_all('/%(.*?)%/mi', $template, $matches);

		$replaced	 = 0;
		$matched	 = count($matches[1]);
		$missing	 = [];

		if (!empty($matches[1])) {
			foreach ($matches[1] as $i => $key) {
				$search = $matches[0][$i];
				if (isset($this -> data[$key])) {
					$replaced++;
					$template = str_replace(
						$search, htmlspecialchars($this -> data[$key], ENT_XML1, 'UTF-8'), $template);
				} else {
					$missing[] = $key;
				}
			}

			if ($matched > $replaced) {
				throw new GatewayException('Not enough data to generate template '.$this -> name.'. Missing: '.implode(', ',
					$missing));
			}
		}

		return $template;
	}

	/**
	 * Create array from response
	 *
	 * @param string $str Response
	 * @return array Decoded data
	 */
	public function decode($str)
	{
		return json_decode(json_encode(simplexml_load_string($str, null, LIBXML_NOCDATA)), true);
	}
}
