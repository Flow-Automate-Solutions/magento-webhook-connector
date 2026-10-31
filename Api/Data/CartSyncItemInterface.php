<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Api\Data;

interface CartSyncItemInterface
{
    /**
     * @return string
     */
    public function getSku(): string;

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku(string $sku): CartSyncItemInterface;

    /**
     * @return int
     */
    public function getQuantity(): int;

    /**
     * @param int $quantity
     * @return $this
     */
    public function setQuantity(int $quantity): CartSyncItemInterface;
}
