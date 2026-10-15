<?php
defined( 'ABSPATH' ) || exit;

class SCSR_Settings {

	const OPTION = 'scsr_settings';
	const PAGE   = 'scsr-settings';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	public static function defaults() {
		return array(
			'badge_text'        => __( 'In-store only: not available for shipping', 'scsr' ),
			'cart_error_text'   => __( 'In-store only items ({instore}) cannot be ordered together with items for shipping ({ship}). Please remove one of the groups to continue.', 'scsr' ),
			'pickup_method_ids' => 'local_pickup, pickup_location',
		);
	}

	public static function get( $key ) {
		$defaults = self::defaults();
		$options  = get_option( self::OPTION, array() );

		if ( isset( $options[ $key ] ) && '' !== $options[ $key ] ) {
			return $options[ $key ];
		}

		return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	}

	public static function get_pickup_ids() {
		return array_filter( array_map( 'trim', explode( ',', self::get( 'pickup_method_ids' ) ) ) );
	}

	public static function add_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Sales Channel Rules', 'scsr' ),
			__( 'Sales Channel Rules', 'scsr' ),
			'manage_woocommerce',
			self::PAGE,
			array( __CLASS__, 'render_page' )
		);
	}

	public static function register() {
		register_setting(
			self::PAGE,
			self::OPTION,
			array( 'sanitize_callback' => array( __CLASS__, 'sanitize' ) )
		);

		add_settings_section( 'scsr_main', '', '__return_false', self::PAGE );

		self::add_field( 'badge_text', __( 'Product badge text', 'scsr' ), 'text', '' );
		self::add_field(
			'cart_error_text',
			__( 'Mixed cart error', 'scsr' ),
			'textarea',
			__( 'Placeholders: {instore} and {ship} are replaced with product names.', 'scsr' )
		);
		self::add_field(
			'pickup_method_ids',
			__( 'Pickup method IDs', 'scsr' ),
			'text',
			__( 'Comma-separated shipping method IDs treated as in-store pickup.', 'scsr' )
		);
	}

	private static function add_field( $key, $label, $type, $help ) {
		add_settings_field(
			$key,
			$label,
			array( __CLASS__, 'render_field' ),
			self::PAGE,
			'scsr_main',
			array(
				'key'  => $key,
				'type' => $type,
				'help' => $help,
			)
		);
	}

	public static function render_field( $args ) {
		$name  = self::OPTION . '[' . $args['key'] . ']';
		$value = self::get( $args['key'] );

		if ( 'textarea' === $args['type'] ) {
			printf( '<textarea name="%s" rows="3" class="large-text">%s</textarea>', esc_attr( $name ), esc_textarea( $value ) );
		} else {
			printf( '<input type="text" name="%s" value="%s" class="regular-text">', esc_attr( $name ), esc_attr( $value ) );
		}

		if ( $args['help'] ) {
			printf( '<p class="description">%s</p>', esc_html( $args['help'] ) );
		}
	}

	public static function sanitize( $input ) {
		$input = is_array( $input ) ? $input : array();
		$clean = array();

		$clean['badge_text']      = sanitize_text_field( $input['badge_text'] ?? '' );
		$clean['cart_error_text'] = sanitize_textarea_field( $input['cart_error_text'] ?? '' );

		$ids                       = array_filter( array_map( 'sanitize_key', explode( ',', $input['pickup_method_ids'] ?? '' ) ) );
		$clean['pickup_method_ids'] = implode( ', ', $ids );

		return $clean;
	}

	public static function render_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Sales Channel Rules', 'scsr' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( self::PAGE );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}