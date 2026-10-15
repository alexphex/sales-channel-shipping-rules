# Sales Channel Shipping Rules

A small WooCommerce plugin that lets a store sell some products **only in person** (for example, a farm store with self-service inventory) and others online with shipping. In-store products can't be shipped, and the plugin makes that clear to customers before they pay.

> Practice project. It was inspired by a real freelance task description (clean up shipping settings and add a separate in-person checkout for a farm store). I built it to work through that scenario end to end. It has not been used on a live store.

## The problem

A store has two kinds of inventory:

- products that are shipped or delivered
- products that exist only in the physical store

WooCommerce has no built-in way to say "this product can't be shipped". Without it, a customer can put both kinds into one cart, pay, and only then find out the order can't be fulfilled as expected.

## What it does

- Adds a **Sales channel** field to each product (General tab):
  - `Both`: shipping and in-store (default)
  - `Shipping only`
  - `In-store only`
- If the cart contains an in-store product, **only pickup methods remain** at checkout.
- If the cart mixes **In-store only** and **Shipping only** products, **checkout is blocked** with a message that names the conflicting products. This is done on purpose: the customer shouldn't be able to pay for a cart that can't be fulfilled.
- Shows an **"In-store only" badge** on the product page and in product loops.
- Settings page (WooCommerce → Sales Channel Rules): badge text, error message (with `{instore}` and `{ship}` placeholders), pickup method IDs.

Products marked `Both` never cause a conflict. They can be picked up in store, so the cart stays valid and only pickup is offered.

## Requirements

- WordPress 6.5+ (uses the `Requires Plugins` header)
- WooCommerce (developed on 11.2.1)
- PHP 7.4+

## Installation

1. Copy the plugin folder to `wp-content/plugins/sales-channel-shipping-rules/`.
2. Activate it in **Plugins**.
3. In **WooCommerce → Settings → Shipping**, make sure the shipping zone has a **Local pickup** method.
4. Set the **Sales channel** on your products.

## How it works

| Piece | Implementation |
|---|---|
| Product field | `woocommerce_wp_select` on `woocommerce_product_options_general_product_data`, saved through the product CRUD (`update_meta_data`), meta key `_scsr_channel` |
| Shipping filter | `woocommerce_package_rates` removes every rate that is not a pickup method |
| Cart validation | `woocommerce_check_cart_items` adds an error notice, which blocks checkout in both classic and Blocks checkout |
| Badge | `woocommerce_single_product_summary` and `woocommerce_after_shop_loop_item_title` |
| Settings | Settings API, single option `scsr_settings`, sanitized in a callback, empty values fall back to defaults |

Variations use the sales channel of their parent product.

## Structure

```
sales-channel-shipping-rules.php   bootstrap, HPOS compatibility, loads classes
includes/
  class-settings.php               settings page and defaults
  class-product-channel.php        product field and channel lookup
  class-shipping-rules.php         shipping rate filtering
  class-cart-validation.php        mixed cart validation
  class-frontend.php               product badge
```

## Extending

Pickup method IDs can be changed in the settings page or with a filter:

```php
add_filter( 'scsr_pickup_method_ids', function ( $ids ) {
	$ids[] = 'my_custom_pickup';
	return $ids;
} );
```

## Tested

- Blocks checkout (cart notice, blocked checkout, pickup-only rates)
- Product page badge in a classic theme
- Scenarios: in-store only, in-store + shipping only (blocked), in-store + both (pickup only), regular cart (all methods)
- Settings: custom texts are applied, empty fields return to defaults

## Known limitations

- Classic (shortcode) checkout uses the same hooks, but I haven't tested it yet.
- The badge uses classic theme hooks. In a block theme it may need a block filter instead.
- Only one shipping package is handled. Multi-package carts (for example from multi-vendor plugins) are not covered.
- There is no automated test suite yet. Testing was manual.
- Mixed carts are always blocked. A "warn instead of block" option is not implemented.
- Strings are translation-ready (`scsr` text domain), but no `.pot` file is generated yet.

## Roadmap

- Sales channel column, filter and bulk edit in the product list
- Option to hide in-store products from the online catalog
- Simplified self-service checkout page
- Free shipping threshold per shipping class
- PHPUnit tests and WordPress Coding Standards check

## License

GPL-2.0-or-later