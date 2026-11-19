<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Api;

use Magic\WebhookConnector\Api\Data\CartSyncResultInterface;

interface CartSyncManagementInterface
{
    /**
     * @param \Magic\WebhookConnector\Api\Data\CartSyncItemInterface[] $items
     * @param \Magic\WebhookConnector\Api\Data\AttributionTouchInterface|null $attributionTouch
     * @return \Magic\WebhookConnector\Api\Data\CartSyncResultInterface
     */
    public function sync(array $items, $attributionTouch = null): CartSyncResultInterface;
}
