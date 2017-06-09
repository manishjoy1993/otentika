<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Excellence\Login\Plugin\Checkout\Controller\Index;

class Index extends \Magento\Checkout\Controller\Index\Index
{
     /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        die('test');
        $this->_request = $request;
        $result = $this->_preDispatchValidateCustomer();
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $result;
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->_objectManager->get('Magento\Checkout\Model\Session')->getQuote();
        if ($quote->isMultipleShippingAddresses()) {
            $quote->removeAllAddresses();
        }

        if (!$this->_canShowForUnregisteredUsers()) {
            throw new NotFoundException(__('Page not found.'));
        }
        return parent::dispatch($request);
    }
}
