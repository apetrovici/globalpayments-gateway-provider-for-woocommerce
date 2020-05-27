<?php
/**
 * Returns information about the package and handles init.
 */

namespace GlobalPayments\WooCommercePaymentGatewayProvider;

use GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\gcOrder;
use GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\HeartlandGateway;
use GlobalPayments\WooCommercePaymentGatewayProvider\Gateways\HeartlandGatewayGift;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 */
class Plugin {
	/**
	 * Version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Init the package.
	 */
	public static function init() {
		load_plugin_textdomain( 'globalpayments-gateway-provider-for-woocommerce', false, self::get_path() . '/languages' );

		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		// probably want something cleaner than this, but for now:
		$gcthing = new gcOrder();
		
		            // Display gift cards used after checkout and on email
		add_filter('woocommerce_get_order_item_totals', array( $gcthing, 'addItemsToPostOrderDisplay'), PHP_INT_MAX, 2);
		add_action('woocommerce_checkout_order_processed', array( $gcthing, 'processGiftCardsZeroTotal'), PHP_INT_MAX, 2);

		add_filter( 'woocommerce_payment_gateways', array( self::class, 'add_gateways' ) );

		$HeartlandGateway = new HeartlandGateway();

		if ($HeartlandGateway->allow_gift_cards === true) {
			$HeartlandGatewayGift = new HeartlandGatewayGift($HeartlandGateway->get_backend_gateway_options()['secretApiKey']);

			add_action('wp_ajax_nopriv_use_gift_card', 					array($HeartlandGatewayGift, 'applyGiftCard'));
			add_action('wp_ajax_use_gift_card', 						array($HeartlandGatewayGift, 'applyGiftCard'));
			add_action('woocommerce_review_order_before_order_total', 	array($HeartlandGatewayGift, 'addGiftCards'));
			add_action('woocommerce_cart_totals_before_order_total', 	array($HeartlandGatewayGift, 'addGiftCards'));
			add_filter('woocommerce_calculated_total',                	array($HeartlandGatewayGift, 'updateOrderTotal'), 10, 2);
			add_action('wp_ajax_nopriv_remove_gift_card',             	array($HeartlandGatewayGift, 'removeGiftCard'));
			add_action('wp_ajax_remove_gift_card',                    	array($HeartlandGatewayGift, 'removeGiftCard'));

			$gcthing = new gcOrder();
			
			add_filter('woocommerce_get_order_item_totals', array( $gcthing, 'addItemsToPostOrderDisplay'), PHP_INT_MAX, 2);
			add_action('woocommerce_checkout_order_processed', array( $gcthing, 'processGiftCardsZeroTotal'), PHP_INT_MAX, 2);
		}
	}

	/**
	 * Appends our payment gateways to WooCommerce's known list
	 *
	 * @param string[] $methods
	 *
	 * @return string[]
	 */
	public static function add_gateways( $methods ) {
		$gateways = array(
			Gateways\HeartlandGateway::class,
			Gateways\GeniusGateway::class,
			Gateways\TransitGateway::class,
		);

		foreach ( $gateways as $gateway ) {
			$methods[] = $gateway;
		}

		return $methods;
	}

	/**
	 * Return the version of the package.
	 *
	 * @return string
	 */
	public static function get_version() {
		return self::VERSION;
	}

	/**
	 * Return the path to the package.
	 *
	 * @return string
	 */
	public static function get_path() {
		return dirname( __DIR__ );
	}

	public static function get_url( $path ) {
		return plugins_url( $path, dirname( __FILE__ ) );
	}
}
