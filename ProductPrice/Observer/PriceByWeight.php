<?php

 

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zehntech\ProductPrice\Observer;

use Magento\Framework\Event\ObserverInterface;

 

class PriceByWeight implements ObserverInterface
{

    /*
     * set price weight
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $_pricePerPound = $product->getPricePerPound();
        if($_pricePerPound > 0){
            $weight = $product->getWeight() ? $product->getWeight() : 1;
            $_pricePerPound = str_replace(',', '', $_pricePerPound);
            $price = $_pricePerPound * $weight;
            $product->setPrice($price);
        }
    }

}