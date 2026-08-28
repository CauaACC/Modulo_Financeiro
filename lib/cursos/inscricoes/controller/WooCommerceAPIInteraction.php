<?php

class WooCommerceAPIInteraction {
    private $consumer_key;
    private $consumer_secret;
    private $api_base_url;

    public function __construct($consumer_key, $consumer_secret, $api_base_url) {
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->api_base_url = $api_base_url;
    }

    public function createOrder($order_data) {
        $response = wp_safe_remote_post($this->api_base_url . '/wp-json/wc/v3/orders', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->consumer_key . ':' . $this->consumer_secret),
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($order_data),
        ]);

        if (is_wp_error($response)) {
            return ['error' => $response->get_error_message()];
        } else {
            $order_data = json_decode(wp_remote_retrieve_body($response), true);
            $checkout_url = $order_data['_links']['checkout'][0]['href'];
            $order_code = $order_data['number'];
            
            return [
                'checkout_url' => $checkout_url,
                'order_code' => $order_code
            ];
        }
    }
    public function getOrderStatus($order_id) {
        $response = wp_safe_remote_get($this->api_base_url . '/wp-json/wc/v3/orders/' . $order_id, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->consumer_key . ':' . $this->consumer_secret),
            ],
        ]);

        if (is_wp_error($response)) {
            return ['error' => $response->get_error_message()];
        } else {
            $order_data = json_decode(wp_remote_retrieve_body($response), true);
            $order_status = $order_data['status'];
            $payment_status = $order_data['payment_status'];
            
            return [
                'order_status' => $order_status,
                'payment_status' => $payment_status
            ];
        }
    }    
}

