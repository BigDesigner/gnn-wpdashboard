# Changelog

All notable changes to **GNN WPDashboard** are documented here.

---

## [1.1.4] - 2026-08-05

### Changed
- Canlı test sürümü — 1.1.3'teki kurulum ve dil dosyası düzeltmelerini yayına taşır

---

## [1.1.3] - 2026-08-05

### Fixed
- "Hedef klasör zaten var." hatası giderildi: `WP_Upgrader::install()` gönderilen `clear_destination` parametresini yok sayıp `overwrite_package` bekliyor; bu bayrak eklendi
- Hedef klasör artık kurulum öncesi silinmiyor — indirme başarısız olursa çalışan eklenti siliniyordu, bu risk kalktı
- Yalnızca farklı isimli eski klasör temizleniyor

### Changed
- Dil dosyaları sıfırdan yeniden üretildi: 13 metin → **40 metin** (`.pot`, `tr_TR.po`, `tr_TR.mo`)
- Derlenmiş `.mo` dosyası eklendi — çeviriler artık gerçekten yükleniyor
- `Donate`, `Settings`, `Check Updates` metinleri Türkçeye çevrildi

---

## [1.1.2] - 2026-08-05

### Changed
- "Kuruluma Hazır" durum filtresi etiketi "Hazır" olarak kısaltıldı

---

## [1.1.1] - 2026-08-05

### Fixed
- "Paket kurulamadı." hatası giderildi: GitHub API rate limit'e takıldığında kod kaynak arşivini (`archive/refs/tags/*.zip`) indiriyordu; bu arşiv depo kökü olduğu ve eklenti alt klasörde durduğu için WordPress paketi geçersiz sayıyordu
- ZIP artık her zaman `/releases/latest` üzerinden gerçek release asset'i bulunarak indiriliyor (API'siz, rate limit'ten etkilenmez)
- Asset dosya adı tahmin edilmiyor, release sayfasından okunuyor (`repo-v1.1.0.zip` / `repo-1.1.0.zip` farkı artık sorun değil)
- Arşiv indirilse bile paketin gerçek klasörü tespit ediliyor (iç içe depo yapısı desteği)
- Aynı fallback WordPress'in kendi güncelleme ekranı için de eklendi

---

## [1.1.0] - 2026-08-05

### Fixed
- Kendi kendini güncelleme sırasında çalışan plugin dizininin silinmesi hatası giderildi (self-update artık "Paket kurulamadı" hatası vermiyor)

### Changed
- Durum filtresi sıralaması: Tümü, Kuruluma Hazır, Aktif, Pasif, Güncelleme
- "Kurulu & Aktif" etiketi "Aktif" olarak sadeleştirildi
- "Güncelleme Var" etiketi "Güncelleme" olarak sadeleştirildi

---

## [1.0.9] - 2026-08-05

### Added
- Durum filtresi paneline "Kuruluma Hazır" sekmesi eklendi (kurulmamış eklenti/temaları filtreler)

### Changed
- "Tüm Eklentiler" durum filtresi etiketi "Tümü" olarak güncellendi (hem eklenti hem tema gösterdiği için)

---

## [1.0.8] - 2026-08-05

### Fixed
- `find_installed_plugin_file()`: gevşek klasör-öneki eşleşmesi kaldırıldı, yanlış eklenti eşleşme riski giderildi
- `resolve_release_zip_url()`: diğer eklentilere ait rastgele sabit sürüm etiketleri fallback listesinden temizlendi
- `install_plugin()`: upgrader `null` sonuç dönerse artık hatalı "başarılı" sayılmıyor
- Bulk deactivate/delete kendini koruma kontrolü tam klasör eşleşmesiyle sağlamlaştırıldı
- Büyük harfli GitHub release etiketleri (`V1.2.0`) artık doğru algılanıyor
- `check_update()`: `$transient->response` güvenli şekilde ilklendiriliyor
- Çoklu güncelleme banner bildirimi artık toplam sayıyı gösteriyor

---

## [1.0.6] - 2026-08-05

### Added
- `CHANGELOG.md` created with full version history

### Fixed
- Plugin/theme active state is now preserved after update (`$was_active` logic)

---

## [1.0.5] - 2026-08-05

### Changed
- All hardcoded `version` keys removed from `$default_repos` — version info is now 100% fetched live from GitHub API
- `$data['version']` fallback reference removed from `resolve_release_zip_url()`
- `latest_version` initial value changed from `'1.0.0'` to `''` (empty string)

### Docs
- `.agents/gnn-dashboard-new-plugin-guide.md` rewritten from scratch: hardcode version ban, zipball_url ban, banner design requirement, ZIP priority order, and validation checklist added

---

## [1.0.4] - 2026-08-05

### Fixed
- `zipball_url` usage completely removed — it requires GitHub authentication and fails on shared hosting
- ZIP URL priority order: 1) Release asset, 2) `archive/refs/tags/{tag}.zip` (public, no auth required)
- `fix_source_folder_name()` regex expanded: all GitHub archive folder name formats such as `gnn-wpdashboard-1.0.3` and `BigDesigner-gnn-wpdashboard-abc1234` are now correctly renamed to `gnn-wpdashboard`

---

## [1.0.3] - 2026-08-05

### Fixed
- Card version badge (`version-badge-pill`) now shows `installed_version` when installed, and `latest_version` when not installed — fixed stale `v1.0.0` fallback display bug

---

## [1.0.2] - 2026-08-05

### Fixed
- "Check for Updates" button now clears all GitHub release transient caches before making a live API request
- `clear_release_cache()` method added — clears all `gnn_wpdash_rel_*` transient keys
- `gnn_wpdashboard_clear_cache` AJAX endpoint added

---

## [1.0.1] - 2026-08-05

### Added
- GNN Dashboard plugin now tracks and updates itself (self-update & self-management)
- `gnn-wpdashboard` added to `$default_repos` as the 10th entry
- **Premium Cyber Matrix Grid** banner design added for GNN Dashboard card (`plugin-banner-wpdashboard`)
- `gnn-wpdashboard` card shows a `System Panel` protection badge instead of Deactivate/Delete buttons

### Fixed
- Notification banner dynamic title: theme update → `"Theme Update Available"`, plugin update → `"Plugin Update Available"`, mixed → `"Plugin & Theme Updates Available"`
- Removed unnecessary `"Show Updates"` button from notification banner
- Theme installation folder name collision fixed: preserves `gnn` (actual theme folder) instead of creating `gnn-wptheme` duplicate

---

## [1.0.0] - 2026-08-05

### Added
- Initial stable release
- Version tracking and update detection for 10 plugins/themes via GitHub Releases API
- One-click install, update, activate, deactivate and delete
- Bulk actions: Install All, Activate All, Deactivate All, Delete All
- Status filters: All, Installed & Active, Has Update, Inactive
- Category filters: SEO & Marketing, Security & Analytics, Media & Files, Tools & Network, Themes
- Unique CSS geometric banner pattern for each card
- `fetch_latest_version_fallback()` fallback mechanism for GitHub API rate limits
- `fix_source_folder_name()` for automatic GitHub archive folder name correction
- Universal installation via WordPress Filesystem API (Direct, FTP, cPanel, IIS, Nginx)
- Silent (AJAX-compatible) installation via `GNN_Silent_Upgrader_Skin`
- Turkish language support (`gnn-wpdashboard-tr_TR.po`)
- Sentinel Agent Memory Bank integration
