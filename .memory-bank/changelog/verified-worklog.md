# Verified Worklog & Audit History

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
