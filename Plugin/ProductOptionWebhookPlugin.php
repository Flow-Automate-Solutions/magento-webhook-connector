<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Plugin;

use Magic\WebhookConnector\Model\WebhookPublisher;
use Magento\Catalog\Model\Product\Option;
use Magento\Store\Model\StoreManagerInterface;

class ProductOptionWebhookPlugin
{
    /** @var array<int, bool> */
    private array $existedBeforeSave = [];

    public function __construct(
        private readonly WebhookPublisher $webhookPublisher,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function beforeSave(Option $subject): void
    {
        $objectId = spl_object_id($subject);
        $this->existedBeforeSave[$objectId] = (int)$subject->getOrigData('option_id') > 0
            || (int)$subject->getId() > 0;
    }

    public function afterSave(Option $subject, Option $result): Option
    {
        $optionId = (int)$subject->getId();
        $objectId = spl_object_id($subject);
        $existed = $this->existedBeforeSave[$objectId]
            ?? (int)$subject->getOrigData('option_id') > 0;
        unset($this->existedBeforeSave[$objectId]);
        if ($optionId === 0) {
            return $result;
        }
        $sku = $this->resolveSku($subject);
        if ($sku === '') {
            return $result;
        }
        $eventType = $existed ? 'product_option.updated' : 'product_option.created';
        $this->publish($subject, $eventType, $optionId, $sku);
        return $result;
    }

    /**
     * @param callable(): mixed $proceed
     */
    public function aroundDelete(Option $subject, callable $proceed): mixed
    {
        $optionId = (int)$subject->getId();
        $sku = $this->resolveSku($subject);
        $storeId = $this->publishStoreId($subject);
        $result = $proceed();
        if ($optionId !== 0) {
            $this->publishPayload('product_option.deleted', $optionId, $sku, $storeId);
        }
        return $result;
    }

    private function publish(Option $option, string $eventType, int $optionId, string $sku): void
    {
        $this->publishPayload($eventType, $optionId, $sku, $this->publishStoreId($option));
    }

    private function publishPayload(
        string $eventType,
        int $optionId,
        string $sku,
        int $storeId
    ): void {
        $this->webhookPublisher->publish(
            $eventType,
            [
                'event_type' => $eventType,
                'store_id' => $storeId,
                'store_url' => $this->resolveStoreUrl($storeId),
                'option_id' => $optionId,
                'sku' => $sku,
                'timestamp' => gmdate('c'),
            ],
            $storeId > 0 ? $storeId : null
        );
    }

    private function publishStoreId(Option $option): int
    {
        $storeId = (int)$option->getStoreId();
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

    private function resolveSku(Option $option): string
    {
        $sku = trim((string)$option->getProductSku());
        if ($sku !== '') {
            return $sku;
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
