# Isolated browser QA

Use the canonical release ZIP and an official Contact Form 7 ZIP. No customer database is used. This fixture disables mail, intercepts AI requests and the exact example webhook URL inside WordPress, and creates four demonstration forms/pages. AI responses are deterministic fixtures, not live provider verification. Never install the fixture MU plugin in a real site.

```sh
npm run package:wp
npm run verify:package
node scripts/prepare-playground.js /absolute/path/to/contact-form-7.zip
npx @wp-playground/cli@latest server --port=9470 --workers=2 --blueprint=output/playwright/playground-bundle --blueprint-may-read-adjacent-files --login
```

Wait for `Ready!` before opening the site. The generated bundle lives under ignored `output/playwright/`. `blueprint.json` is a generated reference; the preparation script refreshes its inline PHP from `fixtures.php` and `seed.php`. It installs the actual ZIP rather than mounting development dependencies. Local credentials are `admin` / `password`.

Open `/wp-admin/admin.php?page=cf7-mate`. The `check-*.js` files are async functions accepted by Playwright CLI's `run-code` command. Run onboarding first in a fresh site; it drives the inline setup card, enables collection and sending features explicitly, leaves Icon and Save and continue off to check that unchosen choices are kept, retries a failed save, then runs setup again from Help. Run form and admin checks after onboarding. The admin check verifies the optional Saddle page and that white label is absent. The layout check verifies the single menu item under Contact, unchanged WordPress admin bar/menu/footer styles, search, checkbox saves, failed saves, retry states, and tablet/mobile layouts. The editor check proves native editor styles and field-tool placement are unchanged, creates a separate form, saves settings across hidden sections and restores the original global feature flags. Repeated form checks create additional fixture responses and view counts. Recreate the Playground for a clean run.

```sh
playwright-cli -s=cf7mate open 'http://127.0.0.1:9470/wp-admin/admin.php?page=cf7-mate'
playwright-cli -s=cf7mate run-code "$(cat tests/playground/check-onboarding.js)"
playwright-cli -s=cf7mate run-code "$(cat tests/playground/check-forms.js)"
playwright-cli -s=cf7mate run-code "$(cat tests/playground/check-admin.js)"
playwright-cli -s=cf7mate run-code "$(cat tests/playground/check-admin-layout.js)"
playwright-cli -s=cf7mate run-code "$(cat tests/playground/check-editor.js)"
```

For the minimum environment, copy the prepared bundle to `output/playwright/playground-min-bundle`, replace its `contact-form-7.zip` with Contact Form 7 5.7.7, and start the server with `--wp=6.0 --php=7.4` on another port. CF7 5.x asks before leaving an edited form; if a check stops on that prompt, run `playwright-cli -s=<session> dialog-accept` and rerun the check. The forms check waits for CF7's validation schema before its first step, so both CF7's validation and CF7 Mate's required-field check are exercised.

The browser must be logged in. If the default login link has expired, use the local credentials. Screenshots and run logs belong in `output/playwright/`; the runtime release excludes all fixture files.

The admin-only `/cf7mate-playground/v1/state` endpoint exposes fixture URLs, feature flags, onboarding state, branding, and redacted sink activity for assertions. It never returns API credentials. Webhook UI testing must not send browser requests to real services; submission delivery uses the PHP sink. The normal plugin retains its permissions and nonces.

Playground uses SQLite. These checks do not replace MySQL/multisite or complete commercial builder visual-editor testing.
