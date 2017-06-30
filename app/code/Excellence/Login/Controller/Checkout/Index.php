<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Excellence\Login\Controller\Checkout;

class Index extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context)
    {
        return parent::__construct($context);
    }
     
    public function execute()
    {
        $this->_redirect('login/index/index');
    } 
}