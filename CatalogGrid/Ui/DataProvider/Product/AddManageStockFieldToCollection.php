<?php

namespace Zehntech\CatalogGrid\Ui\DataProvider\Product;

class AddManageStockFieldToCollection implements \Magento\Ui\DataProvider\AddFieldToCollectionInterface
{
    public function addField(\Magento\Framework\Data\Collection $collection, $field, $alias = null)
    {
        /*
         * $alias, $table, $field, $bind, $cond = null, $joinType = 'inner'
         * Examples:
         * ('country_name', 'directory_country_name', 'name', 'country_id=shipping_country',"{{table}}.language_code='en'", 'left')
         * @param string $alias 'country_name'
         * @param string $table 'directory_country_name'
         * @param string $field 'name'
         * @param string $bind 'PK(country_id)=FK(shipping_country_id)'
         * @param string|array $cond "{{table}}.language_code='en'" OR array('language_code'=>'en')
         * @param string $joinType 'left'
         * @return $this
         * @throws LocalizedException
         */

        $collection->joinField('max_sale_qty', 'cataloginventory_stock_item', 'max_sale_qty', 'product_id=entity_id', null, 'left');
    }
}