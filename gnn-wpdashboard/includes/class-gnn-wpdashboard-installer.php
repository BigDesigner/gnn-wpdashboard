<?php
/**
 * GNN WPDashboard Installer Engine
 *
 * Handles plugin and theme discovery, installation, activation, deactivation, updating, and deletion.
 *
 * @package GNN_WPDashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class GNN_WPDashboard_Installer {

	/**
	 * List of BigDesigner / GNN GitHub repositories (Plugins & Themes).
	 *
	 * @var array
	 */
	private $default_repos = array(
		'gnn-lightbox'     => array(
			'name'         => 'GNN Lightbox',
			'type'         => 'plugin',
			'owner'        => 'BigDesigner',
			'repo'         => 'gnn-lightbox',
			'file'         => 'gnn-lightbox/gnn-lightbox.php',
			'category'     => 'Medya & Dosya',
			'description'  => 'Hafif, modern ve hızlı görsel galeri ve lightbox eklentisi.',
			'icon'         => 'image',
			'banner_class' => 'plugin-banner-lightbox',
		),
		'gnn-terms-popup' => array(
			'name'         => 'GNN Terms Popup',
			'type'         => 'plugin',
			'owner'        => 'BigDesigner',
			'repo'         => 'gnn-terms-popup',
			'file'         => 'gnn-terms-popup/gnn-terms-popup.php',
			'category'     => 'Güvenlik & Analiz',
			'description'  => 'Kullanıcı şartları, gizlilik politikası ve onay pop-up eklentisi.',
			'icon'         => 'policy',
			'banner_class' => 'plugin-banner-terms',
		),
		'gnn-filehub'     => array(
			'name'         => 'GNN Filehub',
			'type'         => 'plugin',
			'owner'        => 'BigDesigner',
			'repo'         => 'gnn-filehub',
			'file'         => 'gnn-filehub/gnn-filehub.php',
			'category'     => 'Medya & Dosya',
			'description'  => 'WordPress dosya yönetimi ve medya kütüphanesi senkronizasyon eklentisi.',
			'icon'         => 'folder_zip',
			'banner_class' => 'plugin-banner-filehub',
		),
		'gnn-sitemap'     => array(
			'name'         => 'GNN Sitemap',
			'type'         => 'plugin',
			'owner'        => 'BigDesigner',
			'repo'         => 'gnn-sitemap',
			'file'         => 'gnn-sitemap/gnn-sitemap.php',
			'category'     => 'SEO & Pazarlama',
			'description'  => 'Arama motorları için performanslı ve dinamik XML site haritası oluşturucu.',
			'icon'         => 'account_tree',
			'banner_class' => 'plugin-banner-sitemap',
		),
		'gnn-ipinfo'      => array(
			'name'         => 'GNN IP Info',
			'type'         => 'plugin',
			'owner'        => 'BigDesigner',
			'repo'         => 'gnn-ipinfo',
			'file'         => 'gnn-ipinfo/gnn-ipinfo.php',
			'category'     => 'Güvenlik & Analiz',
			'description'  => 'Ziyaretçilerin IP adreslerini ve konum bilgilerini sorgulama ve raporlama eklentisi.',
			'icon'         => 'location_on',
			'banner_class' => 'plugin-banner-ipinfo',
		),
		'gnn-whois'       => array(
			'name'         => 'GNN Whois',
			'type'         => 'plugin',
			'owner'        => 'BigDesigner',
			'repo'         => 'gnn-whois',
			'file'         => 'gnn-whois/gnn-whois.php',
			'category'     => 'Araçlar & Network',
			'description'  => 'Domain ve IP WHOIS sorgulama eklentisi.',
			'icon'         => 'travel_explore',
			'banner_class' => 'plugin-banner-whois',
		),
		'gnn-shortner'    => array(
			'name'         => 'GNN Shortner',
			'type'         => 'plugin',
			'owner'        => 'BigDesigner',
			'repo'         => 'gnn-shortner',
			'file'         => 'gnn-shortner/gnn-shortner.php',
			'category'     => 'SEO & Pazarlama',
			'description'  => 'WordPress içi hızlı ve performanslı URL kısaltma ve yönlendirme eklentisi.',
			'icon'         => 'link',
			'banner_class' => 'plugin-banner-shortner',
		),
		'gnn-smtpmail'    => array(
			'name'         => 'GNN SMTP Mail',
			'type'         => 'plugin',
			'owner'        => 'BigDesigner',
			'repo'         => 'gnn-smtpmail',
			'file'         => 'gnn-smtpmail/gnn-smtpmail.php',
			'category'     => 'Araçlar & Network',
			'description'  => 'WordPress e-posta gönderimleri için güvenli SMTP ve mail yapılandırma eklentisi.',
			'icon'         => 'mail',
			'banner_class' => 'plugin-banner-smtpmail',
		),
		'gnn-wptheme'     => array(
			'name'         => 'GNN WPTheme',
			'type'         => 'theme',
			'owner'        => 'BigDesigner',
			'repo'         => 'gnn-wptheme',
			'file'         => 'gnn-wptheme',
			'category'     => 'Temalar',
			'description'  => 'Performanslı, SEO uyumlu ve modern GNN WordPress teması.',
			'icon'         => 'palette',
			'banner_class' => 'plugin-banner-wptheme',
		),
		'gnn-wpdashboard' => array(
			'name'         => 'GNN Dashboard',
			'type'         => 'plugin',
			'owner'        => 'BigDesigner',
			'repo'         => 'gnn-wpdashboard',
			'file'         => 'gnn-wpdashboard/gnn-wpdashboard.php',
			'category'     => 'Araçlar & Network',
			'description'  => 'Eklenti ve temalarınızı tek bir panelden kurmanıza, güncellemenize ve yönetmenize olanak tanır.',
			'icon'         => 'grid_view',
			'banner_class' => 'plugin-banner-wpdashboard',
		),
	);

	/**
	 * Get list of all tracked plugins/themes with installation status.
	 *
	 * @return array List of items.
	 */
	public function get_plugins_list() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$installed_plugins = get_plugins();
		$list              = array();

		foreach ( $this->default_repos as $slug => $data ) {
			$item = array(
				'slug'              => $slug,
				'name'              => $data['name'],
				'type'              => $data['type'],
				'owner'             => $data['owner'],
				'repo'              => $data['repo'],
				'file'              => $data['file'],
				'category'          => $data['category'],
				'description'       => $data['description'],
				'icon'              => $data['icon'],
				'banner_class'      => $data['banner_class'],
				'installed'         => false,
				'active'            => false,
				'installed_version' => null,
				'latest_version'    => ! empty( $data['version'] ) ? $data['version'] : '1.0.0',
				'changelog'         => '',
				'has_update'        => false,
				'repo_url'          => 'https://github.com/' . $data['owner'] . '/' . $data['repo'],
			);

			if ( 'theme' === $data['type'] ) {
				// Check Theme Status with fallback for variations in theme directory name
				$theme             = wp_get_theme( $slug );
				$actual_theme_slug = $slug;

				if ( ! $theme->exists() ) {
					$installed_themes = wp_get_themes();
					foreach ( $installed_themes as $t_slug => $t_obj ) {
						$t_name = strtolower( $t_obj->get( 'Name' ) );
						if ( $t_slug === $slug || 0 === strpos( $t_slug, $slug ) || 0 === strpos( $slug, $t_slug ) || false !== strpos( $t_name, 'gnn' ) ) {
							$theme             = $t_obj;
							$actual_theme_slug = $t_slug;
							$item['file']      = $t_slug;
							break;
						}
					}
				}

				if ( $theme->exists() ) {
					$item['installed']         = true;
					$item['installed_version'] = $theme->get( 'Version' ) ? $theme->get( 'Version' ) : '1.0.0';
					$current_stylesheet        = get_stylesheet();
					$current_template          = get_template();
					$item['active']            = ( $current_stylesheet === $actual_theme_slug || $current_template === $actual_theme_slug || $current_stylesheet === $slug || $current_template === $slug || 0 === strpos( $current_stylesheet, 'gnn' ) );
				}
			} else {
				// Check Plugin Status
				$found_file = $this->find_installed_plugin_file( $slug, $installed_plugins );
				if ( $found_file ) {
					$item['installed']         = true;
					$item['file']              = $found_file;
					$item['installed_version'] = ! empty( $installed_plugins[ $found_file ]['Version'] ) ? $installed_plugins[ $found_file ]['Version'] : '1.0.0';
					$item['active']            = is_plugin_active( $found_file );
				}
			}

			// Fetch latest release info from GitHub API or Redirect Fallback
			$release = $this->fetch_github_release( $data['owner'], $data['repo'] );
			if ( $release && ! empty( $release['tag_name'] ) ) {
				$item['latest_version'] = preg_replace( '/^v/i', '', $release['tag_name'] );
				$item['changelog']      = ! empty( $release['body'] ) ? $release['body'] : '';
			} else {
				$fallback_version = $this->fetch_latest_version_fallback( $data['owner'], $data['repo'] );
				if ( $fallback_version ) {
					$item['latest_version'] = $fallback_version;
				}
			}

			if ( $item['installed'] && version_compare( $item['installed_version'], $item['latest_version'], '<' ) ) {
				$item['has_update'] = true;
			}

			$list[] = $item;
		}

		return $list;
	}

	/**
	 * Find matching plugin file path in WP installed plugins array.
	 *
	 * @param string $slug Slug name.
	 * @param array  $installed_plugins List of installed plugins.
	 * @return string|false
	 */
	private function find_installed_plugin_file( $slug, $installed_plugins ) {
		foreach ( $installed_plugins as $file => $info ) {
			$dir = dirname( $file );
			if ( $file === $slug . '.php' || 0 === strpos( $file, $slug . '/' ) || $dir === $slug ) {
				return $file;
			}
		}
		return false;
	}

	/**
	 * Currently installing slug for folder renaming filter.
	 *
	 * @var string|null
	 */
	private $current_install_slug = null;

	/**
	 * Fix source folder name extracted from GitHub archives.
	 *
	 * @param string      $source        Source directory path.
	 * @param string      $remote_source Remote source directory path.
	 * @param WP_Upgrader $upgrader      Upgrader instance.
	 * @param array       $hook_extra    Hook extra parameters.
	 * @return string Modified source path.
	 */
	public function fix_source_folder_name( $source, $remote_source, $upgrader, $hook_extra = array() ) {
		if ( empty( $this->current_install_slug ) ) {
			return $source;
		}

		$slug            = $this->current_install_slug;
		$source_dir_name = trim( basename( $source ), '/' );

		// Strip GitHub archive suffixes: -1.5.1, -v1.5.1, -main, -master, -abc1234 (hash)
		// e.g. gnn-wpdashboard-1.0.3 → gnn-wpdashboard
		// e.g. BigDesigner-gnn-wpdashboard-a1b2c3d → gnn-wpdashboard
		$cleaned = preg_replace( '/(-v?[0-9]+\.[0-9]+(\.[0-9]+)*|-[0-9a-f]{7,}|-main|-master)$/i', '', $source_dir_name );
		// If cleaning produces the expected slug, use it; otherwise fall back to slug
		if ( ! empty( $cleaned ) && $cleaned !== $source_dir_name ) {
			// Accept if cleaned result ends with or equals slug
			if ( $cleaned === $slug || false !== strpos( $cleaned, $slug ) ) {
				$slug = $slug; // keep original slug, just rename the folder
			}
		} elseif ( ! empty( $source_dir_name ) && ! preg_match( '/-[0-9a-f]{7,}$|-v?[0-9]+\.[0-9]+|-main|-master/i', $source_dir_name ) ) {
			// Folder already clean (e.g. 'gnn') - preserve as-is
			$slug                       = $source_dir_name;
			$this->current_install_slug = $slug;
		}

		$correct_source = trailingslashit( $remote_source ) . $slug . '/';

		if ( $source !== $correct_source ) {
			global $wp_filesystem;
			if ( $wp_filesystem ) {
				if ( $wp_filesystem->exists( $correct_source ) ) {
					$wp_filesystem->delete( $correct_source, true );
				}
				if ( $wp_filesystem->move( $source, $correct_source ) ) {
					return $correct_source;
				}
			}
		}

		return $source;
	}

	/**
	 * Delete all cached GitHub release transients for all tracked repos.
	 * Called by the "Güncellemeleri Kontrol Et" button to force a live GitHub check.
	 */
	public function clear_release_cache() {
		foreach ( $this->default_repos as $data ) {
			$transient_key = 'gnn_wpdash_rel_' . md5( $data['owner'] . '_' . $data['repo'] );
			delete_transient( $transient_key );
		}
	}

	/**
	 * Fetch release data from GitHub API.
	 *
	 * @param string $owner GitHub user.
	 * @param string $repo  GitHub repository.
	 * @return array|false
	 */
	private function fetch_github_release( $owner, $repo ) {
		$transient_key = 'gnn_wpdash_rel_' . md5( $owner . '_' . $repo );
		$cached        = get_transient( $transient_key );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		$url      = 'https://api.github.com/repos/' . $owner . '/' . $repo . '/releases/latest';
		$response = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'User-Agent' => 'GNN-WPDashboard-Installer',
					'Accept'     => 'application/vnd.github.v3+json',
				),
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( is_array( $data ) ) {
			set_transient( $transient_key, $data, 6 * HOUR_IN_SECONDS );
			return $data;
		}

		return false;
	}

	/**
	 * Fetch latest version using GitHub release redirect URL fallback (Bypasses API 403 Rate Limit).
	 *
	 * @param string $owner GitHub user.
	 * @param string $repo  GitHub repository.
	 * @return string|false
	 */
	private function fetch_latest_version_fallback( $owner, $repo ) {
		// Method 1: Check HTTP 302 Location header on /releases/latest
		$url      = 'https://github.com/' . $owner . '/' . $repo . '/releases/latest';
		$response = wp_remote_head(
			$url,
			array(
				'redirection' => 0,
				'timeout'     => 5,
			)
		);

		if ( ! is_wp_error( $response ) ) {
			$headers  = wp_remote_retrieve_headers( $response );
			$location = ! empty( $headers['location'] ) ? $headers['location'] : '';

			if ( preg_match( '/\/releases\/tag\/v?([0-9\.]+)/i', $location, $matches ) ) {
				return $matches[1];
			}
		}

		// Method 2: Fetch /releases page HTML and parse latest tag link
		$releases_url = 'https://github.com/' . $owner . '/' . $repo . '/releases';
		$get_response = wp_remote_get( $releases_url, array( 'timeout' => 5 ) );
		if ( ! is_wp_error( $get_response ) && 200 === wp_remote_retrieve_response_code( $get_response ) ) {
			$body = wp_remote_retrieve_body( $get_response );
			if ( preg_match( '/\/releases\/tag\/v?([0-9\.]+)/i', $body, $matches ) ) {
				return $matches[1];
			}
		}

		return false;
	}

	/**
	 * Resolve ZIP URL for installation, handling GitHub API rate limits and release tag fallbacks.
	 *
	 * @param array       $data    Repository metadata.
	 * @param array|false $release Release API response.
	 * @return string|false
	 */
	private function resolve_release_zip_url( $data, $release ) {
		// 1. Try resolving from GitHub API Release response if available
		if ( is_array( $release ) ) {
			// Priority 1: Custom uploaded ZIP asset on the Release page
			if ( ! empty( $release['assets'] ) && is_array( $release['assets'] ) ) {
				foreach ( $release['assets'] as $asset ) {
					if ( ! empty( $asset['name'] ) && substr( $asset['name'], -4 ) === '.zip' && ! empty( $asset['browser_download_url'] ) ) {
						return $asset['browser_download_url'];
					}
				}
			}

			// Priority 2: Public archive URL — does NOT require GitHub auth (zipball_url is intentionally avoided)
			if ( ! empty( $release['tag_name'] ) ) {
				return 'https://github.com/' . $data['owner'] . '/' . $data['repo'] . '/archive/refs/tags/' . $release['tag_name'] . '.zip';
			}
		}

		// 2. Direct Release Asset Fallback (Bypasses GitHub API 403 Rate Limits on Playground / Shared Hosts)
		$owner       = $data['owner'];
		$repo        = $data['repo'];
		$ver_tag       = ! empty( $data['version'] ) ? $data['version'] : '1.0.0';
		$possible_tags = array( 'v' . $ver_tag, $ver_tag, 'v1.0.0' );

		foreach ( $possible_tags as $tag ) {
			$direct_asset = 'https://github.com/' . $owner . '/' . $repo . '/releases/download/' . $tag . '/' . $repo . '-' . $tag . '.zip';
			$check        = wp_remote_head( $direct_asset, array( 'timeout' => 5 ) );
			if ( ! is_wp_error( $check ) && 200 === wp_remote_retrieve_response_code( $check ) ) {
				return $direct_asset;
			}

			$tag_archive   = 'https://github.com/' . $owner . '/' . $repo . '/archive/refs/tags/' . $tag . '.zip';
			$check_archive = wp_remote_head( $tag_archive, array( 'timeout' => 5 ) );
			if ( ! is_wp_error( $check_archive ) && in_array( wp_remote_retrieve_response_code( $check_archive ), array( 200, 301, 302 ), true ) ) {
				return $tag_archive;
			}
		}

		// Final Tag Release URL fallback constructing standard v1.0.0 tag
		return 'https://github.com/' . $owner . '/' . $repo . '/archive/refs/tags/v1.0.0.zip';
	}

	/**
	 * Initialize WP_Filesystem safely across all web server environments (Direct, FTP, cPanel, IIS, Nginx, Playground).
	 *
	 * @return bool True if filesystem is initialized.
	 */
	private function init_filesystem() {
		global $wp_filesystem;

		if ( $wp_filesystem ) {
			return true;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';

		ob_start();
		$credentials = request_filesystem_credentials( '', '', true, false, null );
		ob_end_clean();

		if ( false === $credentials || ! WP_Filesystem( $credentials ) ) {
			if ( ! defined( 'FS_METHOD' ) ) {
				define( 'FS_METHOD', 'direct' );
			}
			return WP_Filesystem();
		}

		return true;
	}

	/**
	 * Forcibly remove existing directory to prevent "destination directory already exists" errors.
	 *
	 * @param string $dir Path to directory.
	 * @return bool True on success.
	 */
	private function force_remove_directory( $dir ) {
		if ( empty( $dir ) || ! file_exists( $dir ) ) {
			return true;
		}

		global $wp_filesystem;
		if ( $wp_filesystem && $wp_filesystem->delete( $dir, true ) ) {
			return true;
		}

		if ( is_dir( $dir ) ) {
			$files = array_diff( scandir( $dir ), array( '.', '..' ) );
			foreach ( $files as $file ) {
				$path = $dir . '/' . $file;
				is_dir( $path ) ? $this->force_remove_directory( $path ) : @unlink( $path );
			}
			return @rmdir( $dir );
		}

		return @unlink( $dir );
	}

	/**
	 * Install a plugin or theme from GitHub release zip asset or source zip fallback.
	 *
	 * @param string $slug Slug name.
	 * @return true|WP_Error
	 */
	public function install_plugin( $slug ) {
		if ( ! isset( $this->default_repos[ $slug ] ) ) {
			return new WP_Error( 'invalid_slug', __( 'Geçersiz slug adı.', 'gnn-wpdashboard' ) );
		}

		$data    = $this->default_repos[ $slug ];
		$release = $this->fetch_github_release( $data['owner'], $data['repo'] );
		$zip_url = $this->resolve_release_zip_url( $data, $release );

		if ( ! $zip_url ) {
			return new WP_Error( 'no_release_zip', __( 'GitHub Tag Release paketi (.zip) bulunamadı.', 'gnn-wpdashboard' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		class_exists( 'GNN_Silent_Upgrader_Skin' ) || require_once __DIR__ . '/class-gnn-silent-upgrader-skin.php';

		$this->init_filesystem();

		// Record previous active state before updating/installing
		$was_active          = false;
		$target_install_slug = $slug;

		if ( 'theme' === $data['type'] ) {
			$theme = wp_get_theme( $slug );
			if ( ! $theme->exists() ) {
				$installed_themes = wp_get_themes();
				foreach ( $installed_themes as $t_slug => $t_obj ) {
					$t_name = strtolower( $t_obj->get( 'Name' ) );
					if ( $t_slug === $slug || 0 === strpos( $slug, $t_slug ) || 0 === strpos( $t_slug, $slug ) || false !== strpos( $t_name, 'gnn' ) ) {
						$target_install_slug = $t_slug;
						break;
					}
				}
			}
			$was_active = ( get_stylesheet() === $target_install_slug || get_template() === $target_install_slug || get_stylesheet() === $slug || get_template() === $slug );
			$dest_dir   = get_theme_root() . '/' . $target_install_slug;
		} else {
			$dest_dir          = WP_PLUGIN_DIR . '/' . $slug;
			$installed_plugins = get_plugins();
			$found             = $this->find_installed_plugin_file( $slug, $installed_plugins );
			if ( $found ) {
				$was_active = is_plugin_active( $found );
				if ( $was_active ) {
					deactivate_plugins( $found );
				}
				$found_dir = WP_PLUGIN_DIR . '/' . dirname( $found );
				if ( is_dir( $found_dir ) && $found_dir !== WP_PLUGIN_DIR ) {
					$this->force_remove_directory( $found_dir );
				}
			}
		}
		$this->force_remove_directory( $dest_dir );

		$skin = new GNN_Silent_Upgrader_Skin();

		$this->current_install_slug = $target_install_slug;
		add_filter( 'upgrader_source_selection', array( $this, 'fix_source_folder_name' ), 10, 4 );

		if ( 'theme' === $data['type'] ) {
			require_once ABSPATH . 'wp-admin/includes/theme.php';
			$upgrader = new Theme_Upgrader( $skin );
			$result   = $upgrader->install( $zip_url, array( 'clear_destination' => true ) );
		} else {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			$upgrader = new Plugin_Upgrader( $skin );
			$result   = $upgrader->install( $zip_url, array( 'clear_destination' => true ) );
		}

		remove_filter( 'upgrader_source_selection', array( $this, 'fix_source_folder_name' ), 10 );
		$this->current_install_slug = null;

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( ! empty( $skin->errors ) ) {
			return new WP_Error( 'install_failed', implode( ', ', $skin->errors ) );
		}

		if ( true !== $result ) {
			return new WP_Error( 'install_failed', __( 'Yükleme başarısız oldu.', 'gnn-wpdashboard' ) );
		}

		// Re-activate ONLY if item was previously active before update
		if ( $was_active ) {
			if ( 'theme' === $data['type'] ) {
				switch_theme( $target_install_slug );
			} else {
				$new_installed_plugins = get_plugins();
				$new_found             = $this->find_installed_plugin_file( $slug, $new_installed_plugins );
				if ( $new_found ) {
					activate_plugin( $new_found );
				}
			}
		}

		return true;
	}

	/**
	 * Activate a plugin or theme.
	 *
	 * @param string $file Plugin file path or theme slug.
	 * @param string $type Type ('plugin' or 'theme').
	 * @return true|WP_Error
	 */
	public function activate_plugin( $file, $type = 'plugin' ) {
		if ( 'theme' === $type ) {
			switch_theme( $file );
			return true;
		}

		if ( ! function_exists( 'activate_plugin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$result = activate_plugin( $file );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return true;
	}

	/**
	 * Deactivate a plugin.
	 *
	 * @param string $file Plugin file path.
	 * @return true|WP_Error
	 */
	public function deactivate_plugin( $file ) {
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		deactivate_plugins( $file );
		return true;
	}

	/**
	 * Delete a plugin or theme.
	 *
	 * @param string $file Plugin file path or theme slug.
	 * @param string $type Type ('plugin' or 'theme').
	 * @return true|WP_Error
	 */
	public function delete_plugin( $file, $type = 'plugin' ) {
		if ( 'theme' === $type ) {
			require_once ABSPATH . 'wp-admin/includes/theme.php';
			if ( get_stylesheet() === $file || get_template() === $file ) {
				return new WP_Error( 'active_theme', __( 'Aktif durumdaki tema silinemez.', 'gnn-wpdashboard' ) );
			}
			$result = delete_theme( $file );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
			return true;
		}

		if ( ! function_exists( 'delete_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( $file ) ) {
			deactivate_plugins( $file );
		}

		$result = delete_plugins( array( $file ) );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return true;
	}

	/**
	 * Bulk install all uninstalled tracked plugins/themes.
	 *
	 * @return true|WP_Error
	 */
	public function bulk_install_all() {
		@set_time_limit( 300 );
		if ( function_exists( 'wp_raise_memory_limit' ) ) {
			wp_raise_memory_limit( 'admin' );
		}
		$list = $this->get_plugins_list();
		foreach ( $list as $item ) {
			if ( ! $item['installed'] ) {
				$this->install_plugin( $item['slug'] );
			}
		}
		return true;
	}

	/**
	 * Bulk activate all installed tracked plugins/themes.
	 *
	 * @return true|WP_Error
	 */
	public function bulk_activate_all() {
		@set_time_limit( 300 );
		if ( function_exists( 'wp_raise_memory_limit' ) ) {
			wp_raise_memory_limit( 'admin' );
		}
		$list = $this->get_plugins_list();
		foreach ( $list as $item ) {
			if ( $item['installed'] && ! $item['active'] ) {
				$this->activate_plugin( $item['file'], $item['type'] );
			}
		}
		return true;
	}

	/**
	 * Bulk deactivate all active tracked plugins.
	 *
	 * @return true|WP_Error
	 */
	public function bulk_deactivate_all() {
		@set_time_limit( 300 );
		if ( function_exists( 'wp_raise_memory_limit' ) ) {
			wp_raise_memory_limit( 'admin' );
		}
		$list = $this->get_plugins_list();
		foreach ( $list as $item ) {
			if ( $item['installed'] && $item['active'] && 'plugin' === $item['type'] ) {
				if ( 'gnn-wpdashboard' === dirname( $item['file'] ) ) {
					continue;
				}
				$this->deactivate_plugin( $item['file'] );
			}
		}
		return true;
	}

	/**
	 * Bulk delete all installed tracked plugins/themes.
	 *
	 * @return true|WP_Error
	 */
	public function bulk_delete_all() {
		@set_time_limit( 300 );
		if ( function_exists( 'wp_raise_memory_limit' ) ) {
			wp_raise_memory_limit( 'admin' );
		}
		$list = $this->get_plugins_list();
		foreach ( $list as $item ) {
			if ( $item['installed'] ) {
				if ( 'gnn-wpdashboard' === dirname( $item['file'] ) ) {
					continue;
				}
				$this->delete_plugin( $item['file'], $item['type'] );
			}
		}
		return true;
	}
}
