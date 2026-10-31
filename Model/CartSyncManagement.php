<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Model;

use Magic\WebhookConnector\Api\CartSyncManagementInterface;
use Magic\WebhookConnector\Api\Data\CartSyncItemInterface;
use Magic\WebhookConnector\Api\Data\CartSyncResultInterface;
use Magic\WebhookConnector\Api\Data\CartSyncResultInterfaceFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Store\Model\StoreManagerInterface;

class CartSyncManagement implements CartSyncManagementInterface
{
    public const CACHE_PREFIX = 'meera_cart_';
    public const CACHE_TTL = 900;

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly Json $serializer,
        private readonly StoreManagerInterface $storeManager,
        private readonly CartSyncResultInterfaceFactory $resultFactory
    ) {
    }

    /**
     * @param \Magic\WebhookConnector\Api\Data\CartSyncItemInterface[] $items
     * @return \Magic\WebhookConnector\Api\Data\CartSyncResultInterface
     * @throws WebapiException
     */
    public function sync(array $items): CartSyncResultInterface
    {
        $lines = [];
        foreach ($items as $item) {
            [$sku, $quantity] = $this->lineFromItem($item);
            if ($sku === '' || $quantity < 1) {
                throw new WebapiException(
                    __('Invalid cart items.'),
                    0,
                    WebapiException::HTTP_BAD_REQUEST
                );
            }
            $lines[] = ['sku' => $sku, 'quantity' => $quantity];
        }
        if ($lines === []) {
            throw new WebapiException(
                __('Request requires a non-empty items array.'),
                0,
                WebapiException::HTTP_BAD_REQUEST
            );
        }

        try {
            $cartId = bin2hex(random_bytes(16));
        } catch (\Exception $exception) {
            throw new LocalizedException(__('Unable to store the cart.'), $exception);
        }
        $this->cache->save(
            $this->serializer->serialize($lines),
            self::CACHE_PREFIX . $cartId,
            ['meera_cart'],
            self::CACHE_TTL
        );

        $baseUrl = rtrim($this->storeManager->getStore()->getBaseUrl(), '/');
        return $this->resultFactory->create()
            ->setSuccess(true)
            ->setRedirectUrl($baseUrl . '/meera/cart/apply?meera_cart=' . rawurlencode($cartId));
    }

    /**
     * @param mixed $item
     * @return array{0: string, 1: int}
     */
    private function lineFromItem($item): array
    {
        if ($item instanceof CartSyncItemInterface) {
            return [$item->getSku(), $item->getQuantity()];
        }
        if (is_array($item)) {
            return [trim((string)($item['sku'] ?? '')), (int)($item['quantity'] ?? 0)];
        }
        return ['', 0];
    }
}
