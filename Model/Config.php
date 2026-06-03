<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED = 'magic_webhookconnector/webhook/enabled';
    private const XML_PATH_WEBHOOK_URL = 'magic_webhookconnector/webhook/webhook_url';
    private const XML_PATH_WEBHOOK_SECRET = 'magic_webhookconnector/webhook/webhook_secret';
    private const XML_PATH_TIMEOUT = 'magic_webhookconnector/webhook/timeout_seconds';
    private const XML_PATH_RETRY_COUNT = 'magic_webhookconnector/webhook/retry_count';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly EncryptorInterface $encryptor
    ) {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getWebhookUrl(?int $storeId = null): string
    {
        return trim((string)$this->scopeConfig->getValue(
            self::XML_PATH_WEBHOOK_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));
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
        $value = (int)$this->scopeConfig->getValue(
            self::XML_PATH_TIMEOUT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return max(1, $value);
    }

    public function getRetryCount(?int $storeId = null): int
    {
        $value = (int)$this->scopeConfig->getValue(
            self::XML_PATH_RETRY_COUNT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return max(0, $value);
    }
}
