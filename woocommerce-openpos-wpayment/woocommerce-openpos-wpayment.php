<?php
/*
Plugin Name: Woocommerce OpenPos Online Payment
Plugin URI: http://openswatch.com
Description: Use online payment on website on POS panel
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.1
WC requires at least: 2.6
Text Domain: openpos-wpayment
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

if(!function_exists('payment_op_login_format_payment_data'))
{
    function payment_op_login_format_payment_data($payment_method_data,$methods){
        $formatted_payment = $payment_method_data;
        $onlinePayments = array();

        $payment_gateways = WC()->payment_gateways->payment_gateways();

        foreach ( $payment_gateways as $code => $gateway ) {

            $enabled = $gateway->get_option( 'enabled', 'no' );
            if($enabled == 'yes')
            {
                $onlinePayments[] = $code;
            }

        }

        if(!empty($onlinePayments) && in_array($formatted_payment['code'],$onlinePayments) )
        {
            $formatted_payment['type'] = 'online';
            //$formatted_payment['type'] = 'chip_pin';
            $formatted_payment['online_type'] = 'external'; // direct : to instance submit and check
            $formatted_payment['status_url'] = 'http://localhost.com/test/payment.php';

                /*
                 * 'type' => $type,
                'hasRef' => true,
                'partial' => $allow_partial_payment,
                'description' => '',
                'online_type' => $online_checkout_type
                */
        }
        return $formatted_payment;
    }
}
add_filter('op_login_format_payment_data','payment_op_login_format_payment_data',10,2);

function payment_op_before_woocommerce_pay(){
    global $wp;
    if ( ! empty( $wp->query_vars['order-pay'] ) ) {

      
        $order_id = absint( $wp->query_vars['order-pay'] );
        
        if ( isset( $_GET['op_pay_for_order'], $_GET['key'] ) && $order_id ) { 
           
            try {
				$order_key          = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : ''; // WPCS: input var ok, CSRF ok.
				$order              = wc_get_order( $order_id );
				$hold_stock_minutes = (int) get_option( 'woocommerce_hold_stock_minutes', 0 );

				// Order or payment link is invalid.
				if ( ! $order || $order->get_id() !== $order_id || ! hash_equals( $order->get_order_key(), $order_key ) ) {
					throw new Exception( __( 'Sorry, this order is invalid and cannot be paid for.', 'woocommerce' ) );
				}

				

				// Add notice if logged in customer is trying to pay for guest order.
				if ( ! $order->get_user_id() && is_user_logged_in() ) {
					// If order has does not have same billing email then current logged in user then show warning.
					if ( $order->get_billing_email() !== wp_get_current_user()->user_email ) {
						wc_print_notice( __( 'You are paying for a guest order. Please continue with payment only if you recognize this order.', 'woocommerce' ), 'error' );
					}
				}

				

				// Does not need payment.
				if ( ! $order->needs_payment() ) {
					/* translators: %s: order status */
					throw new Exception( sprintf( __( 'This order&rsquo;s status is &ldquo;%s&rdquo;&mdash;it cannot be paid for. Please contact us if you need assistance.', 'woocommerce' ), wc_get_order_status_name( $order->get_status() ) ) );
				}

				// Ensure order items are still stocked if paying for a failed order. Pending orders do not need this check because stock is held.
				if ( ! $order->has_status( wc_get_is_pending_statuses() ) ) {
					$quantities = array();

					foreach ( $order->get_items() as $item_key => $item ) {
						if ( $item && is_callable( array( $item, 'get_product' ) ) ) {
							$product = $item->get_product();

							if ( ! $product ) {
								continue;
							}

							$quantities[ $product->get_stock_managed_by_id() ] = isset( $quantities[ $product->get_stock_managed_by_id() ] ) ? $quantities[ $product->get_stock_managed_by_id() ] + $item->get_quantity() : $item->get_quantity();
						}
					}

					foreach ( $order->get_items() as $item_key => $item ) {
						if ( $item && is_callable( array( $item, 'get_product' ) ) ) {
							$product = $item->get_product();

							if ( ! $product ) {
								continue;
							}

							if ( ! apply_filters( 'woocommerce_pay_order_product_in_stock', $product->is_in_stock(), $product, $order ) ) {
								/* translators: %s: product name */
								throw new Exception( sprintf( __( 'Sorry, "%s" is no longer in stock so this order cannot be paid for. We apologize for any inconvenience caused.', 'woocommerce' ), $product->get_name() ) );
							}

							// We only need to check products managing stock, with a limited stock qty.
							if ( ! $product->managing_stock() || $product->backorders_allowed() ) {
								continue;
							}

							// Check stock based on all items in the cart and consider any held stock within pending orders.
							$held_stock     = ( $hold_stock_minutes > 0 ) ? wc_get_held_stock_quantity( $product, $order->get_id() ) : 0;
							$required_stock = $quantities[ $product->get_stock_managed_by_id() ];

							if ( ! apply_filters( 'woocommerce_pay_order_product_has_enough_stock', ( $product->get_stock_quantity() >= ( $held_stock + $required_stock ) ), $product, $order ) ) {
								/* translators: 1: product name 2: quantity in stock */
								throw new Exception( sprintf( __( 'Sorry, we do not have enough "%1$s" in stock to fulfill your order (%2$s available). We apologize for any inconvenience caused.', 'woocommerce' ), $product->get_name(), wc_format_stock_quantity_for_display( $product->get_stock_quantity() - $held_stock, $product ) ) );
							}
						}
					}
				}

				WC()->customer->set_props(
					array(
						'billing_country'  => $order->get_billing_country() ? $order->get_billing_country() : null,
						'billing_state'    => $order->get_billing_state() ? $order->get_billing_state() : null,
						'billing_postcode' => $order->get_billing_postcode() ? $order->get_billing_postcode() : null,
					)
				);
				WC()->customer->save();

				$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

				if ( count( $available_gateways ) ) {
					current( $available_gateways )->set_current();
				}

				wc_get_template(
					'checkout/form-pay.php',
					array(
						'order'              => $order,
						'available_gateways' => $available_gateways,
						'order_button_text'  => apply_filters( 'woocommerce_pay_order_button_text', __( 'Pay for order', 'woocommerce' ) ),
					)
				);

			} catch ( Exception $e ) {
				wc_print_notice( $e->getMessage(), 'error' );
			}
            return;

        }
        

    }
    
}
add_action('before_woocommerce_pay','payment_op_before_woocommerce_pay');
function custom_op_woocommerce_get_checkout_payment_url($pay_url, $current){
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
    $pos_action = isset($_REQUEST['pos_action']) ? $_REQUEST['pos_action'] : '';
    if($action == 'openpos' && $pos_action == 'pending-order')
    {
       
        $pay_url = str_replace('pay_for_order','op_pay_for_order',$pay_url);
    }

    return $pay_url;
}
add_filter('woocommerce_get_checkout_payment_url','custom_op_woocommerce_get_checkout_payment_url',10,2);
