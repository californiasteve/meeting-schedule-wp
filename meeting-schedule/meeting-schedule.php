<?php
/**
 * Plugin Name:       MeetingSchedule.app
 * Plugin URI:        https://meetingschedule.app/
 * Description:       Build a printable meeting schedule from a BMLT server in wp-admin, then let visitors download it as a PDF, Word or Excel file with the [meeting_schedule] shortcode.
 * Version:           1.1.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            MeetingSchedule.app
 * Author URI:        https://meetingschedule.app/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       meeting-schedule
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MEETING_SCHEDULE_VERSION', '1.1.0' );
define( 'MEETING_SCHEDULE_SCHEDULE', 'meeting_schedule_schedule' );

/**
 * The schedule saved from the builder in wp-admin: array( 'config' => builder settings, 'saved' => timestamp ), or null.
 */
function meeting_schedule_saved() {
	$saved = get_option( MEETING_SCHEDULE_SCHEDULE );
	return ( is_array( $saved ) && isset( $saved['config'] ) && is_array( $saved['config'] ) ) ? $saved : null;
}

function meeting_schedule_app_url( $args = array() ) {
	$args = array_merge( array( 'embed' => '1', 'v' => MEETING_SCHEDULE_VERSION ), $args );
	return add_query_arg( array_map( 'rawurlencode', $args ), plugins_url( 'app/index.html', __FILE__ ) );
}

/* ---------- Public side: [meeting_schedule format="pdf|word|excel"] ---------- */

/**
 * pdf, word or excel (docx, doc, xlsx and xls work too); anything else is empty.
 */
function meeting_schedule_format( $value ) {
	$map = array(
		'pdf'   => 'pdf',
		'print' => 'pdf',
		'word'  => 'docx',
		'docx'  => 'docx',
		'doc'   => 'docx',
		'excel' => 'xlsx',
		'xlsx'  => 'xlsx',
		'xls'   => 'xlsx',
	);
	$value = strtolower( trim( (string) $value ) );
	return isset( $map[ $value ] ) ? $map[ $value ] : '';
}

/**
 * [meeting_schedule] shows buttons for all three formats; format="pdf", "word" or "excel" shows just that one.
 * label="…" changes the button text. The file is made in the visitor's browser from the saved schedule and
 * the meetings on the BMLT server at that moment.
 */
function meeting_schedule_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'format' => '',
			'label'  => '',
		),
		$atts,
		'meeting_schedule'
	);
	if ( ! meeting_schedule_saved() ) {
		return current_user_can( 'manage_options' )
			? '<p class="meeting-schedule-missing">' . esc_html__( 'No meeting schedule is saved yet. Build one under Meeting Schedule in wp-admin and click "Save for downloads".', 'meeting-schedule' ) . '</p>'
			: '';
	}

	$labels = array(
		'pdf'  => __( 'Print or save as PDF', 'meeting-schedule' ),
		'docx' => __( 'Download for Word', 'meeting-schedule' ),
		'xlsx' => __( 'Download for Excel', 'meeting-schedule' ),
	);
	$format  = meeting_schedule_format( $atts['format'] );
	$formats = $format ? array( $format ) : array_keys( $labels );

	wp_enqueue_script( 'meeting-schedule-public' );
	$out = '<span class="meeting-schedule-downloads">';
	foreach ( $formats as $f ) {
		$text = ( $format && '' !== trim( $atts['label'] ) ) ? $atts['label'] : $labels[ $f ];
		$out .= sprintf(
			'<button type="button" class="wp-element-button meeting-schedule-download" data-format="%1$s">%2$s</button> ',
			esc_attr( $f ),
			esc_html( $text )
		);
	}
	return $out . '<span class="meeting-schedule-status" role="status"></span></span>';
}
add_shortcode( 'meeting_schedule', 'meeting_schedule_shortcode' );

