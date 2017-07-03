<?php
/**
 * Copyright © 2016 ToBai. All rights reserved.
 */
namespace Tobai\GeoStoreSwitcher\Plugin\Store;

use Magento\Store\Model\Group;
use Magento\Store\Model\Store;

class GeoGroup
{
    /**
     * @var \Tobai\GeoStoreSwitcher\Model\Config\General
     */
    protected $generalConfig;

    /**
     * @var \Tobai\GeoStoreSwitcher\Model\GeoStore\Switcher
     */
    protected $storeSwitcher;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @param \Tobai\GeoStoreSwitcher\Model\Config\General $generalConfig
     * @param \Tobai\GeoStoreSwitcher\Model\GeoStore\Switcher $storeSwitcher
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        \Tobai\GeoStoreSwitcher\Model\Config\General $generalConfig,
        \Tobai\GeoStoreSwitcher\Model\GeoStore\Switcher $storeSwitcher,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository
    ) {
        $this->generalConfig = $generalConfig;
        $this->storeSwitcher = $storeSwitcher;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @param \Magento\Store\Model\Group $subject
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Store\Model\Store
     */
    public function afterGetDefaultStore(Group $subject, Store $store)
    {
        if ($this->generalConfig->isAvailable() && $this->storeSwitcher->isInitialized()) {
            $storeId = $this->storeSwitcher->getStoreId();
            if ($store->getId() != $storeId && in_array($storeId, $subject->getStoreIds())) {
                $store = $this->storeRepository->getById($storeId);
            }
        }

        return $store;
    }
}
