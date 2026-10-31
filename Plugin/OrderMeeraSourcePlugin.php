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
