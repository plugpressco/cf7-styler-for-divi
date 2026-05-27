<?php
/**
 * Subtle Pro upgrade notice (free build).
 *
 * Shows a single-line, dismissible banner at the top of CF7 Mate's own admin
 * pages. Per WordPress.org guideline §10: never shown site-wide, never an
 * undismissible nag.
 *
 * Cadence:
 *   - First shown 3 days after install (so the user has some context first).
 *   - Hidden once Pro is detected (cf7m_is_pro() === true).
 *   - Dismissing snoozes for 90 days; after a 2nd dismiss it's permanent.
 *
 * @package CF7_Mate
 * @since 3.0.5
 */

namespace CF7_Mate;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Upgrade_Notice {

	private static $instance = null;

	const NOTICE_ID           = 'cf7m_upgrade_notice';
	const COUNT_OPTION        = 'cf7m_upgrade_notice_dismiss_count';
	const NEXT_SHOW_OPTION    = 'cf7m_upgrade_notice_next_show_at';
	const INSTALL_DATE_OPTION = 'cf7m_install_date';
	const INITIAL_DELAY       = 3  * DAY_IN_SECONDS;
	const SNOOZE              = 90 * DAY_IN_SECONDS;
	const MAX_DISMISSALS      = 2;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_notices', [ $this, 'display_notice' ] );
		add_action( 'wp_ajax_cf7m_dismiss_upgrade', [ $this, 'ajax_dismiss' ] );
	}

	private function is_cf7_mate_page() {
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return in_array( $page, [ 'cf7-mate', 'cf7-mate-responses', 'cf7-mate-analytics' ], true );
	}

	private function should_display() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		// Already on Pro — never advertise.
		if ( function_exists( 'cf7m_is_pro' ) && cf7m_is_pro() ) {
			return false;
		}

		$count = (int) get_option( self::COUNT_OPTION, 0 );
		if ( $count >= self::MAX_DISMISSALS ) {
			return false;
		}

		$install_date = (int) get_option( self::INSTALL_DATE_OPTION, 0 );
		if ( ! $install_date ) {
			return false;
		}
		if ( time() < $install_date + self::INITIAL_DELAY ) {
			return false;
		}

		$next_show_at = (int) get_option( self::NEXT_SHOW_OPTION, 0 );
		if ( $next_show_at && time() < $next_show_at ) {
			return false;
		}

		return true;
	}

	public function display_notice() {
		if ( ! $this->is_cf7_mate_page() ) {
			return;
		}
		if ( ! $this->should_display() ) {
			return;
		}
		$this->render();
	}

	private function render() {
		$pricing_url = defined( 'CF7M_URL_PRICING' ) ? CF7M_URL_PRICING : 'https://cf7mate.com/pricing';
		$nonce       = wp_create_nonce( 'cf7m_dismiss_upgrade' );
		?>
		<div id="<?php echo esc_attr( self::NOTICE_ID ); ?>" class="cf7m-up-wrap notice">
			<div class="cf7m-up">
				<span class="cf7m-up__badge"><?php esc_html_e( 'Pro', 'cf7-styler-for-divi' ); ?></span>
				<span class="cf7m-up__text">
					<?php esc_html_e( 'Unlock Multi-Step, Conditional Logic, Form Entries, Analytics, and more.', 'cf7-styler-for-divi' ); ?>
				</span>
				<a class="cf7m-up__cta" href="<?php echo esc_url( $pricing_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'See plans', 'cf7-styler-for-divi' ); ?>
				</a>
				<button type="button" class="cf7m-up__close" data-action="dismiss" aria-label="<?php esc_attr_e( 'Dismiss', 'cf7-styler-for-divi' ); ?>">
					<svg viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M1 1l10 10M11 1L1 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
				</button>
			</div>
		</div>
		<style>
			.cf7m-up-wrap.notice {
				padding: 0 !important;
				border-left: none !important;
				background: linear-gradient(135deg, #f0f4ff 0%, #f8f9ff 100%) !important;
				border: 1px solid #c7d2fe !important;
				border-radius: 8px !important;
				margin: 12px 20px 4px 0 !important;
				overflow: hidden !important;
			}
			.cf7m-up-wrap .notice-dismiss { display: none !important; }
			.cf7m-up { display: flex; align-items: center; gap: 10px; padding: 8px 14px; min-height: 38px; flex-wrap: wrap; }
			.cf7m-up__badge {
				display: inline-flex; align-items: center; padding: 2px 8px;
				background: #3044d7; color: #fff;
				font-size: 10px; font-weight: 600;
				border-radius: 999px; letter-spacing: .04em; text-transform: uppercase;
				flex-shrink: 0;
			}
			.cf7m-up__text { font-size: 13px; color: #1f2937; flex: 1; min-width: 0; }
			.cf7m-up__cta {
				display: inline-flex; align-items: center;
				font-size: 13px; font-weight: 600;
				color: #3044d7; text-decoration: none;
				padding: 4px 10px; border-radius: 6px;
				transition: background .15s;
			}
			.cf7m-up__cta:hover { background: rgba(48, 68, 215, .08); color: #253ab8; }
			.cf7m-up__close {
				background: none; border: 0; padding: 4px; cursor: pointer;
				color: #9ca3af; display: inline-flex; align-items: center;
				border-radius: 4px; transition: color .15s, background .15s; flex-shrink: 0;
			}
			.cf7m-up__close svg { width: 12px; height: 12px; display: block; }
			.cf7m-up__close:hover { color: #374151; background: rgba(0,0,0,.05); }
		</style>
		<script type="text/javascript">
		jQuery(function($){
			var $notice = $('#<?php echo esc_js( self::NOTICE_ID ); ?>');
			var nonce   = <?php echo wp_json_encode( $nonce ); ?>;
			var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
			$notice.on('click', '[data-action="dismiss"]', function(e){
				e.preventDefault();
				$notice.fadeOut(200, function(){ $(this).remove(); });
				$.post(ajaxUrl, { action: 'cf7m_dismiss_upgrade', nonce: nonce });
			});
		});
		</script>
		<?php
	}

	public function ajax_dismiss() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'cf7m_dismiss_upgrade' ) ) {
			wp_send_json_error( [ 'message' => 'Security check failed' ] );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Insufficient permissions' ] );
		}

		$count = (int) get_option( self::COUNT_OPTION, 0 ) + 1;
		update_option( self::COUNT_OPTION, $count, false );

		if ( $count >= self::MAX_DISMISSALS ) {
			delete_option( self::NEXT_SHOW_OPTION );
			wp_send_json_success( [ 'state' => 'forever' ] );
		}
		update_option( self::NEXT_SHOW_OPTION, time() + self::SNOOZE, false );
		wp_send_json_success( [ 'state' => 'snoozed' ] );
	}
}
