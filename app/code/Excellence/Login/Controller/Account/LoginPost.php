<?php

namespace Excellence\Login\Controller\Account;

class LoginPost extends \Magento\Customer\Controller\Account\LoginPost
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * Get scope config
     *
     * @return ScopeConfigInterface
     * @deprecated
     */
    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->scopeConfig;
        }
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            $isCheckoutRedirect = $this->getRequest()->getParam('redirectToCheckout'); 
            if($isCheckoutRedirect) {
                $resultRedirect = $this->resultRedirectFactory->create();
                if (!empty($login['username']) && !empty($login['password'])) {
                    try {
                        $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                        $this->session->setCustomerDataAsLoggedIn($customer);
                        $this->session->regenerateId();
                        if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                            $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                            $metadata->setPath('/');
                            $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                        }
                        $this->accountRedirect->clearRedirectCookie();
                        $resultRedirect = $this->resultRedirectFactory->create();
                        $this->session->setCheckoutLoggedIn(true);
                        // URL is checked to be internal in $this->_redirect->success()
                        $resultRedirect->setPath('checkout', array('username' => $this->session->getCustomer()->getEmail()));
                    } catch (EmailNotConfirmedException $e) {
                        $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                        $message = __(
                            'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                            $value
                        );
                        $this->messageManager->addError($message);
                        $this->session->setUsername($login['username']);
                        $resultRedirect->setPath('login');
                    } catch (UserLockedException $e) {
                        $message = __(
                            'The account is locked. Please wait and try again or contact %1.',
                            $this->getScopeConfig()->getValue('contact/email/recipient_email')
                        );
                        $this->messageManager->addError($message);
                        $this->session->setUsername($login['username']);
                        $resultRedirect->setPath('login');
                    } catch (AuthenticationException $e) {
                        $message = __('Invalid login or password.');
                        $this->messageManager->addError($message);
                        $this->session->setUsername($login['username']);
                        $resultRedirect->setPath('login');
                    } catch (LocalizedException $e) {
                        $message = $e->getMessage();
                        $this->messageManager->addError($message);
                        $this->session->setUsername($login['username']);
                        $resultRedirect->setPath('login');
                    } catch (\Exception $e) {
                        $this->messageManager->addError(
                            __('Invalid login or password. You may contact us for assistance.')
                        );
                        $resultRedirect->setPath('login');
                    }
                } else {
                    $this->messageManager->addError(__('A login and a password are required.'));
                    $resultRedirect->setPath('login');
                }
                return $resultRedirect;
            } else {
                if (!empty($login['username']) && !empty($login['password'])) {
                    try {
                        $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                        $this->session->setCustomerDataAsLoggedIn($customer);
                        $this->session->regenerateId();
                        if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                            $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                            $metadata->setPath('/');
                            $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                        }
                        $redirectUrl = $this->accountRedirect->getRedirectCookie();
                        if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                            $this->accountRedirect->clearRedirectCookie();
                            $resultRedirect = $this->resultRedirectFactory->create();
                            // URL is checked to be internal in $this->_redirect->success()
                            $resultRedirect->setUrl($this->_redirect->success($redirectUrl));
                            return $resultRedirect;
                        }
                    } catch (EmailNotConfirmedException $e) {
                        $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                        $message = __(
                            'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                            $value
                        );
                        $this->messageManager->addError($message);
                        $this->session->setUsername($login['username']);
                    } catch (UserLockedException $e) {
                        $message = __(
                            'The account is locked. Please wait and try again or contact %1.',
                            $this->getScopeConfig()->getValue('contact/email/recipient_email')
                        );
                        $this->messageManager->addError($message);
                        $this->session->setUsername($login['username']);
                    } catch (AuthenticationException $e) {
                        $message = __('Invalid login or password.');
                        $this->messageManager->addError($message);
                        $this->session->setUsername($login['username']);
                    } catch (LocalizedException $e) {
                        $message = $e->getMessage();
                        $this->messageManager->addError($message);
                        $this->session->setUsername($login['username']);
                    } catch (\Exception $e) {
                        // PA DSS violation: throwing or logging an exception here can disclose customer password
                        $this->messageManager->addError(
                            __('An unspecified error occurred. Please contact us for assistance.')
                        );
                    }
                } else {
                    $this->messageManager->addError(__('A login and a password are required.'));
                }
            }
        }

        return $this->accountRedirect->getRedirect();
    }
}