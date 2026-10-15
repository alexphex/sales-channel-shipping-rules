<?php
defined( 'ABSPATH' ) || exit;

class SCSR_Shipping_Rules {

	public static function init() {
		add_filter( 'woocommerce_package_rates', array( __CLASS__, 'filter_rates' ), 100, 2 );
	}

	public static function get_pickup_method_ids() {
		return apply_filters( 'scsr_pickup_method_ids', SCSR_Settings::get_pickup_ids() );
	}

	public static function package_has_instore( $package ) {
		foreach ( $package['contents'] as $item ) {
			if ( 'instore' === SCSR_Product_Channel::get_channel( $item['data'] ) ) {
				return true;
			}
		}
		return false;
	}

	public static function filter_rates( $rates, $package ) {
		if ( ! self::package_has_instore( $package ) ) {
			return $rates;
		}

		$pickup_ids = self::get_pickup_method_ids();

		foreach ( $rates as $rate_id => $rate ) {
			if ( ! in_array( $rate->get_method_id(), $pickup_ids, true ) ) {
				unset( $rates[ $rate_id ] );
			}
		}

		return $rates;
	}
}