<?php
namespace Zehntech\CatalogGrid\Ui\Component\Listing\Columns;

class MaxSaleQuantity extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'column.max_sale_qty';

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName])) {
                    $item[$fieldName] = (int)$item[$fieldName];
                }
            }
        }

        return $dataSource;
    }
}