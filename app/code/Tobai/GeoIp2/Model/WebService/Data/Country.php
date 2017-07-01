<?php
/**
 * Copyright © 2015 ToBai. All rights reserved.
 */

namespace Tobai\GeoIp2\Model\WebService\Data;

use Tobai\GeoIp2\Model\CountryInterface;
use Tobai\GeoIp2\Model\WebService\ClientFactory;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Psr\Log\LoggerInterface as Logger;

class Country implements CountryInterface
{
    /**
     * @var \Tobai\GeoIp2\Model\WebService\ClientFactory
     */
    protected $clientFactory;

    /**
     * @var \GeoIp2\WebService\Client
     */
    protected $client;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\Request
     */
    protected $httpRequest;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param \Tobai\GeoIp2\Model\WebService\ClientFactory $clientFactory
     * @param \Magento\Framework\HTTP\PhpEnvironment\Request $httpRequest
     * @param Logger $logger
     */
    public function __construct(
        ClientFactory $clientFactory,
        Request $httpRequest,
        Logger $logger
    ) {
        $this->clientFactory = $clientFactory;
        $this->httpRequest = $httpRequest;
        $this->logger = $logger;
    }

    /**
     * @return \GeoIp2\Model\Country|bool
     */
    public function getCountry()
    {
        try {
            $ip = $this->httpRequest->getClientIp();
            $country = $this->getClient()->country($ip);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $country = false;
        }
        return $country;
    }

    /**
     * @return bool|string
     */
    public function getCountryCode()
    {
        $country = $this->getCountry();
        return $country ? $country->country->isoCode : false;
    }

    /**
     * @return \GeoIp2\WebService\Client
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getClient()
    {
        if (null === $this->client) {
            $this->client = $this->clientFactory->create();
        }
        return $this->client;
    }
}
