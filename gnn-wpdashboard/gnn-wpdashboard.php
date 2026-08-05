<?php
/**
 * Plugin Name: GNN WPDashboard
 * Plugin URI:  https://github.com/BigDesigner/gnn-wpdashboard
 * Description: Eklentilerinizi tek bir panelden kurmanıza, güncellemenize ve yönetmenize olanak tanır.
 * Version:     1.0.5
 * Author:      BigDesigner
 * Author URI:  https://github.com/BigDesigner
 * Text Domain: gnn-wpdashboard
 * Domain Path: /languages
 * License:     GPL-2.0+
 *
 * @package GNN_WPDashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define Constants
define( 'GNN_WPDASHBOARD_FILE', __FILE__ );
define( 'GNN_WPDASHBOARD_PATH', plugin_dir_path( __FILE__ ) );
define( 'GNN_WPDASHBOARD_URL', plugin_dir_url( __FILE__ ) );

if ( file_exists( GNN_WPDASHBOARD_PATH . 'VERSION' ) ) {
	define( 'GNN_WPDASHBOARD_VERSION', trim( file_get_contents( GNN_WPDASHBOARD_PATH . 'VERSION' ) ) );
} else {
	define( 'GNN_WPDASHBOARD_VERSION', '1.0.0' );
}

/**
 * Main GNN_WPDashboard Class.
 */
class GNN_WPDashboard {

	/**
	 * Single instance of the class.
	 *
	 * @var GNN_WPDashboard
	 */
	private static $instance = null;

	/**
	 * Installer engine instance.
	 *
	 * @var GNN_WPDashboard_Installer
	 */
	public $installer;

	/**
	 * Updater instance.
	 *
	 * @var GNN_WPDashboard_Updater
	 */
	public $updater;

