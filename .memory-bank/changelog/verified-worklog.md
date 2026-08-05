# Verified Worklog & Audit History

## Completed Milestones (v1.0.0-release)

- **Admin Menu Registration**: Positioned at `3.888` titled `GNN Dashboard`.
- **9 Tracked Repositories**:
  1. `gnn-lightbox` (Medya & Dosya - v1.1.0)
  2. `gnn-terms-popup` (Güvenlik & Analiz - v1.3.15)
  3. `gnn-filehub` (Medya & Dosya - v1.6.1)
  4. `gnn-sitemap` (SEO & Pazarlama - v1.1.0)
  5. `gnn-ipinfo` (Güvenlik & Analiz - v0.2.9)
  6. `gnn-whois` (Araçlar & Network - v1.0.0)
  7. `gnn-shortner` (SEO & Pazarlama - v1.0.0)
  8. `gnn-smtpmail` (Araçlar & Network - v1.5.3)
  9. `gnn-wptheme` (Temalar - v1.0.0)
- **Installer & Upgrader Engine**:
  - Multi-tier Tag Release ZIP resolution (`resolve_release_zip_url`).
  - Source folder normalization (`fix_source_folder_name`).
  - Safe filesystem initialization (`init_filesystem`).
  - Pre-install destination cleanup (`force_remove_directory`).
- **Dynamic Bulk Action Bar**:
  - `Tümünü Kur` (Visible if `uninstalled > 0`)
  - `Tümünü Etkinleştir` (Visible if `inactive > 0`)
  - `Tümünü Devre Dışı Bırak` (Visible if `active > 0`)
  - `Tümünü Sil` (Visible if `installedTotal > 0`)
- **Turkish Uppercase & UI Polish**: Fixed `KATEGORİLER` and `DURUM FİLTRESİ` text rendering.
