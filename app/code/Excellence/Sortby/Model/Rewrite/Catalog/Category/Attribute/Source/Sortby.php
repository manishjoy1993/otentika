<?php

namespace Excellence\Sortby\Model\Rewrite\Catalog\Category\Attribute\Source;

class Sortby extends \Magento\Catalog\Model\Category\Attribute\Source\Sortby {
	/**
     * {@inheritdoc}
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [['label' => __('Position'), 'value' => 'position']];
            foreach ($this->_getCatalogConfig()->getAttributesUsedForSortBy() as $attribute) {
                $this->_options[] = [
                    'label' => __($attribute['frontend_label']),
                    'value' => $attribute['attribute_code'],
                ];
            }
          $this->_options[] = ['label' => __('Popularity'), 'value' => 'popularity'];
        	$this->_options[] = ['label' => __('Rating'), 'value' => 'rating_summary'];

					$this->_options[] = ['label' => __('Price (Low to High)'), 'value' => 'price_asc'];
	        $this->_options[] = ['label' => __('Price (High to Low)'), 'value' => 'price_desc'];
        }
        return $this->_options;
    }
}
