<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Plugin;

use Magic\WebhookConnector\Model\WebhookPublisher;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\Store\Model\StoreManagerInterface;

class SourceItemsWebhookPlugin
{
    public function __construct(
        private readonly WebhookPublisher $webhookPublisher,
        private readonly StoreManagerInterface $storeManager,
        private readonly string $eventType
    ) {
    }

    private function storeUrl(): string
    {
        try {
            return rtrim((string)$this->storeManager->getStore()->getBaseUrl(), '/');
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * @param array<int, mixed> $sourceItems
     */
    public function afterExecute(
        mixed $subject,
        mixed $result,
        array $sourceItems
    ): mixed {
        foreach ($sourceItems as $sourceItem) {
            if (!$sourceItem instanceof SourceItemInterface) {
                continue;
            }
            $sourceCode = (string)$sourceItem->getSourceCode();
            $sku = (string)$sourceItem->getSku();
            if ($sourceCode === '' || $sku === '') {
                continue;
            }
            $this->webhookPublisher->publish(
                $this->eventType,
                [
                    'event_type' => $this->eventType,
                    'store_url' => $this->storeUrl(),
                    'source_code' => $sourceCode,
                    'sku' => $sku,
                    'timestamp' => gmdate('c'),
                ]
            );
        }
        return $result;
    }
}
