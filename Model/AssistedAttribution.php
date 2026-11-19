<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Model;

class AssistedAttribution
{
    public const MODEL = 'last_meera_touch_7d_v1';
    public const TTL_SECONDS = 604800;
    public const CLOCK_SKEW_SECONDS = 300;

    /**
     * @param array<string, mixed> $raw
     * @return array{touch_id: string, touch_at: string, model: string}|null
     */
    public static function normalize(array $raw, ?int $now = null): ?array
    {
        $touchId = strtolower(trim((string)($raw['touch_id'] ?? '')));
        $touchAt = trim((string)($raw['touch_at'] ?? $raw['timestamp'] ?? ''));
        $model = trim((string)($raw['model'] ?? self::MODEL));
        $touchTimestamp = strtotime($touchAt);
        $nowTimestamp = $now ?? time();
        if (
            !preg_match(
                '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
                $touchId
            )
            || $touchTimestamp === false
            || $model !== self::MODEL
            || $touchTimestamp < $nowTimestamp - self::TTL_SECONDS
            || $touchTimestamp > $nowTimestamp + self::CLOCK_SKEW_SECONDS
        ) {
            return null;
        }

        return [
            'touch_id' => $touchId,
            'touch_at' => gmdate('Y-m-d\TH:i:s\Z', $touchTimestamp),
            'model' => self::MODEL,
        ];
    }

    /**
     * @param array{touch_id: string, touch_at: string, model: string}|null $current
     * @param array{touch_id: string, touch_at: string, model: string}|null $incoming
     * @return array{touch_id: string, touch_at: string, model: string}|null
     */
    public static function preferLatest(?array $current, ?array $incoming): ?array
    {
        if ($incoming === null) {
            return $current;
        }
        if ($current === null) {
            return $incoming;
        }
        $currentTimestamp = strtotime($current['touch_at']);
        $incomingTimestamp = strtotime($incoming['touch_at']);
        if ($currentTimestamp !== false && $incomingTimestamp !== false && $currentTimestamp > $incomingTimestamp) {
            return $current;
        }

        return $incoming;
    }

    /**
     * @param object $source
     * @return array{touch_id: string, touch_at: string, model: string}|null
     */
    public static function read(object $source): ?array
    {
        if (!method_exists($source, 'getData')) {
            return null;
        }

        return self::normalize([
            'touch_id' => $source->getData('meera_touch_id'),
            'touch_at' => $source->getData('meera_touch_at'),
            'model' => $source->getData('meera_attribution_model') ?: self::MODEL,
        ]);
    }

    /**
     * @param object $target
     * @param array{touch_id: string, touch_at: string, model: string} $touch
     */
    public static function write(object $target, array $touch): void
    {
        if (!method_exists($target, 'setData')) {
            return;
        }
        $target->setData('meera_touch_id', $touch['touch_id']);
        $target->setData('meera_touch_at', $touch['touch_at']);
        $target->setData('meera_attribution_model', $touch['model']);
    }

    /**
     * @param object $source
     * @param object $target
     */
    public static function copy(object $source, object $target): void
    {
        $touch = self::read($source);
        if ($touch !== null) {
            self::write($target, $touch);
        }
    }
}
