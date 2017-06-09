<?php
namespace Excellence\Login\Block\Checkout\Cart;

class Sidebar extends \Magento\Checkout\Block\Cart\AbstractCart
{
    public function getCheckoutUrl()
    {
        return $this->getUrl('login');
    }
}