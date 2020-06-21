<?php
/**
 * Plugin Name: TOUCH Payments Gateway
 * Plugin URI: https://sididoucorp.com
 * Author: Sididou Corp
 * Author URI: https://sididoucorp.com
 * Description: Local Payments Gateway for mobile.
 * Version: 0.1.0
 * License: GPL2
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: touch-payments-woo
 *
 * Class WC_Gateway_Touch file.
 *
 * @package WooCommerce\Touch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

add_action( 'plugins_loaded', 'touch_payment_init', 11 );
add_filter( 'woocommerce_currencies', 'sididoucorp_add_currencies' );
add_filter( 'woocommerce_currency_symbol', 'sididoucorp_add_currencies_symbol', 10, 2 );
add_filter( 'woocommerce_payment_gateways', 'add_to_woo_touch_payment_gateway');

function touch_payment_init() {
    if( class_exists( 'WC_Payment_Gateway' ) ) {
		require_once plugin_dir_path( __FILE__ ) . '/includes/class-wc-payment-gateway-touch.php';
		require_once plugin_dir_path( __FILE__ ) . '/includes/touch-order-statuses.php';
		require_once plugin_dir_path( __FILE__ ) . '/includes/touch-checkout-description-fields.php';
	}
}

function add_to_woo_touch_payment_gateway( $gateways ) {
    $gateways[] = 'WC_Gateway_Touch';
    return $gateways;
}

function sididoucorp_add_currencies( $currencies ) {
	$currencies['GNF'] = __( 'Guinean Franc', 'touch-payments-woo' );
	return $currencies;
}

function sididoucorp_add_currencies_symbol( $currency_symbol, $currency ) {
	switch ( $currency ) {
		case 'GNF':
			$currency_symbol = 'GNF';
		break;
	}
	return $currency_symbol;
}

