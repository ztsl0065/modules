<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Zehntech\DiscountSplit\Model;

use Zehntech\DiscountSplit\Api\OrderDiscountManagementInterface;
use Zehntech\DiscountSplit\Model\Data\OrderDiscount;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartRepositoryInterface;
use Zehntech\DiscountSplit\Api\Data\OrderDiscountInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\ValidatorException;
use Magento\Store\Model\ScopeInterface;

/**
 * Class OrderDiscountManagement
 */
class OrderDiscountManagement implements OrderDiscountManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param int $cartId
     * @param OrderDiscountInterface $orderDiscount
     * @return null|string
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function saveSplitDiscount(
        $cartId,
        OrderDiscountInterface $orderDiscount
    ) {
         $quote = $this->quoteRepository->getActive($cartId);
         
        if (!$quote->getItemsCount()) {
              throw new NoSuchEntityException(
                  __('Cart %1 doesn\'t contain products', $cartId)
              );
        }
        
        $discount = $orderDiscount->getSplitDiscount();

        
        try {
             $quote->setData(OrderDiscount::SPLIT_DISCOUNT, strip_tags($discount));
            
             $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
               throw new CouldNotSaveException(
                   __('The order discount could not be saved')
               );
        }

         return $discount;
    }


}
