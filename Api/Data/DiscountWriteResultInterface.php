<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Api\Data;

interface DiscountWriteResultInterface
{
    /**
     * @return int
     */
    public function getRuleId(): int;

    /**
     * @param int $ruleId
     * @return $this
     */
    public function setRuleId(int $ruleId): self;

    /**
     * @return int|null
     */
    public function getCouponId(): ?int;

    /**
     * @param int|null $couponId
     * @return $this
     */
    public function setCouponId(?int $couponId): self;
}
