<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Observer;

use Magic\WebhookConnector\Model\DiscountWebhookPublisher;
use Magic\WebhookConnector\Model\DiscountWebhookSuppressor;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory;
use Magento\SalesRule\Model\Rule;

class DiscountRuleObserver implements ObserverInterface
{
    private DiscountWebhookSuppressor $webhookSuppressor;

    public function __construct(
        private readonly DiscountWebhookPublisher $publisher,
        private readonly CollectionFactory $couponCollectionFactory,
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
        $rule = $observer->getEvent()->getRule();
        if (!$rule || !(int)$rule->getId()) {
            return;
        }

        $ruleId = (int)$rule->getId();
        $websiteIds = $this->websiteIds($rule);
        $couponIds = $this->couponIds($ruleId);
        if ((string)$observer->getEvent()->getName() === 'salesrule_rule_delete_before') {
            foreach ($couponIds as $couponId) {
                $this->publisher->publish(
                    $websiteIds,
                    'discount.deleted',
                    $ruleId,
                    $couponId,
                    'code'
                );
            }
            $this->publisher->publish(
                $websiteIds,
                'discount.deleted',
                $ruleId,
                null,
                'automatic'
            );
            return;
        }

        $eventType = $rule->getOrigData('rule_id') ? 'discount.updated' : 'discount.created';
        if ((int)$rule->getCouponType() === Rule::COUPON_TYPE_NO_COUPON) {
            foreach ($couponIds as $couponId) {
                $this->publisher->publish(
                    $websiteIds,
                    'discount.deleted',
                    $ruleId,
                    $couponId,
                    'code'
                );
            }
            $this->publisher->publish($websiteIds, $eventType, $ruleId, null, 'automatic');
            return;
        }

        $this->publisher->publish($websiteIds, 'discount.deleted', $ruleId, null, 'automatic');
        foreach ($couponIds as $couponId) {
            $this->publisher->publish($websiteIds, $eventType, $ruleId, $couponId, 'code');
        }
    }

    /**
     * @return int[]
     */
    private function websiteIds(object $rule): array
    {
        $ids = $rule->getWebsiteIds();
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        if (!is_array($ids)) {
            return [];
        }
        $websiteIds = [];
        foreach ($ids as $websiteId) {
            $websiteId = (int)$websiteId;
            if ($websiteId > 0 && !in_array($websiteId, $websiteIds, true)) {
                $websiteIds[] = $websiteId;
            }
        }
        return $websiteIds;
    }

    /**
     * @return int[]
     */
    private function couponIds(int $ruleId): array
    {
        $collection = $this->couponCollectionFactory->create();
        $collection->addFieldToFilter('rule_id', $ruleId);
        $couponIds = [];
        foreach ($collection as $coupon) {
            $couponId = (int)$coupon->getId();
            if ($couponId > 0) {
                $couponIds[] = $couponId;
            }
        }
        return $couponIds;
    }
}
