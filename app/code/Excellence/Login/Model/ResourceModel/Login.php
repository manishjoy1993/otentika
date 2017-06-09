<?php

namespace Excellence\Login\Model\ResourceModel;

/**
 * Login Resource Model
 */
class Login extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('excellence_login', 'login_id');
    }
}
