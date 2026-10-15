<?php
defined( 'ABSPATH' ) || exit;

class SCSR_Cart_Validation {

	public static function init() {
		add_action( 'woocommerce_check_cart_items', array( __CLASS__, 'validate_cart' ) );
	}

	public static function validate_cart() {
		if ( ! WC()->cart ) {
			return;
		}

		$instore = array();
		$ship    = array();

		foreach ( WC()->cart->get_cart() as $item ) {
			$channel = SCSR_Product_Channel::get_channel( $item['data'] );
			$name    = $item['data']->get_name();

			if ( 'instore' === $channel ) {
				$instore[] = $name;
			} elseif ( 'ship' === $channel ) {
				$ship[] = $name;
			}
		}

		if ( empty( $instore ) || empty( $ship ) ) {
			return;
		}

		$message = str_replace(
			array( '{instore}', '{ship}' ),
			array(
				esc_html( implode( ', ', array_unique( $instore ) ) ),
				esc_html( implode( ', ', array_unique( $ship ) ) ),
			),
			esc_html( SCSR_Settings::get( 'cart_error_text' ) )
		);

		wc_add_notice( $message, 'error' );
	}
}