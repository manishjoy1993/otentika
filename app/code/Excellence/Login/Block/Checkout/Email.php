<?php

namespace Excellence\Login\Block\Checkout;

/**
 * Customer login form block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Email extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
    }

    public function getCustomerEmail()
    {
        return $this->_coreRegistry->registry('username');
    }
}
