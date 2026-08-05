# GNN WPDashboard — AI Agent Çalışma Rehberi

Bu belge **GNN Dashboard** projesinde çalışan AI Agent'lar için bağlayıcıdır.
Koda dokunmadan **önce baştan sona okunur**. Tek bir adımın atlanması kabul edilemez.

> **Bu dosyadaki her teknik iddia ölçülerek doğrulanmıştır.** Doğrulama komutları
> ilgili bölümlerde verilmiştir. Bir iddiadan şüphelenirsen komutu çalıştır, tahmin yürütme.

---

## 🚨 BÖLÜM 0 — DAHA ÖNCE YAPILMIŞ HATALAR (ÖNCE BUNU OKU)

Aşağıdaki hataların **hepsi gerçekten yapıldı** ve çalışan sistemi bozdu.
Her biri saatler ve defalarca sürüm yayınlama döngüsü maliyetine yol açtı.

### Hata 0.1 — Anlamadığı kodu "temizlemek"

Bir agent `resolve_release_zip_url()` içindeki şu satırı çirkin bulup sildi:

```php
$possible_tags = array( 'v' . $ver_tag, $ver_tag, 'v1.5.3', '1.5.3', 'v1.1.0', 'v1.3.15', ... );
```

Liste gerçekten çirkindi **ama bir işe yarıyordu**: GitHub API rate limit'e takıldığında
gerçek sürüm etiketlerini yakalıyordu. Silinince kurulum tamamen bozuldu.

> **KURAL:** Bir kod parçasının **neden** orada olduğunu kanıtlayamıyorsan silme.
> "Gereksiz görünüyor", "kod tekrarı", "sadeleştirme" gerekçe değildir.
> Önce o kod olmadan hangi senaryonun bozulduğunu göster.

### Hata 0.2 — Ölçmeden teşhis koymak

Bir agent "eklenti kendi klasörünü sildiği için kurulum patlıyor" teşhisini
**mantık yürüterek** üretti ve düzeltme yazdı. Teşhis yanlıştı.

Kanıt zaten ortadaydı: `gnn-lightbox` kendi kendini silmiyor ama **birebir aynı**
hatayı veriyordu. Yanlış teşhise yazılan düzeltme yeni bir hata doğurdu
(`"Hedef klasör zaten var."`).

> **KURAL:** Düzeltme yazmadan önce hatayı **üret ve ölç**. Bir hata birden fazla
> eklentide görülüyorsa, o eklentilerin ortak noktasını bul — teoriyle başlama.

### Hata 0.3 — Doğrulanmamış varsayımı belgeye yazmak

Bu rehberin eski sürümünde şu yazıyordu:

> ~~"Source code archive (otomatik oluşturulan) da çalışır."~~

**Yanlıştı ve hiç kontrol edilmemişti.** Belge referans oldu, kod belgeyi uyguladı,
sistem aylarca kırılgan kaldı. Tek bir komut yeterdi:

```bash
gh api repos/BigDesigner/gnn-lightbox/contents --jq '.[].name'
# .agents .github .memory-bank .specs .tasks gnn-lightbox
# → kökte eklenti PHP'si YOK
```

> **KURAL:** Bu rehbere yazdığın her teknik cümlenin yanına doğrulama komutunu koy.
> Doğrulayamıyorsan yazma.

### Hata 0.4 — Çalışan davranışı yan etkiyle bozmak

Hedef klasörün kurulum öncesi silinmesi kaldırıldı, ama `WP_Upgrader`'ın bunu
kendisinin yapması için gereken bayrak eklenmedi. Sonuç: `"Hedef klasör zaten var."`

> **KURAL:** Bir güvenlik/temizlik adımını kaldırıyorsan, o adımın işini artık
> **kimin** yaptığını göster. Boşluğu doldurmadan kaldırma.

---

## 🔒 BÖLÜM 1 — DOKUNMA LİSTESİ

Bu fonksiyonlar tuhaf görünür ama **her satırı bir hatanın karşılığıdır**.
Değiştirmeden önce Bölüm 0'ı tekrar oku.

