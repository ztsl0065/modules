<?php

namespace Zehntech\DiscountSplit\Block\Order;

class Totals extends \Magento\Framework\View\Element\AbstractBlock
{
   public function initTotals()
   {
        $parent = $this->getParentBlock();
        $order = $parent->getOrder();
        $this->source = $parent->getSource();

        if($order->getSplitDiscount()){

          $discountData = "[" . $order->getSplitDiscount() . "]";
          $discountData = json_decode($discountData, true);
          $discountArray = [];
          foreach ($discountData as $key => $discount) {
            $keyValue = 'discount_rule_'.$key; 
            if(is_numeric(strpos($discount['amount'], "$")))
            {
              $discount['amount'] = (float)str_replace("$", "-", $discount['amount']);
            }
            $discountRuleData = new \Magento\Framework\DataObject(
                       [
                           'code'  => $keyValue,
                           'value' => $discount['amount'],
                           'label' => __($discount['description']),
                       ]
                   ); 
            $parent->addTotal($discountRuleData, 'discount');
          }
          $parent->removeTotal('discount');
        }
        return $this;

   }
}