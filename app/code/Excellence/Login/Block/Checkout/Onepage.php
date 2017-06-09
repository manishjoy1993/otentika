<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Excellence\Login\Block\Checkout;

/**
 * Onepage checkout block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Onepage extends \Magento\Checkout\Block\Onepage
{
    public function getCustomerEmail()
    {
        return $this->_coreRegistry->registery('username');
    }
}
