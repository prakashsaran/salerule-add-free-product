<?php
/**
 * @package Enhance_SalesRule
 * @version 1.0.0
 * @category magento-module
 */
declare(strict_types=1);

namespace Enhance\SalesRule\Plugin\Rule\Metadata;

/**
 * ValueProvider class
 * @package Enhance\SalesRule\Plugin\Rule\Metadata
 */
class ValueProvider {
    /**
     * add new option to apply action
     *
     * @param \Magento\SalesRule\Model\Rule\Metadata\ValueProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetMetadataValues(
        \Magento\SalesRule\Model\Rule\Metadata\ValueProvider $subject,
        $result
    ) {
        // set new option to options
        $applyOptions = [
            'label' => __('Add Free Product To Cart'),
            'value' => 'add_free_product_to_cart',
        ];
        array_push($result['actions']['children']['simple_action']['arguments']['data']['config']['options'], $applyOptions);
        return $result;
    }
}