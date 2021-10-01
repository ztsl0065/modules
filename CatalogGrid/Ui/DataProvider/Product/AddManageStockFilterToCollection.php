<?php

namespace Zehntech\CatalogGrid\Ui\DataProvider\Product;

class AddManageStockFilterToCollection implements \Magento\Ui\DataProvider\AddFilterToCollectionInterface
{
    public function addFilter(\Magento\Framework\Data\Collection $collection, $field, $condition = null)
    {
        if (isset($condition['eq'])) {
            
            $collection->addFieldToFilter($field, $condition);
        }
    }
}