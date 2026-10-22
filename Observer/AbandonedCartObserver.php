<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Observer;

use Magic\WebhookConnector\Model\WebhookPublisher;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

class AbandonedCartObserver implements ObserverInterface
{
    public function __construct(
        private readonly WebhookPublisher $webhookPublisher,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function execute(Observer $observer): void
    {
        $quote = $observer->getEvent()->getQuote()
            ?: $observer->getEvent()->getDataObject();
        if (!$quote || !(int)$quote->getId()) {
            return;
        }

        $eventName = (string)$observer->getEvent()->getName();
        if ($eventName === 'sales_quote_delete_after') {
            $this->publish($quote, 'abandoned_cart.deleted');
            return;
        }

        $eventType = $quote->getOrigData('entity_id')
            ? 'abandoned_cart.updated'
            : 'abandoned_cart.created';
        $this->publish($quote, $eventType);
    }

    private function publish(object $quote, string $eventType): void
    {
        $storeId = $this->publishStoreId($quote);
        $this->webhookPublisher->publish(
            $eventType,
            [
                'event_type' => $eventType,
                'store_id' => $storeId,
                'store_url' => $this->resolveStoreUrl($storeId),
                'quote_id' => (int)$quote->getId(),
                'timestamp' => gmdate('c'),
            ],
            $storeId > 0 ? $storeId : null
        );
    }

    private function publishStoreId(object $quote): int
    {
        $storeId = (int)$quote->getStoreId();
        if ($storeId > 0) {
            return $storeId;
        }
        try {
            $store = $this->storeManager->getDefaultStoreView();
        } catch (\Throwable) {
            return 0;
        }
        return $store ? (int)$store->getId() : 0;
    }

    private function resolveStoreUrl(int $storeId): string
    {
        try {
            $store = $storeId > 0
                ? $this->storeManager->getStore($storeId)
                : $this->storeManager->getDefaultStoreView();
            if (!$store) {
                return '';
            }
            return rtrim((string)$store->getBaseUrl(), '/');
        } catch (\Throwable) {
            return '';
        }
    }
}
