<?php

/**
 * TOUCH Payments Gateway.
 *
 * Provides a Mobile Payments Gateway.
 *
 * @file     Touch Payment Class
 * @category Mobile_Payment
 * @package  WooCommerce/Classes/Payment
 * @author   Sididou Corp <contact@sididoucorp.com>
 * @license  CC BY-NC 4.0 https://creativecommons.org/licenses/by-nc/4.0
 * @class    WC_Gateway_Touch
 * @version  Realease:1.0.0
 * @extends  WC_Payment_Gateway
 * @link     https://sididoucorp.com/
 */

class WC_Gateway_Touch extends WC_Payment_Gateway
{

    /**
     * Constructor for the gateway.
     */
    public function __construct()
    {
        // Setup general properties.
        $this->setupProperties();

        // Load the settings.
        $this->initFormFields();
        // $this->initSettings();

        // Get settings.
        $this->title              = $this->get_option('title');
        $this->description        = $this->get_option('description');
        $this->partner_id         = $this->get_option('partner_id');
        $this->login_agent        = $this->get_option('login_agent');
        $this->password_agent     = $this->get_option('password_agent');
        $this->callback_url       = $this->get_option('callback_url');
        $this->service_code_mm       = $this->get_option('service_code_mm');
        $this->service_code_om       = $this->get_option('service_code_om');
        $this->instructions       = $this->get_option('instructions');
        $this->enable_for_methods = $this->get_option('enable_for_methods', array());
        $this->enable_for_virtual = $this->get_option('enable_for_virtual', 'yes') === 'yes';

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options'));
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
        add_filter('woocommerce_payment_complete_order_status', array($this, 'change_payment_complete_order_status'), 10, 3);

        // Customer Emails.
        add_action('woocommerce_email_before_order_table', array( $this, 'email_instructions'), 10, 3);
    }

    /**
     * Setup general properties for the gateway.
     *
     * @return array
     */
    protected function setupProperties()
    {
        $this->id                 = 'touch';
        $this->icon               = apply_filters('woocommerce_touch_icon', plugins_url('../assets/icon.png', __FILE__));
        $this->method_title       = __('Touch Mobile Payments', 'touch-payments-woo');
        $this->partner_id         = __('Add Partner ID', 'touch-payments-woo');
        $this->login_agent        = __('Add Agent Login', 'touch-payments-woo');
        $this->password_agent     = __('Add Agent Password', 'touch-payments-woo');
        $this->callback_url       = __('Add Callback URL', 'touch-payments-woo');
        $this->service_code_mm       = __('Add MTN Service Code', 'touch-payments-woo');
        $this->service_code_om       = __('Add Orange Service Code', 'touch-payments-woo');
        $this->method_description = __('', 'touch-payments-woo');
        $this->has_fields         = false;
    }

