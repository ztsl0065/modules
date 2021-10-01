<?php

namespace Zehntech\CatalogGrid\Controller\Adminhtml\Edit;

use Magento\Backend\App\Action;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;


class InlineEdit extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    private $product;
    protected $productRepository;
    protected $resultJsonFactory;
    protected $dataObjectHelper;
    protected $logger;

    public function __construct(
        Action\Context $context, ProductRepositoryInterface $productRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\Product\Action $action,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->productRepository = $productRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->logger = $logger;
        $this->action = $action;
        $this->storeManager = $storeManager;
        $this->stockRegistry = $stockRegistry;
        parent::__construct($context);
    }

    public function execute()
    {

        $resultJson = $this->resultJsonFactory->create();

        $postItems = $this->getRequest()->getParam('items', []);

        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        try {

            foreach ($postItems as $productId => $productRow) {

                $this->setProduct($this->productRepository->getById($productId));

                $sku = $this->product->getsku();

                $stockItem = $this->stockRegistry->getStockItemBySku($sku);

                if (is_numeric($productRow['qty']) && $sku) {

                    $qty = $productRow['qty'];
                    $stockItem = $this->stockRegistry->getStockItemBySku($sku);
                    $stockItem->setQty($qty);
                    $stockItem->setIsInStock((bool)$qty); // this line
                    $this->stockRegistry->updateStockItemBySku($sku, $stockItem);

                    unset($productRow['qty']);
                }

                if ($productRow['max_sale_qty'] && $sku) {

                    $maxSaleQty = $productRow['max_sale_qty'];
                    $stockItem = $this->stockRegistry->getStockItemBySku($sku);
                    $stockItem->setMaxSaleQty($maxSaleQty);
                    $this->stockRegistry->updateStockItemBySku($sku, $stockItem);

                    unset($productRow['max_sale_qty']);
                }

                $store = $this->storeManager->getStore();
                $storeId = $store->getStoreId();
                if($productRow['price_per_pound'] && $productRow['weight'])
                {
                    $productRow['price'] = $productRow['weight'] * $productRow['price_per_pound']; 
                }
                unset($productRow['entity_id']);

                $this->action->updateAttributes(
                    [
                        $productId
                    ],
                    $productRow,
                    $storeId
                );

            }

            return $resultJson->setData([
                'messages' => 'Product Updated.',
                'error' => 0
            ]);

        } catch (\Exception $e) {

            return $resultJson->setData([
                'messages' => $this->getErrorMessages(),
                'error' => $this->isErrorExists()
            ]);

        }


    }

    protected function getErrorMessages()
    {
        $messages = [];

        foreach ($this->getMessageManager()->getMessages()->getItems() as $error) {

            $messages[] = $error->getText();

        }

        return $messages;
    }

    protected function setProduct(ProductInterface $product)
    {
        $this->product = $product;
        return $this;
    }

    protected function getProduct()
    {
        return $this->product;
    }
}