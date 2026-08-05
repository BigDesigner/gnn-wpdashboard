# GNN WPDashboard — AI Agent Yeni Eklenti/Tema Ekleme Rehberi

Bu belge, **GNN Dashboard** sistemine yeni bir eklenti veya tema eklerken izlenmesi gereken **zorunlu** adımları tanımlar.
AI Agent'lar bu rehbere **eksiksiz** uymak zorundadır. Tek bir adımın atlanması kabul edilemez.

---

## ⛔ KESİN YASAKLAR (Asla Yapma)

- `$default_repos` dizisine **`'version'` anahtarı EKLEME.** Sürüm bilgisi her zaman GitHub API'den canlı çekilir.
- `zipball_url` kullanma. Bu URL GitHub authentication gerektirir ve shared hosting'lerde çalışmaz.
- Başka bir kartta zaten kullanılan renk + desen kombinasyonunu tekrarlama.
- Kapak CSS sınıfını (`plugin-banner-*`) oluşturmadan eklentiyi kaydet.

---

## 📋 Adım 1 — Depo Kaydı

**Dosya:** `gnn-wpdashboard/includes/class-gnn-wpdashboard-installer.php`

`$default_repos` dizisine yeni ögeyi **`'version'` anahtarı olmadan** ekle:

```php
'gnn-eklentiadi' => array(
    'name'         => 'GNN Eklenti Adı',
    'type'         => 'plugin',        // 'plugin' veya 'theme'
    'owner'        => 'BigDesigner',
    'repo'         => 'gnn-eklentiadi',
    'file'         => 'gnn-eklentiadi/gnn-eklentiadi.php', // Temalar için sadece klasör adı: 'gnn-temadi'
    'category'     => 'Araçlar & Network',
    'description'  => 'Eklentinin işlevini açıklayan Türkçe net bir açıklama.',
    'icon'         => 'mail',          // Google Material Symbols Outlined ikon adı
    'banner_class' => 'plugin-banner-eklentiadi', // Benzersiz CSS sınıfı — Adım 2'de oluşturulacak
),
```

> **UYARI:** `'version'` anahtarı kesinlikle eklenmez. Sürüm bilgisi `fetch_github_release()` ile GitHub Releases API'den canlı çekilir. Hardcode sürüm, güncelleme algılama mekanizmasını kırar.

### Mevcut Kategoriler (tam eşleşmeli kullan):
| Kategori | Kullanım |
|---|---|
| `SEO & Pazarlama` | Sitemap, URL kısaltma, meta araçları |
| `Güvenlik & Analiz` | IP, WHOIS, güvenlik, onay popup |
| `Medya & Dosya` | Lightbox, dosya yönetimi, galeri |
| `Araçlar & Network` | Mail, ağ araçları, sistem paneli |
| `Temalar` | Yalnızca `'type' => 'theme'` ögeler |

---

## 🎨 Adım 2 — Kapak Tasarımı (ZORUNLU)

**Dosya:** `gnn-wpdashboard/assets/css/admin.css`

Her eklentinin **benzersiz** bir geometrik CSS deseni ve renk gradyanı olmalıdır.
Mevcut 10 kartın kullandığı renk/desen tablosuna bakarak **tekrar etmeyen** yeni bir kombinasyon seç:

| # | Eklenti | Renk | Desen |
|---|---|---|---|
| 1 | GNN Lightbox | Cyan `#0891b2` | Diagonal Lines |
| 2 | GNN Terms Popup | Rose `#e11d48` | Dot Grid |
| 3 | GNN Filehub | Orange `#ea580c` | Vertical Stripes |
| 4 | GNN Sitemap | Violet `#7c3aed` | Crosshatch Circuit |
| 5 | GNN IP Info | Slate `#334155` | Concentric Wave Rings |
| 6 | GNN Whois | Amber `#d97706` | Topographic Waves |
| 7 | GNN Shortner | Red `#e11d48` | Chevron Zig-Zag |
| 8 | GNN SMTP Mail | Emerald `#059669` | Diagonal Hatch |
| 9 | GNN WPTheme | Indigo `#4f46e5` | Diamond Mesh |
| 10 | GNN Dashboard | Deep Navy `#0f172a` | Cyber Matrix Grid |