	/**
	 * Get class instance.
	 *
	 * @return GNN_WPDashboard
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required files.
	 */
	private function includes() {
		require_once GNN_WPDASHBOARD_PATH . 'includes/class-gnn-wpdashboard-updater.php';
		require_once GNN_WPDASHBOARD_PATH . 'includes/class-gnn-wpdashboard-installer.php';

		$this->installer = new GNN_WPDashboard_Installer();
		$this->updater   = new GNN_WPDashboard_Updater( GNN_WPDASHBOARD_FILE, GNN_WPDASHBOARD_VERSION );
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_filter( 'plugin_action_links', array( $this, 'add_plugin_action_links' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// AJAX Endpoints
		add_action( 'wp_ajax_gnn_wpdashboard_get_plugins', array( $this, 'ajax_get_plugins' ) );
		add_action( 'wp_ajax_gnn_wpdashboard_install_plugin', array( $this, 'ajax_install_plugin' ) );
		add_action( 'wp_ajax_gnn_wpdashboard_activate_plugin', array( $this, 'ajax_activate_plugin' ) );
		add_action( 'wp_ajax_gnn_wpdashboard_deactivate_plugin', array( $this, 'ajax_deactivate_plugin' ) );
		add_action( 'wp_ajax_gnn_wpdashboard_delete_plugin', array( $this, 'ajax_delete_plugin' ) );
		add_action( 'wp_ajax_gnn_wpdashboard_bulk_install', array( $this, 'ajax_bulk_install' ) );
		add_action( 'wp_ajax_gnn_wpdashboard_bulk_activate', array( $this, 'ajax_bulk_activate' ) );
		add_action( 'wp_ajax_gnn_wpdashboard_bulk_deactivate', array( $this, 'ajax_bulk_deactivate' ) );
		add_action( 'wp_ajax_gnn_wpdashboard_bulk_delete', array( $this, 'ajax_bulk_delete' ) );
		add_action( 'wp_ajax_gnn_wpdashboard_clear_cache', array( $this, 'ajax_clear_cache' ) );
	}

	/**
	 * Register Admin Menu right under Dashboard at position 3.888.
	 */
	public function register_admin_menu() {
		// Menü Pozisyonu: 3.888 (Çakışmasız benzersiz pozisyon - Başlangıç / Dashboard menüsünün hemen altı)
		add_menu_page(
			__( 'GNN Dashboard', 'gnn-wpdashboard' ),
			'GNN Dashboard',
			'manage_options',
			'wpdashboard-settings',
			array( $this, 'render_dashboard_page' ),
			'dashicons-grid-view',
			'3.888'
		);
	}

	/**
	 * Add plugin action links (Donate -> Settings -> Check Updates).
	 *
	 * @param array  $links Action links.
	 * @param string $file  Plugin file.
	 * @return array Modified links.
	 */
	public function add_plugin_action_links( $links, $file ) {
		if ( $file === plugin_basename( GNN_WPDASHBOARD_FILE ) ) {
			$donate_link = '<a href="https://buymeacoffee.com/bigdesigner" target="_blank" style="font-weight:bold; color:#d63638;">'
				. esc_html__( 'Donate', 'gnn-wpdashboard' ) . '</a>';

			$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=wpdashboard-settings' ) ) . '">'
				. esc_html__( 'Settings', 'gnn-wpdashboard' ) . '</a>';

			$update_url  = wp_nonce_url( admin_url( 'plugins.php?gnn_wpdashboard_check_update=1' ), 'gnn_wpdashboard_manual_update' );
			$update_link = '<a href="' . esc_url( $update_url ) . '">' . esc_html__( 'Check Updates', 'gnn-wpdashboard' ) . '</a>';

			array_unshift( $links, $donate_link, $settings_link, $update_link );
		}
		return $links;
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook_suffix Admin page hook.
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		if ( 'toplevel_page_wpdashboard-settings' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style( 'google-fonts-inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', array(), null );
		wp_enqueue_style( 'google-material-symbols', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap', array(), null );
		wp_enqueue_script( 'tailwind-cdn', 'https://cdn.tailwindcss.com?plugins=forms,container-queries', array(), null, false );

		wp_enqueue_style( 'gnn-wpdashboard-admin-style', GNN_WPDASHBOARD_URL . 'assets/css/admin.css', array(), GNN_WPDASHBOARD_VERSION );
		wp_enqueue_script( 'gnn-wpdashboard-admin-script', GNN_WPDASHBOARD_URL . 'assets/js/admin.js', array( 'jquery' ), GNN_WPDASHBOARD_VERSION, true );

		wp_localize_script(
			'gnn-wpdashboard-admin-script',
			'GNNWPDashboard',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'gnn_wpdashboard_nonce' ),
			)
		);
	}

	/**
	 * Render Dashboard Page HTML.
	 */
	public function render_dashboard_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Bu sayfaya erişim yetkiniz yok.', 'gnn-wpdashboard' ) );
		}

		include GNN_WPDASHBOARD_PATH . 'templates/dashboard-page.php';
	}

	/**
	 * AJAX: Get Plugins list.
	 */
	public function ajax_get_plugins() {
		check_ajax_referer( 'gnn_wpdashboard_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'gnn-wpdashboard' ) );
		}

		$plugins = $this->installer->get_plugins_list();
		wp_send_json_success( $plugins );
	}

	/**
	 * AJAX: Clear GitHub release transient cache for all tracked repos.
	 */
	public function ajax_clear_cache() {
		check_ajax_referer( 'gnn_wpdashboard_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'gnn-wpdashboard' ) );
		}
		$this->installer->clear_release_cache();
		wp_send_json_success( 'cache_cleared' );
	}

	/**
	 * AJAX: Install Plugin from GitHub.
	 */
	public function ajax_install_plugin() {
		check_ajax_referer( 'gnn_wpdashboard_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'gnn-wpdashboard' ) );
		}

		$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		if ( empty( $slug ) ) {
			wp_send_json_error( __( 'Eklenti slug belirtilmedi.', 'gnn-wpdashboard' ) );
		}

		$result = $this->installer->install_plugin( $slug );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( __( 'Eklenti başarıyla yüklendi.', 'gnn-wpdashboard' ) );
	}

