<?php

namespace Zehntech\MultiCoupon\Observer;

use Magento\Framework\Event\ObserverInterface;

class CouponObserver implements ObserverInterface
{
    public function __construct(
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->quoteRepository = $quoteRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $couponCodes = explode(",", $quote->getCouponCode());
        if($quote->getAppliedRuleIds() && count($couponCodes)) {
            $ruleIds = explode(",", $quote->getAppliedRuleIds());

            $rules = $this->_collectionFactory->create()->addFieldToFilter('rule_id',['in'=>$ruleIds]);
            foreach ($couponCodes as $key => $coupon) {
                $flag = false;
                foreach ($rules as $key => $rule) {
                    if (!$rule->getCode()) {
                            continue;
                    }               
                    if($coupon==$rule->getCode()){
                        $flag = true;
                    }                   
                }
                if(!$flag){
                    if (($index = array_search($coupon, $couponCodes)) !== false) {
                        unset($couponCodes[$index]);
                    }
                }
            }
            if(count($couponCodes)) {
                $couponCodes = implode(",", $couponCodes);
                $quote->setCouponCode($couponCodes);
            }
        }
    }

}
  

   