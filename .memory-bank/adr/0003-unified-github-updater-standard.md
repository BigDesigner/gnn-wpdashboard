# ADR 0003: Unified GNN GitHub Updater Standard

- **Status**: Accepted
- **Confidence**: Verified
- **Date**: 2026-11-21

## Context
`GNN_WPDashboard_Updater` diverged from the Unified GNN GitHub Updater standard in several areas:
1. `post_install()` returned `$response` instead of `$result` on the `upgrader_post_install` filter, did not normalize paths with `wp_normalize_path()`, and did not update `$result['destination_name']`.
2. `check_update()` did not populate `$transient->no_update`, and `plugins_api` was registered at priority `10` instead of `20`.
3. `load-update-core.php` cache clearing was missing, `handle_manual_check()` checked `manage_options` instead of `update_plugins` (returning `403` on failure) and redirected to `plugins.php?deactivate-multi=true` instead of `update-core.php?force-check=1`.
4. `sanitize_package_url()` omitted `api.github.com` and `codeload.github.com`, and `get_package_url()` did not fall back to `zipball_url` when no `.zip` asset existed.

## Decision
Align `GNN_WPDashboard_Updater` (`gnn-wpdashboard/includes/class-gnn-wpdashboard-updater.php`) with the Unified GNN GitHub Updater standard while preserving the constructor `($file, $version)`, transient key (`gnn_wpdashboard_github_release`), GET parameter (`gnn_wpdashboard_check_update`), nonce (`gnn_wpdashboard_manual_update`), and `gnn_wpdashboard()->installer->clear_release_cache()` call in `handle_manual_check()`:
1. **Hooks**: `pre_set_site_transient_update_plugins`, `plugins_api` (priority `20`, `3` args), `upgrader_pre_install`, `upgrader_post_install` (priority `10`, `3` args), `admin_init`, and `load-update-core.php`.
2. **Release Resolution & SSRF Allowlist**: Use `WordPress/{ver}; {home_url}` User-Agent, semver tag validation (`/^\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?$/`), negative cache sentinel `(object) ['failed' => true]` (`300s`), `12h` success cache, `.zip` asset preference with `zipball_url` fallback, and HTTPS allowlist (`github.com`, `api.github.com`, `codeload.github.com`, `objects.githubusercontent.com`, `release-assets.githubusercontent.com`, `*.githubusercontent.com`).
3. **Lifecycle & Manual Check Fixes**: Fix `after_install()` to guard `WP_Error`, initialize `$wp_filesystem`, normalize paths, and return `$result`. Update `handle_manual_check()` to enforce `update_plugins` (`403`) and redirect to `update-core.php?force-check=1`.

## Consequences
- Eliminates the `upgrader_post_install` return-value bug and aligns update behavior across all GNN plugins.
- Preserves WPDashboard installer cache clearing during manual update checks.

## Evidence
- `gnn-wpdashboard/includes/class-gnn-wpdashboard-updater.php` (`php -l` verified)


## Amendment (2026-11-25): Disk-First Local Version Detection
- **Root Cause:** Reading in-memory version constants (`GNN_*_VERSION` / `$this->version`) first inside `get_local_version()` returned the pre-upgrade version when WordPress fired `pre_set_site_transient_update_plugins` within the same PHP request right after overwriting plugin files, causing the update banner to persist until a second update run.
- **Resolution:** `get_local_version()` now reads the live `Version` header directly from `WP_PLUGIN_DIR . '/' . $this->plugin_basename` via `clearstatcache( true, $main_file )` and `get_file_data()` first, falling back to in-memory constants only if the file is unavailable.
