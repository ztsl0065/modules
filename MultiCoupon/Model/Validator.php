<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Zehntech\MultiCoupon\Model;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;


class Validator extends \Magento\SalesRule\Model\Validator
{
	protected function _getRules(Address $address = null)
	{
	    $addressId = $this->getAddressId($address);
	    $key = $this->getWebsiteId() . '_'
	        . $this->getCustomerGroupId() . '_'
	        . $this->getCouponCode() . '_'
	        . $addressId;
	    if (!isset($this->_rules[$key])) {
	        $ids = [];
	        $rule = [];
	        foreach (explode(",", $this->getCouponCode()) as $key => $coupon) {
	            $collection = $this->_collectionFactory->create()
	                            ->setValidationFilter(
	                                $this->getWebsiteId(),
	                                $this->getCustomerGroupId(),
	                                $coupon,
	                                null,
	                                $address
	                            );    
	            foreach ($collection as $key => $rule) {
	                $ids[] = $rule->getRuleId();
	            }
	        }
	        $ids = array_unique($ids);
	        $this->_rules[$key] = $this->_collectionFactory->create()->addFieldToFilter('rule_id',['in'=>$ids])->addFieldToFilter('is_active', 1)->load();
	        // custom code
	    }
	    return $this->_rules[$key];
	}
}