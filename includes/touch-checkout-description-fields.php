<?php
/**
 * TOUCH Checkout Description Field.
 *
 * Provides a Mobile Payments Gateway.
 *
 * @file     Touch Checkout Description Field
 * @category Mobile_Payment
 * @package  WooCommerce/Classes/Payment
 * @author   Sididou Corp <contact@sididoucorp.com>
 * @license  CC BY-NC 4.0 https://creativecommons.org/licenses/by-nc/4.0
 * @version  GIT:1.0.0
 * @link     https://sididoucorp.com/
 * php version 7.2.10
 */

add_filter('woocommerce_gateway_description', 'touch_description_fields', 20, 2);
add_action('woocommerce_checkout_process', 'touch_description_fields_validation');
add_action('woocommerce_checkout_update_order_meta', 'touch_checkout_update_order_meta', 10, 1);
add_action('woocommerce_admin_order_data_after_billing_address', 'touch_order_data_after_billing_address', 10, 1);
add_action('woocommerce_order_item_meta_end', 'touch_order_item_meta_end', 10, 3);

/**
 * Add description field for Touch Pay.
 *
 * @param string $description Touch Pay Description
 *
 * @param int    $payment_id  Touch Pay Payment ID
 *
 * @return array
 */
function Touch_Description_fields($description, $payment_id)
{

    if ('touch' !== $payment_id) {
        return $description;
    }

    ob_start();

    echo '<div style="display: block; width:300px; height:auto;">';
    echo '<img src="' . plugins_url('../assets/icon.png', __FILE__) . '">';


    woocommerce_form_field(
        'payment_number',
        array(
            'type' => 'text',
            'label' =>__('Payment Phone Number', 'touch-payments-woo'),
            'class' => array('form-row', 'form-row-wide'),
            'required' => true,
        )
    );

    echo '</div>';

    $description .= ob_get_clean();

    return $description;
}

/**
 * Setup general properties for the gateway.
 *
 * @param
 *
 * @return array
 */
function Touch_Description_Fields_validation()
{
    if ('touch' === $_POST['payment_method'] && ! isset($_POST['payment_number'])  || empty($_POST['payment_number'])) {
        wc_add_notice('Please enter a number that is to be billed', 'error');
    }
}

/**
 * Setup general properties for the gateway.
 *
 * @param
 *
 * @return array
 */
function Touch_Checkout_Update_Order_meta($order_id)
{
    if (isset($_POST['payment_number']) || ! empty($_POST['payment_number'])) {
        update_post_meta($order_id, 'payment_number', $_POST['payment_number']);
    }
}

/**
 * Setup general properties for the gateway.
 *
 * @param
 *
 * @return array
 */
function Touch_Order_Data_After_Billing_address($order)
{
    echo '<p><strong>' . __('Payment Phone Number:', 'touch-payments-woo') . '</strong><br>' . get_post_meta($order->get_id(), 'payment_number', true) . '</p>';
}

/**
 * Setup general properties for the gateway.
 *
 * @param
 *
 * @return array
 */
function Touch_Order_Item_Meta_end($item_id, $item, $order)
{
    echo '<p><strong>' . __('Payment Phone Number:', 'touch-payments-woo') . '</strong><br>' . get_post_meta($order->get_id(), 'payment_number', true) . '</p>';
}

