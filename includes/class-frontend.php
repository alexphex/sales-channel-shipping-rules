<?php
defined( 'ABSPATH' ) || exit;

class SCSR_Frontend {

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ) );
		// Single product page: after title (5), before price (10).
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'render_badge' ), 6 );
		// Shop / category loop: after title.
		add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'render_badge' ), 4 );
	}

	public static function enqueue_styles() {
		wp_register_style( 'scsr-frontend', false, array(), SCSR_VERSION );
		wp_enqueue_style( 'scsr-frontend' );
		wp_add_inline_style(
			'scsr-frontend',
			'.scsr-badge{display:inline-block;margin:0 0 .75em;padding:.3em .7em;border-radius:3px;font-size:.85em;font-weight:600;line-height:1.3;background:#fff4e5;color:#8a4b00;border:1px solid #f0c27b}'
		);
	}

	public static function render_badge() {
		global $product;

		if ( ! $product || 'instore' !== SCSR_Product_Channel::get_channel( $product ) ) {
			return;
		}

		printf(
			'<div class="scsr-badge">%s</div>',
			esc_html( SCSR_Settings::get( 'badge_text' ) )
		);
	}
}