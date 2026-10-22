<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Api;

use Magic\WebhookConnector\Api\Data\DiscountWriteResultInterface;
use Magento\SalesRule\Api\Data\RuleInterface;

interface DiscountWriteManagementInterface
{
    /**
     * @param \Magento\SalesRule\Api\Data\RuleInterface $rule
     * @return \Magic\WebhookConnector\Api\Data\DiscountWriteResultInterface
     */
    public function create(RuleInterface $rule): DiscountWriteResultInterface;

    /**
     * @param int $ruleId
     * @param \Magento\SalesRule\Api\Data\RuleInterface $rule
     * @return \Magic\WebhookConnector\Api\Data\DiscountWriteResultInterface
     */
    public function update(int $ruleId, RuleInterface $rule): DiscountWriteResultInterface;

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
