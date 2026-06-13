<?php
/**
 * dotyard service nudge (free + pro builds).
 *
 * A small floating card in the bottom-right corner of CF7 Mate's own
 * settings page only — never on Dashboard, Plugins, or any other admin
 * screen (per WordPress.org guideline §10: promotional notices must be
 * restricted to the plugin's own pages).
 *
 * Cadence:
 *   - First appears 2 hours after install.
 *   - Any dismiss → never shown again.
 *
 * @package CF7_Mate
 * @since 3.0.7
 */

namespace CF7_Mate;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Dotyard_Nudge {

	private static $instance = null;

	const NUDGE_ID            = 'cf7m_dotyard_nudge';
	const DISMISSED_OPTION    = 'cf7m_dotyard_nudge_dismissed';
	const INSTALL_DATE_OPTION = 'cf7m_install_date';
	const INITIAL_DELAY       = 2 * HOUR_IN_SECONDS;
	const WORK_URL            = 'https://dotyard.co/work?utm_source=cf7mate&utm_medium=nudge&utm_campaign=dotyard';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_footer', [ $this, 'display_nudge' ] );
		add_action( 'wp_ajax_cf7m_dismiss_dotyard', [ $this, 'ajax_dismiss' ] );
	}

	private function is_settings_page() {
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return 'cf7-mate' === $page;
	}

	private function should_display() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( get_option( self::DISMISSED_OPTION ) ) {
			return false;
		}

		$install_date = (int) get_option( self::INSTALL_DATE_OPTION, 0 );
		if ( ! $install_date ) {
			return false;
		}

		return time() >= $install_date + self::INITIAL_DELAY;
	}

	public function display_nudge() {
		if ( ! $this->is_settings_page() || ! $this->should_display() ) {
			return;
		}
		$this->render();
	}

	private function render() {
		$nonce = wp_create_nonce( 'cf7m_dismiss_dotyard' );
		?>
		<div id="<?php echo esc_attr( self::NUDGE_ID ); ?>" class="cf7m-dy" role="complementary" aria-label="<?php esc_attr_e( 'dotyard — custom development service', 'cf7-styler-for-divi' ); ?>">
			<button type="button" class="cf7m-dy__close" data-action="dismiss" aria-label="<?php esc_attr_e( 'Dismiss', 'cf7-styler-for-divi' ); ?>">
				<svg viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M1 1l10 10M11 1L1 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
			</button>

			<span class="cf7m-dy__eyebrow"><?php esc_html_e( 'From the makers of CF7 Mate', 'cf7-styler-for-divi' ); ?></span>

			<h3 class="cf7m-dy__title"><?php esc_html_e( 'Need a custom plugin or MVP built?', 'cf7-styler-for-divi' ); ?></h3>

			<p class="cf7m-dy__desc">
				<?php esc_html_e( 'dotyard is our lean product studio. Custom WordPress plugins and SaaS MVPs — fixed scope, fixed price, one commission at a time.', 'cf7-styler-for-divi' ); ?>
			</p>

			<div class="cf7m-dy__actions">
				<a href="<?php echo esc_url( self::WORK_URL ); ?>" target="_blank" rel="noopener noreferrer" class="cf7m-dy__cta">
					<?php esc_html_e( 'Start a project', 'cf7-styler-for-divi' ); ?>
					<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M5 15L15 5M15 5H7M15 5v8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</a>
				<button type="button" class="cf7m-dy__later" data-action="dismiss">
					<?php esc_html_e( 'No thanks', 'cf7-styler-for-divi' ); ?>
				</button>
			</div>
		</div>

		<style>
			.cf7m-dy {
				position: fixed;
				right: 20px;
				bottom: 20px;
				z-index: 9990;
				width: 320px;
				max-width: calc(100vw - 40px);
				background: #fff;
				border: 1px solid #e5e7eb;
				border-radius: 10px;
				box-shadow: 0 8px 30px rgba(17, 24, 39, .12);
				padding: 18px 18px 16px;
				box-sizing: border-box;
				transform: translateY(12px);
				opacity: 0;
				animation: cf7m-dy-in .35s ease .8s forwards;
			}
			@keyframes cf7m-dy-in { to { transform: translateY(0); opacity: 1; } }
			@media (prefers-reduced-motion: reduce) { .cf7m-dy { animation-duration: 0s; animation-delay: 0s; } }
			.cf7m-dy__close { position: absolute; top: 10px; right: 10px; background: none; border: none; cursor: pointer; color: #9ca3af; padding: 4px; border-radius: 4px; display: inline-flex; }
			.cf7m-dy__close svg { width: 11px; height: 11px; display: block; }
			.cf7m-dy__close:hover { color: #374151; background: #f3f4f6; }
			.cf7m-dy__eyebrow { display: block; font-size: 11px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; color: #3044d7; margin-bottom: 6px; }
			.cf7m-dy__title { margin: 0 0 6px; font-size: 15px; line-height: 1.35; font-weight: 700; color: #111827; }
			.cf7m-dy__desc { margin: 0 0 14px; font-size: 13px; line-height: 1.55; color: #4b5563; }
			.cf7m-dy__actions { display: flex; align-items: center; gap: 12px; }
			.cf7m-dy__cta { display: inline-flex; align-items: center; gap: 5px; background: #3044d7; color: #fff !important; font-size: 13px; font-weight: 600; padding: 7px 14px; border-radius: 6px; text-decoration: none; transition: background .15s; }
			.cf7m-dy__cta:hover { background: #2536b0; color: #fff; }
			.cf7m-dy__cta svg { width: 13px; height: 13px; }
			.cf7m-dy__later { background: none; border: none; cursor: pointer; font-size: 12px; color: #9ca3af; padding: 4px 2px; }
			.cf7m-dy__later:hover { color: #6b7280; }
		</style>

		<script type="text/javascript">
		jQuery(function($){
			var $nudge  = $('#<?php echo esc_js( self::NUDGE_ID ); ?>');
			var nonce   = <?php echo wp_json_encode( $nonce ); ?>;
			var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;

			$nudge.on('click', '[data-action="dismiss"]', function(e){
				e.preventDefault();
				$nudge.fadeOut(180, function(){ $(this).remove(); });
				$.post(ajaxUrl, { action: 'cf7m_dismiss_dotyard', nonce: nonce });
			});
		});
		</script>
		<?php
	}

	public function ajax_dismiss() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'cf7m_dismiss_dotyard' ) ) {
			wp_send_json_error( [ 'message' => 'Security check failed' ] );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Insufficient permissions' ] );
		}

		update_option( self::DISMISSED_OPTION, time(), false );
		wp_send_json_success( [ 'state' => 'dismissed' ] );
	}
}
