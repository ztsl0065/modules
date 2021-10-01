<?php

namespace Zehntech\BulkCatalogUpdate\Controller\Adminhtml\Edit;

class BulkEdit extends \Magento\Backend\App\Action
{
    /**
     * Hello test controller page.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        //echo __('Hello Webkul Team.');
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * Check Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Zehntech_BulkCatalogUpdate::bulk_edit');
    }
}