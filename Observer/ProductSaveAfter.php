<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Observer;

use Magic\WebhookConnector\Model\WebhookPublisher;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductSaveAfter implements ObserverInterface
{
    public function __construct(
        private readonly WebhookPublisher $webhookPublisher
    ) {
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

        $payload = [
            'event_type' => $eventType,
            'store_id' => $storeId,
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
