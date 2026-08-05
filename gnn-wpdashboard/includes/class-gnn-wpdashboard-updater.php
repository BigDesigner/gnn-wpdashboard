<?php
/**
 * GNN WPDashboard Updater Class
 *
 * Handles automatic updates via GitHub Releases matching GNN Plugin Standards.
 *
 * @package GNN_WPDashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class GNN_WPDashboard_Updater {

	/**
	 * Plugin file path.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * Current version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * GitHub repository owner.
	 *
	 * @var string
	 */
	private $owner = 'BigDesigner';

	/**
	 * GitHub repository name.
	 *
	 * @var string
	 */
	private $repo = 'gnn-wpdashboard';

	/**
	 * Constructor.
	 *
	 * @param string $file    Main plugin file.
	 * @param string $version Current version.
	 */
	public function __construct( $file, $version ) {
		$this->file    = $file;
		$this->slug    = plugin_basename( $file );
		$this->version = $version;

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );
		add_action( 'admin_init', array( $this, 'handle_manual_check' ) );
	}

	/**
	 * Handle manual update check from plugin action links.
	 */
	public function handle_manual_check() {
		if ( isset( $_GET['gnn_wpdashboard_check_update'] ) && current_user_can( 'manage_options' ) ) {
			check_admin_referer( 'gnn_wpdashboard_manual_update' );
			delete_site_transient( 'update_plugins' );
			delete_transient( 'gnn_wpdashboard_github_release' );
			wp_safe_redirect( admin_url( 'plugins.php?deactivate-multi=true' ) );
			exit;
		}
	}

	/**
	 * Check for updates against GitHub Releases.
	 *
	 * @param object $transient Plugin update transient.
	 * @return object Modified transient.
	 */
	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$release = $this->get_latest_release();
		if ( ! $release || empty( $release['tag_name'] ) ) {
			return $transient;
		}

		$remote_version = preg_replace( '/^v/i', '', $release['tag_name'] );

		if ( version_compare( $this->version, $remote_version, '<' ) ) {
			$download_url = $this->get_zip_asset_url( $release );

			if ( $download_url ) {
				$res              = new stdClass();
				$res->slug        = 'gnn-wpdashboard';
				$res->plugin      = $this->slug;
				$res->new_version = $remote_version;
				$res->url         = 'https://github.com/' . $this->owner . '/' . $this->repo;
				$res->package     = $download_url;

				if ( empty( $transient->response ) || ! is_array( $transient->response ) ) {
					$transient->response = array();
				}

				$transient->response[ $this->slug ] = $res;
			}
		}

		return $transient;
	}

	/**
	 * Supply plugin details popup in WP Admin.
	 *
	 * @param object|bool $result Result object.
	 * @param string      $action Action type.
	 * @param object      $args   Arguments.
	 * @return object|bool
	 */
	public function plugins_api( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || $args->slug !== 'gnn-wpdashboard' ) {
			return $result;
		}

		$release = $this->get_latest_release();
		if ( ! $release ) {
			return $result;
		}

		$remote_version = preg_replace( '/^v/i', '', $release['tag_name'] );
		$download_url   = $this->get_zip_asset_url( $release );

		$res                = new stdClass();
		$res->name          = 'GNN WPDashboard';
		$res->slug          = 'gnn-wpdashboard';
		$res->version       = $remote_version;
		$res->author        = '<a href="https://github.com/BigDesigner">BigDesigner</a>';
		$res->homepage      = 'https://github.com/' . $this->owner . '/' . $this->repo;
		$res->download_link = $download_url;
		$res->sections      = array(
			'description' => 'WordPress eklentilerinizi GitHub depoları üzerinden tek bir panelden kurmanıza, güncellemenize ve yönetmenize olanak tanır.',
			'changelog'   => ! empty( $release['body'] ) ? esc_html( $release['body'] ) : 'Yeni sürüm yayınlandı.',
		);

		return $res;
	}

	/**
	 * Fetch latest release from GitHub API with 12-hour transient cache.
	 *
	 * @return array|bool Release data or false.
	 */
	private function get_latest_release() {
		$cached = get_transient( 'gnn_wpdashboard_github_release' );
		if ( false !== $cached ) {
			return $cached;
		}

		$url      = 'https://api.github.com/repos/' . $this->owner . '/' . $this->repo . '/releases/latest';
		$response = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'User-Agent' => 'GNN-WPDashboard-Updater',
					'Accept'     => 'application/vnd.github.v3+json',
				),
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $data ) ) {
			return false;
		}

		set_transient( 'gnn_wpdashboard_github_release', $data, 12 * HOUR_IN_SECONDS );
		return $data;
	}

	/**
	 * Extract .zip asset download URL from release assets array.
	 * Strictly avoids GitHub's auto-generated zipball_url per GNN Standards.
	 *
	 * @param array $release Release payload.
	 * @return string|false Asset URL or false.
	 */
	private function get_zip_asset_url( $release ) {
		if ( empty( $release['assets'] ) || ! is_array( $release['assets'] ) ) {
			return false;
		}

		foreach ( $release['assets'] as $asset ) {
			if ( ! empty( $asset['name'] ) && substr( $asset['name'], -4 ) === '.zip' && ! empty( $asset['browser_download_url'] ) ) {
				return $asset['browser_download_url'];
			}
		}

		return false;
	}
}
