<?php

namespace Swedbank\SPP;

/**
 * Describes objects that should return it's data for passing to templates
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
interface DataSource
{

	/**
	 * Returns data for template
	 */
	public function getFields();
}
