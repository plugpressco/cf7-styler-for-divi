=== Styler Mate for Contact Form 7 ===

Contributors: badhonrocks, plugpressco
Tags: contact form 7, cf7, form styler, ai form generator, multi-step form
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 3.0.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Contact Form 7 add-on with an AI form generator, visual styling for every major page builder, and extra field types.

== Description ==

Contact Form 7 is rock-solid but plain. CF7 Mate fills in the parts most people end up writing CSS or shopping around for: a clean visual styler that lives inside your page builder, an AI generator that writes a working CF7 form from a one-sentence description, and a few extra field types CF7 doesn't ship — star ratings, range sliders, an international phone field.

Nothing replaces CF7. You still build and manage forms in the regular CF7 editor. CF7 Mate just makes the result look the way you want and adds the things power users keep asking for.

Works with **Divi 4 & 5**, the **WordPress block editor**, **Elementor**, and **Bricks**.

[Product site](https://cf7mate.com) · [Documentation](https://cf7mate.com/docs) · [Pricing](https://cf7mate.com/pricing) · [GitHub](https://github.com/plugpressco/cf7-styler-for-divi)

= AI Form Generator (free) =

Describe the form you want in plain English — "a booking form with name, date, time slot, and a notes box" — and CF7 Mate writes the Contact Form 7 markup for you. The result is a normal CF7 form you can save, edit field-by-field, or extend with any other CF7 plugin. There's no proprietary format and no lock-in.

You bring your own OpenAI (or compatible) API key. Generation costs a fraction of a cent per form, and your key is stored only in your own WordPress database.

Useful for contact forms, booking forms, surveys, NPS questionnaires, quote requests, job applications, and anything else you'd normally build by hand.

= Visual styling that lives in your builder =

Open a CF7 form inside Divi, Elementor, Bricks, or the WordPress block editor and you get the same point-and-click styling controls you already use for the rest of your page — colors, typography, spacing, focus states, responsive breakpoints. No CSS file to maintain, no shortcode to remember.

The integrations are real builder modules, not generic shortcode wrappers, so they pick up your theme's global colors, presets, and breakpoints automatically.

= Extra field types CF7 doesn't ship =

* **Star rating** for reviews, feedback, and CSAT-style questions.
* **Range slider** for budgets, quantities, and quote calculators.
* **International phone number** with a country flag picker and dial-code prefixing.
* **Heading**, **separator**, **image**, and **icon** decorative elements to break up long forms.

Each one is registered as a real CF7 form tag — the values land in your notification emails like any built-in field.

= Grid layout for CF7 =

Arrange fields into responsive multi-column rows so your forms don't look like a stack of inputs on desktop. Per-breakpoint column counts mean a 3-column lead form on desktop still stacks cleanly on phones.

= Pro features =

The Pro plan ([cf7mate.com/pricing](https://cf7mate.com/pricing)) adds the heavyweight features most form-building plugins charge extra for:

* **Multi-step forms** — split long forms into a guided wizard so visitors don't bounce.
* **Conditional logic** — show, hide, or skip fields and steps based on what the user enters.
* **Form entries** — save every submission to your own WordPress database with search, filter, and CSV export. Never lose a lead to a missed email.
* **Form analytics** — per-form views, submissions, and conversion rate, stored locally, no third-party tracker.
* **Form scheduling** — open and close any form on a date/time window with a custom closed-state message.
* **Conditional email routing** — send notifications to different recipients based on what the user picked.
* **Save and continue later** — let visitors resume long forms from an email link.
* **Style presets** — save a styled form as a reusable preset and apply it to other forms in one click.
* **White label** (Agency plan) — replace CF7 Mate branding with your own for client sites.

Everything stays inside the standard CF7 workflow. Pro doesn't fork CF7 or hide your data — entries live in your database, schedules in post meta, analytics in your database.

= Who this is for =

Anyone who uses Contact Form 7 and wants modern styling, smarter forms, or both. We hear most often from agencies and freelancers building branded forms for clients, service businesses capturing leads, and event organizers running registration windows.

= Privacy =

The free plugin sends nothing off your site. The AI generator sends only your design prompt (not user submissions) to the AI provider you configure. The Pro license check exchanges your license key with our licensing server — no form data, no submissions. Form Entries stores submissions only in your own database.

= Source =

The free plugin is open source: [github.com/plugpressco/cf7-styler-for-divi](https://github.com/plugpressco/cf7-styler-for-divi). Issues and PRs welcome.

== Installation ==

= Standard Installation =

1. In your WordPress admin, go to **Plugins > Add New** and search for "CF7 Mate".
2. Click **Install Now**, then **Activate**.
3. Ensure [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) is also installed and active.
4. Go to **CF7 Mate** in the admin sidebar to enable the features you want to use.
5. Open your page builder and insert the CF7 Mate module, block, widget, or element.

= Manual Installation =

1. Download the plugin ZIP file from WordPress.org or your account at cf7mate.com.
2. In your WordPress admin, go to **Plugins > Add New > Upload Plugin** and upload the ZIP file.
3. Activate the plugin from the Plugins screen.
4. Follow steps 3 to 5 above.

= Requirements =

* WordPress 6.0 or higher
* PHP 7.4 or higher
* [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) installed and active
* One supported page builder if you want visual styling: Divi 4, Divi 5, the WordPress block editor (Gutenberg), Elementor, or Bricks

== Frequently Asked Questions ==

= Is CF7 Mate free? =

Yes. CF7 Mate is free to install and use on unlimited sites. The AI form generator, visual styling for every supported builder, all extra field types, and the grid layout tool are included in the free version. Advanced features — multi-step forms, conditional logic, form entries storage, form analytics, scheduling, email routing, partial save and resume, style presets, and white label — require a CF7 Mate Pro license.

= Is the AI form generator really free? =

Yes. The AI form generator itself is part of the free plugin. You bring your own API key from your AI provider (for example OpenAI). CF7 Mate sends your design prompt to that provider, which charges you only its standard usage cost. Generating a typical form costs a fraction of a cent.

= Does CF7 Mate replace Contact Form 7? =

No. CF7 Mate extends Contact Form 7. You continue to create, edit, and manage your forms inside the regular CF7 editor. CF7 Mate adds visual styling, additional field types, layout tools, and Pro features such as entries and multi-step on top of the standard CF7 workflow.

= Do I need Divi to use CF7 Mate? =

No. CF7 Mate works with Divi 4, Divi 5, the WordPress block editor (Gutenberg), Elementor, and Bricks. You only need one supported builder. If you do not use a page builder at all, you can still use the Gutenberg block in the standard WordPress editor or place a CF7 shortcode anywhere on your site.

= Is CF7 Mate compatible with Divi 5? =

Yes. CF7 Mate ships a dedicated module built for the new Divi 5 architecture, alongside continued support for Divi 4. The same plugin handles both Divi versions automatically.

= Can I save Contact Form 7 submissions to my WordPress database? =

Yes, with CF7 Mate Pro. The Form Entries feature saves every submission to your own database and provides an admin page to view, search, filter by date, and export entries as CSV. You decide on a per-form basis which forms save entries.

= Do multi-step forms work with conditional logic? =

Yes, in Pro. Conditional logic and multi-step forms work together. You can show or hide individual fields and skip entire steps based on what the user has already entered.

= Can I schedule when a Contact Form 7 form opens and closes? =

Yes, with Pro. Form Scheduling lets you set a start and end date for any form and configure a custom message that is shown when the form is closed. Useful for events, time-limited registrations, and seasonal offers.

= Can I route Contact Form 7 emails based on field values? =

Yes, with Pro. The Conditional Email Routing feature sends notification emails to different recipients based on what the user selected or entered. For example, route sales inquiries to one address and support requests to another.

= Will CF7 Mate slow down my site? =

No. CF7 Mate only enqueues its CSS and JavaScript on pages that actually render a CF7 Mate form. Pages without a CF7 Mate form load zero additional assets from the plugin. The plugin uses asynchronous loading where supported and the admin app code is split out of front-end bundles.

= Can I use the free version on multiple sites? =

Yes. The free plugin is GPL-licensed and may be used on unlimited sites at no cost.

= Where can I get support? =

* Documentation: [cf7mate.com/docs](https://cf7mate.com/docs)
* Support requests: [cf7mate.com/support](https://cf7mate.com/support)
* Community: [PlugPress Facebook Group](https://facebook.com/groups/plugpress)
* WordPress.org support forum (free plugin only): the Support tab on this plugin's WordPress.org page

= Why is the plugin slug "cf7-styler-for-divi" if it works with other builders? =

The plugin was originally released as CF7 Styler for Divi. The WordPress.org slug cannot be changed without losing the install base, ratings, and review history, so the slug remains the same while the product name has been updated to CF7 Mate. The plugin now supports Gutenberg, Elementor, and Bricks in addition to Divi.

= What happens if I install Pro alongside the free plugin? =

The free plugin detects when CF7 Mate Pro is also active and automatically deactivates itself to avoid running two copies. You will see a one-time admin notice confirming this. All settings, feature toggles, and form configurations are preserved across the switch.

= Does CF7 Mate work with caching plugins and page caching? =

Yes. CF7 Mate renders forms through standard Contact Form 7 hooks, so any caching plugin that already works with CF7 (WP Rocket, W3 Total Cache, LiteSpeed Cache, and others) works with CF7 Mate too. AJAX submissions remain dynamic and are not affected by full-page caching.

= Is the data stored by Form Entries GDPR-compliant? =

Form Entries stores submission data only in your own WordPress database — no third-party service is involved. To support GDPR data-subject requests you can delete individual entries from the admin, bulk-delete by date range, or export an entry to fulfill an access request. You are responsible for displaying your own privacy policy and obtaining consent through the form (the standard CF7 Acceptance field can be used for this).

== Screenshots ==

1. AI form generator. Describe a Contact Form 7 form in plain English and receive a ready-to-use CF7 shortcode.
2. Visual form styling inside the Divi Builder. Customize every part of a Contact Form 7 form without writing CSS.
3. Gutenberg block with live server-rendered preview in the WordPress editor.
4. Multi-step Contact Form 7 form with progress indicator and per-step navigation (Pro).
5. Form Entries admin page. View, search, filter, and export every Contact Form 7 submission (Pro).
6. Conditional logic editor. Show or hide fields based on user answers (Pro).
7. CF7 Mate admin dashboard with feature toggles and per-feature settings panels.

== Changelog ==

= 3.0.5 =
* Fixed: forms saved with the pre-3.0 plugin (using `[dcs_row]` / `[dcs_col_half]` / `[dcs_col_full]` shortcodes) now render correctly again. The legacy column shortcodes are translated into the current grid markup on the fly, including any custom class attribute on the row or column.
* Fixed: international phone field no longer drops the digits the user typed when JavaScript is unavailable on the page. The visible input is now the submitted field; emails contain the full number every time.
* Fixed: required validation for Star Rating, Range Slider, and Phone Number now actually fails server-side when the user skips the field. Previously the `*` suffix only added a marker without enforcing anything.
* Improved: AI Form Generator modal redesigned. Smaller footprint, two clean states (write your prompt → review and insert), single-row preset list, lighter image-attach affordance.
* Improved: Star Rating supports keyboard navigation (Arrow keys, Home, End) and uses a single CSS mask sprite instead of repeating inline SVG for every star.
* Improved: Range Slider accepts decimal `step` values (e.g. `step:0.5`) and announces the new value to screen readers as the user drags.
* Improved: free fields (star, range, phone) share a single focus ring and baseline so they line up with native CF7 text inputs in the same form.
* Improved: review notice and Pro upgrade notice now only appear on CF7 Mate's own admin pages, never site-wide on Dashboard, Plugins list, or post editors. Both are dismissible with sensible re-show cadences (review: 30 days → 90 days → never; upgrade: 90 days → never).
* Improved: "More from PlugPress" sidebar now uses the real DiviPeople and DiviTorque brand logos.
* Internal: author and URLs updated to the new PlugPress organisation.

= 3.0.4 =
* Improved: "More from PlugPress" sidebar redesigned with colored brand avatars, a "See all" link, and a curated product list.
* Improved: Admin app bundles are now cache-busted by file modification time so source updates take effect without a manual hard reload.
* Fixed: The free plugin now correctly auto-deactivates when CF7 Mate Pro is also active, with a one-time confirmation notice.
* Fixed: "Go to CF7 Mate dashboard" button on the onboarding Finish step now actually navigates to the dashboard.
* Fixed: Stale `cf7-mate-settings` admin slug references in onboarding redirects, the review notice, and the upsell placeholder now resolve to the current `cf7-mate` slug.
* Fixed: Review link in the admin sidebar now uses the correct WordPress.org plugin slug.
* Fixed: Onboarding feature toggles (including AI Form Generator and Heading) are no longer silently dropped when completing the wizard.

= 3.0.3 =
* New: AI Form Generator moved to the free plugin — available to all users with no Pro plan required.
* New: Top-level CF7 Mate admin menu replaces the prior Settings-submenu location.
* Improved: Admin UI redesigned for a cleaner, faster, more focused layout.
* Improved: Features page presents all modules in a single organized list.
* Improved: Tools tab consolidates AI Generator settings.

= 3.0.2 =
* Fixed: Minor bug fixes and performance improvements.
* Improved: Free vs Pro feature detection.
* Compatibility: WordPress 6.9.

= 3.0.1 =
* Fixed: Plugin naming corrections.

= 3.0.0 =
* New: Rebranded to CF7 Mate (formerly CF7 Styler for Divi).
* New: Divi 5 module with full visual builder support.
* New: Gutenberg block with live server-rendered preview.
* New: Admin dashboard with feature toggles.
* New: Elementor and Bricks builder integrations.
* Improved: Codebase rewritten for better performance and maintainability.

= 2.3.4 =
* Improved: Admin notice system.
* Improved: Review request flow.
* Fixed: Minor performance optimizations.

= 2.3.3 =
* Fixed: Compatibility with recent WordPress versions.
* Improved: Form styling performance and mobile responsiveness.

== Upgrade Notice ==

= 3.0.5 =
Fixes legacy [dcs_row] / [dcs_col_*] shortcodes in forms saved with the pre-3.0 plugin, a phone-number bug that dropped user input from email notifications, and required validation for Star/Range/Phone. AI generator modal redesigned. Recommended update.

= 3.0.4 =
Fixes for onboarding navigation, admin slug references, sidebar redesign, and auto-deactivation when CF7 Mate Pro is also active. Recommended update.

= 3.0.3 =
AI Form Generator is now free for all users. New top-level CF7 Mate admin menu and a refreshed admin UI. Recommended update.

= 3.0.0 =
Major update. The plugin is now CF7 Mate, with Divi 5 support, a Gutenberg block, and integrations for Elementor and Bricks. Existing settings carry over automatically.
