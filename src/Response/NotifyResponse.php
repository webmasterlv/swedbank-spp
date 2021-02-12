<?php

namespace Swedbank\SPP\Response;

use Swedbank\SPP\Gateway;
use Swedbank\SPP\Order;

class NotifyResponse extends GenericResponse
{
	private $order	 = false;
	private $message = '';

	public function __construct($data = array(), $message = '')
	{
		parent::__construct($data);

		if ($this -> isDataOk()) {
			$orderId		 = $data['Event']['Purchase']['@attributes']['TransactionId'];
			list ($cId, ) = explode('/', $orderId);
			$this -> order	 = new Order(ltrim($cId, '0'), '', $this -> getAmount() / 100);
		}

		$this -> message = $message;
	}

	public function isDataOk()
	{
		return isset($this -> data['Event']);
	}

	private function getAmount()
	{
		if (!$this -> isDataOk()) {
			return 0;
		}

		return (int) $this -> data['Event']['Purchase']['Amount'];
	}

	public function getMessage()
	{
		if ($this -> message) {
			return $this -> message;
		}

		if (!$this -> isDataOk()) {
			return '';
		}

		return $this -> data['Event']['Purchase']['RecommendedClientMessage'];
	}

	public function getOrder()
	{
		return $this -> order;
	}

	public function getReference()
	{
		if (!$this -> isDataOk()) {
			return '';
		}

		return $this -> data['Event']['Purchase']['@attributes']['DPGReferenceId'];
	}

	public function getRemoteStatus()
	{
		if (!$this -> isDataOk()) {
			return 'ERROR';
		}

		return $this -> data['Event']['Purchase']['Status'];
	}

	public function getSource()
	{
		return 'spp';
	}

	public function getStatus()
	{
		if (!$this -> isDataOk()) {
			return Gateway::STATUS_ERROR;
		}

		$code = $this -> getRemoteStatus();

		switch ($code) {
			case 'AUTHORISED': return Gateway::STATUS_SUCCESS;
			case 'REQUIRES_INVESTIGATION': return Gateway::STATUS_INVESTIGATE;
			case 'CANCELLED': return Gateway::STATUS_CANCELED;
			case 'REFUSED': return Gateway::STATUS_REFUSED;
			case 'PENDING': return Gateway::STATUS_PENDING;
			case 'ERROR': return Gateway::STATUS_ERROR;
			default: return Gateway::STATUS_ERROR;
		}
	}

	public function isSuccess()
	{
		return $this -> getStatus() === Gateway::STATUS_SUCCESS;
	}
}
