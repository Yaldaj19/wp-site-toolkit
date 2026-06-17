# WP Site Toolkit

> A modular WordPress plugin that bundles three essential site tools — **PWA**, **RSS Feeds**, and **XML Sitemap** — under one unified, extensible admin panel.
>
> یک افزونه‌ی ماژولار وردپرس که سه ابزار ضروری سایت — **PWA**، **فیدهای RSS** و **نقشه‌ی سایت XML** — را در یک پنل مدیریت واحد و قابل‌توسعه گرد هم می‌آورد.

[English](#english) · [فارسی](#فارسی)

---

<a name="english"></a>
## 🇬🇧 English

**WP Site Toolkit** is a single WordPress plugin that replaces three separate plugins. Instead of installing a PWA plugin, an RSS/feed plugin, and a sitemap plugin separately, you get all three as **modules** inside one lightweight, modular toolkit — with a single dashboard and a shared, extensible architecture.

- **Type:** WordPress plugin
- **Requires:** WordPress 5.8+, PHP 7.4+
- **Bilingual:** English + Persian (RTL-aware admin UI)
- **License:** GPL v2 or later

### Modules

This plugin contains three independent modules. Each one is self-contained and can be developed, enabled, or extended on its own.

#### 1. PWA — Progressive Web App

Turn your WordPress site into an **installable Progressive Web App (PWA)** on mobile and desktop.

- Dynamic **`manifest.webmanifest`** (name, short name, icons, theme color, display mode, start URL, scope)
- **Service Worker** with smart caching (offline support)
- Customizable **"Add to Home Screen" install button**
- Per-site theme color and branding
- **Keywords:** wordpress pwa, progressive web app, web app manifest, service worker, add to home screen, installable website, offline wordpress, mobile app from wordpress

#### 2. RSS Feeds

Manage and optimize your **RSS feeds** for content-discovery platforms and feed readers.

- Tuned feeds for **Google Discover** and RSS reader apps
- Per-post **exclude-from-feed** control
- Clean, valid RSS output with correct content type
- **Keywords:** wordpress rss, rss feed manager, google discover feed, feed optimization, syndication, atom feed, news feed, content distribution

#### 3. XML Sitemap

Generate a professional **XML Sitemap** so search engines crawl and index your content efficiently.

- Full support for all **post types**, **taxonomies**, **authors**, and **dates**
- **WooCommerce** products & product categories supported
- Dynamic or static (cached) generation modes
- Priority / change-frequency control, URL exclusions, search-engine **ping**
- **Keywords:** wordpress sitemap, xml sitemap generator, seo sitemap, google sitemap, woocommerce sitemap, sitemap.xml, search engine indexing, technical seo

### Unified Dashboard

All three modules live under a single top-level admin menu — **«ابزارهای سایت» / Site Tools** — with a dashboard that shows each module as a card with its status and a direct link.

### Modular & Extensible

Adding a new tool is a one-step process:

1. Create a folder under `modules/<your-slug>/`
2. Add a `loader.php` to it

The core auto-discovers and loads it. No edits to the main plugin file required.

```
wp-site-toolkit/
├── wp-site-toolkit.php       # Main plugin file (header, constants, bootstrap)
├── uninstall.php             # Full cleanup on delete
├── includes/
│   ├── class-toolkit.php     # Core: parent menu + module auto-loader
│   └── class-dashboard.php   # Tools dashboard (module cards)
├── modules/
│   ├── pwa/                  # PWA module
│   ├── rss-feeds/            # RSS Feeds module
│   └── sitemap/              # XML Sitemap module
└── assets/admin/             # Admin styles
```

### Installation

1. Copy the `wp-site-toolkit` folder into `wp-content/plugins/`
2. Go to **Plugins** in wp-admin and activate **WP Site Toolkit**
3. Open the **Site Tools** menu and configure each module (PWA / RSS Feeds / Sitemap)

### Uninstall

Deleting the plugin from WordPress runs `uninstall.php`, which removes module options, sitemap transients, and generated `sitemap*.xml` files. (Deactivating does **not** delete data.)

### Author

**Yalda Jahanshahi** — [yaldajahanshahi.ir](https://yaldajahanshahi.ir) · yaldaj.619@gmail.com

---

<a name="فارسی"></a>
## 🇮🇷 فارسی

**WP Site Toolkit** یک افزونه‌ی واحد وردپرس است که جای سه افزونه‌ی جداگانه را می‌گیرد. به‌جای نصب جداگانه‌ی یک افزونه‌ی PWA، یک افزونه‌ی RSS/فید و یک افزونه‌ی نقشه‌ی سایت، هر سه را به‌صورت **ماژول** درون یک toolkit سبک و ماژولار دریافت می‌کنید — با یک داشبورد واحد و معماری مشترک و قابل‌توسعه.

- **نوع:** افزونه‌ی وردپرس
- **نیازمندی‌ها:** وردپرس ۵.۸ به بالا، PHP 7.4 به بالا
- **دوزبانه:** انگلیسی + فارسی (رابط مدیریت با پشتیبانی RTL)
- **مجوز:** GPL v2 یا بالاتر

### ماژول‌ها

این افزونه شامل سه ماژول مستقل است. هر ماژول خودکفاست و می‌تواند جداگانه توسعه، فعال یا گسترش یابد.

#### ۱. PWA — وب‌اپ نصب‌شونده

سایت وردپرسی‌تان را به یک **وب‌اپ نصب‌شونده (PWA)** روی موبایل و دسکتاپ تبدیل کنید.

- **`manifest.webmanifest`** پویا (نام، نام کوتاه، آیکون‌ها، رنگ تم، حالت نمایش، آدرس شروع، scope)
- **Service Worker** با کش هوشمند (پشتیبانی آفلاین)
- **دکمه‌ی نصب «افزودن به صفحه‌ی اصلی»** قابل سفارشی‌سازی
- رنگ تم و برندینگ مخصوص هر سایت
- **کلیدواژه‌ها:** پی دبلیو ای وردپرس، وب اپلیکیشن، نصب سایت روی موبایل، اپ از سایت وردپرس، سرویس ورکر، آفلاین

#### ۲. فیدهای RSS

**فیدهای RSS** سایت را برای پلتفرم‌های کشف محتوا و اپ‌های خوانش فید مدیریت و بهینه کنید.

- فیدهای تنظیم‌شده برای **Google Discover** و اپ‌های فیدخوان
- کنترل **استثنا کردن هر نوشته از فید**
- خروجی RSS تمیز و معتبر با content type درست
- **کلیدواژه‌ها:** آر اس اس وردپرس، مدیریت فید، فید گوگل دیسکاور، سیندیکیشن، فید خبری، توزیع محتوا

#### ۳. نقشه‌ی سایت XML (Sitemap)

یک **نقشه‌ی سایت XML** حرفه‌ای بسازید تا موتورهای جستجو محتوای شما را کارآمد بخزند و ایندکس کنند.

- پشتیبانی کامل از همه‌ی **post type‌ها**، **taxonomy‌ها**، **نویسنده‌ها** و **تاریخ‌ها**
- پشتیبانی از محصولات و دسته‌بندی‌های **ووکامرس**
- حالت تولید پویا یا ساکن (کش‌شده)
- کنترل priority / change-frequency، استثنای URL، **پینگ** به موتور جستجو
- **کلیدواژه‌ها:** سایت مپ وردپرس، نقشه سایت، تولید sitemap.xml، سئو سایت مپ، سایت مپ ووکامرس، ایندکس گوگل، سئو تکنیکال

### داشبورد یکپارچه

هر سه ماژول زیر یک منوی اصلی واحد — **«ابزارهای سایت»** — قرار می‌گیرند، با داشبوردی که هر ماژول را به‌صورت یک کارت همراه با وضعیت و لینک مستقیم نشان می‌دهد.

### ماژولار و قابل‌توسعه

افزودن ابزار جدید یک مرحله است:

۱. یک پوشه زیر `modules/<اسلاگ>/` بسازید
۲. یک فایل `loader.php` در آن قرار دهید

هسته آن را خودکار شناسایی و لود می‌کند. نیازی به ویرایش فایل اصلی افزونه نیست.

### نصب

۱. پوشه‌ی `wp-site-toolkit` را در `wp-content/plugins/` کپی کنید
۲. در پیشخوان وردپرس به **افزونه‌ها** بروید و **WP Site Toolkit** را فعال کنید
۳. منوی **«ابزارهای سایت»** را باز کنید و هر ماژول (PWA / RSS / Sitemap) را تنظیم کنید

### حذف

حذف افزونه از وردپرس فایل `uninstall.php` را اجرا می‌کند که option‌های ماژول‌ها، transient‌های sitemap و فایل‌های `sitemap*.xml` تولیدشده را پاک می‌کند. (غیرفعال‌سازی داده‌ها را حذف **نمی‌کند**.)

### نویسنده

**یلدا جهانشاهی** — [yaldajahanshahi.ir](https://yaldajahanshahi.ir) · yaldaj.619@gmail.com
