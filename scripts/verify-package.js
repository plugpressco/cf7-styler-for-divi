const fs = require('fs');
const { execFileSync } = require('child_process');
const pkg = require('../package.json');
const zip = process.argv[2] || `cf7-styler-for-divi-${pkg.version}.zip`;
const files = execFileSync('unzip', ['-Z1', zip], { encoding: 'utf8' }).split('\n');
const root = 'cf7-styler-for-divi/';
const required = ['cf7-styler.php', 'includes/admin/form-editor.php', 'assets/lite/js/cf7m-editor-panel.js', 'assets/lite/css/cf7m-editor-panel.css', 'includes/feature-settings.php', 'includes/pro/loader.php',
    'includes/admin/saddle.php', 'includes/saddle-abilities.php', 'assets/pro/js/cf7m-tag-generators.js',
    'assets/pro/css/cf7m-pro-forms.css', 'dist/js/admin.js', 'dist/js/bundle.js',
    'dist/js/bundle-4.js', 'dist/js/blocks.js', 'dist/css/admin.css',
    'modules-json/cf7-styler/module.json', 'languages/cf7-styler-for-divi.pot', 'LICENSE', 'third-party-notices.txt'];
for (const module of ['multi-column', 'multi-steps', 'entries', 'conditional', 'presets', 'webhook', 'analytics', 'scheduling', 'email-routing', 'redirect', 'partial-save']) {
    required.push(`includes/pro/features/${module}/module.php`);
}
for (const file of required) {
    if (!files.includes(root + file)) throw new Error(`Missing runtime file: ${file}`);
}
for (const file of files) {
    if (/(node_modules|vendor|update-server|includes\/pro\/(license|white-label)|AGENTS\.md|package\.json|tests\/|docs\/|src\/|admin-pro\.js|admin-notice\.js|notices\/(upgrade|dotyard)\.php|calculator|dist\/(js|css)\/onboarding\.)/.test(file)) {
        throw new Error(`Unexpected release file: ${file}`);
    }
    if (file && !file.startsWith(root)) throw new Error(`Unexpected archive root: ${file}`);
}
// Admin CSS must stay inside the app root so WordPress screens and chrome are untouched.
const postcss = require('postcss');
const adminCss = postcss.parse(execFileSync('unzip', ['-p', zip, root + 'dist/css/admin.css'], { encoding: 'utf8' }));
adminCss.walkRules((rule) => {
    if (rule.parent && rule.parent.type === 'atrule' && /keyframes$/.test(rule.parent.name)) return;
    for (const selector of rule.selectors) {
        if (!selector.trim().startsWith('#cf7-mate-app-root')) throw new Error(`Unscoped admin CSS selector: ${selector}`);
    }
});
adminCss.walkAtRules(/keyframes$/, (rule) => {
    if (!rule.params.startsWith('cf7m-')) throw new Error(`Unprefixed admin keyframes: ${rule.params}`);
});
const bootstrap = execFileSync('unzip', ['-p', zip, root + 'cf7-styler.php'], { encoding: 'utf8' });
const readme = execFileSync('unzip', ['-p', zip, root + 'readme.txt'], { encoding: 'utf8' });
if (!bootstrap.includes(`Version: ${pkg.version}`) || !readme.includes(`Stable tag: ${pkg.version}`)) throw new Error('Release versions differ');
console.log(`Package verified: ${zip} (${files.filter(Boolean).length} entries)`);
