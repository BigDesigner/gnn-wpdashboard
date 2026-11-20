# Bug & Verification List

## Active Issues
- None. All identified bugs through `v1.2.4` have been resolved and verified via `php -l` and `node --check`.

## Resolved Issues
1. **Destination Directory Exists Error (`folder_exists`)**: Resolved via `overwrite_package => true` and `WP_Upgrader` package root descent (`descend_to_package_root()`).
2. **AJAX FTP Credentials Prompt**: Resolved via `init_filesystem()` output buffer suppression and direct filesystem fallback.
3. **Prefix Matching Collision (`find_installed_plugin_file`)**: Resolved by matching exact directory names (`$dir === $slug`) before filename fallback.
4. **Stale Folder Deletion Guard (`install_plugin`)**: Added `'.' !== $rel_dir` and `0 === validate_file( $rel_dir )` checks to prevent accidental `WP_PLUGIN_DIR` deletion.
5. **Self-Protection Guardrail**: Enforced `is_self_plugin()` in `deactivate_plugin()`, `delete_plugin()`, and bulk operations so `gnn-wpdashboard` cannot be deactivated or deleted.
6. **Theme Detection & Update Sync (`gnn-wptheme`)**: Mapped `gnn-wptheme` directory to `gnn` and cleared `update_themes` + `gnn_github_update_check_v2` transients on sync/update (`v1.1.6`).
7. **Cross-Filter Counter Inconsistency**: Scoped status tab counters (`#count-*`) in `admin.js` to the active category and added smart fallback switching when clicking empty update/favorite filters (`v1.2.1`).
8. **Non-Functional Favorites & Persistent Server Notice**: Implemented `localStorage`-backed favorites toggle + `Favoriler` filter tab and dismissible server limits notice (`v1.2.1`).
9. **Broken `.githubusercontent.com` Host Suffix Check**: Fixed `substr( $host, -18 ) === '.githubusercontent.com'` (22 chars vs 18) using `-strlen( $suffix )` in both `Updater` and `Installer` (`v1.2.4`).
10. **Core Updater Active-State & Folder Normalization**: Added `upgrader_pre_install` (`pre_install()`) and `upgrader_post_install` (`post_install()`) with `wp_clean_plugins_cache( true )` in `GNN_WPDashboard_Updater` (`v1.2.4`).
11. **Missing Modal Focus Trap & Hardcoded JS Version**: Added WCAG 2.1 `Tab`/`Shift+Tab` focus trap on `#details-modal` and passed `GNNWPDashboard.version` dynamically via `wp_localize_script()` (`v1.2.4`).
