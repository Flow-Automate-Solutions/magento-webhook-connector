<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Observer;

use Magic\WebhookConnector\Model\WebhookPublisher;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

class ProductOptionDeleteAfter implements ObserverInterface
{
    public function __construct(
        private readonly WebhookPublisher $webhookPublisher,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function execute(Observer $observer): void
    {
        $option = $observer->getEvent()->getOption()
            ?: $observer->getEvent()->getDataObject();
        if (!$option || !(int)$option->getId()) {
            return;
        }

        $storeId = (int)$option->getStoreId();
        $eventType = 'product_option.deleted';
        $this->webhookPublisher->publish(
            $eventType,
            [
                'event_type' => $eventType,
                'store_id' => $storeId,
                'store_url' => $this->resolveStoreUrl($storeId),
                'option_id' => (int)$option->getId(),
                'sku' => $this->resolveSku($option),
                'timestamp' => gmdate('c'),
            ],
            $storeId
        );
    }

    private function resolveSku(object $option): string
    {
        if (method_exists($option, 'getProductSku')) {
            $sku = trim((string)$option->getProductSku());
            if ($sku !== '') {
                return $sku;
            }
        }
        if (!method_exists($option, 'getProduct')) {
            return '';
        }
        try {
            $product = $option->getProduct();
        } catch (\Throwable) {
            return '';
        }
        if (!$product) {
            return '';
        }
        return trim((string)$product->getSku());
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
