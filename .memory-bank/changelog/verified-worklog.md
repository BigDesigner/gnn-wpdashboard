# Verified Worklog & Audit History

## Session: Logic & Security Hardening — 2026-10-18

### Comprehensive Audit & Bug Fixes
- **Stale Folder Deletion Guard (`install_plugin`):** Added strict `$rel_dir !== '.'` and `0 === validate_file( $rel_dir )` checks before `force_remove_directory()` to prevent accidental deletion of `WP_PLUGIN_DIR` if a root-level plugin file is matched, while adding two-pass `basename( $file ) === $slug . '.php'` matching in `find_installed_plugin_file()`.
- **Rate-Limit Fallback Caching (`Installer` & `Updater`):** Both `GNN_WPDashboard_Installer::fetch_github_release()` and `GNN_WPDashboard_Updater::get_latest_release()` now resolve the latest tag via `/releases/latest` HTTP 302 redirect when GitHub API returns 403 and cache the result for 1 hour (`HOUR_IN_SECONDS`), preventing 20+ live HTTP requests per page load and making `scrape_release_asset_url()` reachable under rate limits.
- **Transient Cache Synchronization:** `clear_release_cache()` and `handle_manual_check()` now clear both `gnn_wpdash_rel_*` and `gnn_wpdashboard_github_release` transients plus `update_plugins` site transient.
- **Bulk Operations Hardening (`bulk_*`):** Local bulk operations (`bulk_activate_all`, `bulk_deactivate_all`, `bulk_delete_all`) now use `get_plugins_list( false )` to skip remote GitHub requests, exclude themes from `bulk_activate_all()` so the active site theme is never switched unintentionally, and propagate `WP_Error` instances back to AJAX responses.
- **Target Allowlist & Self-Protection (`is_managed_item`, `is_self_plugin`):** Enforced server-side `validate_file()` path traversal protection, GNN repository allowlist verification, and self-deactivation/deletion blocking in `activate_plugin()`, `deactivate_plugin()`, and `delete_plugin()`.
- **Frontend Security & UX (`admin.js`):** Added `escapeHtml()` to prevent DOM XSS in card templates, excluded `gnn-wpdashboard` and active themes from bulk button visibility counters, handled empty version strings in card badges and details modal, and added `error:` callbacks to all AJAX handlers.
- **Translations & Guide Sync:** Added new security error strings to `.pot`/`.po`, recompiled `gnn-wpdashboard-tr_TR.mo` (`0x950412de`, 43 entries), and synchronized category/color tables in `.agents/gnn-dashboard-new-plugin-guide.md`.

---

## Session: v1.0.6 — 2026-08-05

### Self-Update & Release Pipeline (v1.0.1 → v1.0.6)

- **Self-Management Added (v1.0.1):** `gnn-wpdashboard` registered as the 10th entry in `$default_repos`. Dashboard card now shows a `System Panel` protection badge instead of Deactivate/Delete buttons.
- **Cache-Busting Fix (v1.0.2):** "Check for Updates" button now calls `gnn_wpdashboard_clear_cache` AJAX endpoint first, deleting all `gnn_wpdash_rel_*` transients, then fires `loadPlugins()` to fetch live GitHub data.
- **Badge Display Fix (v1.0.3):** Card version badge (`version-badge-pill`) now shows `installed_version` when installed and `latest_version` when not installed. Previously showed stale `v1.0.0` for all installed items.
- **ZIP Resolution Fix (v1.0.4):** `zipball_url` completely removed. `resolve_release_zip_url()` now uses: (1) custom Release asset ZIP, (2) public `archive/refs/tags/{tag}.zip`. `fix_source_folder_name()` regex expanded to catch `gnn-wpdashboard-1.0.3` and `BigDesigner-gnn-wpdashboard-abc1234` patterns.
- **Hardcode Version Purge (v1.0.5):** All `'version'` keys removed from `$default_repos`. `latest_version` initial value changed from `'1.0.0'` to `''`. All `$data['version']` fallback references removed from installer. `.agents/gnn-dashboard-new-plugin-guide.md` rewritten with strict prohibitions.
- **Changelog Added (v1.0.6):** `CHANGELOG.md` created with full version history at `gnn-wpdashboard/CHANGELOG.md`.

---

## Completed Milestones (v1.0.0-release)

- **Admin Menu Registration**: Positioned at `3.888` titled `GNN Dashboard`.
- **10 Tracked Repositories** (as of v1.0.1):
  1. `gnn-lightbox` (Medya & Dosya)
  2. `gnn-terms-popup` (Güvenlik & Analiz)
  3. `gnn-filehub` (Medya & Dosya)
  4. `gnn-sitemap` (SEO & Pazarlama)
  5. `gnn-ipinfo` (Güvenlik & Analiz)
  6. `gnn-whois` (Araçlar & Network)
  7. `gnn-shortner` (SEO & Pazarlama)
  8. `gnn-smtpmail` (Araçlar & Network)
  9. `gnn-wptheme` (Temalar)
  10. `gnn-wpdashboard` (Araçlar & Network) — self-managed
- **Installer & Upgrader Engine**:
  - Multi-tier Tag Release ZIP resolution (`resolve_release_zip_url`) — no `zipball_url`.
  - Source folder normalization (`fix_source_folder_name`) — full regex coverage.
  - Safe filesystem initialization (`init_filesystem`).
  - Pre-install destination cleanup (`force_remove_directory`).
  - Cache-aware GitHub API fetch with `clear_release_cache()`.
- **Dynamic Bulk Action Bar**:
  - `Tümünü Kur` (Visible if `uninstalled > 0`)
  - `Tümünü Etkinleştir` (Visible if `inactive > 0`)
  - `Tümünü Devre Dışı Bırak` (Visible if `active > 0`)
  - `Tümünü Sil` (Visible if `installedTotal > 0`)
- **Agent Standards**: `.agents/gnn-dashboard-new-plugin-guide.md` — strict integration protocol for all future plugins/themes.
