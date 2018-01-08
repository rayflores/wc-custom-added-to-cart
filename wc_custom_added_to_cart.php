<?php 
/*
* Plugin Name: WooCommerce Custom Added To Cart
* Plugin URI:  https://www.rayflores.com/plugins/wc-catc/
* Description: Adds a Custom Added To Cart Fly-In with image to shop pages.
* Author:      Ray Flores
* Author URI:  https://www.rayflores.com
* Version:     1.1
*/
class WC_CATC {
	/**
 	 * Option key, and option page slug
 	 * @var string
 	 */
	private $key = 'wc_catc_options';
	/**
 	 * Options page metabox id
 	 * @var string
 	 */
	private $metabox_id = 'wc_catc_metabox';
	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = 'WooCommerce Custom Added To Cart';
	/**
	 * Holds an instance of the object
	 *
	 * @var Myprefix_Admin
	 **/
	private static $instance = null;
	
	/**
	 * Constructor
	 * @since 0.1.0
	 */
	private function __construct() {
		// Set our title
		$this->title = __( 'WC CATC', 'wc-catc' );
		//  enqueue scripts/styles
		add_action( 'wp_enqueue_scripts', array($this, 'wc_catc_enqueue_scripts'), 9999 );
		// Wrap notice text
		add_filter('woocommerce_add_notice', array($this, 'wc_catc_custom_add_notice'), 10, 1);
		// Wrap success notification text
		add_filter('woocommerce_add_success', array($this, 'wc_catc_custom_add_success'), 10, 1);
		// Wrap error notification taxt
		add_filter('woocommerce_add_error', array($this, 'wc_catc_custom_add_error'), 10, 1);
		// Add custom coupon wrappers
		add_filter('woocommerce_coupon_error', array($this, 'wc_catc_custom_coupon_error'), 10, 2);
		// Add image to added to cart notice 
		add_filter('wc_add_to_cart_message_html', array($this, 'wc_catc_custom_add_to_cart_message'), 10, 2);
		// Whitelist style for wp_kses_post()
		add_action('init', array( $this, 'wc_catc_html_tags_code'), 10);
		
		// testing to see what is being submitted
		//add_action( 'woocommerce_before_main_content', array( $this, 'see_whats_in_here' ),10, 2 );
	}
	public function wc_catc_custom_add_error( $message ){
		$options = get_option( $this->key );
		return '<style>.woocommerce-error{ background:'. $options['wc_catc_options_message_bg_color'].'!important; } </style><span>'.$message.'</span>';
	}
	public function wc_catc_custom_coupon_error( $err, $err_code ){
		if ( $err_code > 0 ){
			$custom_err = '<i class="fa fa-times-circle-o fa-3" aria-hidden="true"></i>' .$err;
		}
		return $custom_err;
	}
	
	public function wc_catc_enqueue_scripts(){
		wp_register_script( 'wc-catc-script', plugins_url( 'js/wc_catc.js', __FILE__), array('jQuery'), false, false );
		wp_register_script( 'wc-catc-fonta', '//use.fontawesome.com/bb21afade5.js' );
		wp_register_style( 'wc-catc-style', plugins_url('css/wc_catc.css', __FILE__) );

		wp_enqueue_style( 'wc-catc-style' );		
		wp_enqueue_script( 'wc-catc-fonta' );
		wp_enqueue_script( 'wc-catc-script' );
		
	}
	public function wc_catc_html_tags_code() {
	  global $allowedposttags;
		$allowedposttags["style"] = array();
	}
	
