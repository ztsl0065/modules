<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Zehntech\DiscountSplit\Api\Data;

/**
 * Interface OrderDiscountInterface
 * @api
 */
interface OrderDiscountInterface
{
    /**
     * @return string|null
     */
    public function getSplitDiscount();

    /**
     * @param string $discount
     * @return null
     */
    public function setSplitDiscount($discount);
}
