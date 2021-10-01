<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Zehntech\DiscountSplit\Model\Data;

use Zehntech\DiscountSplit\Api\Data\OrderDiscountInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Class OrderDiscount
 */
class OrderDiscount extends AbstractSimpleObject implements OrderDiscountInterface
{
    const SPLIT_DISCOUNT = 'split_discount';
    
    /**
     * @return string|null
     */
    public function getSplitDiscount()
    {
        return $this->_get(static::SPLIT_DISCOUNT);
    }

    /**
     * @param string $discount
     * @return $this
     */
    public function setSplitDiscount($discount)
    {
        return $this->setData(static::SPLIT_DISCOUNT, $discount);
    }
}
