<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const WEBHOOK_ENABLED = true;
    private const WEBHOOK_URL = 'https://meera-dev-api.flowautomate.com/webhook/magento';
    private const XML_PATH_WEBHOOK_SECRET = 'magic_webhookconnector/webhook/webhook_secret';
    private const WEBHOOK_TIMEOUT_SECONDS = 5;
    private const WEBHOOK_RETRY_COUNT = 1;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly EncryptorInterface $encryptor
    ) {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return self::WEBHOOK_ENABLED;
    }

    public function getWebhookUrl(?int $storeId = null): string
    {
        return self::WEBHOOK_URL;
    }

    public function getWebhookSecret(?int $storeId = null): string
    {
        $value = (string)$this->scopeConfig->getValue(
            self::XML_PATH_WEBHOOK_SECRET,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($value === '') {
            return '';
        }

        return (string)$this->encryptor->decrypt($value);
    }

    public function getTimeoutSeconds(?int $storeId = null): int
    {
        $value = self::WEBHOOK_TIMEOUT_SECONDS;
        return max(1, $value);
    }

    public function getRetryCount(?int $storeId = null): int
    {
        $value = self::WEBHOOK_RETRY_COUNT;
        return max(0, $value);
    }

}
