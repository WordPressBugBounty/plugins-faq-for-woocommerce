=== Happy FAQs - WordPress FAQ and Product FAQ ===
Contributors: happydevs, nayanchamp7, azha18
Tags: faq, woocommerce faq, product faq, accordion, schema
Requires at least: 3.6
Tested Up To: 7.0
Requires PHP: 7.4
Stable tag: 2.0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress FAQ and WooCommerce product FAQ plugin with accordion layouts, Google FAQ schema, AI generator, Gutenberg, Elementor, Divi, and shortcodes.

== Description ==

**Happy FAQs** is a WordPress FAQ plugin and a WooCommerce product FAQ plugin. Create unlimited FAQs in one library, then show them as an accordion on help pages, posts, and product pages.

Write the answer once. Reuse it on a FAQ page, in Gutenberg, Elementor, or Divi, and on WooCommerce products. WooCommerce is optional — use it as a site FAQ plugin, then add product FAQs when you have a store.

**Google FAQ schema** (FAQPage), an AI FAQ generator, comments, and custom accordion layouts are included.

[Demo](https://demo.happydevs.net/happyfaqs/product/beanie-with-logo/) | [Documentation](https://happydevs.net/docs/faq-for-woocommerce/overview) | [Video Tutorials](https://www.youtube.com/watch?v=CnsPBdgIbaU&list=PLqgTGQxEn_rZbnleeeuK18QJ984t6o3ZX&ab_channel=HappyDevs) | [Get Support](https://happydevs.net/support)

[youtube https://www.youtube.com/watch?v=CnsPBdgIbaU]

> **Upgrade to Happy FAQs Pro**<br />
> Upgrade to [Happy FAQs Pro](https://happydevs.net/happy-woocommerce-faqs-pro) now for priority email support and all PRO features.

> Pro adds FAQ Groups, AJAX FAQ search, extra accordion layouts, import/export, customer questions, variation product FAQs, and shop, cart, and checkout FAQs.

= Who it is for =

* **WordPress sites** that need a FAQ accordion, help center, or FAQ page with Google schema
* **WooCommerce stores** that need product FAQs, category FAQs, and question-and-answer content on products
* **Builders** using Gutenberg, Elementor, or Divi

= How it works =

Create a FAQ once. On that FAQ screen, the **Happy FAQs Panel** metabox is where you choose where it appears.

1. Go to **Happy FAQs → Add New**. The title is the question. The editor is the answer (HTML, images, and video are allowed).
2. Optionally assign a **FAQ category** (for example Shipping or Returns). You can later show that whole category on a help page.
3. Open the **Happy FAQs Panel** metabox and assign:
   * **Products** — search and attach specific WooCommerce products.
   * **Product categories** — every product in those categories inherits this FAQ.
   * **Product tags** — every product with those tags inherits this FAQ.
   * **Display in pages** — product pages (free). Shop, cart, and checkout are (PRO).
   * **Global FAQs (PRO)** — show this FAQ on every product.
   * **Product attributes (PRO)** — insert live values such as `{product_price}` in the answer.
4. To show FAQs on a normal WordPress page, use a shortcode, **Happy Prebuilt FAQs** or **Happy Custom FAQs** (Gutenberg block, Elementor widget, or Divi 4/5 module), or **Settings → FAQ page**.

You can also attach FAQs from the product edit screen. One FAQ can appear in more than one place.

= Top features =

* Unlimited WordPress FAQs and WooCommerce product FAQs
* FAQ accordion layouts (5 free, 4 more in Pro)
* Google FAQ schema (FAQPage) on pages and products
* FAQ categories, shortcodes, and an FAQ page setting
* Happy Prebuilt FAQs: Gutenberg block, Elementor widget, and Divi 4/5 module (library FAQs)
* Happy Custom FAQs: Gutenberg block, Elementor widget, and Divi 4/5 module (write Q&A on the page)
* AI FAQ generator
* Inline FAQ search
* Comments, media in answers, expand/collapse all
* Assign FAQs to products, product categories, and product tags
* WPML and Polylang
* FAQ Groups, AJAX search, import/export (PRO)
* Customer questions, variation FAQs, shop/cart/checkout FAQs (PRO)

= Free features =

**General FAQs**

* Unlimited FAQs in one reusable library
* Place FAQs on any WordPress page or post
* FAQ categories (Shipping, Returns, and so on)
* FAQ page setting: pick a page and Happy FAQs can inject the accordion
* Shortcodes for all FAQs, a FAQ category, selected FAQ IDs, or the current product

**Display and extras**

* Happy Prebuilt FAQs: Gutenberg block, Elementor widget, and Divi 4/5 module (library FAQs, same markup as the shortcode)
* Happy Custom FAQs: Gutenberg block, Elementor widget, and Divi 4/5 module (write Q&A on the page)
* Google FAQPage schema on help pages and product pages
* Inline search that filters the FAQs already on the page
* AI FAQ generator (OpenAI)
* Images, video, and other media in answers
* Expand / collapse all
* Comments on FAQs
* Five accordion layouts, colors, width, and display options
* Classic Editor and block editor support
* WPML and Polylang support

**Product FAQs**

* Assign FAQs to products from the product screen or the FAQ screen
* Inherit FAQs from WooCommerce product categories and tags
* Drag to sort FAQs on each product
* Product tab, show/hide answers on load, and tab order

= Shortcodes =

[Shortcode docs](https://happydevs.net/docs/faq-for-woocommerce/shortcodes/)

Show **all FAQs**:

`[ffw_template]`

Show a **FAQ category**. `faq_cat` is the FAQ category ID (Happy FAQs → Categories):

`[ffw_template faq_cat="32"]`

Show **selected FAQs**. `faq_ids` are FAQ post IDs (Happy FAQs → All FAQs):

`[ffw_template faq_ids="10,11,12"]`

Show FAQs for a **WooCommerce product**:

`[ffw_template id="20"]`

`id` is the WooCommerce **product ID**, not the FAQ ID. Find it under **Products → All Products**: hover the product, or open it and read `post=20` in the browser URL. Replace `20` with your product’s ID.

Show FAQs for the **current product** (use this on a single product page or product template):

`[ffw_template dynamic_post="true"]`

Pick an accordion layout with `template`:

* 1 Classic, 2 Whitish, 3 Trip, 4 Pop, 5 Basic
* Pro layouts (PRO): 6 Glow, 7 Smart, 8 Wow, 9 Zoom

Example: `[ffw_template template="1" id="20"]`

Sort with `order` (`ASC` or `DESC`) and `order_by` (`id`, `date`, `title`, `name`, `modified`, `comment_count`, `author`).

Pro adds `group_id` for FAQ Groups (PRO), and `[ffw_search]` for AJAX library search (PRO).

= Pro features =

[Upgrade to Happy FAQs Pro](https://happydevs.net/happy-woocommerce-faqs-pro)

**Library**

* FAQ Groups (PRO): named collections with drag-and-drop order and a copyable shortcode
* AJAX library search (PRO): autocomplete across all published FAQs (`[ffw_search]`, block, or Elementor widget)
* Hash deep-links (PRO): open a FAQ with a link such as `#ffw-faq-123`
* Extra accordion layouts (PRO): Glow, Smart, Wow, Zoom
* Import / export (PRO): FAQs, categories, groups, and product assignments as JSON
* RTL and multi-column layout (PRO)

**Store extras** (need WooCommerce)

* Customer asking questions (PRO): shoppers can submit a question, with email notifications
* Variation product FAQs (PRO)
* Extra product page locations (PRO): after Add to cart, top or bottom of the product page, and similar
* Product archive, shop, cart, and checkout FAQs (PRO)
* Dynamic product attributes (PRO): placeholders such as `{product_price}`
* Global product FAQs (PRO): appear on every product
* AJAX search on the product FAQ list (PRO)

= Get started =

1. Install and activate Happy WooCommerce FAQs.
2. Follow the setup wizard that opens on first activation, or add a few FAQs yourself.
3. Paste `[ffw_template]` on a page, insert **Happy Prebuilt FAQs** or **Happy Custom FAQs** (Gutenberg, Elementor, or Divi), or choose an FAQs page in **Happy FAQs → Settings**.
4. For a store, open a FAQ, use the **Happy FAQs Panel** metabox, and assign products, product categories, or tags.

[Documentation](https://happydevs.net/docs/faq-for-woocommerce/overview/) | [Support](https://wordpress.org/support/plugin/faq-for-woocommerce/) | [Rate the plugin](https://wordpress.org/support/plugin/faq-for-woocommerce/reviews/?filter=5)

== Installation ==

Requirements:

* WordPress 3.6 or later
* PHP 7.4 or later
* WooCommerce is optional. Install WooCommerce only if you need product FAQs.

= From the dashboard =

1. Go to **Plugins → Add New**.
2. Search for **Happy WooCommerce FAQs**, **Product FAQ**, or **WooCommerce FAQ**.
3. Install and activate.

= Manual =

1. Upload the `faq-for-woocommerce` folder to `/wp-content/plugins/`.
2. Activate the plugin.
3. Open **Happy FAQs** in the admin menu.
4. Add a FAQ, then show it with a shortcode, Gutenberg block, Elementor widget, Divi module, or Settings → FAQ page.

== Frequently Asked Questions ==

= Is this a WooCommerce FAQ plugin or a WordPress FAQ plugin? =

Both. Happy WooCommerce FAQs is a WordPress FAQ accordion plugin. Product FAQs work when WooCommerce is active. You can use it without WooCommerce for site FAQs.

= Do I need WooCommerce? =

No. Use it as a site FAQ plugin. WooCommerce is only needed for product FAQs and store extras.

= Does it work with any theme? =

Yes. It works with standard WordPress themes.

= How do I add a product FAQ? =

Go to **Happy FAQs → Add New**, write the question and answer, then in the **Happy FAQs Panel** metabox search and select products. You can also assign product categories or tags so many products inherit the same FAQ. Or open a product and attach FAQs there.

= How do I show FAQs on a normal page, not a product? =

Paste `[ffw_template]` in the page, insert **Happy Prebuilt FAQs** (library) or **Happy Custom FAQs** (write Q&A on the page) as a Gutenberg block, Elementor widget, or Divi 4/5 module, or pick a page in **Happy FAQs → Settings → FAQ page**.

= How do I find the product ID for the shortcode? =

Go to **Products → All Products**. Hover the product, or edit it and look at the URL: `post=20` means the product ID is `20`. Use `[ffw_template id="20"]`.

= Can I assign product categories and tags to FAQs? =

Yes, in the free version. Every product in that category or tag can inherit those FAQs.

= Can I show FAQs on shop, cart, checkout, or archive pages? =

Product pages are included in free. Shop, cart, checkout, and product archive locations are Pro.

= Does it output Google FAQ schema? =

Yes. FAQPage schema is included for help pages and product pages. Duplicate questions are removed so Google can show FAQ rich results.

= Can I use Gutenberg, Elementor, or Divi? =

Yes. Free includes **Happy Prebuilt FAQs** and **Happy Custom FAQs** as a Gutenberg block, Elementor widget, and Divi 4/5 module. Prebuilt shows FAQs from your library. Custom lets you write questions and answers on the page.

= Can I write HTML or add media in answers? =

Yes. Answers use the WordPress editor, so you can add HTML, images, and video.

= Can I sort FAQs? =

Yes. Drag FAQs on a product, or use `order` and `order_by` on the shortcode.

= How many accordion layouts are there? =

Five in free (Classic, Whitish, Trip, Pop, Basic). Four more in Pro (Glow, Smart, Wow, Zoom).

= What is the difference between inline search and AJAX library search? =

Inline search (free) filters the FAQs already on the page. AJAX library search (Pro) searches all published FAQs and can be placed anywhere with `[ffw_search]`.

== Screenshots ==
1. FAQs in Product Page
2. Google FAQs Schema Support
3. AI FAQs Generator
4. Customer Asking Questions
5. FAQs in Shop Page
6. FAQs in Cart Page
7. FAQs in Checkout Page
8. Dynamic Product Attributes
9. Global FAQs
10. RTL Support
11. Beautiful FAQs Templates

== Changelog ==

= 2.0.5 =
* Added: Setup wizard on first activation. Choose General or Product FAQs, start from common questions and replies, copy your shortcode, and finish in a few clicks. Every step can be skipped.
* Fixed: Removing a FAQ from a product, category, tag, or page could remove a different FAQ from it.
* Fixed: Saving a FAQ after removing all of its product tags caused an error.
* Fixed: AI FAQ generator now shows API errors and rejects empty fields.
* Security: Product, category, tag, and page inputs are sanitized when a FAQ is saved.

= 2.0.4 =
* Fixed: Happy Prebuilt FAQs Gutenberg block styles in the editor.
* Updated: Gutenberg block metadata so WordPress.org can list the plugin's blocks.

= 2.0.3 =
* Fixed: Product archive FAQs displayed when Enable FAQs in Archive was off.
* Updated: Removed the Shortcode column from the All FAQs list.

= 2.0.2 =
* Added: WordPress version 7.0.0 compatability.

= 2.0.1 =
* Added: Site-wide FAQs. WooCommerce is now optional.
* Added: Shortcode modes for all FAQs, FAQ categories, and selected FAQ IDs.
* Added: FAQ page setting, FAQPage schema on general pages, and onboarding samples.
* Added: Happy Prebuilt FAQs Gutenberg block, Elementor widget, and Divi 4/5 module. Same markup as the shortcode.
* Added: Happy Custom FAQs Gutenberg block, Elementor widget, and Divi 4/5 module. Write questions and answers on the page.

= 1.8.17 =
* Added: WooCommerce 10.6 version compatability.
* Fixed: Shortcode in archive page not working.

= 1.8.16 =
* Added: WPML translations support.
* Added: WordPress 6.9 version compatability.

= 1.8.15 =
* Added: Shortcode page in admin.

= 1.8.14 =
* Fixed: Plugin add new page warning has been fixed.

= 1.8.13 =
* Fixed: Product name missing in FAQs admin list, issue has been fixed.
* Fixed: Appsero Tracker warning fixed.
* Enhancement: Essentials resources enhanced.

= 1.8.12 =
* Fixed: Responsive issue fixed.

= 1.8.11 =
* Added: Polylang translations support added.

= 1.8.10 =
* Added: Filter to change faqs of shortcode.
* Added: WordPress 6.8 compatability added.

= 1.8.9 =
* Fixed: Responsive issue fixed.
* Enhancement: Responsive issue fixed.

= 1.8.8 =
* Enhancement: Readme updated.

= 1.8.7 =
* Fixed: Duplicate function issue for free and premium version fixed.

= 1.8.6 =
* Added: WooCommerce 9.6 compatibility added.

= 1.8.5 =
* Added: Video playlist in readme file.

= 1.8.4 =
* Fixed: Shortcode override - issue has been fixed

= 1.8.3 =
* Fixed: Customer Question Form shortcode displaying - issue has been fixed

= 1.8.2 =
* Fixed: 'Basic Template' style override - issue has been fixed
* Fixed: 'Glow Template' style override - issue has been fixed

= 1.8.1 =
* Fixed: Tabs hidden for product who have no faqs - issue has been fixed

= 1.8.0 =
* Added: Customer Question Asking compatability - Advanced Version

= 1.7.8 =
* Added: WordPress 6.7 compatability added
* Fixed: Shortcode content appears before content - issue has been fixed.

= 1.7.7 =
* Fixed: Discount Banner is not closing - issue has been fixed.
* Added: Compatability to display FAQs in Shop Page, Cart Page and Checkout Page (Advanced Version).

= 1.7.6 =
* Added: Rebranding to Happy WooCommerce FAQs.
* Added: Support for Display FAQs in Product Archive pages (category & tag) - Advanced version.

= 1.7.5 =
* Added: FAQs by Product Tags - controls added.
* Added: FAQs by Product Categories - controls added.

= 1.7.4 =
* Added: AppSero latest version compatability.

= 1.7.3 =
* Fixed: Search input html broken - issue fixed.
* Fixed: ffw_template shortcode not fetching correct faqs - issue fixed.

= 1.7.2 =
* Enhancement: Template page has been separated from setting submenu.

= 1.7.1 =
* Added: WordPress 6.6 compatibility added.
* Added: WooCommerce 9.0 compatibility added.
* Fixed: New faqs can not be added for the 'shop manager' role - issue has been fixed.
* Fixed: FAQs schema generated null faqs - issue has been fixed.

= 1.7.0 =
* Added: Introducing 'AI FAQs Generator'.
* Added: Global FAQs appearance option to set global faqs displaying first or last of other faqs.

= 1.6.4 =
* Fixed: Broken UI in product page when empty faqs - issue has been fixed.

= 1.6.3 =
* Enhancement: Removed extra buttons when faqs are published.
* Enhancement: Dashboard UI improvement and more user-friendly.

= 1.6.2 =
* Fixed: FAQs fetching from the advanced version previous setup issue has been fixed.

= 1.6.1 =
* Added: Filter for Global FAQs [Pro Feature].

= 1.6.0 =
* Added: Assign product categories to FAQs compatibility [Pro Feature].
* Fixed: Removing products from the FAQs not working, The issue is fixed.

= 1.5.1 =
* Updated: AppSero SDK updated.

= 1.5.0 =
* Added: Variation product support (Premium version).
* Added: WordPress 6.4 compatability added.
* Fixed: Bulk faqs causing issue has been fixed.
* Enhancement: Query speedup.

= 1.4.14 =
* Added: HPOS (High Performance Order Storage) compatability added.

= 1.4.13 =
* Added: Display location to display faqs anywhere on product page [Advanced Version].

= 1.4.12 =
* Fixed: Hiding menu issue fixed.

= 1.4.11 =
* Added: Advanced FAQs templates support.
* Fixed: Basic template with custom styling issue fixed.
* Fixed: FAQs templates not be displayed after activation issue fixed.

= 1.4.10 =
* Added: Dashboard page added.

= 1.4.8 =
* Updated: Admin assets version updated.

= 1.4.7 =
* Added: Gutenberg editor support.

= 1.4.6 =
* Fixed: Happy WooCommerce FAQs admin menu hide issue has been fixed.

= 1.4.5 =
* Added: New template added (Basic template).
* Added: Introduced template page.
* Updated: New settings UI.
* Tested: WordPress 6.3 version compatibility tested.

= 1.4.4 =
* Fixed: Clear search input at product edit page.
* Updated: Settings priority input text to number.
* Updated: Settings UI updated to new UI.

= 1.4.3 =
* Added: WooCommerce 7.9.0 version compatibility tested.
* Added: WordPress 6.2.0 version compatibility tested.

= 1.4.2 =
* Fixed: FAQ Count style broken issue fixed.

= 1.4.1 =
* Added: WordPress 6.1.0 version compatibility tested.
* Added: WooCommerce 7.0.0 version compatibility tested.
* Fixed: Undefined index `post_type` issue has been fixed.
* Fixed: Undefined index `ffw_faq_products` issue has been fixed.
* Fixed: Deleted FAQ shows in the product issue has been fixed.

= 1.4.0 =
* Added: Product selector metabox in FAQ edit page.
* Fixed: Can't declare FS_Storage class issue has been fixed.

= 1.3.35 =
* Fixed: After deleting all the faqs, new insertion does not work properly has been fixed.
* Updated: Performance boosted.

= 1.3.34 =
* Fixed: Question and border color doesn't work for classic template has been fixed.
* Updated: Classic template UI updated.

= 1.3.33 =
* Fixed: Setting page design broken issue has been fixed.

= 1.3.32 =
* Added: WordPress Tested upto 6.0 version.

= 1.3.31 =
* Added: Expand/Collapse all faqs settings.
* Added: Dashboard new UI.

= 1.3.30 =
* Added: FAQ post type pages index/noindex settings.

= 1.3.29 =
* Added: FAQ schema support for faq shortcode.

= 1.3.28 =
* Fixed: FAQ tab shows empty faq - issue has been fixed.

= 1.3.27 =
* Added: Order and sorting feature for shortcode, order and orderby parameter added.

= 1.3.26 =
* Fixed: Woocommerce Tested upto 6.3 version.

= 1.3.25 =
* Fixed: WordPress Tested upto 5.9.2 version.

= 1.3.24 =
* Fixed: Fatal Error about Too few arguments has been solved.

= 1.3.23 =
* Added: FAQs by categories shortcode added, please see description to know how to add specific categories faqs by shortcode.

= 1.3.22 =
* Added: WordPress 5.9 version compatibility added.

= 1.3.21 =
* Added: Woocommerce 6.0.0 version compatibility added.
* Fixed: Undefined before & after output for fresh installation - issue has been fixed.

= 1.3.20 =
* Fixed: Undefined function for schema does not work - issue has been fixed.

= 1.3.19 =
* Added: Schema description type options and schema settings. Now user can choose to add description with HTML and without HTML.
* Fixed: New product adding page has faq broken markup - issue has been fixed.

= 1.3.18 =
* Added: Product FAQs Comments support has been added. You just have to enable the comment support from "Woo FAQs > Settings > Comment" page.

= 1.3.17 =
* Added: Show or Hide FAQs Counter in front with faqs tab. By default, the counter option is hide, you need to enable it from the settings and faqs counter will show for the current product page.
* Fixed: Performance enhancement.

= 1.3.16 =
* Added: Extra form submit button added in setting page at the top.
* Added: Documentation page link has been added in the plugin page.

= 1.3.15 =
* Fixed: During FAQ search clear an extra faq added in product edit page, the issue has been fixed.
* Fixed: Deleting the extra FAQ not working, the issue has been fixed.

= 1.3.14 =
* Added: FAQ answer text color style setting has been added.
* Added: WooCommerce 5.7.1 compatibility added.

= 1.3.13 =
* Fixed: Ajax issue of the faqs adding from search in product page has been fixed.

= 1.3.12 =
* Improved: Manages boring review notice.
* Improved: WordPress 5.8.1 compatibility tested.

= 1.3.11 =
* Added: New Settings for general and dynamic shortcode preview.

= 1.3.10 =
* Added: Dynamic product id feature for shortcode to use in single product page has been added.

= 1.3.9 =
* Added: WordPress 5.8 version compatibility tested.

= 1.3.8 =
* Fixed: FAQ sorting does not work after insert from search and quick add form - issue has been solved.
* Added: Loader added after adding faq from search options.

= 1.3.7 =
* Added: Demo and Documentation link added in readme.
* Fixed: Classic template spacing issue has been fixed.

= 1.3.6 =
* Fixed: FAQ answer page load issue fixed.
* Fixed: Code enhancement.

= 1.3.5 =
* Added: FAQ Reorder settings added.
* Added: FAQ Answers on page load settings added.

= 1.3.4 =
* Fixed: Post content as faq answer showing issue has been fixed.

= 1.3.3 =
* Fixed: Trip template Iframe issue has been solved. Thanks to @gao9099 to inform us.

= 1.3.2 =
* Fixed: Select2 undefined issue has been solved.

= 1.3.1 =
* Added: FAQs counter in product list table, counter show/hide options in settings page.
* Added: FAQs settings instruction has been added, every options are instructed.
* Added: FAQs post type access roles, admin can set roles to access ffw post type.

= 1.3.0 =
* Added: FAQs post feature.
* Added: FAQs sortable feature.
* Added: FAQs translation file.

= 1.2.8 =
* Added: FAQs width control in style.
* Added: FAQs tab label is now dynamic.
* Fixed: FAQs Schema mainEntity issue solved.

= 1.2.7 =
* Tweak: WordPress 5.7 compatibility tested.

= 1.2.6 =
* Added: Minified all the assets files.

= 1.2.5 =
* Tweak: Admin setting preview templates in apple monitor look.

= 1.2.4 =
* Fixed: Popup position wrong has been solved.

= 1.2.3 =
* Added: Setting link added as action link.

= 1.2.2 =
* Added: Schema support added.
* Fixed: Global bootstrap file loaded issue solved.

= 1.2.1 =
* Fixed: Setting page design broken issue solved.
* Fixed: Undefined function issue solved.

= 1.2.0 =
* Added: Custom styling options.
* Fixed: Popup wrong position solved.

= 1.1.7 =
* Fixed: Junk file cleaned.
* Tweak: Performance enhancement.

= 1.1.6 =
* Added: Plugin compatibility test with wordpress 5.6.

= 1.1.5 =
* Added: Media/Image support for faq answer.
* Added: Media/Image support for before/after content.

= 1.1.4 =
* Fix: Speed Optimization.
* Fix: Clean junks.

= 1.1.3 =
* Added: Shortcode support.
* Fix: Label showing when faqs empty, solved.

= 1.1.2 =
* Tweak: Admin panel preview new look.
* Fix: After insert faq answer as html.

= 1.1.1 =
* Tweak: Textdomain updated.
* Fix: Pop template animation.

= 1.1.0 =
* Added: Pop template.

= 1.0.9 =
* Fix: Setting page escaping issues.
* Tweak: Performance enhancement.

= 1.0.8 =
* Added: Trip template.
* Fix: Junk cleaned.

= 1.0.7 =
* Fix: Coding Standard solved.
* Tweak: Cleaning and enhancement.

= 1.0.6 =
* Tweak: Runtime template preview.

= 1.0.5 =
* Added: Whitish front template.

= 1.0.4 =
* Added: Product metabox loader.

= 1.0.3 =
* Tweak: FAQ options header button new style.
* Fix: FAQ options static line to dynamic.
* Fix: Junk clean up.

= 1.0.2 =
* Added: Update faq feature.
* Tweak: DOM manipulation in faq list.
* Fix: Code enhancement.

= 1.0.1 =
* Fix: Add or delete faq issue.
* Tweak: Rename Labels.

= 1.0.0 =
* First Release.