<?php
/**
 * Parse shortcode-style attributes (e.g. title:"Step 1" gap:16).
 *
 * @package CF7_Mate\Pro\Traits
 * @since 3.0.0
 */

namespace CF7_Mate\Pro\Traits;

if (!defined('ABSPATH')) {
    exit;
}

trait Shortcode_Atts_Trait
{
    protected function parse_atts(string $text): array
    {
        $atts = [];
        if (preg_match_all('/(\w+):(?:"([^"]+)"|\'([^\']+)\'|([^\s\]]+))/', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $atts[$match[1]] = !empty($match[2]) ? $match[2] : (!empty($match[3]) ? $match[3] : $match[4]);
            }
        }
        return $atts;
    }
}
