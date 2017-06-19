<?php

namespace Excellence\Login\Controller\Account;

use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Customer\Model\Session;

class Guest extends \Magento\Framework\App\Action\Action
{
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        Session $customerSession,
        RedirectFactory $resultRedirectFactory
    )
    {
        $this->session = $customerSession;
    	$this->resultRedirectFactory = $resultRedirectFactory;
        return parent::__construct($context);
    }
     
    public function execute()
    {
    	$resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPost('login');
        try {
            if($data) {
                $this->session->setCheckoutLoggedIn(true);
                $this->session->setCheckoutUsername($data['username']);
                $resultRedirect->setPath('checkout/cart');
            } else {
                $resultRedirect->setPath('login');
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('Unknown Error Occured. You may contact us for assistance.')
            );
            $resultRedirect->setPath('login');
        }
            
        return $resultRedirect;
    }
}