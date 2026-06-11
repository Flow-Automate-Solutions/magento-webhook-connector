# Magic WebhookConnector (Magento 2 Module)

`Magic_WebhookConnector` publishes product webhooks to your connector.

## Features

- Product lifecycle webhooks for:
  - `product.created`
  - `product.updated`
  - `product.deleted`
- Signed webhook delivery with `X-Magic-Signature` (HMAC SHA256).
- Configurable webhook URL, secret, timeout, and retries from Magento Admin.

## Install via Private Composer (non-published module)

In your Magento project `composer.json`, add a private repository:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@bitbucket.org:your-org/magento-webhook-connector.git"
    }
  ]
}
```

Require the module:

```bash
composer require magic/module-webhook-connector:dev-main
```

Enable and upgrade Magento:

```bash
bin/magento module:enable Magic_WebhookConnector
bin/magento setup:upgrade
bin/magento cache:flush
```

## Admin Configuration

Go to:

- `Stores` -> `Configuration` -> `General` -> `Magic Webhook Connector`

Set:

- Enable Webhook Publishing
- Webhook URL
- Webhook Secret
- Timeout (seconds)
- Retry Count

## Webhook Payload

Sample payload:

```json
{
  "event_type": "product.updated",
  "store_id": 1,
  "store_url": "https://store.example.com",
  "entity_id": 42,
  "sku": "SKU-42",
  "name": "Sample Product",
  "status": 1,
  "visibility": 4,
  "type_id": "simple",
  "updated_at": "2026-06-03 10:11:12",
  "timestamp": "2026-06-03T10:11:12+00:00"
}
```

Headers sent by module:

- `Content-Type: application/json`
- `X-Magic-Event: <event_type>`
- `X-Magic-Signature: <hmac_sha256(body, webhook_secret)>`

