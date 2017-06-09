<?php

/**
 * Login Resource Collection
 */
namespace Excellence\Login\Model\ResourceModel\Login;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Excellence\Login\Model\Login', 'Excellence\Login\Model\ResourceModel\Login');
    }
}
