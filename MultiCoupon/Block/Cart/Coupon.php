<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 */
namespace Zehntech\MultiCoupon\Block\Cart;

class Coupon extends \Magento\Checkout\Block\Cart\AbstractCart
{
    /**
     * @return array
     */
    public function getCouponCodes()
    {
        return array_filter(explode(",", $this->getQuote()->getCouponCode()));
    }
}
