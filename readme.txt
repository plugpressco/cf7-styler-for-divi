=== CF7 Mate – Contact Form 7 Styler, Multi-Step Forms & Conditional Logic ===
Contributors: badhonrocks, plugpressco
Tags: contact form 7, contact form 7 styler, multi step form, conditional fields, contact form 7 database
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 4.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Style Contact Form 7 forms. Add multi-step forms, conditional logic and a submissions database. All free, with an AI form builder.

== Description ==

**Style Contact Form 7. Add multi-step forms, conditional logic and a submissions database. 100% free.**

CF7 Mate is a free add-on for Contact Form 7. It makes your forms look good and do more.

Style your forms in Divi, Elementor, Bricks or the block editor. Split long forms into steps. Show fields only when they are needed. Save every submission in WordPress.

You still build forms in the Contact Form 7 editor. There is nothing new to learn.

**NEW!** Build forms with ChatGPT or Claude. Connect your AI app with [Saddle](https://saddle.to/), describe the form, and it appears in Contact Form 7.

All features are free on unlimited sites. No Pro version. No license key.

= 🎨 Contact Form 7 styler =

* **Visual form styler:** Style labels, fields and buttons in Divi 4, Divi 5, Elementor, Bricks or the block editor. No CSS needed.
* **Style presets:** Pick a ready-made look, then change it as you like.
* **Columns:** Put fields side by side.
* **Extra fields:** Star rating, range slider, phone number with country code, heading, separator, image and icon.

= 🧩 Multi-step forms and conditional logic =

* **Multi-step forms:** Break a long form into steps. A progress bar shows how far along the visitor is.
* **Conditional logic:** Show or hide fields based on earlier answers.
* **Save and continue:** Visitors can stop and finish later in the same browser, within seven days.
* **Scheduling:** Open and close a form on the dates you choose.

= 📬 Save submissions and act on them =

* **Submissions database:** Save every Contact Form 7 submission in WordPress. Search, export to CSV or print them. Turn on Form responses to start.
* **Form analytics:** See views and submissions for each form.
* **Email routing:** Send the email to a different person based on an answer.
* **Redirect after submit:** Send visitors to a thank-you page or any URL. You can add their answers to the URL.
* **Webhooks:** Send each submission to Zapier, Make, n8n or any URL.

= ✨ AI form builder =

* **AI form generator:** Describe your form in plain words. CF7 Mate writes the form for you. It uses your own OpenAI, Anthropic, Gemini or OpenRouter key.
* **AI agents:** Let ChatGPT or Claude create and edit forms with [Saddle](https://saddle.to/).

= 🚀 Get started =

1. Install and activate [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) and CF7 Mate.
2. Go to **Contact > CF7 Mate**. Turn on the features you need.
3. Edit a form in Contact Form 7. Use the CF7 Mate tag buttons to add fields, steps and conditions. Use the **CF7 Mate** tab for responses, redirects, email routing and scheduling.
4. Add the form to a page. Use the CF7 Mate module, block, widget or element in your builder, or the CF7 shortcode.

Need help? Read the [getting started guide](https://plugpress.io/docs/cf7-mate-getting-started/) or browse the [docs](https://plugpress.io/docs/).

= 🤖 Build forms with ChatGPT or Claude =

[Saddle](https://saddle.to/) is a free plugin. It connects ChatGPT, Claude and other AI apps to your site. Then you can build forms just by asking.

1. Install [Saddle](https://saddle.to/) and [connect your AI app](https://saddle.to/docs/connect-your-ai-app/).
2. In Saddle, turn on the CF7 Mate integration. Give the app Edit content access.
3. Ask for a form. For example: "Create a quote form with name, email, budget and project details."

Good to know:

* The app can find, read, create and edit forms.
* It cannot see responses or AI keys, and it never sends test submissions.
* Check each form and its Mail tab before you use it.
* It needs WordPress 6.9 or later.
* CF7 Mate never installs or connects Saddle for you. The AI app uses its own account, not your CF7 Mate AI key.

Read more in the [CF7 Mate and Saddle guide](https://plugpress.io/docs/cf7-mate-saddle/).

= ⚙️ Settings =

Turn features on or off in **Contact > CF7 Mate**. Add AI keys and webhook URLs under **Tools**.

On new sites, Form responses, Analytics, Webhooks and Save and continue start turned off. Sites updated from Pro keep their settings. Updates never delete your data.

Analytics only counts submissions saved by Form responses, so treat its numbers as estimates.

= 🔒 Privacy =

CF7 Mate does not check a license. It sends no usage data to PlugPress. Updates come from WordPress.org.

* **Form responses** are saved in your database. Each one stores the form fields, the visitor's IP address and browser details.
* **Save and continue** keeps progress for seven days in WordPress transients. The visitor's browser keeps a random key in local storage.
* **Webhooks** send each submission as JSON to the URLs you add. This includes the form ID and title, time, fields, IP address and browser details. Nothing is sent until you turn on webhooks and add a URL. The Test button sends sample data from your browser.
* **Images** in a form load from where they are hosted. Use images from your own site to avoid outside requests.

You decide the privacy notice, consent and data retention for your site.

= 🔌 AI providers =

The AI form generator only contacts a provider after an admin adds a key and asks for a form. It sends your prompt, the chosen model, any attached image and your key. Keep personal data out of prompts. Your provider may charge for each request.

* OpenAI (api.openai.com): [Service](https://openai.com/api/) · [Terms](https://openai.com/policies/terms-of-use/) · [Privacy](https://openai.com/policies/privacy-policy/)
* Anthropic (api.anthropic.com): [Service](https://www.anthropic.com/api) · [Terms](https://www.anthropic.com/legal/commercial-terms) · [Privacy](https://www.anthropic.com/legal/privacy)
* Google Gemini (generativelanguage.googleapis.com): [Service](https://ai.google.dev/) · [Terms](https://ai.google.dev/gemini-api/terms) · [Privacy](https://policies.google.com/privacy)
* OpenRouter (openrouter.ai): [Service](https://openrouter.ai/) · [Terms](https://openrouter.ai/terms) · [Privacy](https://openrouter.ai/privacy)

= 💻 Source code =

Full source and build files are on [GitHub](https://github.com/plugpressco/cf7-styler-for-divi). To build, run `npm ci` and then `npm run build`.

[Docs](https://plugpress.io/docs/) · [Support](https://plugpress.io/support/) · [Saddle](https://saddle.to/)

== Installation ==

1. Install and activate Contact Form 7.
2. Install and activate CF7 Mate.
3. Go to **Contact > CF7 Mate** and turn on what you need.
4. Add the form to a page. Use your builder's CF7 Mate module, block or widget, or the CF7 shortcode.

For a step-by-step walkthrough, see the [getting started guide](https://plugpress.io/docs/cf7-mate-getting-started/).

= Switching from CF7 Mate Pro =

1. Back up your site.
2. Install CF7 Mate from the plugin directory, or upload its ZIP under Plugins > Add New > Upload Plugin.
3. Deactivate CF7 Mate Pro, then activate CF7 Mate.
4. Check your forms and responses. Both plugins share the same data, so there is nothing to import.

Leave Pro inactive. While it is active, CF7 Mate stays off and shows a notice. It never deactivates Pro for you.

Installing CF7 Mate does not cancel a Pro subscription. Contact support about billing. More detail is in the [switching guide](https://plugpress.io/docs/cf7-mate-switch-from-pro/).

== Frequently Asked Questions ==

= Is everything free? =

Yes, on any number of sites. Only your AI provider may charge for AI use.

= How do I style Contact Form 7 forms? =

Add the CF7 Mate module, block, widget or element to a page in Divi, the block editor, Elementor or Bricks. Pick your form. Then style the labels, fields and buttons right in the builder.

= How do I make a multi-step Contact Form 7 form? =

Turn on Multi-step forms in **Contact > CF7 Mate**. In the form editor, click the **step** button to add a step. Visitors see one step at a time, with a progress bar.

= How do I add conditional fields to Contact Form 7? =

Turn on Conditional logic in Settings. In the form editor, click the **conditional** button. Choose which fields to show, and when.

= How do I save Contact Form 7 submissions to the database? =

Turn on Form responses in **Contact > CF7 Mate**. New submissions are then saved in WordPress. Find them under **Contact > CF7 Mate > Responses**. You can search, export to CSV or print them.

= Does it replace Contact Form 7? =

No. It works on top of Contact Form 7, so you keep the same editor and forms.

= Do I need Divi? =

No. It also works with the block editor, Elementor, Bricks or a plain CF7 shortcode.

= Will an update start saving submissions? =

Not on its own. Sites from the old free version must turn on Form responses. Sites from Pro keep their settings, and forms set not to save stay that way.

= How does save and continue work? =

Turn it on in Settings, then on each form. A visitor can return in the same browser within seven days. There are no email resume links, and uploaded files are not restored.

= Can ChatGPT or Claude build my forms? =

Yes, with [Saddle](https://saddle.to/). Connect your AI app, turn on the CF7 Mate integration, and ask for the form you need. It needs WordPress 6.9 or later.

= Why is the plugin slug cf7-styler-for-divi? =

It is the original WordPress.org slug. Keeping it means updates keep working.

= Where can I get help? =

Ask on the WordPress.org support forum or [PlugPress support](https://plugpress.io/support/). Guides are in the [docs](https://plugpress.io/docs/).

== Screenshots ==

1. AI form generator in the Contact Form 7 editor.
2. Visual form styling in Divi Builder.
3. CF7 Mate block in the WordPress block editor.
4. Multi-step form with progress and navigation.
5. Form responses with search, filtering and export.
6. Conditional fields that respond to visitor answers.
7. Feature toggles in the CF7 Mate dashboard.

== Changelog ==

= 4.1.0 =
* CF7 Mate now lives under the Contact menu. Old admin links still work.
* A calmer admin: checkbox tiles for features, shorter text and the new brand colors.
* Setup is a small card on the Features tab instead of a full screen wizard and notice.
* Shorter Saddle tab.
* Better support for right-to-left languages and longer translations in the admin and the CF7 Mate editor tab.
* Fixed: on Contact Form 7 5.x, multi-step forms let visitors skip empty required fields once the page had fully loaded.
* Fixed: CF7 Mate's field tag tools did not load in the Contact Form 7 6.2 editor.
* Fixed: the AI form generator showed an image remove button with no image attached.

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

= 4.1.0 =
CF7 Mate moves under the Contact menu with a simpler admin. Fixes the field tag tools on Contact Form 7 6.2.

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