    /**
     * Initialise Gateway Settings Form Fields.
     *
     * @return array
     */
    public function initFormFields()
    {
        $this->form_fields = array(
            'enabled'            => array(
                'title'       => __('Enable/Disable', 'touch-payments-woo'),
                'label'       => __('Enable Touch Mobile Payments', 'touch-payments-woo'),
                'type'        => 'checkbox',
                'description' => '',
                'default'     => 'no',
            ),
            'title'           => array(
                'title'       => __('Title', 'touch-payments-woo'),
                'type'        => 'text',
                'description' => __('Touch Mobile Payment method description that the customer will see on your checkout.', 'touch-payments-woo'),
                'default'     => __('Touch Mobile Payments', 'touch-payments-woo'),
                'desc_tip'    => true,
            ),
            'partner_id'      => array(
                'title'       => __('Partner ID', 'touch-payments-woo'),
                'type'        => 'text',
                'description' => __('Add your Partner ID', 'touch-payments-woo'),
                'desc_tip'    => true,
            ),
            'login_agent'     => array(
                'title'       => __('Agent Login', 'touch-payments-woo'),
                'type'        => 'text',
                'description' => __('Add your Agent Login', 'touch-payments-woo'),
                'desc_tip'    => true,
            ),
            'password_agent'  => array(
                'title'       => __('Agent Password', 'touch-payments-woo'),
                'type'        => 'text',
                'description' => __('Add your Agent Password', 'touch-payments-woo'),
                'desc_tip'    => true,
            ),
            'callback_url'  => array(
                'title'       => __('Callback URL', 'touch-payments-woo'),
                'type'        => 'textarea',
                'description' => __('Add your Callback URL', 'touch-payments-woo'),
                'desc_tip'    => true,
            ),
            'service_code_mm'  => array(
                'title'       => __('MTN Service Code', 'touch-payments-woo'),
                'type'        => 'text',
                'description' => __('Add MTN Service Code', 'touch-payments-woo'),
                'desc_tip'    => true,
            ),
            'service_code_om'  => array(
                'title'       => __('Orange Service Code', 'touch-payments-woo'),
                'type'        => 'text',
                'description' => __('Add Orange Service Code', 'touch-payments-woo'),
                'desc_tip'    => true,
            ),
            'description'     => array(
                'title'       => __('Description', 'touch-payments-woo'),
                'type'        => 'textarea',
                'description' => __('Touch Mobile Payment support Orange Money, Mobile Money and YUP.', 'touch-payments-woo'),
                'default'     => __('Touch Mobile Payments.', 'touch-payments-woo'),
                'desc_tip'    => true,
            ),
            'instructions'    => array(
                'title'       => __('Instructions', 'touch-payments-woo'),
                'type'        => 'textarea',
                'description' => __('Instructions that will be added to the thank you page.', 'touch-payments-woo'),
                'default'     => __('Validate the Payment on your phone.', 'touch-payments-woo'),
                'desc_tip'    => true,
            ),
            'enable_for_methods'    => array(
                'title'             => __('Enable for shipping methods', 'touch-payments-woo'),
                'type'              => 'multiselect',
                'class'             => 'wc-enhanced-select',
                'css'               => 'width: 400px;',
                'default'           => '',
                'description'       => __('If touch is only available for certain methods, set it up here. Leave blank to enable for all methods.', 'touch-payments-woo'),
                'options'           => $this-> _loadShippingMethodOptions(),
                'desc_tip'          => true,
                'custom_attributes' => array(
                'data-placeholder'  => __('Select shipping methods', 'touch-payments-woo'),
                ),
            ),
            'enable_for_virtual' => array(
                'title'   => __('Accept for virtual orders', 'touch-payments-woo'),
                'label'   => __('Accept touch if the order is virtual', 'touch-payments-woo'),
                'type'    => 'checkbox',
                'default' => 'yes',
            ),
        );
    }

    /**
     * Check If The Gateway Is Available For Use.
     *
     * @return bool
     */
    public function isAvailable()
    {
        $order          = null;
        $needs_shipping = false;

        // Test if shipping is needed first.
        if (WC()->cart && WC()->cart->needs_shipping()) {
            $needs_shipping = true;
        } elseif (is_page(wc_get_page_id('checkout')) && 0 < get_query_var('order-pay') ) {
            $order_id = absint(get_query_var('order-pay'));
            $order    = wc_get_order($order_id);

            // Test if order needs shipping.
            if (0 < count($order->get_items())) {
                foreach ($order->get_items() as $item) {
                    $_product = $item->get_product();
                    if ($_product && $_product->needs_shipping()) {
                        $needs_shipping = true;
                        break;
                    }
                }
            }
        }

        $needs_shipping = apply_filters('woocommerce_cart_needs_shipping', $needs_shipping);

        // Virtual order, with virtual disabled.
        if (! $this->enable_for_virtual && ! $needs_shipping) {
            return false;
        }

        // Only apply if all packages are being shipped via chosen method, or order is virtual.
        if (! empty($this->enable_for_methods) && $needs_shipping) {
            $order_shipping_items            = is_object($order) ? $order->get_shipping_methods() : false;
            $chosen_shipping_methods_session = WC()->session->get('chosen_shipping_methods');

            if ($order_shipping_items) {
                $canonical_rate_ids = $this->_getCanonicalOrderShippingItemRateIds($order_shipping_items);
            } else {
                $canonical_rate_ids = $this->_getCanonicalPackageRateIds($chosen_shipping_methods_session);
            }

            if (! count($this->get_matching_rates($canonical_rate_ids))) {
                return false;
            }
        }

        return parent::isAvailable();
    }

