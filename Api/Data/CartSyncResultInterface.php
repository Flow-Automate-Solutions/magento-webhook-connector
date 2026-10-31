<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Api\Data;

interface CartSyncResultInterface
{
    /**
     * @return bool
     */
    public function getSuccess(): bool;

    /**
     * @param bool $success
     * @return $this
     */
    public function setSuccess(bool $success): CartSyncResultInterface;

    /**
     * @return string
     */
    public function getRedirectUrl(): string;

    /**
     * @param string $redirectUrl
     * @return $this
     */
    public function setRedirectUrl(string $redirectUrl): CartSyncResultInterface;
}
