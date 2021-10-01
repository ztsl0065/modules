<?php

namespace Zehntech\DiscountSplit\Model\Quote;

use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\SalesRule\Model\Validator;

class Discounts extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var \Zehntech\DiscountByZip\Model\ResourceModel\ZipTransitDays\Collection
     */
    protected $_zipTransitDaysFactory;

    /**
     * @var \Zehntech\DiscountByZip\Model\ResourceModel\WeightDiscount\Collection
     */
    protected $_weightDiscount;

    /**
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency [description]
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        Validator $validator,
        \Magento\SalesRule\Model\Utility $utility,
        \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $calculatorFactory
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->ruleFactory = $ruleFactory;
        $this->priceCurrency = $priceCurrency;
        $this->validator = $validator;
        $this->validatorUtility = $utility;
        $this->calculatorFactory = $calculatorFactory;
        $this->setCode('split_discount');
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return array
     */
    public function fetch(Quote $quote, Total $total)
    {

        $ruleArray = [];
        $ruleArray = $this->getDiscountsArray($quote);
        if(!count($ruleArray))
            return [];

        return [
            'code' => $this->getCode(),
            'title' => 'Fee Discount',
            'value' => $ruleArray
        ];
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Fee Discount');
    }



    /**
     * @param Total $total
     */
    protected function clearValues(Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }

    public function getDiscountsArray(Quote $quote) {
        $ruleList = explode(',', $quote->getAppliedRuleIds());
        $ruleArray = [];
        $items = $quote->getAllItems();
        $rules = $this->ruleFactory->create()->getCollection()->addFieldToFilter('rule_id',array('in'=>$ruleList))->setOrder('sort_order', 'ASC');;
        foreach ($items as $key => $item) {
            $discount = 0;
            
            $count = 0;
            foreach ($rules as $keyRules => $rule) {

                $validate = $rule->getActions()->validate($item);
                if(!$validate)
                    continue;

                $ruleData = [];
                $item->setDiscountAmount($discount);
                $item->setBaseDiscountAmount($discount);
                $qty = $rule->getDiscountQty() ? $rule->getDiscountQty() : $item->getQty();

                if($rule->getSimpleAction()=='cart_fixed')
                {
                    // get all items which are caluculated by the above sort order rules
                    $address = $quote->getShippingAddress();
                    $items = $this->getItems($quote, $rule->getRuleId());
                    $this->validator->initTotals($items, $address);
                     
                }

                try{
                    $data = $this->getDiscountData($item, $rule);
                }catch(\Exception $e){

                }
                    if(isset($data)){
                    $ruleData['id'] = $rule->getRuleId();
                    $ruleData['description'] = $rule->getDescription() ? $rule->getDescription() : 'Discount';
                    $ruleData['amount'] = $data->getAmount();
                    $discount += $data->getAmount();
                    $ruleArray[] = $ruleData;
                }
            }
        }

        if(!count($ruleArray))
            return [];
        $discountArray = [];
        foreach ($ruleList as $key => $ruleId) {
            $amount = 0;
            foreach ($ruleArray as $key => $ruleData) {
                if($ruleData['id'] == $ruleId){
                    $amount += $ruleData['amount'];
                    $discountData['description'] = $ruleData['description'];
                }
            }
            $discountData['amount'] = $this->priceCurrency->format($amount,false);
            $discountArray[] = $discountData;  
        }
        return $discountArray;
    }


    public function getDiscountData($item, $rule)
    {
        $qty = $this->validatorUtility->getItemQty($item, $rule);

        $discountCalculator = $this->calculatorFactory->create($rule->getSimpleAction());
        $qty = $discountCalculator->fixQuantity($qty, $rule);
        $discountData = $discountCalculator->calculate($rule, $item, $qty);

        $this->validatorUtility->deltaRoundingFix($discountData, $item);

        return $discountData;
    }

    public function getItems($quote, $ruleId){
        $ruleList = explode(',', $quote->getAppliedRuleIds());
        $ruleArray = [];
        $items = $quote->getAllItems();
        $discount = 0;
        $rules = $this->ruleFactory->create()->getCollection()->addFieldToFilter('rule_id',array('in'=>$ruleList))->setOrder('sort_order', 'ASC');
        foreach ($rules as $key => $rule) {
            $count = 0;
            // if($rule->getSimpleAction()=='cart_fixed')
            if($rule->getRuleId()==$ruleId)
                return $items;
            foreach ($items as $key => $item) {
                $validate = $rule->getActions()->validate($item);
                if(!$validate)
                    continue;
                if(!$count)
                    $item->setDiscountAmount($discount);
                try{

                $data = $this->getDiscountData($item, $rule);
                $custom = $item->getDiscountAmount() + $data->getAmount();
                $item->setDiscountAmount($custom);
                }catch(\Exception $e){

                }
            }
            $count++;
        }
    }

}

?>
