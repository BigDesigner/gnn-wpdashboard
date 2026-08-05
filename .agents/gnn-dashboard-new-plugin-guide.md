# GNN WPDashboard — AI Agent Yeni Eklenti/Tema Ekleme Talimatı

Bu belge, gelecekte **GNN Dashboard** sistemine yeni bir eklenti veya tema eklerken arayüz, kapak deseni, kategori ve yükleme mekanizması düzenini bozmadan doğru entegrasyon yapabilmek için AI Agent'lar tarafından izlenecek standart prosedürü tanımlar.

---

## 📋 1. Adım: Depo Kaydı (`includes/class-gnn-wpdashboard-installer.php`)

`GNN_WPDashboard_Installer` sınıfı içerisindeki `$default_repos` dizisine yeni ögeyi ekleyin:

```php
'gnn-eklentiadi' => array(
    'name'         => 'GNN Eklenti Adı',
    'type'         => 'plugin', // 'plugin' veya 'theme'
    'owner'        => 'BigDesigner',
    'repo'         => 'gnn-eklentiadi',
    'file'         => 'gnn-eklentiadi/gnn-eklentiadi.php', // Temalar için sadece 'gnn-temadi'
    'category'     => 'Araçlar & Network', // Mevcut kategorilerden biri
    'description'  => 'Eklentinin işlevini açıklayan Türkçe net bir açıklama.',
    'icon'         => 'mail', // Google Material Symbols Outlined ikon adı
    'banner_class' => 'plugin-banner-eklentiadi', // Benzersiz CSS sınıfı
    'version'      => '1.0.0', // GitHub'daki güncel Tag Release sürümü
),
```

### 🎯 Mevcut Kategoriler (Tam Eşleşmeli):
- `SEO & Pazarlama`
- `Güvenlik & Analiz`
- `Medya & Dosya`
- `Araçlar & Network`
- `Temalar`

---

## 🎨 2. Adım: Kapak Tasarımı & CSS Deseni (`assets/css/admin.css`)

Her ögenin kapak kartı benzersiz bir CSS geometrik çizgi desenine ve renk gradyanına sahip olmalıdır. `assets/css/admin.css` dosyasına yeni sınıfı ekleyin:

```css
/* GNN Eklenti Adı - Özel Geometrik Desen */
.plugin-banner-eklentiadi {
	background: repeating-linear-gradient(45deg, rgba(255, 255, 255, 0.12) 0px, rgba(255, 255, 255, 0.12) 3px, transparent 3px, transparent 15px), linear-gradient(135deg, #059669 0%, #047857 100%) !important;
}
```

*Not: Başka bir kartta kullanılan renk ve desen kombinasyonunu birebir tekrar etmeyin (renk örn: Emerald, Indigo, Amber, Rose, Sky Blue).*

---

## 🛠️ 3. Adım: İkon İçi ve Rozet Koruması

Arayüz bileşenlerinin bütünlüğü için aşağıdaki CSS kurallarının korunduğundan emin olun:
- **Sol Beyaz İkon Rozeti:** `.gnn-card-icon-badge` %100 solid `#ffffff` arka plana sahip olmalı, yarı şeffaf renk sızdırma olmamalıdır.
- **Detay Modalı İkon:** Modaldaki `.gnn-modal-icon-badge` koyu lacivert `#0f172a` kutu ve `#ffffff` solid ikona sahip olmalıdır.
- **Sürüm Rozeti:** `.version-badge-pill` glassmorphism cam efektine sahip olmalıdır.

---

## 🔄 4. Adım: Yükleyici ve Klasör Adı İzolasyonu

- İndirmeler varsayılan olarak GitHub **Tag Release** `.zip` paketlerini kullanır.
- Extract işlemi sonrasında klasör adında versiyon veya `-main` takısı oluşsa bile `fix_source_folder_name` filtresi klasörü otomatik olarak standart slug adına (`gnn-eklentiadi`) dönüştürür. Ek bir kod yazılmasına gerek yoktur.

---

## ✅ 5. Adım: Doğrulama Adımları

Değişiklik tamamlandıktan sonra aşağıdaki kontrolleri çalıştırın:
1. `php -l gnn-wpdashboard/includes/class-gnn-wpdashboard-installer.php` (Sözdizimi kontrolü)
2. Dashboard'da kartın doğru kategoride belirdiğini, kapak deseninin düzgün göründüğünü ve "Şimdi Kur" butonunun çalıştığını doğrulayın.
