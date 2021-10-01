<?php

namespace Zehntech\BulkCatalogUpdate\Controller\Adminhtml\Edit;

use Magento\Backend\App\Action;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;

class BulkUpdate extends \Magento\Backend\App\Action
{
    private $product;
    protected $productRepository;
    protected $resultJsonFactory;
    protected $dataObjectHelper;
    protected $logger;
    protected $_stockRegistry;

    public function __construct(
        Action\Context $context, ProductRepositoryInterface $productRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\Product\Action $action,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->productRepository = $productRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->logger = $logger;
        $this->action = $action;
        $this->storeManager = $storeManager;
        $this->_stockRegistry = $stockRegistry;
        $this->_messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $test = $this->getRequest()->getParams();
        $forms = [];
        $error = true;
        $message = 'Default';

        $store = $this->storeManager->getStore();
        $storeId = $store->getStoreId();

        try {

            foreach ($test['name'] as $key => $value) {

                $form = [
                    'id' => $test['id'][$key],
                    'name' => $test['name'][$key],
                    'sku' => $test['sku'][$key],
                    'qty' => $test['qty'][$key],
                    'price' => $test['price'][$key],
                    'weight' => $test['weight'][$key],
                    'price_per_pound' => $test['price-per-pound'][$key],
                    'max_sale_qty' => $test['max_sale_qty'][$key],
                    'cost' => $test['cost'][$key]
                ];

                array_push($forms, $form);
            }

        } catch (\Exception $e) {

            $message = 'Please correct the data sent.';
            $error = true;
            $description = $e->getMessage();
        }

        if (empty($forms)) {

            $message = 'Please correct the data sent.';
            $description = 'Data is empty';
            $error = true;

        }

        if (!empty($forms)) {

            try {

                foreach ($forms as $key => $productRow) {

                    $productId = $productRow['id'];

                    $this->setProduct($this->productRepository->getById($productId));

                    $sku = $this->product->getsku();

                    if (is_numeric($productRow['qty']) && $sku) {

                        $qty = $productRow['qty'];
                        $stockItem = $this->_stockRegistry->getStockItemBySku($sku);
                        $stockItem->setQty($qty);
                        $stockItem->setIsInStock((bool)$qty);

                        if (isset($productRow['max_sale_qty'])) {

                            $maxSaleQty = $productRow['max_sale_qty'];
                            $stockItem->setMaxSaleQty($maxSaleQty);
                        }

                        $this->_stockRegistry->updateStockItemBySku($sku, $stockItem);
                    }


                    if ((isset($productRow['price_per_pound']) && !empty($productRow['price_per_pound']) && isset($productRow['weight']) && !empty($productRow['weight'])) || $productRow['price']) {

                        if (!empty($productRow['price_per_pound']) && !empty($productRow['weight'])) {

                            $productRow['price'] = $productRow['weight'] * $productRow['price_per_pound'];

                            $productData = [
                                'price_per_pound' => $productRow['price_per_pound'],
                                'price' => $productRow['price'],
                                'weight' => $productRow['weight']
                            ];

                        } else {

                            $productData = [
                                'price' => $productRow['price'],
                                'weight' => $productRow['weight']
                            ];

                        }
                        if($productRow['cost']){
                            $productData['cost'] = $productRow['cost'];
                        }
                        $this->action->updateAttributes(
                            [
                                $productId
                            ],
                            $productData,
                            $storeId
                        );
                    }
                }

                $message = 'Product Updated successfully.';
                $error = false;
                $description = 'Success';

            } catch (\Exception $e) {

                $message = 'Error during update.';
                $error = true;
                $description = $e->getMessage();
            }
        }

        if ($error):

            $this->_messageManager->addErrorMessage($message . $description);
        else:

            $this->_messageManager->addSuccessMessage($message);
        endif;

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;

    }

    protected function setProduct(ProductInterface $product)
    {
        $this->product = $product;
        return $this;
    }


}