	/**
	 * AJAX: Activate Plugin or Theme.
	 */
	public function ajax_activate_plugin() {
		check_ajax_referer( 'gnn_wpdashboard_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'gnn-wpdashboard' ) );
		}

		$file = isset( $_POST['file'] ) ? sanitize_text_field( wp_unslash( $_POST['file'] ) ) : '';
		$type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'plugin';
		if ( empty( $file ) ) {
			wp_send_json_error( __( 'Dosya veya slug belirtilmedi.', 'gnn-wpdashboard' ) );
		}

		$result = $this->installer->activate_plugin( $file, $type );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( __( 'Başarıyla etkinleştirildi.', 'gnn-wpdashboard' ) );
	}

	/**
	 * AJAX: Deactivate Plugin.
	 */
	public function ajax_deactivate_plugin() {
		check_ajax_referer( 'gnn_wpdashboard_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'gnn-wpdashboard' ) );
		}

		$file = isset( $_POST['file'] ) ? sanitize_text_field( wp_unslash( $_POST['file'] ) ) : '';
		if ( empty( $file ) ) {
			wp_send_json_error( __( 'Eklenti dosyası belirtilmedi.', 'gnn-wpdashboard' ) );
		}

		$result = $this->installer->deactivate_plugin( $file );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( __( 'Eklenti devre dışı bırakıldı.', 'gnn-wpdashboard' ) );
	}

	/**
	 * AJAX: Delete Plugin or Theme.
	 */
	public function ajax_delete_plugin() {
		check_ajax_referer( 'gnn_wpdashboard_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'gnn-wpdashboard' ) );
		}

		$file = isset( $_POST['file'] ) ? sanitize_text_field( wp_unslash( $_POST['file'] ) ) : '';
		$type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'plugin';
		if ( empty( $file ) ) {
			wp_send_json_error( __( 'Dosya veya slug belirtilmedi.', 'gnn-wpdashboard' ) );
		}

		$result = $this->installer->delete_plugin( $file, $type );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( __( 'Başarıyla silindi.', 'gnn-wpdashboard' ) );
	}

	/**
	 * AJAX: Bulk Install All Uninstalled Items.
	 */
	public function ajax_bulk_install() {
		check_ajax_referer( 'gnn_wpdashboard_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'gnn-wpdashboard' ) );
		}

		$this->installer->bulk_install_all();
		wp_send_json_success( __( 'Tüm ögeler kuruldu.', 'gnn-wpdashboard' ) );
	}

	/**
	 * AJAX: Bulk Activate All Installed Items.
	 */
	public function ajax_bulk_activate() {
		check_ajax_referer( 'gnn_wpdashboard_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'gnn-wpdashboard' ) );
		}

		$this->installer->bulk_activate_all();
		wp_send_json_success( __( 'Tüm ögeler etkinleştirildi.', 'gnn-wpdashboard' ) );
	}

	/**
	 * AJAX: Bulk Deactivate All Active Items.
	 */
	public function ajax_bulk_deactivate() {
		check_ajax_referer( 'gnn_wpdashboard_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'gnn-wpdashboard' ) );
		}

		$this->installer->bulk_deactivate_all();
		wp_send_json_success( __( 'Tüm eklentiler devre dışı bırakıldı.', 'gnn-wpdashboard' ) );
	}

	/**
	 * AJAX: Bulk Delete All Installed Items.
	 */
	public function ajax_bulk_delete() {
		check_ajax_referer( 'gnn_wpdashboard_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Yetkisiz erişim.', 'gnn-wpdashboard' ) );
		}

		$this->installer->bulk_delete_all();
		wp_send_json_success( __( 'Tüm eklentiler ve temalar silindi.', 'gnn-wpdashboard' ) );
	}
}

// Initialize Plugin
function gnn_wpdashboard() {
	return GNN_WPDashboard::get_instance();
}
add_action( 'plugins_loaded', 'gnn_wpdashboard' );
