<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Observer;

use Magic\WebhookConnector\Model\WebhookPublisher;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

class CustomerDeleteAfter implements ObserverInterface
{
    public function __construct(
        private readonly WebhookPublisher $webhookPublisher,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function execute(Observer $observer): void
    {
        $customer = $observer->getEvent()->getCustomer()
            ?: $observer->getEvent()->getDataObject();
        if (!$customer || !(int)$customer->getId()) {
            return;
        }

        $storeId = (int)$customer->getStoreId();
        $eventType = 'customer.deleted';
        $this->webhookPublisher->publish(
            $eventType,
            [
                'event_type' => $eventType,
                'store_id' => $storeId,
                'store_url' => $this->resolveStoreUrl($storeId),
                'customer_id' => (int)$customer->getId(),
                'timestamp' => gmdate('c'),
            ],
            $storeId
        );
    }

    private function resolveStoreUrl(int $storeId): string
    {
        try {
            return rtrim((string)$this->storeManager->getStore($storeId)->getBaseUrl(), '/');
        } catch (\Throwable) {
            return '';
        }
    }
}
