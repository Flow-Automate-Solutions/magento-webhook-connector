<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Observer;

use Magic\WebhookConnector\Model\WebhookPublisher;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

class ProductDeleteAfter implements ObserverInterface
{
    public function __construct(
        private readonly WebhookPublisher $webhookPublisher,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    private function resolveStoreUrl(int $storeId): string
    {
        try {
            return rtrim((string)$this->storeManager->getStore($storeId)->getBaseUrl(), '/');
        } catch (\Throwable) {
            return '';
        }
    }

    public function execute(Observer $observer): void
    {
        $product = $observer->getEvent()->getProduct();
        if (!$product) {
            return;
        }

        $storeId = (int)$product->getStoreId();
        $storeUrl = $this->resolveStoreUrl($storeId);
        $eventType = 'product.deleted';
        $payload = [
            'event_type' => $eventType,
            'store_id' => $storeId,
            'store_url' => $storeUrl,
            'entity_id' => (int)$product->getId(),
            'sku' => (string)$product->getSku(),
            'timestamp' => gmdate('c'),
        ];

        $this->webhookPublisher->publish($eventType, $payload, $storeId);
    }
}
