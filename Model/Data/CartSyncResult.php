<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Model\Data;

use Magic\WebhookConnector\Api\Data\CartSyncResultInterface;
use Magento\Framework\DataObject;

class CartSyncResult extends DataObject implements CartSyncResultInterface
{
    /**
     * @return bool
     */
    public function getSuccess(): bool
    {
        return (bool)$this->getData('success');
    }

    /**
     * @param bool $success
     * @return $this
     */
    public function setSuccess(bool $success): CartSyncResultInterface
    {
        return $this->setData('success', $success);
    }

    /**
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return (string)$this->getData('redirect_url');
    }

    /**
     * @param string $redirectUrl
     * @return $this
     */
    public function setRedirectUrl(string $redirectUrl): CartSyncResultInterface
    {
        return $this->setData('redirect_url', $redirectUrl);
    }
}