| Fonksiyon | Dosya | Neden korunuyor |
|---|---|---|
| `resolve_release_zip_url()` | `class-gnn-wpdashboard-installer.php` | API'siz fallback zinciri. Basitleştirilirse rate limit altında kurulum ölür. |
| `fetch_latest_tag_fallback()` | aynı | API kullanmadan gerçek tag'i bulur. Silinirse 403 durumunda sürüm bilinemez. |
| `fetch_release_asset_url()` | aynı | Asset **adını** release sayfasından okur. Tahmine çevrilirse kurulum bozulur. |
| `descend_to_package_root()` | aynı | Arşiv indirildiğinde gerçek eklenti klasörünü bulur. |
| `dir_contains_package()` | aynı | `Plugin Name:` başlığı / `style.css` araması. |
| `fix_source_folder_name()` | aynı | Çıkarılan klasörü slug'a çevirir. `upgrader_source_selection` filtresi. |
| `$install_args['overwrite_package']` | aynı | Bkz. Bölüm 3.2 — WordPress çekirdek davranışı. |

---

## 🔄 BÖLÜM 2 — ZIP İNDİRME MEKANİZMASI (DOĞRULANMIŞ)

### 2.1 Temel gerçek: eklenti depo kökünde değil

Bu depolarda eklenti **alt klasörde** durur:

```
gnn-lightbox/                 ← depo kökü
├── .agents/
├── .github/
├── .memory-bank/
├── .specs/
├── .tasks/
└── gnn-lightbox/             ← EKLENTİ BURADA
    └── gnn-lightbox.php      ← "Plugin Name:" başlığı burada
```

Doğrulama:

```bash
gh api repos/BigDesigner/gnn-lightbox/contents --jq '.[].name'
```

**Sonuç:** `archive/refs/tags/{tag}.zip` (source code archive) **geçerli bir eklenti
paketi DEĞİLDİR.** WordPress kökte `Plugin Name:` bulamaz ve şu hatayı verir:

| WP hata kodu | İngilizce | Türkçe (ekranda görülen) |
|---|---|---|
| `incompatible_archive_no_plugins` | The package could not be installed. | **"Paket kurulamadı."** |

Çekirdek kaynağı: `wp-admin/includes/class-plugin-upgrader.php:490`

### 2.2 Doğru kaynak: release'e ekli ZIP asset

Kaynak **her zaman** şudur:
`https://github.com/BigDesigner/{repo}/releases/latest` sayfasındaki `.zip` paketi.

Çözüm sırası (`resolve_release_zip_url()`):

| # | Yöntem | API? |
|---|---|---|
| 1 | Release asset (`browser_download_url`) | ✅ API |
| 2 | `releases/latest` yönlendirmesi → tag, sonra `releases/expanded_assets/{tag}` → asset adı | ❌ API'siz |
| 3 | `archive/refs/tags/{tag}.zip` — yalnızca son çare, `descend_to_package_root()` kurtarır | ❌ |
| — | ~~`zipball_url`~~ **KULLANILMAZ** — authentication gerektirir | — |

### 2.3 GitHub API rate limit istisna değil, NORMAL durum

Panel her açılışta depo sayısı kadar API isteği yapar. Paylaşımlı hosting'de IP
ortak olduğu için saatlik limit (403) **sık sık** dolar.

> **KURAL:** Kurulum yolu API'siz çalışmak **zorundadır**. API'yi tek kaynak
> yapan hiçbir düzenleme kabul edilmez. 2. adım hiçbir API çağrısı yapmaz.

### 2.4 Asset adını ASLA tahmin etme

İsimlendirme tutarsızdır — ölçülmüştür:

| Depo | Tag | Asset adı |
|---|---|---|
| `gnn-lightbox` | `v1.1.0` | `gnn-lightbox-v1.1.0.zip` (**v var**) |
| `gnn-terms-popup` | `v1.3.16` | `gnn-terms-popup-1.3.16.zip` (**v yok**) |
| `gnn-sitemap` | `v1.1.1` | `gnn-sitemap-1.1.1.zip` (**v yok**) |

