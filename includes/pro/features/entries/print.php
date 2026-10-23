<?php
/**
 * Entry print handler – streams a self-contained print-ready HTML page.
 * Browser print dialog (Ctrl+P / Cmd+P) → Save as PDF.
 *
 * @package CF7_Mate\Features\Entries
 * @since 4.0.0
 */

namespace CF7_Mate\Features\Entries;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Entry_Print {

	const REST_NAMESPACE = 'cf7-styler/v1';

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_route' ] );
	}

	public function register_route() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/entries/(?P<id>\d+)/print',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'print_entry' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'args'                => [
					'id' => [
						'validate_callback' => function ( $v ) {
							return is_numeric( $v ) && $v > 0;
						},
					],
				],
			]
		);
	}

	public function check_permission() {
		return current_user_can( 'manage_options' );
	}

	public function print_entry( \WP_REST_Request $request ) {
		$id   = absint( $request['id'] );
		$post = get_post( $id );

		if ( ! $post || $post->post_type !== Entries_CPT::POST_TYPE ) {
			return new \WP_Error( 'not_found', __( 'Entry not found.', 'cf7-styler-for-divi' ), [ 'status' => 404 ] );
		}

		$form_id    = (int) get_post_meta( $id, '_cf7m_form_id', true );
		$form_title = (string) get_post_meta( $id, '_cf7m_form_title', true );
		$status     = (string) get_post_meta( $id, '_cf7m_status', true ) ?: 'new';
		$created    = (string) get_post_meta( $id, '_cf7m_created', true ) ?: $post->post_date;
		$ip         = (string) get_post_meta( $id, '_cf7m_ip', true );
		$data_raw   = get_post_meta( $id, '_cf7m_data', true );
		$data       = is_string( $data_raw ) ? json_decode( $data_raw, true ) : [];
		if ( ! is_array( $data ) ) {
			$data = [];
		}

		$site_name = get_bloginfo( 'name' );

		// Stream clean HTML — bypass WP REST response envelope.
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		header( 'Content-Type: text/html; charset=utf-8' );
		header( 'X-Robots-Tag: noindex' );
		?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html( $form_title ?: __( 'Form Submission', 'cf7-styler-for-divi' ) ); ?> — <?php echo esc_html( $site_name ); ?></title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 14px; color: #111; background: #fff; padding: 40px; }
.cf7mp-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; border-bottom: 2px solid #111; padding-bottom: 16px; }
.cf7mp-title { font-size: 22px; font-weight: 700; }
.cf7mp-meta { font-size: 12px; color: #555; text-align: right; }
.cf7mp-section { margin-bottom: 28px; }
.cf7mp-section-title { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #888; margin-bottom: 10px; }
table { width: 100%; border-collapse: collapse; }
th, td { text-align: left; padding: 9px 12px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
th { font-weight: 600; color: #374151; width: 35%; background: #f9fafb; }
td { color: #111; word-break: break-word; }
.cf7mp-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; background: #d1f0e0; color: #065f46; }
.cf7mp-footer { margin-top: 40px; font-size: 11px; color: #aaa; text-align: center; }
.cf7mp-print-btn { position: fixed; top: 16px; right: 16px; padding: 8px 18px; background: #3858e9; color: #fff; border: 0; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; }
@media print {
  .cf7mp-print-btn { display: none; }
  body { padding: 20px; }
  @page { margin: 15mm; }
}
</style>
</head>
<body>
<button class="cf7mp-print-btn" onclick="window.print()"><?php esc_html_e( 'Print / Save PDF', 'cf7-styler-for-divi' ); ?></button>

<div class="cf7mp-header">
  <div>
    <div class="cf7mp-title"><?php echo esc_html( $form_title ?: __( 'Form Submission', 'cf7-styler-for-divi' ) ); ?></div>
    <div style="margin-top:4px;font-size:12px;color:#555;"><?php echo esc_html( $site_name ); ?></div>
  </div>
  <div class="cf7mp-meta">
    <div><?php echo esc_html( __( 'Entry #', 'cf7-styler-for-divi' ) . $id ); ?></div>
    <div><?php echo esc_html( $created ); ?></div>
    <div style="margin-top:4px;"><span class="cf7mp-badge"><?php echo esc_html( ucfirst( $status ) ); ?></span></div>
  </div>
</div>

<?php if ( ! empty( $data ) ) : ?>
<div class="cf7mp-section">
  <div class="cf7mp-section-title"><?php esc_html_e( 'Form Fields', 'cf7-styler-for-divi' ); ?></div>
  <table>
    <?php foreach ( $data as $key => $val ) :
      if ( is_array( $val ) ) { $val = implode( ', ', $val ); }
      ?>
    <tr>
      <th><?php echo esc_html( ucwords( str_replace( [ '-', '_' ], ' ', $key ) ) ); ?></th>
      <td><?php echo nl2br( esc_html( (string) $val ) ); ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php endif; ?>

<div class="cf7mp-section">
  <div class="cf7mp-section-title"><?php esc_html_e( 'Submission Info', 'cf7-styler-for-divi' ); ?></div>
  <table>
    <?php if ( $form_id ) : ?>
    <tr><th><?php esc_html_e( 'Form ID', 'cf7-styler-for-divi' ); ?></th><td><?php echo esc_html( (string) $form_id ); ?></td></tr>
    <?php endif; ?>
    <tr><th><?php esc_html_e( 'Submitted', 'cf7-styler-for-divi' ); ?></th><td><?php echo esc_html( $created ); ?></td></tr>
    <?php if ( $ip ) : ?>
    <tr><th><?php esc_html_e( 'IP Address', 'cf7-styler-for-divi' ); ?></th><td><?php echo esc_html( $ip ); ?></td></tr>
    <?php endif; ?>
    <tr><th><?php esc_html_e( 'Status', 'cf7-styler-for-divi' ); ?></th><td><?php echo esc_html( ucfirst( $status ) ); ?></td></tr>
  </table>
</div>

<div class="cf7mp-footer">
  <?php
  /* translators: plugin name */
  printf( esc_html__( 'Exported by CF7 Mate · %s', 'cf7-styler-for-divi' ), esc_html( gmdate( 'Y-m-d H:i' ) ) );
  ?>
</div>
<script>window.addEventListener('load', function(){ window.print(); });</script>
</body>
</html>
		<?php
		// phpcs:enable
		exit;
	}
}
