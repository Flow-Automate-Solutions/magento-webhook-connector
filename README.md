# Magic WebhookConnector (Magento 2 Module)

`Magic_WebhookConnector` publishes product, product-option, review, customer, order, cart-price-rule, and MSI source webhooks to your connector.

## Features

- Product lifecycle webhooks for:
  - `product.created`
  - `product.updated`
  - `product.deleted`
- Review lifecycle webhooks for:
  - `review.created`
  - `review.updated`
  - `review.deleted`
- Inventory source lifecycle webhooks for:
  - `location.updated`
  - `location.deleted`
- Custom product-option lifecycle webhooks for:
  - `product_option.created`
  - `product_option.updated`
  - `product_option.deleted`
- Customer lifecycle webhooks for:
  - `customer.created`
  - `customer.updated`
  - `customer.deleted`
- Order lifecycle webhooks for:
  - `order.created`
  - `order.updated`
  - `order.deleted`
- Signed webhook delivery with `X-Magic-Signature` (HMAC SHA256).
- Webhook secret configured from Magento Admin (encrypted).
- Anonymous REST ping so Magic CMS can detect whether the module is installed.
- Authenticated, paginated product-review read APIs.
- Authenticated product-review create, update, and delete APIs.

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
  "version": "1.5.0",
  "webhook_secret_configured": true
}
```

If the module is missing or disabled, Magento returns 404 (`Request does not match any route.`). Existing installs need a module update (commands above) before this route exists.

## Review REST API

The connector's Magento integration must be granted the `Magic Webhook Connector > Read Product Reviews` ACL resource. It can then call:

```text
GET {store_base_url}/rest/V1/magic-webhook-connector/reviews?page=1&pageSize=100
GET {store_base_url}/rest/V1/magic-webhook-connector/reviews/{reviewId}
```

The list endpoint returns a page of review IDs. The detail endpoint returns the review, product SKU, rating votes, status, customer identity when available, and timestamps.

To write reviews, also grant `Magic Webhook Connector > Write Product Reviews` and reauthorize the Magento integration:

```text
POST   {store_base_url}/rest/V1/magic-webhook-connector/reviews
PUT    {store_base_url}/rest/V1/magic-webhook-connector/reviews/{reviewId}
DELETE {store_base_url}/rest/V1/magic-webhook-connector/reviews/{reviewId}
```

Create and update requests wrap the input as `{"review": {...}}`. Reviews are created as guests. Rating writes apply the selected 1–5 star option to every active product-rating dimension for the target store. CMS-origin writes suppress this module's review webhooks to prevent sync loops.

## Inventory Source Access

Magento locations use Multi-Source Inventory (MSI) sources. The connector integration
must be granted read access to inventory sources and reauthorized after upgrading.
Magic CMS reads `/rest/V1/inventory/sources` and
`/rest/V1/inventory/sources/{sourceCode}`. Source saves publish `location.updated`;
explicit source deletes publish `location.deleted`.

Source-item saves publish one `stock.updated` event per item, and source-item
deletes publish `stock.deleted`. Each event contains `source_code` and `sku`.
The integration also needs native Inventory source-item read access. Grant that
resource and reauthorize after upgrading to 1.5.0.

## Product options and customers

Custom-option saves and deletes publish `product_option.created`,
`product_option.updated`, or `product_option.deleted` with `option_id` and `sku`.
Saving a product can publish one event per option on that product. Customer
saves and deletes publish `customer.created`, `customer.updated`, or
`customer.deleted` with `customer_id`. Upgrade to 1.6.0 before these events
are sent. Order saves publish `order.created` or `order.updated`, and order
deletes publish `order.deleted`, each with `order_id`. Cancel, refund, and
shipment stay order updates. Upgrade to 1.6.2 before order events are sent.
Cart price rule and coupon saves publish `discount.created` or
`discount.updated`. Deletes publish `discount.deleted`. Each payload includes
`rule_id`, `coupon_id`, and `method` (`code` or `automatic`). A rule save
publishes the current shape and deletes the other: one message per coupon, or
one automatic message. A rule delete publishes a delete for every coupon and
for the automatic id. Upgrade to 1.6.3 before discount events are sent.

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

Review payloads use the same headers. Example:

```json
{
  "event_type": "review.updated",
  "store_id": 1,
  "store_url": "https://store.example.com",
  "review_id": 123,
  "product_id": 42,
  "timestamp": "2026-10-07T06:30:00+00:00"
}
```

