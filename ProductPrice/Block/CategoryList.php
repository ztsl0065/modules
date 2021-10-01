<?php

namespace Zehntech\ProductPrice\Block;

class CategoryList extends \Magento\Framework\View\Element\Template
{
	public function __construct(\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Helper\Category $categoryHelper,
        array $data = []
	) {
		$this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_categoryHelper = $categoryHelper;   
        parent::__construct($context, $data);
	}

	public function getCategoryCollection($isActive = true) {
		$collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*')->addAttributeTofilter('level','5')->addAttributeToSort('position');
  
        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }
        return $collection;
    }

}