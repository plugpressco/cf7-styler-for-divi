<?php
/**
 * White Label — lets Agency plan holders rebrand the plugin for their clients.
 *
 * @package CF7_Mate\Pro
 */

namespace CF7_Mate\Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class White_Label {

	private static ?self $instance = null;

	const OPT_KEY = 'cf7m_white_label';

	const DEFAULTS = [
		'enabled'     => false,
		'plugin_name' => '',
		'logo_url'    => '',
		'docs_url'    => '',
		'support_url' => '',
	];

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ( ! self::is_agency_plan() ) {
			return;
		}
		add_filter( 'cf7m_admin_menu_label',   [ $this, 'filter_menu_label' ] );
		add_filter( 'cf7m_admin_app_localize', [ $this, 'filter_localize' ], 20, 2 );
	}

	/**
	 * True when the active license variant contains "agency" (case-insensitive).
	 */
	public static function is_agency_plan(): bool {
		if ( ! class_exists( 'CF7_Mate\License\License_Manager' ) ) {
			return false;
		}
		$status = \CF7_Mate\License\License_Manager::instance()->get_status();
		if ( empty( $status['is_valid'] ) ) {
			return false;
		}
		$variant = strtolower( $status['variant_name'] ?? '' );
		return false !== strpos( $variant, 'agency' );
	}

	/**
	 * Return stored settings merged with defaults.
	 */
	public static function get(): array {
		$saved = get_option( self::OPT_KEY, [] );
		return wp_parse_args( is_array( $saved ) ? $saved : [], self::DEFAULTS );
	}

	/**
	 * Sanitize and persist new settings. Returns the saved values.
	 */
	public static function save( array $input ): array {
		$clean = [
			'enabled'     => ! empty( $input['enabled'] ),
			'plugin_name' => sanitize_text_field( $input['plugin_name'] ?? '' ),
			'logo_url'    => esc_url_raw( $input['logo_url']    ?? '' ),
			'docs_url'    => esc_url_raw( $input['docs_url']    ?? '' ),
			'support_url' => esc_url_raw( $input['support_url'] ?? '' ),
		];
		update_option( self::OPT_KEY, $clean, false );
		return $clean;
	}

	/**
	 * Filter: replace admin menu label with custom plugin name when WL is active.
	 */
	public function filter_menu_label( string $label ): string {
		$s = self::get();
		if ( ! empty( $s['enabled'] ) && ! empty( $s['plugin_name'] ) ) {
			return sanitize_text_field( $s['plugin_name'] );
		}
		return $label;
	}

	/**
	 * Filter: inject white_label data into dcsCF7Styler; override docs/support URLs.
	 *
	 * Runs at priority 20, after inject_license_data (priority 10), so is_agency is
	 * already present in $localize['license'] when this runs.
	 */
	public function filter_localize( array $localize, array $options ): array {
		$s = self::get();
		$localize['white_label'] = $s;

		if ( ! empty( $s['enabled'] ) ) {
			// Override or blank-out docs/support URLs.
			$localize['docs_url']    = $s['docs_url']    ?: '';
			$localize['support_url'] = $s['support_url'] ?: '';
			// Always hide community link under white label.
			$localize['community_url'] = '';
			// Hide pricing (clients shouldn't see CF7 Mate's pricing page).
			$localize['pricing_url'] = '';
		}

		return $localize;
	}
}
