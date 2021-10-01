<?php

namespace Zehntech\BulkCatalogUpdate\Block\Adminhtml;

use Magento\Backend\Block\Template;

class BulkEditBlock extends Template
{

    protected $_stockRegistryInterface;
    protected $urlBuilder;

    /**
     * @param Context $context
     * @param array $data
     */

    public function __construct(
        Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockResistryInterface,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    )
    {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_stockRegistryInterface = $stockResistryInterface;
        $this->formKey = $formKey;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        parent::__construct($context, $data);

    }

    /**
     * @return string
     */
    public function greet()
    {
        return "Hello world";
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getQty($id)
    {
        return $this->_stockRegistryInterface->getStockItem($id)->getQty();
    }

    public function getMaxSalableQty($id)
    {
        return $this->_stockRegistryInterface->getStockItem($id)->getMaxSaleQty();
    }

    public function getProductCollection()
    {
        $page = $this->getRequest()->getParam('page');
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*')->setOrder('sku','Asc');
        //$collection->setPageSize(100);
        $collection->setPage($page, 100);

        return $collection;
    }

    public function getSize()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $size = $collection->getSize();
        $result = $this->getQuotientAndRemainder($size, 100);

        $totalPages = 0;

        if (!empty($result)) {

            $totalPages = $result[0];

            if (isset($result[1])) {
                $totalPages += 1;
            }
        }

        return $totalPages;
    }

    public function getQuotientAndRemainder($divisor, $dividend)
    {
        $quotient = (int)($divisor / $dividend);
        $remainder = $divisor % $dividend;
        return array($quotient, $remainder);
    }

    public function getCurrentPageId() {
        return $this->request->getParam('page');
    }

}
