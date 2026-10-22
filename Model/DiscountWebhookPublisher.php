<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Model;

use Magento\Store\Model\StoreManagerInterface;

class DiscountWebhookPublisher
{
    public function __construct(
        private readonly WebhookPublisher $webhookPublisher,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @param int[] $websiteIds
     */
    public function publish(
        array $websiteIds,
        string $eventType,
        int $ruleId,
        ?int $couponId,
        string $method
    ): void {
        foreach ($this->stores($websiteIds) as $store) {
            $storeId = (int)$store->getId();
            $this->webhookPublisher->publish(
                $eventType,
                [
                    'event_type' => $eventType,
                    'store_id' => $storeId,
                    'store_url' => rtrim((string)$store->getBaseUrl(), '/'),
                    'rule_id' => $ruleId,
                    'coupon_id' => $couponId,
                    'method' => $method,
                    'timestamp' => gmdate('c'),
                ],
                $storeId > 0 ? $storeId : null
            );
        }
    }

    /**
     * @param int[] $websiteIds
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    private function stores(array $websiteIds): array
    {
        $stores = [];
        foreach ($websiteIds as $websiteId) {
            $websiteId = (int)$websiteId;
            if ($websiteId < 1) {
                continue;
            }
            try {
                $store = $this->storeManager->getWebsite($websiteId)->getDefaultStore();
            } catch (\Throwable) {
                continue;
            }
            if ($store && (int)$store->getId() > 0) {
                $stores[(int)$store->getId()] = $store;
            }
        }
        if ($stores) {
            return array_values($stores);
        }
        try {
            $store = $this->storeManager->getDefaultStoreView();
        } catch (\Throwable) {
            return [];
        }
        return $store ? [$store] : [];
    }
}
