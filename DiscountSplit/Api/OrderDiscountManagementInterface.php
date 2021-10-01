<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Zehntech\DiscountSplit\Api;

use Zehntech\DiscountSplit\Api\Data\OrderDiscountInterface;

/**
 * Interface for saving the checkout order discount
 * to the quote for logged in users
 * @api
 */
interface OrderDiscountManagementInterface
{
    /**
     * @param int $cartId
     * @param OrderDiscountInterface $orderDiscount
     * @return string
     */
    public function saveSplitDiscount(
        $cartId,
        OrderDiscountInterface $orderDiscount
    );
}
