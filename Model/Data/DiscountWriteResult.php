<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Model\Data;

use Magic\WebhookConnector\Api\Data\DiscountWriteResultInterface;
use Magento\Framework\DataObject;

class DiscountWriteResult extends DataObject implements DiscountWriteResultInterface
{
    public function getRuleId(): int
    {
        return (int)$this->getData('rule_id');
    }

    public function setRuleId(int $ruleId): DiscountWriteResultInterface
    {
        return $this->setData('rule_id', $ruleId);
    }

    public function getCouponId(): ?int
    {
        $couponId = $this->getData('coupon_id');
        if ($couponId === null || $couponId === '') {
            return null;
        }
        return (int)$couponId;
    }

    public function setCouponId(?int $couponId): DiscountWriteResultInterface
    {
        return $this->setData('coupon_id', $couponId);
    }
}