Doğrulama:

```bash
for r in gnn-lightbox gnn-terms-popup gnn-sitemap; do
  tag=$(curl -s -I "https://github.com/BigDesigner/$r/releases/latest" \
        | grep -i '^location:' | grep -o 'tag/[^[:space:]]*' | cut -d/ -f2 | tr -d '\r')
  curl -s "https://github.com/BigDesigner/$r/releases/expanded_assets/$tag" \
    | grep -o "/BigDesigner/$r/releases/download/[^\"']*\.zip" | head -1
done
```

> **KURAL:** Ad her zaman release sayfasından **okunur**. `{repo}-v{ver}.zip` gibi
> bir kalıp üretme.

---

## ⚙️ BÖLÜM 3 — WORDPRESS UPGRADER DAVRANIŞI (DOĞRULANMIŞ)

### 3.1 İşlem sırası

`WP_Upgrader::run()` → `install_package()` içindeki gerçek sıra
(`wp-admin/includes/class-wp-upgrader.php`):

| Satır | İşlem |
|---|---|
| 76 | `download_package()` — indir |
| 114 | `unpack_package()` — aç |
| 601 | `upgrader_source_selection` filtresi — **bizim doğrulamamız burada** |
| 646 | `clear_destination()` — **hedef klasör burada silinir** |
| 702 | `copy_dir()` — kopyala |

> **KURAL:** Hedef klasörü **kurulum öncesi silme**. İndirme başarısız olursa
> çalışan eklenti yok olur. Silme işini upgrader'a bırak — o, paket indirilip
> doğrulandıktan **sonra** siler (646 > 601).

### 3.2 `install()` gönderilen `clear_destination`'ı YOK SAYAR

`Plugin_Upgrader::install()` ve `Theme_Upgrader::install()` yalnızca şu iki argümanı tanır:

```php
$defaults = array(
    'clear_update_cache' => true,
    'overwrite_package'  => false, // Do not overwrite files.
);
...
'clear_destination' => $parsed_args['overwrite_package'],   // ← BURASI
```

Yani `array( 'clear_destination' => true )` göndermek **hiçbir işe yaramaz.**
Doğru bayrak `overwrite_package`'tir. Eksikse şu hata alınır:

| WP hata kodu | İngilizce | Türkçe (ekranda görülen) |
|---|---|---|
| `folder_exists` | Destination folder already exists. | **"Hedef klasör zaten var."** |

Çekirdek kaynağı: `class-wp-upgrader.php:676`

Doğrulama:

```bash
curl -s https://raw.githubusercontent.com/WordPress/WordPress/master/wp-admin/includes/class-plugin-upgrader.php \
  | sed -n '/public function install(/,/^\t}/p' | grep -n "clear_destination\|overwrite_package"
```

---

## 📋 BÖLÜM 4 — YENİ EKLENTİ/TEMA EKLEME

### Adım 1 — Depo kaydı

**Dosya:** `gnn-wpdashboard/includes/class-gnn-wpdashboard-installer.php`

`$default_repos` dizisine **`'version'` anahtarı olmadan** ekle:

```php
'gnn-eklentiadi' => array(
    'name'         => 'GNN Eklenti Adı',
    'type'         => 'plugin',        // 'plugin' veya 'theme'
    'owner'        => 'BigDesigner',
    'repo'         => 'gnn-eklentiadi',
    'file'         => 'gnn-eklentiadi/gnn-eklentiadi.php', // Temalar için sadece klasör adı: 'gnn-temadi'
    'category'     => 'Araçlar & Network',
    'description'  => 'Eklentinin işlevini açıklayan Türkçe net bir açıklama.',
    'icon'         => 'mail',          // Google Material Symbols Outlined
    'banner_class' => 'plugin-banner-eklentiadi', // Adım 2'de oluşturulacak
),
```

