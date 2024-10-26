<?php
/**
 * @package Enhance_SalesRule
 * @version 1.0.0
 * @category magento-module
 */
declare(strict_types=1);

namespace Enhance\SalesRule\Observer\Sales;
use Magento\Framework\DataObject;
/**
 * @package Enhance\SalesRule\Observer\Sales
 * QuoteAddressCollectTotalsAfter class
 */
class QuoteAddressCollectTotalsAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * logger variable
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * ruleFactory variable
     *
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * ruleFactory variable
     *
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $ruleFactory;

   /**
    * Constructor
    *
    * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
    * @param \Magento\Catalog\Model\ProductRepository $productRepository
    * @param \Psr\Log\LoggerInterface $logger
    */
    public function __construct(
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->ruleFactory = $ruleFactory;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

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
        $freeProducts = [];
        $exitSkus = explode(',',$quote->getData('free_products') ??'');
        try {
            $ids = $this->getRuleIds($quote);
            if (count($ids)) {
                $skus = $this->getProductSkus($ids);
                if (count($skus)){
                    $cartItems = $quote->getAllItems();
                    foreach ($skus as $sku) {
                        if (!$this->isProductAlreadyExist($cartItems, $sku)) {
                            $product = $this->getProductBySKU($sku);
                            $product->setPrice(0);
                            $pid = $quote->addProduct($product);
                        }
                        $freeProducts[] = $sku;
                    }
                    $quote->setData('free_products', implode(',',$freeProducts));
                } 
            }
            $removeSku = $this->getRemoveSkus($exitSkus, $freeProducts);
           $this->updateItemsFromCart($quote, $removeSku);
           $quote->collectTotals()->save();
        } catch (\Exception $exception) {
            $this->logger->error("Quote Error ".$exception->getMessage());
        }
    }

    /**
     * Get Remove Skus function
     *
     * @param array $skuList
     * @param array $newSkus
     * @return array
     */
    private function getRemoveSkus($skuList, $newSkus) {
        $removeSkus = [];
        foreach ($skuList as $sku) {
            if (!in_array($sku, $newSkus)) {
                $removeSkus[] = $sku;
            }
        }
        return $removeSkus;
    }

    /**
     * update Items From Cart function
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param array $removeSku
     * @return void
     */
    private function updateItemsFromCart($quote, $removeSku) {
        $items = $quote->getAllItems();
        $freeProducts = explode(',',$quote->getData('free_products') ??'');

        foreach ($items as $item) {
            if (in_array($item->getSku(), $freeProducts)) {
                $item->setCustomPrice(0);
                $item->setOriginalCustomPrice(0); 
                $item->getProduct()->setIsSuperMode(true);
            }
            if (in_array($item->getSku(), $removeSku)) {
                $item->delete();
            }
        }
    }

    /**
     * Check Is Product exist in cart function
     *
     * @param mixed $items
     * @param string $sku
     * @return boolean
     */
   private function isProductAlreadyExist($items, $sku) {
        foreach ($items as $item) {
            if($item->getSku() == $sku && $item->getQty()){
                return true;
            }
        }
        return false;
    }
    /**
     * Get Products skus function
     *
     * @param array $ids
     * @return array
     */
    private function getProductSkus($ids) {
        $skus = [];
        $rules = $this->ruleFactory->create()
        ->getCollection()
        ->addFieldToFilter('simple_action', 'add_free_product_to_cart')
        ->addFieldToFilter('rule_id', ['in' => $ids]);
        foreach ($rules as $rule) {
            $skus[] = $rule->getProductSku();
        }
        return $skus;
    }

    /**
     * Get Rule Ids function
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    private function getRuleIds($quote) {
        $ids = [];
        $items = $quote->getAllItems();
        foreach ($items as $item) {
            $ruleIds = $item->getAppliedRuleIds() ?? "";
            $eids = explode(',', $ruleIds);
            $ids = array_merge($ids, $eids);
        }
        return array_unique($ids);
    }

    /**
     * Get Product by SKU
     * @param mixed
     * @return \Magento\Catalog\Model\Product $product
     */
    public function getProductBySKU($sku)
    {
        try {
            $product = $this->productRepository->get($sku);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Such product doesn`t exist'));
        }
        return $product;
    }
}
