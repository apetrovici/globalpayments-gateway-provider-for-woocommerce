<?php

namespace GlobalPayments\WooCommercePaymentGatewayProvider\Gateways;

defined( 'ABSPATH' ) || exit;

class HeartlandGateway extends AbstractGateway {
	public function __construct() {
		parent::__construct();

		$this->id                 = 'globalpayments_heartland';
		$this->method_title       = __( 'Heartland', 'globalpayments-gateway-provider-for-woocommerce' );
		$this->method_description = __( 'Connect to the Heartland Portico gateway', 'globalpayments-gateway-provider-for-woocommerce' );

		$this->init_form_fields();
		$this->init_settings();

		$this->has_fields = true;
		$this->title      = $this->get_option( 'title' );
		$this->enabled    = $this->get_option( 'enabled' );

		$this->add_hooks();
	}
}
