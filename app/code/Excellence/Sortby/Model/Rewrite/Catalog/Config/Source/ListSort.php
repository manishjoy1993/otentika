<?php

namespace Excellence\Sortby\Model\Rewrite\Catalog\Config\Source;

class ListSort extends \Magento\Catalog\Model\Config\Source\ListSort {
	public function toOptionArray()
    {
        $options = [];
        $options[] = ['label' => __('Position'), 'value' => 'position'];

        foreach ($this->_getCatalogConfig()->getAttributesUsedForSortBy() as $attribute) {
            $options[] = ['label' => __($attribute['frontend_label']), 'value' => $attribute['attribute_code']];
        }
        $options[] = ['label' => __('Popularity'), 'value' => 'popularity'];
        $options[] = ['label' => __('Rating'), 'value' => 'rating_summary'];
				$options[] = ['label' => __('Price (Low to High)'), 'value' => 'price_asc'];
        $options[] = ['label' => __('Price (High to Low)'), 'value' => 'price_desc'];

        return $options;
    }
}