function meeting_schedule_register_assets() {
	wp_register_script( 'meeting-schedule-public', plugins_url( 'assets/public.js', __FILE__ ), array(), MEETING_SCHEDULE_VERSION, true );
	$saved = meeting_schedule_saved();
	wp_add_inline_script(
		'meeting-schedule-public',
		'window.meetingSchedule = ' . wp_json_encode(
			array(
				'app'    => meeting_schedule_app_url(),
				'config' => $saved ? $saved['config'] : null,
				'text'   => array(
					'preparing' => __( 'Preparing…', 'meeting-schedule' ),
					'failed'    => __( "The schedule couldn't be made right now. Please try again later.", 'meeting-schedule' ),
				),
			),
			JSON_HEX_TAG | JSON_HEX_AMP
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'meeting_schedule_register_assets' );

/* ---------- wp-admin: Meeting Schedule menu with the builder ---------- */

function meeting_schedule_admin_menu() {
	add_menu_page(
		__( 'Meeting Schedule', 'meeting-schedule' ),
		__( 'Meeting Schedule', 'meeting-schedule' ),
		'manage_options',
		'meeting-schedule',
		'meeting_schedule_admin_page',
		'dashicons-calendar-alt',
		58
	);
}
add_action( 'admin_menu', 'meeting_schedule_admin_menu' );

function meeting_schedule_admin_assets( $hook ) {
	if ( 'toplevel_page_meeting-schedule' !== $hook ) {
		return;
	}
	wp_enqueue_script( 'meeting-schedule-admin', plugins_url( 'assets/admin.js', __FILE__ ), array(), MEETING_SCHEDULE_VERSION, true );
	$saved = meeting_schedule_saved();
	wp_localize_script(
		'meeting-schedule-admin',
		'meetingScheduleAdmin',
		array(
			'ajax'   => admin_url( 'admin-ajax.php' ),
			'nonce'  => wp_create_nonce( 'meeting_schedule_save' ),
			'config' => $saved ? wp_json_encode( $saved['config'] ) : '',
			'text'   => array(
				'saving' => __( 'Saving…', 'meeting-schedule' ),
				'saved'  => __( 'Saved. The download buttons on your site now use this schedule.', 'meeting-schedule' ),
				'failed' => __( "Couldn't save. Reload the page and try again.", 'meeting-schedule' ),
				'opened' => __( 'Opened the saved schedule in the builder.', 'meeting-schedule' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'meeting_schedule_admin_assets' );

function meeting_schedule_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$saved = meeting_schedule_saved();
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Meeting Schedule', 'meeting-schedule' ); ?></h1>
		<p>
			<button type="button" class="button button-primary" id="ms-save"><?php esc_html_e( 'Save for downloads', 'meeting-schedule' ); ?></button>
			<button type="button" class="button" id="ms-open"<?php echo $saved ? '' : ' hidden'; ?>><?php esc_html_e( 'Open the saved schedule', 'meeting-schedule' ); ?></button>
			<span id="ms-status" role="status" style="margin-left:.5em">
				<?php
				if ( $saved ) {
					/* translators: %s: date and time */
					printf( esc_html__( 'Last saved %s.', 'meeting-schedule' ), esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $saved['saved'] ) ) );
				} else {
					esc_html_e( 'Nothing saved yet.', 'meeting-schedule' );
				}
				?>
			</span>
		</p>
		<p class="description">
			<?php echo wp_kses( __( 'Build the schedule below, then click <b>Save for downloads</b>. On any page, <code>[meeting_schedule]</code> shows PDF, Word and Excel buttons; <code>[meeting_schedule format="pdf"]</code>, <code>format="word"</code> or <code>format="excel"</code> shows just one, and <code>label="…"</code> changes its text. Meetings load live from BMLT each time someone downloads.', 'meeting-schedule' ), array( 'b' => array(), 'code' => array() ) ); ?>
		</p>
		<iframe id="ms-builder" src="<?php echo esc_url( meeting_schedule_app_url() ); ?>" title="<?php esc_attr_e( 'Meeting schedule builder', 'meeting-schedule' ); ?>" style="display:block;width:100%;height:calc(100vh - 220px);min-height:600px;border:1px solid #c3c4c7;background:#fff"></iframe>
	</div>
	<?php
}

/**
 * Saves the builder's settings from the admin page. They are the same JSON the app keeps in the browser.
 */
function meeting_schedule_ajax_save() {
	check_ajax_referer( 'meeting_schedule_save' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( null, 403 );
	}
	$config = json_decode( wp_unslash( isset( $_POST['config'] ) ? $_POST['config'] : '' ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON, stored as data and never printed as HTML
	if ( ! is_array( $config ) || ! isset( $config['source'] ) ) {
		wp_send_json_error( null, 400 );
	}
	update_option(
		MEETING_SCHEDULE_SCHEDULE,
		array(
			'config' => $config,
			'saved'  => time(),
		),
		false
	);
	wp_send_json_success();
}
add_action( 'wp_ajax_meeting_schedule_save', 'meeting_schedule_ajax_save' );

function meeting_schedule_action_links( $links ) {
	$url = admin_url( 'admin.php?page=meeting-schedule' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Builder', 'meeting-schedule' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'meeting_schedule_action_links' );
