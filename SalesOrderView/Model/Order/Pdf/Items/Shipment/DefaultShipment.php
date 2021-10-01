<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zehntech\SalesOrderView\Model\Order\Pdf\Items\Shipment;

use Magento\Sales\Model\Order\Pdf\Items\Shipment\DefaultShipment as DefaultShipmentOrg;

/**
 * Sales Order Shipment Pdf default items renderer
 */
class DefaultShipment extends DefaultShipmentOrg
{

    protected $string;
    public $_pricingHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $registry,
            $taxData,
            $filesystem,
            $filterManager,
            $string,
            $resource,
            $resourceCollection,
            $data
        );
        $this->string = $string;
        $this->_pricingHelper = $pricingHelper;
    }

    /**
     * Draw item line
     *
     * @return void
     */
    public function draw()
    {
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();

        $lines = [];

        // draw Product name
        $lines[0] = [
            [
                'text' => $this->string->split(html_entity_decode($item->getName()), 60, true, true),
                'feed' => 110
            ]
        ];

        // draw QTY
        $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 30];

        $lines[0][] = ['text' => $this->_pricingHelper->currency($item->getPrice() * 1, true, false), 'feed' => 330];

        // draw SKU
        $lines[0][] = [
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            'text' => $this->string->split(html_entity_decode($this->getSku($item)), 25),
            'feed' => 90,
            'align' => 'right',
        ];


        $lines[0][] = ['text' => ($item->getWeight() * 1). " lb", 'feed' => 390];

        $lines[0][] = ['text' => $this->_pricingHelper->currency($item->getPrice() * $item->getQty(), true, false), 'feed' => 500];

        $lines[0][] = ['text' => ($item->getWeight() * $item->getQty())." lb", 'feed' => 450];

        // Custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = [
                    'text' => $this->string->split($this->filterManager->stripTags($option['label']), 70, true, true),
                    'font' => 'italic',
                    'feed' => 110,
                ];

                // draw options value
                if ($option['value'] !== null) {
                    $printValue = isset(
                        $option['print_value']
                    ) ? $option['print_value'] : $this->filterManager->stripTags(
                        $option['value']
                    );
                    $values = explode(', ', $printValue);
                    foreach ($values as $value) {
                        $lines[][] = ['text' => $this->string->split($value, 50, true, true), 'feed' => 115];
                    }
                }
            }
        }

        $lineBlock = ['lines' => $lines, 'height' => 20];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);

        $this->setPage($page);
    }
}
