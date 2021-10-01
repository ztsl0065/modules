<?php

namespace Zehntech\ProductPrice\Block;

class MaxSaleQty extends \Magento\Framework\View\Element\Template
{

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockItemRepository,
        \Magento\Framework\Registry $registry,
        array $data = [])
    {
        $this->stockItemRegistry = $stockItemRepository;
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    public function getMaxSaleQuantity()
    {
        $stockManager = $this->stockItemRegistry->getStockItem($this->_registry->registry('current_product')->getId());
        return $maxQtyForProduct = $stockManager->getMaxSaleQty();
    }

    public function getMaxSalableQuantity($productId)
    {
        $stockManager = $this->stockItemRegistry->getStockItem($productId);
        return $maxQtyForProduct = $stockManager->getMaxSaleQty();
    }

}