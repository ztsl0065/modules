<?php

namespace Zehntech\ProductPrice\Controller\Product;

class Update extends \Magento\Framework\App\Action\Action
{
    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
    }

    public function execute()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*')->setStoreId(1);
        // ->setPageSize(100);
        foreach ($collection as $key => $product) {
            $pricePerPound = $product->getPricePerPound() ? $product->getPricePerPound() : $product->getPrice();
            $product->setPricePerPound($pricePerPound);
            $product->save();
        }
        $result = $this->resultJsonFactory->create();
        return $result->setData(['message'=>'successs']);
    }
}