<?php
/**
 * Copyright © 2016 ToBai. All rights reserved.
 */
namespace Tobai\GeoStoreSwitcher\Model\GeoStore\Switcher\Rule;

use Tobai\GeoStoreSwitcher\Model;

class Country implements Model\GeoStore\Switcher\RuleInterface
{
    /**
     * @var \Tobai\GeoStoreSwitcher\Model\Config\General
     */
    protected $generalConfig;

    /**
     * @param \Tobai\GeoStoreSwitcher\Model\Config\General $generalConfig
     */
    public function __construct(
        Model\Config\General $generalConfig
    ) {
        $this->generalConfig = $generalConfig;
    }

    /**
     * @param string $countryCode
     * @return int|bool
     */
    public function getStoreId($countryCode)
    {
        return $this->isRuleForCountry($countryCode) ? $this->generalConfig->getCountryStore($countryCode) : false;
    }

    /**
     * @param string $countryCode
     * @return bool
     */
    protected function isRuleForCountry($countryCode)
    {
        return in_array($countryCode, $this->generalConfig->getCountryList());
    }
}
