<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Plugin;

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
        if (!$extension || !method_exists($extension, 'getMeeraSource')) {
            return;
        }
        if (trim((string)$extension->getMeeraSource()) !== 'meera') {
            return;
        }
        $order->setData('meera_source', 'meera');
    }

    private function apply(OrderInterface $order): void
    {
        if (!$order instanceof DataObject) {
            return;
        }
        $value = trim((string)$order->getData('meera_source'));
        if ($value !== 'meera') {
            return;
        }
        $extension = $order->getExtensionAttributes() ?: $this->orderExtensionFactory->create();
        $extension->setMeeraSource('meera');
        $order->setExtensionAttributes($extension);
    }
}
