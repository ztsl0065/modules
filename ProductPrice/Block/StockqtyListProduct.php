<?php

namespace Zehntech\ProductPrice\Block;

class StockqtyListProduct extends \Magento\Framework\View\Element\Template
{

    private $product;
    protected $_stockRegistry;

    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->_stockRegistry = $stockRegistry;
    }

    public function getAvailableQuantity($product)
    {
        $this->product = $product;
        $sku = $product->getSku();
        $stockItem = $this->_stockRegistry->getStockItemBySku($sku);
        return $this->_stockRegistry->getStockItem($product->getId())->getQty();
        // return $stockItem->getQty();

    }
}