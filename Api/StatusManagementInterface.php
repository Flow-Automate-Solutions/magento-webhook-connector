<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Api;

use Magic\WebhookConnector\Api\Data\StatusInterface;

interface StatusManagementInterface
{
    /**
     * @return \Magic\WebhookConnector\Api\Data\StatusInterface
     */
    public function getStatus(): StatusInterface;
}
