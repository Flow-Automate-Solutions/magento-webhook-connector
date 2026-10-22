<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Model;

class DiscountWebhookSuppressor
{
    private int $depth = 0;

    public function isSuppressed(): bool
    {
        return $this->depth > 0;
    }

    public function run(callable $operation): mixed
    {
        $this->depth++;
        try {
            return $operation();
        } finally {
            $this->depth--;
        }
    }
}
