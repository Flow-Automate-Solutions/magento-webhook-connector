<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class MeeraSourceOrder implements ObserverInterface
{
    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        if (!$order instanceof DataObject || !$quote instanceof DataObject) {
            return;
        }
        if (trim((string)$quote->getData('meera_source')) !== 'meera') {
            return;
        }
        $order->setData('meera_source', 'meera');
    }
}
