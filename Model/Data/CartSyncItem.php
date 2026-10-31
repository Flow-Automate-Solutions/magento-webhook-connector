<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Model\Data;

use Magic\WebhookConnector\Api\Data\CartSyncItemInterface;
use Magento\Framework\DataObject;

class CartSyncItem extends DataObject implements CartSyncItemInterface
{
    /**
     * @return string
     */
    public function getSku(): string
    {
        return trim((string)$this->getData('sku'));
    }

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku(string $sku): CartSyncItemInterface
    {
        return $this->setData('sku', $sku);
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return (int)$this->getData('quantity');
    }

    /**
     * @param int $quantity
     * @return $this
     */
    public function setQuantity(int $quantity): CartSyncItemInterface
    {
        return $this->setData('quantity', $quantity);
    }
}
