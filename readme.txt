=== CF7 Mate ===
Contributors: badhonrocks, plugpressco
Tags: contact form 7, cf7, form styler, multi-step form, conditional logic
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 4.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Style Contact Form 7 forms, add steps and conditional fields, and save responses in WordPress.

== Description ==

CF7 Mate adds styling and extra tools to Contact Form 7. You keep building forms in the normal CF7 editor.

All features are free on any number of sites. There is no Pro version and no license key.

= Features =

* **Styling:** Design forms in Divi 4, Divi 5, the block editor, Elementor or Bricks.
* **Multi-step forms:** Split a long form into steps with a progress bar.
* **Conditional logic:** Show or hide fields based on other answers.
* **Columns:** Place fields side by side.
* **Extra fields:** Star rating, range slider, phone number, heading, separator, image and icon.
* **Style presets:** Start from a ready-made look.
* **Responses:** Save submissions in WordPress. Search, export or print them.
* **Analytics:** See views and submissions for each form.
* **Webhooks:** Send submissions to the URLs you add.
* **Email routing:** Send the notification to a different address based on an answer.
* **Redirect:** Take visitors to any page after they submit.
* **Scheduling:** Open and close a form on set dates.
* **Save and continue:** Visitors finish later in the same browser, within seven days.
* **AI form generator:** Describe a form and get the CF7 markup. Uses your own AI key.
* **AI agent tools:** Let an AI app create and edit forms through Saddle.

= Settings =

Turn features on or off under CF7Mate > Settings. Add AI keys and webhook URLs under Tools.

Form responses, analytics, webhooks and save and continue start off on new sites and on sites updated from the old free version. Sites updated from Pro keep their settings. Updates never delete saved data.

Analytics counts submissions only while Form responses is on, so its numbers are estimates.

= Privacy =

CF7 Mate does not check a license or send usage data to PlugPress. Updates come from WordPress.org.

* Form responses stay in your database. Each one stores the submitted fields, the visitor's IP address and browser details.
* Save and continue keeps progress for seven days in WordPress transients, with a random key in the visitor's browser local storage.
* Webhooks send each submission as JSON to the URLs you add: form ID and title, time, fields, IP address and browser details. Nothing is sent until you turn on webhooks and add a URL. The Test button sends sample data from your browser.
* An image placed in a form loads from its host. Use images from your own site to avoid that request.

You choose the privacy notice, consent and data retention for your site.

= AI providers =

The AI form generator contacts a provider only after an admin adds a key and asks for a form. It sends the prompt, the chosen model, any attached image and your key. Keep personal data out of prompts. Your provider may charge for each request.

