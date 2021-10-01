<?php

namespace Zehntech\SalesOrderView\Block\Adminhtml\Sales;

class Weight extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Model\Currency $currency,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_currency = $currency;
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    }

    /**
     *
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getOrder();

        if (!$this->getOrder()->getWeight()) {
            return $this;
        }

        $weight = new \Magento\Framework\DataObject(
            [
                'code' => 'weight',
                'value' => number_format($this->getOrder()->getWeight(), 4),
                'label' => 'Total Weight',
            ]
        );
        $this->getParentBlock()->addTotalBefore($weight, 'grand_total');

        return $this;
    }
}
