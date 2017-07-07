<?php

namespace Excellence\Login\Controller\Index;

use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action
{
	/**
     * @var PageFactory
     */
    protected $resultPageFactory;
	
	/**
     * @param \Magento\Framework\App\Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $session,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $session;
        parent::__construct($context);
    }
	
    /**
     * Default Login Index page
     *
     * @return void
     */
    public function execute()
    {
        $customerSession = $this->session;
        if($customerSession->isLoggedIn()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $customerSession->setCheckoutLoggedIn(true);
            $email = $customerSession->getCustomer()->getEmail();
            $customerSession->setCheckoutUsername($email);
            $resultRedirect->setPath('checkout');
            return $resultRedirect;
        }
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
