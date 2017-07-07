<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Excellence\Login\Controller\Checkout\Index;

class Index extends \Magento\Checkout\Controller\Index\Index
{
    /**
     * Checkout page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if($this->scopeConfig->getValue('checkout/login/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            if(!($this->_customerSession->getCheckoutLoggedIn())) {
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('login');
                return $resultRedirect;
            }
            if(empty($this->getRequest()->getParam('username')) && !($this->_customerSession->isLoggedIn())){
                $resultRedirect = $this->resultRedirectFactory->create();
                // $this->messageManager->addNotice(__('Please select any of the following method.'));
                $resultRedirect->setPath('login');
                return $resultRedirect;
            } else {
                if($this->_customerSession->isLoggedIn()) {
                    $this->_coreRegistry->register('username', $this->_customerSession->getCustomer()->getEmail());
                } else {
                    $this->_coreRegistry->register('username', $this->getRequest()->getParam('username'));
                }
            }
        }
        /** @var \Magento\Checkout\Helper\Data $checkoutHelper */
        $checkoutHelper = $this->_objectManager->get('Magento\Checkout\Helper\Data');
        if (!$checkoutHelper->canOnepageCheckout()) {
            // $this->messageManager->addError(__('One-page checkout is turned off.'));
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError() || !$quote->validateMinimumAmount()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        if (!$this->_customerSession->isLoggedIn() && !$checkoutHelper->isAllowedGuestCheckout($quote)) {
            $this->messageManager->addError(__('Guest checkout is disabled.'));
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $this->_customerSession->regenerateId();
        $this->_objectManager->get('Magento\Checkout\Model\Session')->setCartWasUpdated(false);
        $this->getOnepage()->initCheckout();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Checkout'));
        return $resultPage;
    }
}
