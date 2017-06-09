<?php

namespace Excellence\Login\Model;

/**
 * Login Model
 *
 * @method \Excellence\Login\Model\Resource\Page _getResource()
 * @method \Excellence\Login\Model\Resource\Page getResource()
 */
class Login extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Excellence\Login\Model\ResourceModel\Login');
    }

}
