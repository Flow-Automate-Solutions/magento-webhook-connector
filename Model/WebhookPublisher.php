<?php

declare(strict_types=1);

namespace Magic\WebhookConnector\Model;

use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class WebhookPublisher
{
    public function __construct(
        private readonly Config $config,
        private readonly Curl $curl,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function publish(string $eventType, array $payload, ?int $storeId = null): void
    {
        if (!$this->config->isEnabled($storeId)) {
            return;
        }

        $url = $this->config->getWebhookUrl($storeId);
        if ($url === '') {
            $this->logger->warning('Magic webhook skipped: webhook URL is empty.');
            return;
        }

        $secret = $this->config->getWebhookSecret($storeId);
        $timeout = $this->config->getTimeoutSeconds($storeId);
        $retries = $this->config->getRetryCount($storeId);
        $attempts = $retries + 1;
        $body = (string)json_encode($payload);
        $signature = hash_hmac('sha256', $body, $secret);

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                $this->curl->setTimeout($timeout);
                $this->curl->setHeaders([
                    'Content-Type' => 'application/json',
                    'X-Magic-Event' => $eventType,
                    'X-Magic-Signature' => $signature,
                ]);
                $this->curl->post($url, $body);

                $statusCode = (int)$this->curl->getStatus();
                if ($statusCode >= 200 && $statusCode < 300) {
                    return;
                }

                $this->logger->warning(
                    sprintf(
                        'Magic webhook delivery failed (attempt %d/%d), status: %d',
                        $attempt,
                        $attempts,
                        $statusCode
                    )
                );
            } catch (\Throwable $exception) {
                $this->logger->error(
                    sprintf(
                        'Magic webhook delivery error (attempt %d/%d): %s',
                        $attempt,
                        $attempts,
                        $exception->getMessage()
                    )
                );
            }
        }
    }
}
