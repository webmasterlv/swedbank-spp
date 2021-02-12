<?php

namespace Swedbank\SPP\Response;

use Swedbank\SPP\Payment\Method;
use Swedbank\SPP\Payment\PayPal;
use Swedbank\SPP\Gateway;

/**
 * Response for 'Setup' operation
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class RecurringSetupResponse extends GenericResponse
{
	/**
	 * @inheritDoc
	 */
	public function isSuccess() {
		return in_array($this -> data['status'], [1, 150, 162]);
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
	 * Returns recurring reference
	 *
	 * @return  string	Recurring reference
	 */
	public function getRecurringReference() {
		if (!isset($this -> data['ContAuthTxn'])) {
			return null;
		}

		if (!isset($this -> data['ContAuthTxn']['ca_reference'])) {
			return null;
		}

		return $this -> data['ContAuthTxn']['ca_reference'];
	}

	public function getNextAction()
	{
		$code = $this -> getStatus();
		$result = [
			'action' => 'unknown'
		];

		switch ($code) {
			case 1: {
				$result['action'] = 'success';
				break;
			}
			case 150: {
				$result['action'] = 'post_redirect';
				$result['form'] = [
					'action' => $this -> data['CardTxn']['ThreeDSecure']['acs_url'],
					'method' => 'POST',
					'fields' => [
						'PaReq' => $this -> data['CardTxn']['ThreeDSecure']['pareq_message'],
					]
				];
				break;
			}
			case 162: {
				$result['action'] = '3ds_verify';
				break;
			}
		}

		return $result;
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
