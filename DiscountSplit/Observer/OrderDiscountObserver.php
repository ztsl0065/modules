<?php

namespace Zehntech\DiscountSplit\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\Validator;
use Zehntech\DiscountSplit\Model\Data\OrderDiscount;

class OrderDiscountObserver implements ObserverInterface {

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
	    $order = $observer->getEvent()->getOrder();
	    /** @var $order \Magento\Sales\Model\Order **/

	    $quote = $observer->getEvent()->getQuote();
	    /** @var $quote \Magento\Quote\Model\Quote **/

	    $order->setData(
	        OrderDiscount::SPLIT_DISCOUNT,
	        $quote->getData(OrderDiscount::SPLIT_DISCOUNT)
	    );
	}
	
}