`admin.css` dosyasına yeni sınıfı ekle:

```css
/* N. GNN Eklenti Adı - Benzersiz Geometrik Desen */
.plugin-banner-eklentiadi {
    background:
        repeating-linear-gradient(45deg, rgba(255,255,255,0.12) 0px, rgba(255,255,255,0.12) 3px, transparent 3px, transparent 15px),
        linear-gradient(135deg, #BAŞLANGIÇ_RENGİ 0%, #BİTİŞ_RENGİ 100%) !important;
}
```

> **Kural:** Her kartta farklı bir `repeating-*` veya `radial-gradient` kombinasyonu kullan. Aynı renk tonunu iki kartta kullanma.

---

## 🔄 Adım 3 — ZIP İndirme Mekanizması (Bilgi)

Kurulum ve güncelleme sırasında ZIP URL şu öncelik sırasıyla çözülür:

1. **Release'e yüklenmiş özel ZIP asset** (`browser_download_url`) — en güvenilir
2. **`github.com/{owner}/{repo}/archive/refs/tags/{tag}.zip`** — public, auth gerektirmez ✅
3. ~~`zipball_url`~~ — **KULLANILMAZ**, GitHub authentication gerektirir ❌

GitHub Tag Release'e özel bir ZIP asset yüklemek **önerilir** ancak zorunlu değildir.
Source code archive (otomatik oluşturulan) da çalışır.

Çıkarılan klasör adı (`gnn-eklentiadi-1.0.3` veya `BigDesigner-gnn-eklentiadi-abc1234`) `fix_source_folder_name()` filtresi tarafından otomatik olarak `gnn-eklentiadi` haline getirilir.

---

## 🛡️ Adım 4 — Rozet ve Kart Koruması

Eklenti veya tema kartı oluşturulurken şu kurallara uy:

- **Sürüm Rozeti:** Kurulu ise `installed_version`, kurulu değilse `latest_version` gösterilir.
- **`gnn-wpdashboard` özel durumu:** Bu eklentinin kartında `Devre Dışı Bırak` ve `Sil` butonları gösterilmez. Yerine `Sistem Paneli` koruma rozeti yer alır. Bu mantık `createCardHTML()` içinde `item.slug === 'gnn-wpdashboard'` koşuluyla korunur.
- **Kart İkon Rozeti:** `.gnn-card-icon-badge` %100 solid `#ffffff` zemin — yarı şeffaflık olmamalı.
- **Modal İkon:** `.gnn-modal-icon-badge` koyu `#0f172a` kutu, `#ffffff` ikon.

---

## ✅ Adım 5 — Doğrulama Kontrol Listesi

Değişiklik tamamlandıktan sonra aşağıdaki kontrolleri sırasıyla çalıştır:

```bash
# 1. PHP sözdizimi kontrolü (ZORUNLU)
php -l gnn-wpdashboard/includes/class-gnn-wpdashboard-installer.php
php -l gnn-wpdashboard/gnn-wpdashboard.php
```

- [ ] `php -l` hatasız geçiyor
- [ ] Kart doğru kategoride görünüyor
- [ ] Kapak deseni (`plugin-banner-*`) düzgün render ediliyor
- [ ] Sürüm rozeti `installed_version` gösteriyor (kuruluysa)
- [ ] "Şimdi Kur" → "Güncelle" → kurulum akışı çalışıyor
- [ ] `$default_repos` içinde `'version'` anahtarı **yok**
- [ ] Kod içinde `zipball_url` kullanımı **yok**

---

## 📦 Release Alma Talimatı

GitHub'da yeni bir sürüm yayınlarken:

1. GitHub → **Releases** → **Draft a new release**
2. **Tag:** `v1.0.0` formatında (örn: `v1.2.3`)
3. **Target:** `main`
4. ZIP asset olarak `gnn-eklentiadi.zip` yüklemek **önerilir** (opsiyonel)
5. **Publish release**

> Panel, "Güncellemeleri Kontrol Et" butonuna basıldığında tüm transient cache'leri temizler ve GitHub API'den canlı sürüm bilgisini çeker.
