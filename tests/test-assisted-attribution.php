<?php

declare(strict_types=1);

require dirname(__DIR__) . '/Model/AssistedAttribution.php';

use Magic\WebhookConnector\Model\AssistedAttribution;

function assert_same($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . ' expected ' . var_export($expected, true) . ' got ' . var_export($actual, true));
    }
}

$now = strtotime('2026-02-13T13:30:00Z');
$touch = [
    'touch_id' => '123E4567-E89B-42D3-A456-426614174000',
    'touch_at' => '2026-02-13T13:30:00Z',
    'model' => AssistedAttribution::MODEL,
];

$normalized = AssistedAttribution::normalize($touch, $now);
assert_same(true, $normalized !== null, 'valid touch is accepted');
assert_same(strtolower($touch['touch_id']), $normalized['touch_id'], 'touch id is lowercase');
assert_same('2026-02-13T13:30:00Z', $normalized['touch_at'], 'touch time is normalized');
assert_same(null, AssistedAttribution::normalize(['touch_id' => 'bad'], $now), 'invalid touch is rejected');
assert_same(
    null,
    AssistedAttribution::normalize($touch, $now + AssistedAttribution::TTL_SECONDS + 1),
    'expired touch is rejected'
);

$newer = AssistedAttribution::normalize([
    'touch_id' => '123e4567-e89b-42d3-a456-426614174001',
    'touch_at' => '2026-02-13T13:31:00Z',
    'model' => AssistedAttribution::MODEL,
], $now + 60);
assert_same($newer['touch_id'], AssistedAttribution::preferLatest($normalized, $newer)['touch_id'], 'newer touch replaces older');
assert_same($newer['touch_id'], AssistedAttribution::preferLatest($newer, $normalized)['touch_id'], 'older touch does not replace newer');

echo "assisted attribution tests passed\n";
