<?php

namespace Zehntech\OrderGrid\Ui\Component\Listing\Column;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;

class Status extends Column
{
    protected $_orderRepository;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        array $components = [],
        array $data = [])
    {
        $this->_orderRepository = $orderRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);

    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as & $item) {

                $order = $this->_orderRepository->get($item["entity_id"]);

                $item[$this->getData('name')] = number_format($order->getWeight(), 2);
            }
        }

        return $dataSource;
    }
}

?>
