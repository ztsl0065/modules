<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Zehntech\OrderMassAction\Model;

class MassStatus extends \Magento\Sales\Model\Service\OrderService
{
	public function changeStatus($id) {
		$order = $this->orderRepository->get($id);
		if($order->hasInvoices() && $order->hasShipments() && $order->getState() == 'complete') {
			$order->setStatus('complete');
			$this->orderRepository->save($order);
			return true;
		}
		return false;
	}
}