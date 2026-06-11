<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Observer;

use Magic\WebhookConnector\Model\WebhookPublisher;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

class ProductSaveAfter implements ObserverInterface
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
        $entityId = (int)$product->getId();
        $isCreate = !$product->getOrigData('entity_id');
        $eventType = $isCreate ? 'product.created' : 'product.updated';
        $storeUrl = $this->resolveStoreUrl($storeId);

        $payload = [
            'event_type' => $eventType,
            'store_id' => $storeId,
            'store_url' => $storeUrl,
            'entity_id' => $entityId,
            'sku' => (string)$product->getSku(),
            'name' => (string)$product->getName(),
            'status' => (int)$product->getStatus(),
            'visibility' => (int)$product->getVisibility(),
            'type_id' => (string)$product->getTypeId(),
            'updated_at' => (string)$product->getUpdatedAt(),
            'timestamp' => gmdate('c'),
        ];

        $this->webhookPublisher->publish($eventType, $payload, $storeId);
    }
}
