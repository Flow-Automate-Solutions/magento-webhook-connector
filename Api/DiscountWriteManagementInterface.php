<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Api;

use Magic\WebhookConnector\Api\Data\DiscountWriteResultInterface;
use Magic\WebhookConnector\Model\Data\DiscountRule;

interface DiscountWriteManagementInterface
{
    /**
     * @param \Magic\WebhookConnector\Model\Data\DiscountRule $rule
     * @return \Magic\WebhookConnector\Api\Data\DiscountWriteResultInterface
     */
    public function create(DiscountRule $rule): DiscountWriteResultInterface;

    /**
     * @param int $ruleId
     * @param \Magic\WebhookConnector\Model\Data\DiscountRule $rule
     * @return \Magic\WebhookConnector\Api\Data\DiscountWriteResultInterface
     */
    public function update(int $ruleId, DiscountRule $rule): DiscountWriteResultInterface;

    /**
     * @param int $ruleId
     * @return bool
     */
    public function delete(int $ruleId): bool;

    /**
     * @param int $couponId
     * @return bool
     */
    public function deleteCoupon(int $couponId): bool;
}
