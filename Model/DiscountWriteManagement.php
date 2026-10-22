<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Model;

use Magic\WebhookConnector\Api\Data\DiscountWriteResultInterface;
use Magic\WebhookConnector\Api\DiscountWriteManagementInterface;
use Magic\WebhookConnector\Model\Data\DiscountRule;
use Magic\WebhookConnector\Model\Data\DiscountWriteResult;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\SalesRule\Api\Data\CouponInterfaceFactory;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Rule;

class DiscountWriteManagement implements DiscountWriteManagementInterface
{
    public function __construct(
        private readonly RuleRepositoryInterface $ruleRepository,
        private readonly CouponRepositoryInterface $couponRepository,
        private readonly CouponInterfaceFactory $couponFactory,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly DiscountWebhookSuppressor $webhookSuppressor
    ) {
    }

    /**
     * @param \Magic\WebhookConnector\Model\Data\DiscountRule $rule
     * @return \Magic\WebhookConnector\Api\Data\DiscountWriteResultInterface
     */
    public function create(DiscountRule $rule): DiscountWriteResultInterface
    {
        return $this->save($rule, null);
    }

    /**
     * @param int $ruleId
     * @param \Magic\WebhookConnector\Model\Data\DiscountRule $rule
     * @return \Magic\WebhookConnector\Api\Data\DiscountWriteResultInterface
     */
    public function update(int $ruleId, DiscountRule $rule): DiscountWriteResultInterface
    {
        $this->ruleRepository->getById($ruleId);
        return $this->save($rule, $ruleId);
    }

    /**
     * @param int $ruleId
     * @return bool
     */
    public function delete(int $ruleId): bool
    {
        return (bool)$this->webhookSuppressor->run(
            fn (): bool => $this->ruleRepository->deleteById($ruleId)
        );
    }

    /**
     * @param int $couponId
     * @return bool
     */
    public function deleteCoupon(int $couponId): bool
    {
        return (bool)$this->webhookSuppressor->run(
            fn (): bool => $this->couponRepository->deleteById($couponId)
        );
    }

    private function save(DiscountRule $rule, ?int $ruleId): DiscountWriteResultInterface
    {
        $couponType = $this->couponType($rule);
        if (!in_array($couponType, [Rule::COUPON_TYPE_NO_COUPON, Rule::COUPON_TYPE_SPECIFIC], true)) {
            throw new InputException(__('coupon_type must be no coupon or specific coupon.'));
        }
        $code = trim((string)$rule->getCouponCode());
        if ($couponType === Rule::COUPON_TYPE_SPECIFIC && $code === '') {
            throw new InputException(__('A coupon code is required.'));
        }
        $rule->setCouponType($couponType);
        if ($ruleId === null) {
            $rule->setRuleId(null);
        } else {
            $rule->setRuleId($ruleId);
        }

        return $this->webhookSuppressor->run(function () use ($rule, $couponType, $code): DiscountWriteResultInterface {
            $saved = $this->ruleRepository->save($rule);
            $savedRuleId = (int)$saved->getRuleId();
            $couponId = $couponType === Rule::COUPON_TYPE_SPECIFIC
                ? $this->syncPrimaryCoupon($savedRuleId, $code, $rule)
                : null;
            return (new DiscountWriteResult())
                ->setRuleId($savedRuleId)
                ->setCouponId($couponId);
        });
    }

    private function syncPrimaryCoupon(int $ruleId, string $code, DiscountRule $rule): int
    {
        $coupon = $this->primaryCoupon($ruleId) ?? $this->couponFactory->create();
        if (!(int)$coupon->getCouponId()) {
            $coupon->setRuleId($ruleId);
            $coupon->setIsPrimary(true);
            $coupon->setType(CouponInterface::TYPE_MANUAL);
        }
        $usageLimit = (int)$rule->getUsesPerCoupon();
        $perCustomer = (int)$rule->getUsesPerCustomer();
        $coupon->setCode($code);
        $coupon->setUsageLimit($usageLimit > 0 ? $usageLimit : null);
        $coupon->setUsagePerCustomer($perCustomer > 0 ? $perCustomer : null);
        $saved = $this->couponRepository->save($coupon);
        return (int)$saved->getCouponId();
    }

    private function primaryCoupon(int $ruleId): ?CouponInterface
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter('rule_id', $ruleId)
            ->create();
        $fallback = null;
        foreach ($this->couponRepository->getList($criteria)->getItems() as $coupon) {
            $fallback = $coupon;
            if ($coupon->getIsPrimary()) {
                return $coupon;
            }
        }
        return $fallback;
    }

    private function couponType(DiscountRule $rule): int
    {
        $value = $rule->getCouponType();
        $text = strtolower(trim((string)$value));
        if ($text === '1' || $text === 'no_coupon') {
            return Rule::COUPON_TYPE_NO_COUPON;
        }
        if ($text === '2' || $text === 'specific_coupon') {
            return Rule::COUPON_TYPE_SPECIFIC;
        }
        return (int)$value;
    }
}
