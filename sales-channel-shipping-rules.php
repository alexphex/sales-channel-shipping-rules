<?php
/**
 * Plugin Name: Sales Channel Shipping Rules
 * Description: Sales channel per product (ship / in-store only / both) and shipping rules for mixed carts.
 * Version: 0.1.0
 * Requires Plugins: woocommerce
 * Text Domain: scsr
 * License: GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'SCSR_VERSION', '0.1.0' );
define( 'SCSR_FILE', __FILE__ );
define( 'SCSR_PATH', plugin_dir_path( __FILE__ ) );

// Declare HPOS compatibility.
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', SCSR_FILE, true );
	}
} );

// Load the plugin only when WooCommerce is active.
add_action( 'plugins_loaded', function () {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	require_once SCSR_PATH . 'includes/class-product-channel.php';
	SCSR_Product_Channel::init();

	require_once SCSR_PATH . 'includes/class-shipping-rules.php';
	SCSR_Shipping_Rules::init();

	require_once SCSR_PATH . 'includes/class-frontend.php';
	SCSR_Frontend::init();

	require_once SCSR_PATH . 'includes/class-cart-validation.php';
	SCSR_Cart_Validation::init();

	require_once SCSR_PATH . 'includes/class-settings.php';
	SCSR_Settings::init();
} );