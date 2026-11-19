<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Model\Data;

use Magic\WebhookConnector\Api\Data\AttributionTouchInterface;
use Magento\Framework\DataObject;

class AttributionTouch extends DataObject implements AttributionTouchInterface
{
    /**
     * @return string
     */
    public function getTouchId(): string
    {
        return trim((string)$this->getData('touch_id'));
    }

    /**
     * @param string $touchId
     * @return $this
     */
    public function setTouchId(string $touchId): AttributionTouchInterface
    {
        return $this->setData('touch_id', $touchId);
    }

    /**
     * @return string
     */
    public function getTouchAt(): string
    {
        return trim((string)$this->getData('touch_at'));
    }

    /**
     * @param string $touchAt
     * @return $this
     */
    public function setTouchAt(string $touchAt): AttributionTouchInterface
    {
        return $this->setData('touch_at', $touchAt);
    }

    /**
     * @return string
     */
    public function getModel(): string
    {
        return trim((string)$this->getData('model'));
    }

    /**
     * @param string $model
     * @return $this
     */
    public function setModel(string $model): AttributionTouchInterface
    {
        return $this->setData('model', $model);
    }
}
