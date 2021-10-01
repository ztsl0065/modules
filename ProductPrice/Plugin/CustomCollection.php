<?php

namespace Zehntech\ProductPrice\Plugin;

use Rokanthemes\BestsellerProduct\Block\Bestseller;

class CustomCollection
{

	public function __construct(
	    \Magento\Store\Model\StoreManagerInterface $storeManager,
	    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
	    \Magento\Catalog\Model\CategoryFactory $categoryFactory,
	    array $data = []
	) {
		$this->productCollectionFactory = $productCollectionFactory;
		$this->categoryFactory = $categoryFactory;
		$this->storeManager = $storeManager;
	}


	public function afterGetProducts(BestSeller $bestSeller,$result) {
				$category = $this->categoryFactory->create();
				$category = $category->load(270);
		    	$storeId    = $this->storeManager->getStore()->getId();
				$collection = $this->productCollectionFactory->create();
		        $collection->addAttributeToSelect('*');
		        $collection->addCategoryFilter($category);
		        $collection->addMinimalPrice()
		            ->addFinalPrice()
		            ->addTaxPercents()
		            ->addUrlRewrite();
		            // ->setPageSize($bestSeller->getConfig('qty'))->setCurPage(1);
		        return $collection;
	}

}