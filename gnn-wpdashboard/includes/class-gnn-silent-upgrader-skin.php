<?php
/**
 * Silent Upgrader Skin for GNN WPDashboard AJAX Installers
 *
 * @package GNN_WPDashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';

class GNN_Silent_Upgrader_Skin extends WP_Upgrader_Skin {

	/**
	 * Accumulated skin error messages.
	 *
	 * @var array
	 */
	public $errors = array();

	public function header() {}
	public function footer() {}
	public function feedback( $string, ...$args ) {}
	public function error( $errors ) {
		if ( is_wp_error( $errors ) ) {
			$this->errors[] = $errors->get_error_message();
		} elseif ( is_string( $errors ) ) {
			$this->errors[] = $errors;
		}
	}
	public function before() {}
	public function after() {}
}
