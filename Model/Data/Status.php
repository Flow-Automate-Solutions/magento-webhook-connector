<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Model\Data;

use Magic\WebhookConnector\Api\Data\StatusInterface;
use Magento\Framework\DataObject;

class Status extends DataObject implements StatusInterface
{
    public function getModule(): string
    {
        return (string)$this->getData('module');
    }

    public function setModule(string $module): StatusInterface
    {
        $this->setData('module', $module);
        return $this;
    }

    public function getVersion(): string
    {
        return (string)$this->getData('version');
    }

    public function setVersion(string $version): StatusInterface
    {
        $this->setData('version', $version);
        return $this;
    }

    public function getWebhookSecretConfigured(): bool
    {
        return (bool)$this->getData('webhook_secret_configured');
    }

    public function setWebhookSecretConfigured(bool $webhookSecretConfigured): StatusInterface
    {
        $this->setData('webhook_secret_configured', $webhookSecretConfigured);
        return $this;
    }
}
