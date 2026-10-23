/** Prepare an isolated local Blueprint bundle from the verified release ZIP. */
const fs = require('fs');
const path = require('path');
const root = path.resolve(__dirname, '..');
const version = require('../package.json').version;
const cf7Archive = process.argv[2];
if (!cf7Archive || !fs.existsSync(cf7Archive)) {
  console.error('Usage: node scripts/prepare-playground.js /path/to/contact-form-7.zip');
  process.exit(1);
}
const blueprint = JSON.parse(fs.readFileSync(path.join(root, 'tests/playground/blueprint.json'), 'utf8'));
blueprint.steps.find(step => step.step === 'writeFile').data = fs.readFileSync(path.join(root, 'tests/playground/fixtures.php'), 'utf8').replace('<?php', '<?php\ndefine("CF7M_PLAYGROUND", true);');
blueprint.steps.find(step => step.step === 'runPHP').code = fs.readFileSync(path.join(root, 'tests/playground/seed.php'), 'utf8');
const bundle = path.join(root, 'output/playwright/playground-bundle');
fs.mkdirSync(bundle, { recursive: true });
fs.copyFileSync(path.join(root, `cf7-styler-for-divi-${version}.zip`), path.join(bundle, 'cf7-styler-for-divi.zip'));
fs.copyFileSync(path.resolve(cf7Archive), path.join(bundle, 'contact-form-7.zip'));
fs.writeFileSync(path.join(bundle, 'blueprint.json'), JSON.stringify(blueprint, null, 2) + '\n');
console.log(`Playground bundle prepared: ${bundle}`);