* OpenAI (api.openai.com): [Service](https://openai.com/api/) · [Terms](https://openai.com/policies/terms-of-use/) · [Privacy](https://openai.com/policies/privacy-policy/)
* Anthropic (api.anthropic.com): [Service](https://www.anthropic.com/api) · [Terms](https://www.anthropic.com/legal/commercial-terms) · [Privacy](https://www.anthropic.com/legal/privacy)
* Google Gemini (generativelanguage.googleapis.com): [Service](https://ai.google.dev/) · [Terms](https://ai.google.dev/gemini-api/terms) · [Privacy](https://policies.google.com/privacy)
* OpenRouter (openrouter.ai): [Service](https://openrouter.ai/) · [Terms](https://openrouter.ai/terms) · [Privacy](https://openrouter.ai/privacy)

= AI agents with Saddle =

[Saddle](https://wordpress.org/plugins/saddle/) is a separate free plugin that connects an AI app to your site. On WordPress 6.9 or later, CF7 Mate gives it four tools: find, read, create and edit forms.

* An admin must enable the CF7 Mate integration in Saddle and give the app Edit content access.
* The tools cannot read responses or AI keys, and never send a test submission.
* Review each form and its Mail tab before you publish it.

CF7 Mate never installs or connects Saddle for you. The AI app uses its own account, not your CF7 Mate AI key.

= Source code =

Full source and build files are on [GitHub](https://github.com/plugpressco/cf7-styler-for-divi). To build, run `npm ci` and then `npm run build`.

[Docs](https://plugpress.io/docs-category/cf7-mate/) · [Support](https://plugpress.io/support/)

== Installation ==

1. Install and activate Contact Form 7.
2. Install and activate CF7 Mate.
3. Open CF7Mate > Settings and turn on what you need.
4. Add the form with your builder's CF7 Mate module, block or widget, or with the usual CF7 shortcode.

= Switching from CF7 Mate Pro =

1. Back up your site.
2. Install CF7 Mate from the plugin directory, or upload its ZIP under Plugins > Add New > Upload Plugin.
3. Deactivate CF7 Mate Pro, then activate CF7 Mate.
4. Check your forms and responses. Both plugins share the same data, so there is nothing to import.

Leave Pro inactive. While it is active, CF7 Mate stays off and shows a notice. It never deactivates Pro for you.

Installing CF7 Mate does not cancel a Pro subscription. Contact support about billing.

== Frequently Asked Questions ==

= Is everything free? =

Yes, on any number of sites. Only your AI provider may charge for AI use.

= Does it replace Contact Form 7? =

No. It works on top of Contact Form 7, so you keep the same editor and forms.

= Do I need Divi? =

No. It also works with the block editor, Elementor, Bricks or a plain CF7 shortcode.

= Will an update start saving submissions? =

Not on its own. Sites from the old free version must turn on Form responses. Sites from Pro keep their settings, and forms set not to save stay that way.

= How does save and continue work? =

Turn it on in Settings, then on each form. A visitor can return in the same browser within seven days. There are no email resume links, and uploaded files are not restored.

= Why is the plugin slug cf7-styler-for-divi? =

It is the original WordPress.org slug. Keeping it means updates keep working.

= Where can I get help? =

Ask on the WordPress.org support forum or [PlugPress support](https://plugpress.io/support/). Guides are in the [docs](https://plugpress.io/docs-category/cf7-mate/).

== Screenshots ==

1. AI form generator in the Contact Form 7 editor.
2. Visual form styling in Divi Builder.
3. CF7 Mate block in the WordPress block editor.
4. Multi-step form with progress and navigation.
5. Form responses with search, filtering and export.
6. Conditional fields that respond to visitor answers.
7. Feature toggles in the CF7 Mate dashboard.

== Changelog ==

= 4.0.0 =
* All features are now free: multi-step forms, conditional logic, responses, analytics, webhooks, email routing, redirects, scheduling, save and continue and style presets.
* Removed the license screen, upgrade prompts and custom update checker. Updates come from WordPress.org.
* Removed white label. Its saved settings are kept.
* Added an optional Saddle page and AI agent tools. They need WordPress 6.9 or later.
* Redesigned the settings, Responses and Analytics pages, and the CF7 Mate tab in the form editor. The other CF7 tabs are unchanged.
* Kept Pro settings and saved responses. Sites from the old free version choose which new features to turn on.
* Saving one setting no longer resets the others.
* Hardened save and continue, webhooks and CSV export.
* Fixed HTML emails losing their line breaks when Grid layout is on.
* Fixed saved responses, routing rules and redirect rules losing quotes, line breaks and accented letters.
* Fixed CSV export for responses that contain quotes.
* Fixed required phone fields passing with only the country code, and phone placeholders with spaces.
* Fixed redirect URLs not filling in [field] values.
* Fixed the CF7 Mate tag buttons in the form editor ignoring your choices. The Grid buttons now insert their tags.
* Fixed form scheduling, and analytics now compares views and submissions over the same period.

= 3.0.8 =
* Security: fixed a stored XSS in the Divi 4 CF7 Styler module. The Header Image, Header Title, and Header Text fields are now escaped on output (`esc_url`/`esc_html`), matching the Divi 5, Elementor, and Bricks builds. Previously a user able to edit a module could inject markup that executed when the post was viewed.

= 3.0.7 =
* Fixed: fatal parse error on PHP 7.4 (`syntax error, unexpected '|'` in includes/rest-api.php). Version 3.0.6 accidentally used PHP 8.0-only syntax; the plugin now runs on PHP 7.4 again as advertised.
* Fixed: AI Form Generator modal was unstyled and non-functional in the free plugin — its CSS/JS still lived under the Pro asset path, which the free package doesn't ship. The assets now live with the other free features.
* Internal: every release is now scanned against PHP 7.4 with PHPCompatibility so this class of error can't ship again.
* New: the settings sidebar links to dotyard, our studio for custom plugin and MVP work, with a small one-time note on the settings page (shown only there, dismissible forever with one click).

= 3.0.6 =
* Compatibility: tested up to WordPress 7.0.
* Maintenance: housekeeping pass — PHP lint clean across the codebase, no unsanitised superglobals on the free side.

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

= 4.0.0 =
Major release. All features are now free and your data is kept. On former free sites, the new data features start off. Pro users: read "Switching from CF7 Mate Pro" first.

= 3.0.8 =
Security release. Fixes a stored XSS in the Divi 4 CF7 Styler module where the Header Image, Title, and Text fields were output without escaping. Update as soon as possible.

= 3.0.7 =
Fixes a fatal error on PHP 7.4 introduced in 3.0.6. If your site runs PHP 7.4, update immediately (or first if the plugin is currently deactivated).

= 3.0.6 =
WordPress 7.0 compatibility confirmed. Maintenance release.

= 3.0.5 =
Fixes legacy [dcs_row] / [dcs_col_*] shortcodes in forms saved with the pre-3.0 plugin, a phone-number bug that dropped user input from email notifications, and required validation for Star/Range/Phone. AI generator modal redesigned. Recommended update.

= 3.0.4 =
Fixes for onboarding navigation, admin slug references, sidebar redesign, and auto-deactivation when CF7 Mate Pro is also active. Recommended update.

= 3.0.3 =
AI Form Generator is now free for all users. New top-level CF7 Mate admin menu and a refreshed admin UI. Recommended update.

= 3.0.0 =
Major update. The plugin is now CF7 Mate, with Divi 5 support, a Gutenberg block, and integrations for Elementor and Bricks. Existing settings carry over automatically.
