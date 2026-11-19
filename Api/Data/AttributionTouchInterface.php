<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Api\Data;

interface AttributionTouchInterface
{
    /**
     * @return string
     */
    public function getTouchId(): string;

    /**
     * @param string $touchId
     * @return $this
     */
    public function setTouchId(string $touchId): AttributionTouchInterface;

    /**
     * @return string
     */
    public function getTouchAt(): string;

    /**
     * @param string $touchAt
     * @return $this
     */
    public function setTouchAt(string $touchAt): AttributionTouchInterface;

    /**
     * @return string
     */
    public function getModel(): string;

    /**
     * @param string $model
     * @return $this
     */
    public function setModel(string $model): AttributionTouchInterface;
}
