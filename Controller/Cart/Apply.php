<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Controller\Cart;

use Magic\WebhookConnector\Model\CartSyncManagement;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableResource;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;

class Apply extends Action implements HttpGetActionInterface
{
    public function __construct(
        Context $context,
        private readonly CacheInterface $cache,
        private readonly Json $serializer,
        private readonly Cart $cart,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ConfigurableResource $configurableResource,
        private readonly ConfigurableType $configurableType
    ) {
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();
        $cartId = (string)$this->getRequest()->getParam('meera_cart');
        if (!preg_match('/^[a-f0-9]{32}$/', $cartId)) {
            return $redirect->setPath('checkout/cart');
        }

        $cacheKey = CartSyncManagement::CACHE_PREFIX . $cartId;
        $raw = $this->cache->load($cacheKey);
        $this->cache->remove($cacheKey);
        $items = $this->decodeItems($raw);
        if ($items === []) {
            return $redirect->setPath('checkout/cart');
        }

        try {
            $this->cart->truncate();
            $added = 0;
            foreach ($items as $item) {
                $this->addSku((string)$item['sku'], (int)$item['quantity']);
                $added++;
            }
            if ($added < 1) {
                return $redirect->setPath('checkout/cart');
            }
            $this->cart->getQuote()->setData('meera_source', 'meera');
            $this->cart->save();
        } catch (LocalizedException $exception) {
            return $redirect->setPath('checkout/cart');
        }

        return $redirect->setPath('checkout');
    }

    /**
     * @param mixed $raw
     * @return array<int, array{sku: string, quantity: int}>
     */
    private function decodeItems($raw): array
    {
        if (!is_string($raw) || $raw === '') {
            return [];
        }
        try {
            $decoded = $this->serializer->unserialize($raw);
        } catch (\InvalidArgumentException $exception) {
            return [];
        }
        if (!is_array($decoded)) {
            return [];
        }
        $items = [];
        foreach ($decoded as $item) {
            if (!is_array($item)) {
                continue;
            }
            $sku = trim((string)($item['sku'] ?? ''));
            $quantity = (int)($item['quantity'] ?? 0);
            if ($sku === '' || $quantity < 1) {
                continue;
            }
            $items[] = ['sku' => $sku, 'quantity' => $quantity];
        }
        return $items;
    }

    /**
     * @param string $sku
     * @param int $qty
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function addSku(string $sku, int $qty): void
    {
        $product = $this->productRepository->get($sku);
        $parentIds = $this->configurableResource->getParentIdsByChild((int)$product->getId());
        if ($parentIds) {
            $parent = $this->productRepository->getById((int)$parentIds[0]);
            $superAttributes = [];
            foreach ($this->configurableType->getConfigurableAttributes($parent) as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                if (!$productAttribute) {
                    continue;
                }
                $value = $product->getData($productAttribute->getAttributeCode());
                if ($value === null || $value === '') {
                    continue;
                }
                $superAttributes[(int)$attribute->getAttributeId()] = $value;
            }
            $this->cart->addProduct($parent, ['qty' => $qty, 'super_attribute' => $superAttributes]);
            return;
        }
        $this->cart->addProduct($product, ['qty' => $qty]);
    }
}
