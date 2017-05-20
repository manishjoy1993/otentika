<?php

namespace Excellence\Sortby\Block\Rewrite\Catalog\Product\ProductList;

class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar {

	/**
     * Set collection to pager
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        if ($this->getCurrentOrder()) {
        	if($this->getCurrentOrder() == 'popularity') {
        		$this->_collection->getSelect()
                     ->joinLeft(
                            array('sfoi' => $collection->getResource()->getTable('sales_order_item')),
                             'e.entity_id = sfoi.product_id',
                             array('qty_ordered' => 'SUM(sfoi.qty_ordered)')
                         )
                     ->group('e.entity_id')
                     ->order('qty_ordered ' . $this->getCurrentDirection());
			} else {
				$this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
			}
        }
        return $this;
    }

    /**
     * Retrieve current direction
     *
     * @return string
     */
    public function getCurrentDirection()
    {
        if($this->getCurrentOrder() == 'price' && $this->getRequest()->getParam('product_list_dir') == null) {
            return 'desc';
        }
        
        $dir = $this->_getData('_current_grid_direction');
        if ($dir) {
            return $dir;
        }

        $directions = ['asc', 'desc'];
        $dir = strtolower($this->_toolbarModel->getDirection());
        if (!$dir || !in_array($dir, $directions)) {
            $dir = $this->_direction;
        }

        if ($dir != $this->_direction) {
            $this->_memorizeParam('sort_direction', $dir);
        }

        $this->setData('_current_grid_direction', $dir);
        return $dir;
    }
}