    /**
     * Checks to see whether or not the admin settings are being accessed by the current request.
     *
     * @return bool
     */
    private function _isAccessingSettings()
    {
        if (isAdmin()) {
            // phpcs:disable WordPress.Security.NonceVerification
            if (! isset($_REQUEST['page']) || 'wc-settings' !== $_REQUEST['page']) {
                return false;
            }
            if (! isset($_REQUEST['tab']) || 'checkout' !== $_REQUEST['tab']) {
                return false;
            }
            if (! isset($_REQUEST['section']) || 'touch' !== $_REQUEST['section']) {
                return false;
            }
            // phpcs:enable WordPress.Security.NonceVerification

            return true;
        }

        return false;
    }

    /**
     * Loads all of the shipping method options for the enable_for_methods field.
     *
     * @return array
     */
    private function _loadShippingMethodOptions()
    {
        // Since this is expensive, we only want to do it if we're actually on the settings page.
/*         if (! $this->is_accessing_settings()) {
            return array();
        } */

        $data_store = WC_Data_Store::load('shipping-zone');
        $raw_zones  = $data_store->get_zones();

        foreach ($raw_zones as $raw_zone) {
            $zones[] = new WC_Shipping_Zone($raw_zone);
        }

        $zones[] = new WC_Shipping_Zone(0);

        $options = array();
        foreach ( WC()->shipping()->load_shipping_methods() as $method ) {

            $options[ $method->get_method_title() ] = array();

            // Translators: %1$s shipping method name.
            $options[ $method->get_method_title() ][ $method->id ] = sprintf(__('Any &quot;%1$s&quot; method', 'touch-payments-woo'), $method->get_method_title());

            foreach ( $zones as $zone ) {

                $shipping_method_instances = $zone->get_shipping_methods();

                foreach ( $shipping_method_instances as $shipping_method_instance_id => $shipping_method_instance ) {

                    if ($shipping_method_instance->id !== $method->id) {
                        continue;
                    }

                    $option_id = $shipping_method_instance->get_rate_id();

                    // Translators: %1$s shipping method title, %2$s shipping method id.
                    $option_instance_title = sprintf(__('%1$s (#%2$s)', 'touch-payments-woo'), $shipping_method_instance->get_title(), $shipping_method_instance_id);

                    // Translators: %1$s zone name, %2$s shipping method instance name.
                    $option_title = sprintf(__('%1$s &ndash; %2$s', 'touch-payments-woo'), $zone->get_id() ? $zone->get_zone_name() : __('Other locations', 'touch-payments-woo'), $option_instance_title);

                    $options[ $method->get_method_title() ][ $option_id ] = $option_title;
                }
            }
        }

        return $options;
    }

    /**
     * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
     *
     * @param array Array of WC_Order_Item_Shipping objects $order_shipping_items .
     *
     * @return array $canonical_rate_ids    Rate IDs in a canonical format.
     */
    private function _getCanonicalOrderShippingItemRateIds($order_shipping_items)
    {

        $canonical_rate_ids = array();

        foreach ( $order_shipping_items as $order_shipping_item ) {
            $canonical_rate_ids[] = $order_shipping_item->get_method_id() . ':' . $order_shipping_item->get_instance_id();
        }

        return $canonical_rate_ids;
    }

    /**
     * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
     *
     * @param array $chosen_package_rate_ids Rate IDs as generated by shipping methods. Can be anything if a shipping method doesn't honor WC conventions.
     *
     * @return array
     */
    private function _getCanonicalPackageRateIds($chosen_package_rate_ids)
    {

        $shipping_packages  = WC()->shipping()->get_packages();
        $canonical_rate_ids = array();

        if (! empty($chosen_package_rate_ids) && is_array($chosen_package_rate_ids)) {
            foreach ($chosen_package_rate_ids as $package_key => $chosen_package_rate_id) {
                if (! empty($shipping_packages[ $package_key ]['rates'][ $chosen_package_rate_id ])) {
                    $chosen_rate          = $shipping_packages[ $package_key ]['rates'][ $chosen_package_rate_id ];
                    $canonical_rate_ids[] = $chosen_rate->get_method_id() . ':' . $chosen_rate->get_instance_id();
                }
            }
        }

        return $canonical_rate_ids;
    }

