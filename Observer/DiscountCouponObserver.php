<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Observer;

use Magic\WebhookConnector\Model\DiscountWebhookPublisher;
use Magic\WebhookConnector\Model\DiscountWebhookSuppressor;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Rule;

class DiscountCouponObserver implements ObserverInterface
{
    private DiscountWebhookSuppressor $webhookSuppressor;

    public function __construct(
        private readonly DiscountWebhookPublisher $publisher,
        private readonly RuleRepositoryInterface $ruleRepository,
        ?DiscountWebhookSuppressor $webhookSuppressor = null
    ) {
        $this->webhookSuppressor = $webhookSuppressor
            ?? ObjectManager::getInstance()->get(DiscountWebhookSuppressor::class);
    }

    public function execute(Observer $observer): void
    {
        if ($this->webhookSuppressor->isSuppressed()) {
            return;
        }
        $coupon = $observer->getEvent()->getCoupon();
        if (!$coupon || !(int)$coupon->getId() || !(int)$coupon->getRuleId()) {
            return;
        }

        $ruleId = (int)$coupon->getRuleId();
        $couponId = (int)$coupon->getId();
        $websiteIds = $this->websiteIds($ruleId);
        if ((string)$observer->getEvent()->getName() === 'salesrule_coupon_delete_after') {
            $this->publisher->publish(
                $websiteIds,
                'discount.deleted',
                $ruleId,
                $couponId,
                'code'
            );
            return;
        }

        if (!$this->ruleUsesCoupon($ruleId)) {
            $this->publisher->publish(
                $websiteIds,
                'discount.deleted',
                $ruleId,
                $couponId,
                'code'
            );
            return;
        }

        $eventType = $coupon->getOrigData('coupon_id')
            ? 'discount.updated'
            : 'discount.created';
        $this->publisher->publish($websiteIds, $eventType, $ruleId, $couponId, 'code');
    }

    /**
     * @return int[]
     */
    private function websiteIds(int $ruleId): array
    {
        try {
            $rule = $this->ruleRepository->getById($ruleId);
        } catch (\Throwable) {
            return [];
        }
        $ids = $rule->getWebsiteIds() ?: [];
        if (!is_array($ids)) {
            return [];
        }
        return array_values(array_unique(array_map('intval', $ids)));
    }

    private function ruleUsesCoupon(int $ruleId): bool
    {
        try {
            $rule = $this->ruleRepository->getById($ruleId);
        } catch (\Throwable) {
            return false;
        }
        return (int)$rule->getCouponType() === Rule::COUPON_TYPE_SPECIFIC;
    }
}
