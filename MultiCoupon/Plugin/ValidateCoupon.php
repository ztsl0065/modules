<?php

namespace Zehntech\MultiCoupon\Plugin;

use Magento\Quote\Model\CouponManagement;
use Magento\Framework\Exception\NoSuchEntityException;

class ValidateCoupon
{
	public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory,
		\Magento\Quote\Api\CartRepositoryInterface $quoteRepository
	) {
		$this->storeManager = $storeManager;
        $this->_collectionFactory = $collectionFactory;
		$this->quoteRepository = $quoteRepository;
	}

	public function afterSet(CouponManagement $cousponService, $result, $cartId, $couponCode){
		$quote = $this->quoteRepository->get($cartId);

		if($quote->getAppliedRuleIds()) {
			$ruleIds = explode(",", $quote->getAppliedRuleIds());
			$coupons = explode(",", $couponCode);
			$invalidCoupon = [];
			$flagArray = [];
			$rules = $this->_collectionFactory->create()->addFieldToFilter('rule_id',['in'=>$ruleIds]);
			foreach ($coupons as $key => $coupon) {
				$flag = false;
				foreach ($rules as $key => $rule) {
					if (!$rule->getCode()) {
					        continue;
				    }			    
			    	if($coupon==$rule->getCode()){
			    		$flag = true;
			    	}				    
				}
				$flagArray[] = $flag;
			    if(!$flag){
			    	if (($index = array_search($coupon, $coupons)) !== false) {
			    	    unset($coupons[$index]);
			    	}
			    }
			}
			if(count(explode(",", $couponCode)) > count($coupons)) {
				$couponCodes = implode(",", $coupons);
				$quote->setCouponCode($couponCodes);
				$this->quoteRepository->save($quote->collectTotals());
				throw new NoSuchEntityException(__("The coupon code isn't valid. Verify the code and try again. overrride"));
			}
		}
		return $result;
	}
}