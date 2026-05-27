<?php
/**
 * Review request notice (free build).
 *
 * Lives at the top of CF7 Mate's own admin pages only — never on Dashboard,
 * Plugins, or any other admin screen (per WordPress.org guideline §10:
 * upsell/review notices must be restricted to the plugin's own pages).
 *
 * Cadence:
 *   - First appears 7 days after install.
 *   - 1st dismiss → re-appears 30 days later.
 *   - 2nd dismiss → re-appears 90 days later.
 *   - 3rd dismiss → never shown again.
 *   - 4–5★ rating → opens wp.org review form + permanent dismiss.
 *   - 1–3★ rating → permanent dismiss (no nag; route to support instead).
 *
 * @package CF7_Mate
 * @since 3.0.5
 */

namespace CF7_Mate;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Review_Notice {

	private static $instance = null;

	const NOTICE_ID           = 'cf7m_review_notice';
	const COUNT_OPTION        = 'cf7m_review_dismiss_count';
	const NEXT_SHOW_OPTION    = 'cf7m_review_next_show_at';
	const INSTALL_DATE_OPTION = 'cf7m_install_date';
	const INITIAL_DELAY       = 7  * DAY_IN_SECONDS;
	const FIRST_DEFER         = 30 * DAY_IN_SECONDS;
	const SECOND_DEFER        = 90 * DAY_IN_SECONDS;
	const MAX_DISMISSALS      = 3;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_notices', [ $this, 'display_notice' ] );
		add_action( 'wp_ajax_cf7m_dismiss_review', [ $this, 'ajax_dismiss' ] );
	}

	private function is_cf7_mate_page() {
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return in_array( $page, [ 'cf7-mate', 'cf7-mate-responses', 'cf7-mate-analytics' ], true );
	}

	private function should_display() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// Permanently dismissed.
		$count = (int) get_option( self::COUNT_OPTION, 0 );
		if ( $count >= self::MAX_DISMISSALS ) {
			return false;
		}

		$install_date = (int) get_option( self::INSTALL_DATE_OPTION, 0 );
		if ( ! $install_date ) {
			return false;
		}

		// Initial 7-day delay after install.
		if ( time() < $install_date + self::INITIAL_DELAY ) {
			return false;
		}

		// Honour the snooze timestamp set by previous dismissals.
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
		$review_url  = 'https://wordpress.org/support/plugin/cf7-styler-for-divi/reviews/#new-post';
		$support_url = 'https://cf7mate.com/support';
		$nonce       = wp_create_nonce( 'cf7m_dismiss_review' );
		?>
		<div id="<?php echo esc_attr( self::NOTICE_ID ); ?>" class="cf7m-rn-wrap notice">
			<div class="cf7m-rn">
				<svg class="cf7m-rn__icon" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path d="M10 1l2.39 5.26 5.61.82-4.06 3.95.96 5.58L10 14.27 5.1 16.61l.96-5.58L2 7.08l5.61-.82L10 1z" fill="#3044d7" fill-opacity=".15" stroke="#3044d7" stroke-width="1.4" stroke-linejoin="round"/>
				</svg>

				<div class="cf7m-rn__body">
					<span class="cf7m-rn__label" id="cf7m-rn-label">
						<?php
						printf(
							/* translators: %s: plugin name */
							esc_html__( 'How are you finding %s?', 'cf7-styler-for-divi' ),
							'<strong>CF7 Mate</strong>'
						);
						?>
					</span>

					<span class="cf7m-rn__stars" id="cf7m-rn-stars" role="group" aria-label="<?php esc_attr_e( 'Rate CF7 Mate', 'cf7-styler-for-divi' ); ?>">
						<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
						<button type="button" class="cf7m-rn__star" data-rating="<?php echo (int) $i; ?>"
								aria-label="<?php echo esc_attr( sprintf( /* translators: %d: number of stars */ __( '%d star', 'cf7-styler-for-divi' ), $i ) ); ?>">
							<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path d="M10 1l2.39 5.26 5.61.82-4.06 3.95.96 5.58L10 14.27 5.1 16.61l.96-5.58L2 7.08l5.61-.82L10 1z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
							</svg>
						</button>
						<?php endfor; ?>
					</span>
				</div>

				<button type="button" class="cf7m-rn__later" data-action="later">
					<?php esc_html_e( 'Remind me later', 'cf7-styler-for-divi' ); ?>
				</button>
				<button type="button" class="cf7m-rn__close" data-action="dismiss" aria-label="<?php esc_attr_e( 'Dismiss', 'cf7-styler-for-divi' ); ?>">
					<svg viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M1 1l10 10M11 1L1 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
				</button>
			</div>
		</div>

		<style>
			.cf7m-rn-wrap.notice {
				padding: 0 !important;
				border-left: none !important;
				background: #fff !important;
				border: 1px solid #e5e7eb !important;
				border-radius: 8px !important;
				box-shadow: 0 1px 3px rgba(0,0,0,.06) !important;
				margin: 12px 20px 4px 0 !important;
				overflow: hidden !important;
			}
			.cf7m-rn-wrap .notice-dismiss { display: none !important; }
			.cf7m-rn { display: flex; align-items: center; gap: 10px; padding: 10px 14px; min-height: 44px; flex-wrap: wrap; }
			.cf7m-rn__icon { width: 18px; height: 18px; flex-shrink: 0; }
			.cf7m-rn__body { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; flex-wrap: wrap; }
			.cf7m-rn__label { font-size: 13px; color: #374151; }
			.cf7m-rn__label strong { color: #111827; font-weight: 600; }
			.cf7m-rn__stars { display: inline-flex; gap: 2px; align-items: center; }
			.cf7m-rn__star { background: none; border: none; padding: 2px; cursor: pointer; color: #d1d5db; transition: color .1s, transform .1s; display: inline-flex; line-height: 1; }
			.cf7m-rn__star svg { width: 18px; height: 18px; display: block; }
			.cf7m-rn__star:hover, .cf7m-rn__star.is-lit { color: #f59e0b; transform: scale(1.15); }
			.cf7m-rn__star.is-lit svg path { fill: #f59e0b; stroke: #f59e0b; }
			.cf7m-rn__later, .cf7m-rn__close { background: none; border: none; cursor: pointer; color: #9ca3af; border-radius: 4px; transition: color .15s, background .15s; }
			.cf7m-rn__later { padding: 4px 8px; font-size: 12px; white-space: nowrap; margin-left: auto; }
			.cf7m-rn__later:hover { color: #6b7280; }
			.cf7m-rn__close { padding: 4px; display: inline-flex; align-items: center; flex-shrink: 0; }
			.cf7m-rn__close svg { width: 12px; height: 12px; display: block; }
			.cf7m-rn__close:hover { color: #374151; background: #f3f4f6; }
		</style>

		<script type="text/javascript">
		jQuery(function($){
			var $notice  = $('#<?php echo esc_js( self::NOTICE_ID ); ?>');
			var $stars   = $notice.find('.cf7m-rn__star');
			var $label   = $notice.find('#cf7m-rn-label');
			var $starWrap = $notice.find('#cf7m-rn-stars');
			var nonce    = <?php echo wp_json_encode( $nonce ); ?>;
			var ajaxUrl  = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
			var reviewUrl = <?php echo wp_json_encode( $review_url ); ?>;
			var supportUrl = <?php echo wp_json_encode( $support_url ); ?>;

			function send(action) {
				return $.post(ajaxUrl, {
					action: 'cf7m_dismiss_review',
					nonce:  nonce,
					mode:   action
				});
			}
			function hideAndForget(action) {
				$notice.fadeOut(220, function(){ $(this).remove(); });
				send(action);
			}

			$stars.on('mouseenter', function(){
				var r = parseInt($(this).data('rating'), 10);
				$stars.each(function(){ $(this).toggleClass('is-lit', parseInt($(this).data('rating'),10) <= r); });
			}).on('mouseleave', function(){
				$stars.removeClass('is-lit');
			});

			$stars.on('click', function(){
				var r = parseInt($(this).data('rating'), 10);
				if ( r >= 4 ) {
					$label.html('<?php echo esc_js( __( 'Thank you! Your review really helps us out.', 'cf7-styler-for-divi' ) ); ?>');
					$starWrap.hide();
					window.open(reviewUrl, '_blank', 'noopener');
					setTimeout(function(){ hideAndForget('forever'); }, 3000);
				} else {
					$label.html('<?php echo esc_js( __( 'Thanks for the feedback! Anything we can fix?', 'cf7-styler-for-divi' ) ); ?> <a href="' + supportUrl + '" target="_blank" rel="noopener" style="color:#3044d7;font-weight:600;text-decoration:none;"><?php echo esc_js( __( 'Contact support →', 'cf7-styler-for-divi' ) ); ?></a>');
					$starWrap.hide();
					setTimeout(function(){ hideAndForget('forever'); }, 4500);
				}
			});

			$notice.on('click', '[data-action="later"]', function(e){ e.preventDefault(); hideAndForget('later'); });
			$notice.on('click', '[data-action="dismiss"]', function(e){ e.preventDefault(); hideAndForget('forever'); });
		});
		</script>
		<?php
	}

	public function ajax_dismiss() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'cf7m_dismiss_review' ) ) {
			wp_send_json_error( [ 'message' => 'Security check failed' ] );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Insufficient permissions' ] );
		}

		$mode  = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : 'later';
		$count = (int) get_option( self::COUNT_OPTION, 0 );

		if ( 'forever' === $mode ) {
			update_option( self::COUNT_OPTION, self::MAX_DISMISSALS, false );
			delete_option( self::NEXT_SHOW_OPTION );
			wp_send_json_success( [ 'state' => 'forever' ] );
		}

		// "Remind me later" cadence: 30 days → 90 days → never.
		$count++;
		update_option( self::COUNT_OPTION, $count, false );

		if ( $count >= self::MAX_DISMISSALS ) {
			delete_option( self::NEXT_SHOW_OPTION );
			wp_send_json_success( [ 'state' => 'forever' ] );
		}

		$defer = ( 1 === $count ) ? self::FIRST_DEFER : self::SECOND_DEFER;
		update_option( self::NEXT_SHOW_OPTION, time() + $defer, false );
		wp_send_json_success( [ 'state' => 'snoozed', 'next' => time() + $defer ] );
	}
}
