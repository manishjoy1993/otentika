<?php

namespace Excellence\Login\Block;

/**
 * Customer login form block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Summary extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterfaceFactory
     */
    protected $productRepositoryFactory;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $imageHelperFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Cart $cart,
        \Excellence\Login\Model\Url $customerUrl,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_isScopePrivate = false;
        $this->_customerUrl = $customerUrl;
        $this->_customerSession = $customerSession;
        $this->_cart = $cart;
        $this->storeManager = $storeManager;
        $this->appEmulation = $appEmulation;
        $this->productRepositoryFactory = $productRepositoryFactory;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->_priceHelper = $priceHelper;
    }

    public function getCartItems()
    {
        return $this->_cart->getQuote()->getAllVisibleItems();
    }

    public function getProductThumbnail($sku)
    {
        // get the store ID from somewhere (maybe a specific store?)
        $storeId = $this->storeManager->getStore()->getId();
        // emulate the frontend environment
        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
        // load the product however you want
        $product = $this->productRepositoryFactory->create()->get($sku);
        // now the image helper will get the correct URL with the frontend environment emulated
        $imageUrl = $this->imageHelperFactory->create()
          ->init($product, 'product_thumbnail_image')->getUrl();
        return $imageUrl;
    }
    public function getFormattedPrice($price)
    {
        return $this->_priceHelper->currency($price, true, false);
    }

    public function getSubtotal()
    {
        return $this->getFormattedPrice($this->_cart->getQuote()->getSubtotal());
    }

    public function getGrandTotal()
    {
        return $this->getFormattedPrice($this->_cart->getQuote()->getGrandTotal());
    }

    public function getDiscount()
    {
        return $this->getFormattedPrice(($this->_cart->getQuote()->getSubtotal() - $this->_cart->getQuote()->getGrandTotal()));
    }

    public function getCouponCode()
    {
        return $this->_cart->getQuote()->getCouponCode();
    }
}
