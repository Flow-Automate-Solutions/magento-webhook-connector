<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Controller\Attribution;

use Magic\WebhookConnector\Model\AssistedAttribution;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class Touch extends Action implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        private readonly Cart $cart,
        private readonly JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();
        $incoming = AssistedAttribution::normalize([
            'touch_id' => $this->getRequest()->getParam('touch_id'),
            'touch_at' => $this->getRequest()->getParam('touch_at'),
            'model' => $this->getRequest()->getParam('model'),
        ]);
        if ($incoming === null) {
            return $result->setHttpResponseCode(400)->setData([
                'success' => false,
                'error' => 'A valid Meera assistance touch is required.',
            ]);
        }

        $quote = $this->cart->getQuote();
        $touch = AssistedAttribution::preferLatest(AssistedAttribution::read($quote), $incoming);
        if ($touch === $incoming) {
            AssistedAttribution::write($quote, $touch);
            $this->cart->save();
        }

        return $result->setData([
            'success' => true,
            'touch' => $touch,
        ]);
    }
}
