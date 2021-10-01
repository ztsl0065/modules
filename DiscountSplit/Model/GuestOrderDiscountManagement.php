<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Zehntech\DiscountSplit\Model;

use Zehntech\DiscountSplit\Api\GuestOrderDiscountManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Zehntech\DiscountSplit\Api\OrderDiscountManagementInterface;
use Zehntech\DiscountSplit\Api\Data\OrderDiscountInterface;

/**
 * Class GuestOrderDiscountManagement
 */
class GuestOrderDiscountManagement implements GuestOrderDiscountManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var OrderDiscountManagementInterface
     */
    protected $orderDiscountManagement;
    
    /**
     * GuestOrderDiscountManagement constructor.
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param OrderDiscountManagementInterface $orderDiscountManagement
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        OrderDiscountManagementInterface $orderDiscountManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->orderDiscountManagement = $orderDiscountManagement;
    }

    /**
     * {@inheritDoc}
     */
    public function saveSplitDiscount(
        $cartId,
        OrderDiscountInterface $orderDiscount
    ) {

        $quoteIdMask = $this->quoteIdMaskFactory->create()
            ->load($cartId, 'masked_id');
                 
        return $this->orderDiscountManagement->saveSplitDiscount(
            $quoteIdMask->getQuoteId(),
            $orderDiscount
        );
    }
}
