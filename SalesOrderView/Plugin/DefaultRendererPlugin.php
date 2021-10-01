<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Zehntech\SalesOrderView\Plugin;

use \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer;

class DefaultRendererPlugin
{

    public function aroundGetColumnHtml(DefaultRenderer $defaultRenderer, \Closure $proceed, \Magento\Framework\DataObject $item, $column, $field = null)
    {
        if ($column == 'weight') {

            $result = number_format($item->getWeight(), 2);
            //$result = $this->displayPriceAttribute('item_comment');

        } else {

            if ($field) {

                $result = $proceed($item, $column, $field);
            } else {

                $result = $proceed($item, $column);

            }
        }

        return $result;
    }
}
