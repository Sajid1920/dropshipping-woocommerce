<?php
/**
 * Common functions class for Knawat Dropshipping Woocommerce.
 *
 * @link       http://knawat.com/
 * @since      1.0.0
 *
 * @package    Knawat_Dropshipping_Woocommerce
 * @subpackage Knawat_Dropshipping_Woocommerce/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Knawat_Dropshipping_Woocommerce_Common {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Do anything Here. 
	}

	/**
	 * Check is WooCommerce Activate or not.
	 *
	 * @since    1.0.0
	 * @return 	 boolean
	 */
	public function knawat_dropshipwc_is_woocommerce_activated() {
		if( !function_exists( 'is_plugin_active' ) ){
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Check is WooCommerce Activate or not.
	 *
	 * @since    1.0.0
	 * @return 	 boolean
	 */
	public function knawat_dropshipwc_is_woomulti_currency_activated() {
		if( !function_exists( 'is_plugin_active' ) ){
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active( 'woocommerce-currency-switcher/index.php' ) || is_plugin_active( 'currency-switcher-woocommerce/currency-switcher-woocommerce.php' ) || is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) || is_plugin_active( 'woo-multi-currency/woo-multi-currency.php' ) ) {
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Check order contains knawat products or not.
	 *
	 * @since    1.0.0
	 * @return 	 boolean
	 */
	public function is_knawat_order( $order_id ) {
		if( empty( $order_id ) ){ return false; }

		$order = wc_get_order( $order_id );
		$items = $order->get_items();
		foreach ( $items as $item ) {
			if ( is_a( $item, 'WC_Order_Item_Product' ) ) {
				$product_id = $item->get_product_id();
				$dropshipping = get_post_meta( $product_id, 'dropshipping', true );
				if( $dropshipping == 'knawat' ){
					return true;
				}
			}
		}
		return false;
	}
}

/*
 * Woocommerce WebHooks Utilities
 */
add_filter( 'http_request_args', function( $args ) {
    $args['reject_unsafe_urls'] = false;

    return $args;
});

/*
 * Store is contected to knawat.com or not
 */
function knawat_dropshipwc_is_connected(){
	global $wpdb, $knawat_dropshipwc;

	if( $knawat_dropshipwc->is_woocommerce_activated() ){
		$t_api_keys = $wpdb->prefix.'woocommerce_api_keys';
		$api_key_query = "SELECT COUNT( key_id ) as count FROM {$t_api_keys} WHERE `description` LIKE '%Knawat - API%'";
		$key_count = $wpdb->get_var( $api_key_query );
		if( $key_count > 0 ){
			return true;
		}
	}

	return false;
}

/**
 * Get Defined DropShippers.
 *
 * @access public
 * @since 1.2.0
 * @return array
 */
function knawat_dropshipwc_get_dropshippers() {
	global $knawat_dropshipwc;
    $dropshippers = $knawat_dropshipwc->get_dropshippers();
    if( empty( $dropshippers ) && !is_array( $dropshippers ) ){
		$dropshippers = array();
    }
    return $dropshippers;
}

/**
 * Get Knawat DropShipping Options.
 *
 * @access public
 * @since 2.0.0
 * @return array
 */
function knawat_dropshipwc_get_options( $key = '' ) {
	$knawat_options = get_option( KNAWAT_DROPWC_OPTIONS, array() );
	if ( $key != '' ) {
		$knawat_options = isset( $knawat_options[$key] ) ? $knawat_options[$key] : '';
	}
	return $knawat_options;
}

/**
 * Update Knawat DropShipping Options.
 *
 * @access public
 * @since 2.0.0
 * @return array
 */
function knawat_dropshipwc_update_options( $knawat_options ) {
	update_option( KNAWAT_DROPWC_OPTIONS, $knawat_options );
}

/**
 * Get Knawat MP Consumer Keys
 *
 * @return array Knawat MP Consumer Keys
 */
function knawat_dropshipwc_get_consumerkeys(){
	$consumer_keys  = array();
	$knawat_options = knawat_dropshipwc_get_options();
	if( isset( $knawat_options['mp_consumer_key'] ) && !empty( $knawat_options['mp_consumer_key'] ) ){
		$consumer_keys['consumer_key'] = $knawat_options['mp_consumer_key'];
	}
	if( isset( $knawat_options['mp_consumer_secret'] ) && !empty( $knawat_options['mp_consumer_secret'] ) ){
		$consumer_keys['consumer_secret'] = $knawat_options['mp_consumer_secret'];
	}
	return $consumer_keys;
}

/**
 * Get Knawat Compatible Active plugins
 *
 * @return array Knawat Compatible plugins with status.
 */
function knawat_dropshipwc_get_activated_plugins(){
	$active_plugins = array(
		'featured-image-by-url' =>false,
		'woocommerce-currency-switcher' =>false,
		'qtranslate-x' =>false		
	);

	$blog_plugins = get_option( 'active_plugins', array() );
	$site_plugins = is_multisite() ? (array) maybe_unserialize( get_site_option('active_sitewide_plugins' ) ) : array();
	
	// Check if qTranslate X is activated
	if ( in_array( 'qtranslate-x/qtranslate.php', $blog_plugins ) || isset( $site_plugins['qtranslate-x/qtranslate.php'] ) ) {
		$active_plugins['qtranslate-x'] = true;
	}

	// Check if Featued image by URL is activated
	if ( in_array( 'featured-image-by-url/featured-image-by-url.php', $blog_plugins ) || isset( $site_plugins['featured-image-by-url/featured-image-by-url.php'] ) ) {
		$active_plugins['featured-image-by-url'] = true;
	}

	// Check if WooCommerce Currency Switcher is activated
	if ( in_array( 'woocommerce-currency-switcher/index.php', $blog_plugins ) || isset( $site_plugins['woocommerce-currency-switcher/index.php'] ) ) {
		$active_plugins['woocommerce-currency-switcher'] = true;
	}

	return $active_plugins;
}