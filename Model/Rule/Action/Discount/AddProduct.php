<?php
/**
 * @package Enhance_SalesRule
 * @version 1.0.0
 * @category magento-module
 */
declare(strict_types=1);

namespace Enhance\SalesRule\Model\Rule\Action\Discount;

use Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount;
/**
 * @package Enhance\SalesRule\Model\Rule\Action\Discount
 * AddProduct class
 */
class AddProduct extends AbstractDiscount
{
    /**
     * Calculate Add Free Product To Cart
     * 
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function calculate($rule, $item, $qty)
    {
        $quote = $item->getQuote();
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();
        return $discountData;
    }
}