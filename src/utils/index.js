jQuery(function ($) {
    const gridShortcodeTypes = [
        'dipe_row', // Full width container
        'dipe_one', // Single column (12/12)
        'dipe_one_half', // Half width (6/12)
        'dipe_one_third', // One third (4/12)
        'dipe_one_fourth', // One fourth (3/12)
        'dipe_two_third', // Two thirds (8/12)
        'dipe_three_fourth', // Three fourths (9/12)
    ];

    // CF7 5.x recomposes generator tags on load and would drop the closing tag.
    const taggen = window.wpcf7 && window.wpcf7.taggen;
    if (taggen && typeof taggen.update === 'function') {
        gridShortcodeTypes.forEach((type) => {
            const tag = `[${type}][/${type}]`;
            taggen.update[type] = ($form) => $form.find('input.tag').val(tag);
            $(`form.tag-generator-panel[data-id="${type}"] input.tag`).val(tag);
        });
    }

    $('#tag-generator-list button').on('click', function (e) {
        e.preventDefault();
        const target = $(this).data('target');
        const shortcodeType = extractShortcodeType('target', target);

        // CF7 5.x/6.x open their own dialogs; jQuery UI is only present on old setups.
        if (shortcodeType.startsWith('dipe') && $.fn.dialog) {
            $(`#${target}`).dialog({
                modal: true,
                dialogClass: 'wp-dialog',
                width: 'auto',
                title: $(this).text(),
            });
        }
    });

    window.insertGridShortcode = function (shortcodeType) {
        insertGridLayoutShortcode(shortcodeType);
        if ($.fn.dialog) {
            $(`#tag-generator-panel-${shortcodeType}`).dialog('close');
        }
    };

    function extractShortcodeType(paramName, target) {
        if (!target) {
            return '';
        }
        return target.replace('tag-generator-panel-', '');
    }

    function insertGridLayoutShortcode(shortcodeType) {
        const $formEditor = $('#wpcf7-form');
        const formElement = $formEditor[0];
        const shortcodeOpen = `[${shortcodeType}]`;
        const shortcodeClose = `[/${shortcodeType}]`;

        const cursorStart = formElement.selectionStart;
        const cursorEnd = formElement.selectionEnd;
        const currentContent = $formEditor.val();

        const updatedContent =
            currentContent.slice(0, cursorStart) +
            shortcodeOpen +
            currentContent.slice(cursorStart, cursorEnd) +
            shortcodeClose +
            currentContent.slice(cursorEnd);

        $formEditor.val(updatedContent);
    }
});
