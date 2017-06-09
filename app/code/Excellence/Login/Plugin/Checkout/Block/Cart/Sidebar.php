<?php

namespace Excellence\Login\Plugin\Checkout\Block\Cart;

class Sidebar extends \Magento\Checkout\Block\Cart\Sidebar
{
    /**
     * Get one page checkout page url
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl('login');
    }
}