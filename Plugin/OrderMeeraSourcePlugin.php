<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Plugin;

use Magic\WebhookConnector\Model\AssistedAttribution;
use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderMeeraSourcePlugin
{
    public function __construct(
        private readonly OrderExtensionFactory $orderExtensionFactory
    ) {
    }

    /**
     * @return array{0: OrderInterface}
     */
    public function beforeSave(OrderRepositoryInterface $subject, OrderInterface $order): array
    {
        $this->persistColumn($order);
        return [$order];
    }

    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order): OrderInterface
    {
        $this->apply($order);
        return $order;
    }

    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $result
    ): OrderSearchResultInterface {
        foreach ($result->getItems() as $order) {
            $this->apply($order);
        }
        return $result;
    }

    private function persistColumn(OrderInterface $order): void
    {
        if (!$order instanceof DataObject) {
            return;
        }
        $extension = $order->getExtensionAttributes();
        if (!$extension) {
            return;
        }
        if (
            method_exists($extension, 'getMeeraSource')
            && trim((string)$extension->getMeeraSource()) === 'meera'
        ) {
            $order->setData('meera_source', 'meera');
        }
        $touch = [];
        if (method_exists($extension, 'getMeeraTouchId')) {
            $touch['touch_id'] = $extension->getMeeraTouchId();
        }
        if (method_exists($extension, 'getMeeraTouchAt')) {
            $touch['touch_at'] = $extension->getMeeraTouchAt();
        }
        if (method_exists($extension, 'getMeeraAttributionModel')) {
            $touch['model'] = $extension->getMeeraAttributionModel();
        }
        $normalized = AssistedAttribution::normalize($touch);
        if ($normalized !== null) {
            AssistedAttribution::write($order, $normalized);
        }
    }

    private function apply(OrderInterface $order): void
    {
        if (!$order instanceof DataObject) {
            return;
        }
        $source = trim((string)$order->getData('meera_source'));
        $touchId = trim((string)$order->getData('meera_touch_id'));
        $touchAt = trim((string)$order->getData('meera_touch_at'));
        $model = trim((string)$order->getData('meera_attribution_model'));
        if ($source !== 'meera' && $touchId === '' && $touchAt === '' && $model === '') {
            return;
        }
        $extension = $order->getExtensionAttributes() ?: $this->orderExtensionFactory->create();
        if ($source === 'meera' && method_exists($extension, 'setMeeraSource')) {
            $extension->setMeeraSource('meera');
        }
        if ($touchId !== '' && method_exists($extension, 'setMeeraTouchId')) {
            $extension->setMeeraTouchId($touchId);
        }
        if ($touchAt !== '' && method_exists($extension, 'setMeeraTouchAt')) {
            $extension->setMeeraTouchAt($touchAt);
        }
        if ($model !== '' && method_exists($extension, 'setMeeraAttributionModel')) {
            $extension->setMeeraAttributionModel($model);
        }
        $order->setExtensionAttributes($extension);
    }
}