	public function wc_catc_custom_add_to_cart_message( $message, $product_id) {
		$options = get_option( $this->key );
		$img = wp_get_attachment_image_src( get_post_thumbnail_id(key($product_id)), 'shop_catalog' );
		$img_url = $img[0];

		$added_to_cart = '<div class="product_notification_wrapper"><style type="text/css">
		.woocommerce-message, .woocommerce-error 
		{
			background:'.$options['wc_catc_options_message_bg_color'].'!important;
		}
		.product_notification_background 
		{ 
			background:url('.$img_url.');
		}
			</style>
			 <div class="product_notification_background"></div><div class="product_notification_text">'.$message.'</div></div>';
		return $added_to_cart;
	}
	public function wc_catc_custom_add_success($message) {
		$options = get_option( $this->key );
		if (strpos($message, 'product_notification_background') === false){
			return '<div class="woocommerce-message-wrapper" style="background:'.$options['wc_catc_options_message_bg_color'].'!important;"><span class="success-icon"><i class="fa fa-shopping-cart"></i></span><span class="notice_text">'. $message .'</span></div>';
		} else {
			return $message;
		}
	}
	public function wc_catc_custom_add_notice($message) {
		$options = get_option( $this->key );
		if (strpos($message, 'product_notification_background') === false){
			return '<style>.woocommerce-info{background:'.$options['wc_catc_options_message_bg_color'].'!important;}</style><div class="woocommerce-message-wrapper" style="background:'.$options['wc_catc_options_message_bg_color'].'!important;"><i class="fa fa-check fa-3" aria-hidden="true"></i></span><span class="notice_text">'. $message .'</span></div>';
		} else {
			// do nothing
		}
	}


	/**
	 * Returns the running object
	 *
	 * @return Myprefix_Admin
	 **/
	public static function get_instance() {
		if( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->hooks();
		}
		return self::$instance;
	}
		/**
	 * Initiate our hooks
	 * @since 0.1.0
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		// enqueue admin scripts/styles
		add_action( 'admin_enqueue_scripts', array($this, 'wc_catc_admin_enqueue_scripts') );
	}
	public function wc_catc_admin_enqueue_scripts(){
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'admin-wc-catc-script', plugins_url('js/admin_wc_catc.js', __FILE__), array( 'iris'), false, 2);
	}
	/**
	 * Register our setting to WP
	 * @since  0.1.0
	 */
	public function init() {
		register_setting( $this->key, $this->key );

		add_settings_section(
			'wc_catc_options_section', 
			__( 'WooCommerce Custom Added To Cart Settings', 'wc-catc' ), 
			array( $this, 'wc_catc_options_section_callback'), 
			$this->key
		);

		add_settings_field( 
			'wc_catc_options_message_bg_color', 
			__( 'Added To Cart Background Color', 'wc-catc' ), 
			array( $this, 'wc_catc_options_color_field_render'), 
			$this->key, 
			'wc_catc_options_section' 
		);
		
	}
	/**
	 * Register our field to WP
	 * @since  0.1.0
	 */
	public function wc_catc_options_color_field_render(  ) {  

		$options = get_option( $this->key );
		$val = ( isset( $options['wc_catc_options_message_bg_color'] ) ) ? $options['wc_catc_options_message_bg_color'] : '';
		?>
		<input type="text" name="wc_catc_options[wc_catc_options_message_bg_color]" id="wc_catc_options_color_field" class="wp-color-picker-field" value="<?php echo $val; ?>"/>
		<?php

	}
	/**
	 * Register our settings description to WP
	 * @since  0.1.0
	 */
	public function wc_catc_options_section_callback(  ) { 

		echo __( 'Adjust the background color of the added to cart box.', 'wc-catc' );

	}
		/**
	 * Add menu options page
	 * @since 0.1.0
	 */
	public function add_options_page() {
		$this->options_page = add_menu_page( $this->title, $this->title, 'manage_options', $this->key, array( $this, 'admin_page_display' ) );
	}
	/**
	 * Admin page markup. Mostly handled by CMB2
	 * @since  0.1.0
	 */
	public function admin_page_display() {
		?>
		<div class="wrap wc_catc-options-page <?php echo $this->key; ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
				<form action='options.php' method='post'>
					<?php
						settings_fields( $this->key );
						do_settings_sections( $this->key );
						submit_button();
					?>
				</form>
		</div>
		<?php
	}

}
/**
 * Helper function to get/return the Myprefix_Admin object
 * @since  0.1.0
 * @return Myprefix_Admin object
 */
function wc_catc() {
	return WC_CATC::get_instance();
}

// fire it up!
wc_catc();