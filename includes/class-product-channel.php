<?php
defined( 'ABSPATH' ) || exit;

class SCSR_Product_Channel {

	const META_KEY = '_scsr_channel';

	public static function init() {
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'render_field' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_field' ) );
	}

	public static function get_options() {
		return array(
			'both'    => __( 'Both (shipping and in-store)', 'scsr' ),
			'ship'    => __( 'Shipping only', 'scsr' ),
			'instore' => __( 'In-store only', 'scsr' ),
		);
	}

	public static function render_field() {
		woocommerce_wp_select(
			array(
				'id'          => self::META_KEY,
				'label'       => __( 'Sales channel', 'scsr' ),
				'options'     => self::get_options(),
				'desc_tip'    => true,
				'description' => __( 'Where this product can be sold.', 'scsr' ),
			)
		);
	}

	public static function save_field( $product_id ) {
		// Nonce is already verified by WooCommerce on product save.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$value = isset( $_POST[ self::META_KEY ] ) ? sanitize_key( wp_unslash( $_POST[ self::META_KEY ] ) ) : 'both';

		if ( ! array_key_exists( $value, self::get_options() ) ) {
			$value = 'both';
		}

		$product = wc_get_product( $product_id );
		if ( $product ) {
			$product->update_meta_data( self::META_KEY, $value );
			$product->save();
		}
	}

	/**
	 * Returns 'both', 'ship' or 'instore'. Variations use the parent's value.
	 */
	public static function get_channel( $product ) {
		$product = wc_get_product( $product );
		if ( ! $product ) {
			return 'both';
		}

		if ( $product->is_type( 'variation' ) ) {
			$product = wc_get_product( $product->get_parent_id() );
			if ( ! $product ) {
				return 'both';
			}
		}

		$value = $product->get_meta( self::META_KEY );

		return array_key_exists( $value, self::get_options() ) ? $value : 'both';
	}
}