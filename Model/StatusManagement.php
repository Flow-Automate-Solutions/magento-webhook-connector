<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Model;

use Magic\WebhookConnector\Api\Data\StatusInterface;
use Magic\WebhookConnector\Api\Data\StatusInterfaceFactory;
use Magic\WebhookConnector\Api\StatusManagementInterface;

class StatusManagement implements StatusManagementInterface
{
    public const MODULE_NAME = 'Magic_WebhookConnector';
    public const MODULE_VERSION = '1.1.0';

    public function __construct(
        private readonly Config $config,
        private readonly StatusInterfaceFactory $statusFactory
    ) {
    }

    public function getStatus(): StatusInterface
    {
        return $this->statusFactory->create()
            ->setModule(self::MODULE_NAME)
            ->setVersion(self::MODULE_VERSION)
            ->setWebhookSecretConfigured($this->config->getWebhookSecret() !== '');
    }
}
