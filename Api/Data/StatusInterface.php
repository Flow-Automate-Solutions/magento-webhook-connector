<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Api\Data;

interface StatusInterface
{
    /**
     * @return string
     */
    public function getModule(): string;

    /**
     * @param string $module
     * @return \Magic\WebhookConnector\Api\Data\StatusInterface
     */
    public function setModule(string $module): StatusInterface;

    /**
     * @return string
     */
    public function getVersion(): string;

    /**
     * @param string $version
     * @return \Magic\WebhookConnector\Api\Data\StatusInterface
     */
    public function setVersion(string $version): StatusInterface;

    /**
     * @return bool
     */
    public function getWebhookSecretConfigured(): bool;

    /**
     * @param bool $webhookSecretConfigured
     * @return \Magic\WebhookConnector\Api\Data\StatusInterface
     */
    public function setWebhookSecretConfigured(bool $webhookSecretConfigured): StatusInterface;
}