    /**
     * Indicates whether a rate exists in an array of canonically-formatted rate IDs that activates this gateway.
     *
     * @param array $rate_ids Rate ids to check.
     *
     * @since 3.4.0
     *
     * @return boolean
     */
    private function _getMatchingRates($rate_ids)
    {
        // First, match entries in 'method_id:instance_id' format. Then, match entries in 'method_id' format by stripping off the instance ID from the candidates.
        return array_unique(array_merge(array_intersect($this->enable_for_methods, $rate_ids), array_intersect($this->enable_for_methods, array_unique(array_map('wc_get_string_before_colon', $rate_ids)))));
    }

    /**
     * Process the payment and return the result.
     *
     * @param int $order_id Order ID.
     *
     * @return array
     */
    public function processPayment($order_id)
    {
        $order = wc_get_order($order_id);

        if ($order->get_total() > 0) {
            $this->_touchPaymentProcessing($order_id, $order);
        }
    }

    /**
     * Process the payment using TOUCH PAY API and return the result.
     *
     * @param int $order Order
     *
     * @return array
     */
    private function _touchPaymentProcessing($order_id, $order)
    {

        $total = intval($order->get_total());
        var_dump($total);

        $phone = esc_attr( $_POST['payment_number'] );

        if(substr($phone, 0, 2)== '66' || substr($phone, 0, 5)== '22466'){
            $service_code = $this->service_code_mm;
            return $service_code;

        }elseif (substr($phone, 0, 2)== '62' || substr($phone, 0, 5)== '22462') {
            $service_code = $this->service_code_om;
			return $service_code;
			}



			 $url = 'https://dev-api.gutouch.com/dist/api/touchpayapi/v1/' . $partner_id . '/transaction?loginAgent=' . $login_agent.'&passwordAgent=' . $password_agent;
        //var_dump($url);

/*             $body = array(
            'idFromClient'   =>$order_id,
            'additionnalInfos' => array(

            ),
            'amount'         =>$total,
            'callback'       =>$this->callback_url,
            'recipientNumber'=>$phone,
            'serviceCode'    =>$service_code
        ); */

        $body = array(
            'idFromClient'   => '7000005123114011100104',
            'additionnalInfos' => array(
                'recipientEmail'=> 'junior@gmail.com',
                'recipientFirstName' => 'junior',
                'recipientLastName' => 'ndiaye',
                'destinataire' => '620631099'
            ),
            'amount'         =>$total,
            'callback'       =>'https://myshippingpack.azurewebsites.net/API/WebServicePayments.asmx/callbackInTouch',
            'recipientNumber'=>'620631099',
            'serviceCode'    =>'GN_PAIEMENTMARCHAND_OM_TP'
        );

        $args = array(
            'body'    => $body,
            'method'  => 'PUT',
            'timeout' => 5
        );

        $response = wp_remote_request($url, $args);

		var_dump($response);
		return;

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            return "Something went wrong: $error_message";
        }

        if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
            $order->update_status( apply_filters('woocommerce_touch_process_payment_order_status', $order->has_downloadable_item() ? 'wc-invoiced' : 'processing', $order ), __('Payments pending.', 'touch-payments-woo'));
        }

        if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
            $response_body = wp_remote_retrieve_body( $response );
            var_dump($response_body['message']);
            if ('Thank you! Your payment was successful' === $response_body['message'] ) {
                $order->payment_complete();

                // Remove cart.
                WC()->cart->empty_cart();

                // Return thankyou redirect.
                return array(
                    'result'   => 'success',
                    'redirect' => $this->get_return_url( $order ),
                );
            }
        }
}

    /**
     * Output for the order received page.
     */
public function thankyou_page() {
    if ( $this->instructions ) {
            echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ));
    }
}

    /**
     * Change payment complete order status to completed for touch orders.
     *
     * @since  3.1.0
     * @param  string         $status Current order status.
     * @param  int            $order_id Order ID.
     * @param  WC_Order|false $order Order object.
     * @return string
     */
public function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {
    if ($order && 'touch' === $order->get_payment_method()) {
            $status = 'completed';
    }
        return $status;
}

    /**
     * Add content to the WC emails.
     *
     * @param WC_Order $order Order object.
     * @param bool     $sent_to_admin  Sent to admin.
     * @param bool     $plain_text Email format: plain text or HTML.
     */
public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
    if ($this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method()) {
            echo wp_kses_post( wpautop( wptexturize($this->instructions)) . PHP_EOL);
    }
}
}
