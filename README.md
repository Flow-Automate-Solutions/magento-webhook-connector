# Magic WebhookConnector (Magento 2 Module)

`Magic_WebhookConnector` publishes product webhooks to your connector.

## Features

- Product lifecycle webhooks for:
  - `product.created`
  - `product.updated`
  - `product.deleted`
- Signed webhook delivery with `X-Magic-Signature` (HMAC SHA256).
- Webhook secret configured from Magento Admin (encrypted).
- Anonymous REST ping so Magic CMS can detect whether the module is installed.

## Install via Private Composer (non-published module)

In your Magento project `composer.json`, add a private repository:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/Flow-Automate-Solutions/magento-webhook-connector.git"
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

## Update Existing Installations

If the module is already installed, update it with:

```bash
composer update magic/module-webhook-connector
php bin/magento setup:upgrade
php bin/magento cache:flush
```

For production mode, also run:

```bash
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
```

## Admin Status Page

Go to:

- `Stores` -> `Configuration` -> `General` -> `Magic Webhook Connector`

This page shows runtime status details and provides an encrypted `Webhook Secret` field. Copy the secret from Magic CMS admin UI.

## REST Status Ping

When the module is installed and enabled, Magento answers:

```bash
GET {store_base_url}/rest/V1/magic-webhook-connector/status
```

Example response:

```json
{
  "module": "Magic_WebhookConnector",
  "version": "1.1.0",
  "webhook_secret_configured": true
}
```

If the module is missing or disabled, Magento returns 404 (`Request does not match any route.`). Existing installs need a module update (commands above) before this route exists.

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

