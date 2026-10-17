<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Plugin;

use Magic\WebhookConnector\Model\WebhookPublisher;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class SourceRepositoryWebhookPlugin
{
    public function __construct(
        private readonly WebhookPublisher $webhookPublisher,
        private readonly StoreManagerInterface $storeManager
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

    private function publish(string $eventType, string $sourceCode): void
    {
        if ($sourceCode === '') {
            return;
        }
        $this->webhookPublisher->publish(
            $eventType,
            [
                'event_type' => $eventType,
                'store_url' => $this->storeUrl(),
                'source_code' => $sourceCode,
                'timestamp' => gmdate('c'),
            ]
        );
    }

    public function afterSave(
        SourceRepositoryInterface $subject,
        mixed $result,
        SourceInterface $source
    ): mixed {
        $this->publish('location.updated', (string)$source->getSourceCode());
        return $result;
    }

    public function afterDelete(
        SourceRepositoryInterface $subject,
        mixed $result,
        SourceInterface $source
    ): mixed {
        $this->publish('location.deleted', (string)$source->getSourceCode());
        return $result;
    }

    public function afterDeleteById(
        SourceRepositoryInterface $subject,
        mixed $result,
        string $sourceCode
    ): mixed {
        $this->publish('location.deleted', $sourceCode);
        return $result;
    }
}
