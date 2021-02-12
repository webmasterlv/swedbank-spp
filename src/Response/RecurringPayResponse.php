<?php

namespace Swedbank\SPP\Response;

use Swedbank\SPP\Gateway;
use Swedbank\SPP\Operation\Query;

/**
 * Response for 'Query' operation
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class RecurringPayResponse extends GenericResponse
{
/**
	 * @inheritDoc
	 */
	public function isSuccess() {
		return in_array($this -> data['status'], [1]);
	}

	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		return $this -> data['reason'];
	}

	/**
	 * @inheritDoc
	 */
	public function getStatus() {
		return $this -> data['status'];
	}

	/**
	 * @inheritDoc
	 */
	public function getRemoteStatus() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getReference() {
		return isset($this -> data['datacash_reference']) ? $this -> data['datacash_reference'] : '';
	}

	/**
	 * @inheritDoc
	 */
	public function getData() {
		return $this -> data;
	}

	/**
	 * @inheritDoc
	 */
	public function getSource() {

	}
}
