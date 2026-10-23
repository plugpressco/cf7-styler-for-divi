module.exports = function (grunt) {
    'use strict';
    const pkg = grunt.file.readJSON('package.json');
    // Positive runtime manifest: development/private files cannot enter a release by accident.
    const runtime = [
        'cf7-styler.php', 'readme.txt', 'LICENSE', 'third-party-notices.txt',
        'includes/**', 'assets/**', 'dist/**', 'modules-json/**', 'languages/**',
        '!includes/stubs/**', '!includes/pro/license/**', '!includes/pro/white-label/**',
        '!includes/pro/features/calculator/**',
        '!includes/pro/features/heading/**', '!includes/pro/features/phone-number/**',
        '!assets/pro/js/cf7m-calculator.js', '!assets/pro/js/cf7m-phone-number.js',
        '!assets/pro/js/cf7m-star-rating.js', '!assets/pro/js/cf7m-range-slider.js',
        '!assets/pro/css/cf7m-phone-number.css', '!**/*.map', '!**/.DS_Store',
    ];
    grunt.initConfig({
        copy: { wp: { options: { mode: true }, src: runtime, dest: 'package/cf7-styler-for-divi/' } },
        compress: { wp: {
            options: { archive: `cf7-styler-for-divi-${pkg.version}.zip`, mode: 'zip', level: 5 },
            files: [{ expand: true, cwd: 'package/', src: ['cf7-styler-for-divi/**'], dest: '/' }],
        } },
        clean: { staging: ['package'] },
    });
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.registerTask('package:wp', ['clean:staging', 'copy:wp', 'compress:wp', 'clean:staging']);
};
