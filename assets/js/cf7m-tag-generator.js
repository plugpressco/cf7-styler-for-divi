/**
 * CF7 Mate – Tag generator: sync tag code for cf7m-star and cf7m-range from panel fields.
 * Ensures the shortcode printed when "Insert Tag" is clicked reflects the form options.
 *
 * @package CF7_Mate
 * @since 3.0.0
 */
(function () {
  "use strict";

  function ready(fn) {
    if (document.readyState !== "loading") {
      fn();
    } else {
      document.addEventListener("DOMContentLoaded", fn);
    }
  }

  function updateStarTag(panel) {
    var input = panel.querySelector('input.tag.code[name="cf7m-star"]');
    if (!input) return;
    var nameEl = panel.querySelector('input.tg-name[name="name"]');
    var maxEl = panel.querySelector('input[name="max"]');
    var defaultEl = panel.querySelector('input[name="default"]');
    var starColorEl = panel.querySelector('input[name="color"]') || panel.querySelector('input[name="star_color"]');
    var requiredEl = panel.querySelector('input[name="required"]');
    var name = (nameEl && nameEl.value) ? nameEl.value.trim() : "rating";
    var max = (maxEl && maxEl.value) ? maxEl.value : "5";
    var def = (defaultEl && defaultEl.value) ? defaultEl.value : "0";
    var starColor = (starColorEl && starColorEl.value) ? starColorEl.value.trim() : "";
    var required = requiredEl && requiredEl.checked;
    var type = required ? "cf7m-star*" : "cf7m-star";
    var tag = "[" + type + " " + name + " max:" + max + " default:" + def;
    if (starColor) tag += " color:" + starColor;
    tag += "]";
    input.value = tag;
  }

  function updateRangeTag(panel) {
    var input = panel.querySelector('input.tag.code[name="cf7m-range"]');
    if (!input) return;
    var nameEl = panel.querySelector('input.tg-name[name="name"]');
    var minEl = panel.querySelector('input[name="min"]');
    var maxEl = panel.querySelector('input[name="max"]');
    var stepEl = panel.querySelector('input[name="step"]');
    var defaultEl = panel.querySelector('input[name="default"]');
    var prefixEl = panel.querySelector('input[name="prefix"]');
    var suffixEl = panel.querySelector('input[name="suffix"]');
    var trackColorEl = panel.querySelector('input[name="track_color"]');
    var thumbColorEl = panel.querySelector('input[name="thumb_color"]');
    var requiredEl = panel.querySelector('input[name="required"]');
    var name = (nameEl && nameEl.value) ? nameEl.value.trim() : "amount";
    var min = (minEl && minEl.value) ? minEl.value : "0";
    var max = (maxEl && maxEl.value) ? maxEl.value : "100";
    var step = (stepEl && stepEl.value) ? stepEl.value : "1";
    var def = (defaultEl && defaultEl.value) ? defaultEl.value : "50";
    var prefix = (prefixEl && prefixEl.value) ? prefixEl.value.trim() : "";
    var suffix = (suffixEl && suffixEl.value) ? suffixEl.value.trim() : "";
    var trackColor = (trackColorEl && trackColorEl.value) ? trackColorEl.value.trim() : "";
    var thumbColor = (thumbColorEl && thumbColorEl.value) ? thumbColorEl.value.trim() : "";
    var required = requiredEl && requiredEl.checked;
    var type = required ? "cf7m-range*" : "cf7m-range";
    var tag = "[" + type + " " + name + " min:" + min + " max:" + max + " step:" + step + " default:" + def;
    if (prefix) tag += " prefix:" + prefix;
    if (suffix) tag += " suffix:" + suffix;
    if (trackColor) tag += " track_color:" + trackColor;
    if (thumbColor) tag += " thumb_color:" + thumbColor;
    tag += "]";
    input.value = tag;
  }

  function findPanel(el) {
    for (; el; el = el.parentElement) {
      if (el.classList && (el.classList.contains("cf7m-tag-panel") || el.classList.contains("ui-dialog-content") || el.classList.contains("tag-generator-panel"))) {
        return el;
      }
    }
    return null;
  }

  function runUpdates() {
    [].forEach.call(document.querySelectorAll('input.tag.code[name="cf7m-star"]'), function (input) {
      var panel = findPanel(input);
      if (panel) updateStarTag(panel);
    });
    [].forEach.call(document.querySelectorAll('input.tag.code[name="cf7m-range"]'), function (input) {
      var panel = findPanel(input);
      if (panel) updateRangeTag(panel);
    });
  }

  function onPanelChange(e) {
    var target = e.target;
    var panel = findPanel(target);
    if (!panel) return;
    var starFields = ["name", "max", "default", "required", "color", "star_color"];
    if (starFields.indexOf(target.name) !== -1 && panel.querySelector('input.tag.code[name="cf7m-star"]')) {
      updateStarTag(panel);
    }
    var rangeFields = ["name", "min", "max", "step", "default", "prefix", "suffix", "required", "track_color", "thumb_color"];
    if (rangeFields.indexOf(target.name) !== -1 && panel.querySelector('input.tag.code[name="cf7m-range"]')) {
      updateRangeTag(panel);
    }
  }

  ready(function () {
    setTimeout(runUpdates, 150);
    document.addEventListener("input", onPanelChange);
    document.addEventListener("change", onPanelChange);
    if (typeof jQuery !== "undefined") {
      jQuery(document).on("wpcf7_editor_loaded", function () {
        setTimeout(runUpdates, 150);
      });
      jQuery(document).on("click", "#tag-generator-list button", function () {
        setTimeout(runUpdates, 200);
      });
    }
  });
})();