> **`'version'` anahtarı eklenmez.** Sürüm release'ten canlı çözülür
> (önce API, sonra API'siz fallback — Bölüm 2.2). Hardcode sürüm, güncelleme
> algılamayı kırar.

**Kategoriler (tam eşleşmeli):**

| Kategori | Kullanım |
|---|---|
| `SEO & Pazarlama` | Sitemap, URL kısaltma, meta araçları |
| `Güvenlik & Analiz` | IP, WHOIS, güvenlik, onay popup |
| `Medya & Dosya` | Lightbox, dosya yönetimi, galeri |
| `Araçlar & Network` | Mail, ağ araçları, sistem paneli |
| `Temalar` | Yalnızca `'type' => 'theme'` ögeler |

### Adım 2 — Kapak tasarımı (ZORUNLU)

**Dosya:** `gnn-wpdashboard/assets/css/admin.css`

Her kartın **benzersiz** geometrik deseni ve renk gradyanı olmalı:

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

```css
/* N. GNN Eklenti Adı - Benzersiz Geometrik Desen */
.plugin-banner-eklentiadi {
    background:
        repeating-linear-gradient(45deg, rgba(255,255,255,0.12) 0px, rgba(255,255,255,0.12) 3px, transparent 3px, transparent 15px),
        linear-gradient(135deg, #BAŞLANGIÇ 0%, #BİTİŞ 100%) !important;
}
```

> **Kural:** Her kartta farklı bir `repeating-*` veya `radial-gradient` kombinasyonu
> kullan. Aynı renk tonunu veya deseni iki kartta kullanma.

> **YASAK:** `plugin-banner-*` CSS sınıfını `admin.css` içinde oluşturmadan
> eklentiyi `$default_repos` dizisine kaydetme. Kapağı olmayan kart bozuk görünür.

### Adım 3 — Rozet ve kart koruması

- **Sürüm rozeti:** kuruluysa `installed_version`, değilse `latest_version`.
- **`gnn-wpdashboard` özel durumu:** kendi kartında `Devre Dışı Bırak` ve `Sil`
  butonları gösterilmez; yerine `Sistem Paneli` rozeti çıkar.
  `createCardHTML()` içinde `item.slug === 'gnn-wpdashboard'` koşulu korunur.
- **Kart ikon rozeti:** `.gnn-card-icon-badge` %100 solid `#ffffff`.
- **Modal ikon:** `.gnn-modal-icon-badge` koyu `#0f172a` kutu, `#ffffff` ikon.

### Adım 4 — Dil dosyaları

Yeni çevrilebilir metin eklediysen üç dosya da güncellenir:

```
gnn-wpdashboard/languages/gnn-wpdashboard.pot
gnn-wpdashboard/languages/gnn-wpdashboard-tr_TR.po
gnn-wpdashboard/languages/gnn-wpdashboard-tr_TR.mo   ← derlenmiş, ZORUNLU
```

> **`.mo` olmadan çeviri yüklenmez.** `.po` tek başına işe yaramaz.
> Ortamda `msgfmt` yoksa `.mo` ikili formatta üretilir; ürettikten sonra
> magic sayı (`0x950412de`) ve kayıt sayısı kontrol edilerek doğrulanır.

> `assets/js/admin.js` içindeki metinler PHP çeviri sistemine **bağlı değildir**
> (sabit Türkçe). Çevrilebilir yapılacaksa `wp_set_script_translations` gerekir.

---

## ✅ BÖLÜM 5 — DOĞRULAMA KONTROL LİSTESİ

```bash
# 1. PHP sözdizimi (ZORUNLU — istisnasız)
php -l gnn-wpdashboard/gnn-wpdashboard.php
php -l gnn-wpdashboard/includes/class-gnn-wpdashboard-installer.php
php -l gnn-wpdashboard/includes/class-gnn-wpdashboard-updater.php
php -l gnn-wpdashboard/templates/dashboard-page.php
```

**Kod kontrolleri:**

- [ ] `php -l` hatasız geçiyor
- [ ] `$default_repos` içinde `'version'` anahtarı **yok**
- [ ] Kod içinde `zipball_url` kullanımı **yok**
- [ ] Kurulum yolu API'siz çalışıyor (Bölüm 2.3)
- [ ] Asset adı tahmin edilmiyor, okunuyor (Bölüm 2.4)
- [ ] Hedef klasör kurulum öncesi silinmiyor (Bölüm 3.1)
- [ ] `overwrite_package` bayrağı yerinde (Bölüm 3.2)
- [ ] Yeni metin varsa `.pot` + `.po` + `.mo` güncel
- [ ] `VERSION`, plugin header ve `CHANGELOG.md` aynı sürümü gösteriyor

**Panelde gözle kontrol (kod kontrolleri yerine geçmez):**

- [ ] Kart doğru kategoride görünüyor
- [ ] Kapak deseni (`plugin-banner-*`) düzgün render ediliyor
- [ ] Sürüm rozeti kuruluysa `installed_version` gösteriyor
- [ ] "Şimdi Kur" → "Güncelle" → kurulum akışı uçtan uca çalışıyor
- [ ] `gnn-wpdashboard` kartında `Sistem Paneli` rozeti var, `Sil` butonu **yok**

---

## 🧑‍💻 BÖLÜM 6 — ÇALIŞMA KURALLARI (AGENT DAVRANIŞI)

### 6.1 Git

- **Commit mesajları İNGİLİZCE yazılır.**
- **Yalnızca `main` branch'ine push yapılır.**
- **Tag OLUŞTURULMAZ. Release ALINMAZ.** Bunları proje sahibi kendisi yapar.
  `git tag`, `git push origin v*`, `gh release create` **çalıştırılmaz**.
- Geçmiş commit'ler yeniden yazılmaz (`rebase`, `--force`).

### 6.2 Sürüm yükseltme

Sürüm yükseltilirken **üç dosya birlikte** güncellenir:

| Dosya | Alan |
|---|---|
| `gnn-wpdashboard/VERSION` | tek satır, ör. `1.1.4` |
| `gnn-wpdashboard/gnn-wpdashboard.php` | `* Version:     1.1.4` |
| `gnn-wpdashboard/CHANGELOG.md` | yeni `## [1.1.4]` bölümü |

### 6.3 Değişiklik yapmadan önce

1. Hatayı **üret ve ölç.** Ekran görüntüsündeki hata metnini WordPress çekirdek
   kaynağında ara — hangi `WP_Error` kodundan geldiğini bul.
2. Aynı hata birden fazla eklentide mi görülüyor? Ortak noktayı bul.
3. Düzeltmeyi yazdıktan sonra **gerçek veriyle** doğrula (curl / gh api).
4. Doğrulamadığın hiçbir şeyi "düzeltildi" diye bildirme.

> **KURAL:** "Muhtemelen şundan kaynaklanıyor" cümlesi kullanıcıya aktarılmaz.
> Ya ölç, ya sus.

---

## 📦 BÖLÜM 7 — RELEASE ALMA (PROJE SAHİBİ İÇİN)

1. GitHub → **Releases** → **Draft a new release**
2. **Tag:** `v1.0.0` formatında (ör. `v1.2.3`)
3. **Target:** `main`
4. **ZIP asset ZORUNLUDUR.** Asset'siz release **kurulamaz** (Bölüm 2.1).
   Workflow (`.github/workflows/release.yml`) `v*` tag push'unda paketi otomatik ekler.
5. **Release açıklaması yazılmalıdır** — panel "Detaylar" ekranındaki sürüm notu
   bu alandan okunur. `CHANGELOG.md` bu amaçla **kullanılmaz**.
6. **Publish release**

> Panel, "Güncellemeleri Kontrol Et" butonuna basıldığında tüm transient cache'leri
> temizler ve sürüm bilgisini yeniden çözer (önce API, sonra API'siz fallback).

> **Not:** Kurulum mantığında değişiklik yapan bir sürüme geçilirken, kurulu olan
> **eski sürüm** eski kodu çalıştırdığı için panelden kendini güncelleyemeyebilir.
> Bu durumda yeni ZIP bir kez elle yüklenir; sonraki güncellemeler panelden çalışır.
