<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Observer;

use Magic\WebhookConnector\Model\WebhookPublisher;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductDeleteAfter implements ObserverInterface
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
        $eventType = 'product.deleted';
        $payload = [
            'event_type' => $eventType,
            'store_id' => $storeId,
            'entity_id' => (int)$product->getId(),
            'sku' => (string)$product->getSku(),
            'timestamp' => gmdate('c'),
        ];

        $this->webhookPublisher->publish($eventType, $payload, $storeId);
    }
}
