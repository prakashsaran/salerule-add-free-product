<?php
/**
 * @package Enhance_SalesRule
 * @version 1.0.0
 * @category magento-module
 */
declare(strict_types=1);

namespace Enhance\SalesRule\Observer\Paradoxlabs;

class SubscriptionCollectTotalsBefore implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $quote = $observer->getData('quote');
        $subscriptions = $observer->getData('subscriptions');
        $subscription = current($subscriptions);
        if(!$subscription) return;
        $quote->setParentId($subscription->getQuoteId());
    }
}