<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Observer;

use Magic\WebhookConnector\Model\WebhookPublisher;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

class OrderDeleteAfter implements ObserverInterface
{
    public function __construct(
        private readonly WebhookPublisher $webhookPublisher,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getOrder()
            ?: $observer->getEvent()->getDataObject();
        if (!$order || !(int)$order->getId()) {
            return;
        }

        $this->publish($order, 'order.deleted');
    }

    private function publish(object $order, string $eventType): void
    {
        $storeId = $this->publishStoreId($order);
        $this->webhookPublisher->publish(
            $eventType,
            [
                'event_type' => $eventType,
                'store_id' => $storeId,
                'store_url' => $this->resolveStoreUrl($storeId),
                'order_id' => (int)$order->getId(),
                'timestamp' => gmdate('c'),
            ],
            $storeId > 0 ? $storeId : null
        );
    }

    private function publishStoreId(object $order): int
    {
        $storeId = (int)$order->getStoreId();
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
