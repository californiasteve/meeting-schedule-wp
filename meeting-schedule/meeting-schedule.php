<?php
/**
 * Plugin Name:       MeetingSchedule.app
 * Plugin URI:        https://meetingschedule.app/
 * Description:       Build printable meeting schedules from a BMLT server on any page. Add the [meeting_schedule] shortcode where you want the builder to appear.
 * Version:           1.0.0
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

define( 'MEETING_SCHEDULE_VERSION', '1.0.0' );
define( 'MEETING_SCHEDULE_OPTION', 'meeting_schedule_settings' );

/**
 * Saved defaults, filled in with built-in values.
 */
function meeting_schedule_settings() {
	$saved = get_option( MEETING_SCHEDULE_OPTION, array() );
	return wp_parse_args(
		is_array( $saved ) ? $saved : array(),
		array(
			'server'   => '',
			'services' => '',
			'height'   => '85vh',
		)
	);
}

/**
 * Keeps only digits and commas: "12, 34" becomes "12,34".
 */
function meeting_schedule_clean_ids( $value ) {
	$ids = array_filter( array_map( 'trim', explode( ',', (string) $value ) ), 'ctype_digit' );
	return implode( ',', $ids );
}

/**
 * A CSS length such as 900px, 85vh or 100%; anything else falls back to 85vh.
 */
function meeting_schedule_clean_height( $value ) {
	$value = trim( (string) $value );
	if ( ctype_digit( $value ) ) {
		$value .= 'px';
	}
	return preg_match( '/^\d{2,4}(px|vh|%)$/', $value ) ? $value : '85vh';
}

/**
 * [meeting_schedule server="https://…/main_server/" services="12,34" height="85vh"]
 * Shows the builder in a frame on the same site, so the theme's styles can't change it.
 */
function meeting_schedule_shortcode( $atts ) {
	$defaults = meeting_schedule_settings();
	$atts     = shortcode_atts( $defaults, $atts, 'meeting_schedule' );

	$args = array(
		'embed' => '1',
		'v'     => MEETING_SCHEDULE_VERSION,
	);
	$server = esc_url_raw( trim( $atts['server'] ), array( 'https' ) );
	if ( $server ) {
		$args['server'] = $server;
	}
	$services = meeting_schedule_clean_ids( $atts['services'] );
	if ( $services ) {
		$args['services'] = $services;
	}
	$src    = add_query_arg( array_map( 'rawurlencode', $args ), plugins_url( 'app/index.html', __FILE__ ) );
	$height = meeting_schedule_clean_height( $atts['height'] );

	return sprintf(
		'<div class="meeting-schedule-embed"><iframe src="%1$s" title="%2$s" style="display:block;width:100%%;height:%3$s;border:0" loading="lazy"></iframe><p style="margin:.5em 0 0;font-size:.875em"><a href="%1$s" target="_blank" rel="noopener">%4$s</a></p></div>',
		esc_url( $src ),
		esc_attr__( 'Meeting schedule builder', 'meeting-schedule' ),
		esc_attr( $height ),
		esc_html__( 'Open the schedule builder in its own window', 'meeting-schedule' )
	);
}
add_shortcode( 'meeting_schedule', 'meeting_schedule_shortcode' );

/* ---------- Settings → Meeting Schedule ---------- */

function meeting_schedule_sanitize( $input ) {
	$input = is_array( $input ) ? $input : array();
	return array(
		'server'   => esc_url_raw( trim( isset( $input['server'] ) ? $input['server'] : '' ), array( 'https' ) ),
		'services' => meeting_schedule_clean_ids( isset( $input['services'] ) ? $input['services'] : '' ),
		'height'   => meeting_schedule_clean_height( isset( $input['height'] ) ? $input['height'] : '' ),
	);
}

function meeting_schedule_admin_init() {
	register_setting(
		'meeting_schedule',
		MEETING_SCHEDULE_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'meeting_schedule_sanitize',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'meeting_schedule_admin_init' );

function meeting_schedule_admin_menu() {
	add_options_page(
		__( 'Meeting Schedule', 'meeting-schedule' ),
		__( 'Meeting Schedule', 'meeting-schedule' ),
		'manage_options',
		'meeting-schedule',
		'meeting_schedule_settings_page'
	);
}
add_action( 'admin_menu', 'meeting_schedule_admin_menu' );

function meeting_schedule_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s    = meeting_schedule_settings();
	$name = MEETING_SCHEDULE_OPTION;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Meeting Schedule', 'meeting-schedule' ); ?></h1>
		<p><?php echo wp_kses( __( 'Add <code>[meeting_schedule]</code> to any page or post to show the schedule builder. The settings below are where it starts for someone visiting for the first time; after that it remembers their own choices in their browser.', 'meeting-schedule' ), array( 'code' => array() ) ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'meeting_schedule' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="ms-server"><?php esc_html_e( 'BMLT server', 'meeting-schedule' ); ?></label></th>
					<td>
						<input type="url" class="regular-text code" id="ms-server" name="<?php echo esc_attr( $name ); ?>[server]" value="<?php echo esc_attr( $s['server'] ); ?>" placeholder="https://aggregator.bmltenabled.org/main_server/">
						<p class="description"><?php esc_html_e( 'The root server address, starting with https://. Leave empty to use the BMLT aggregator.', 'meeting-schedule' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="ms-services"><?php esc_html_e( 'Service body IDs', 'meeting-schedule' ); ?></label></th>
					<td>
						<input type="text" class="regular-text code" id="ms-services" name="<?php echo esc_attr( $name ); ?>[services]" value="<?php echo esc_attr( $s['services'] ); ?>" placeholder="12, 34">
						<p class="description"><?php esc_html_e( 'One or more service body ID numbers, separated by commas. Their meetings load as soon as the page opens. Leave empty to let visitors pick.', 'meeting-schedule' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="ms-height"><?php esc_html_e( 'Height', 'meeting-schedule' ); ?></label></th>
					<td>
						<input type="text" class="small-text code" id="ms-height" name="<?php echo esc_attr( $name ); ?>[height]" value="<?php echo esc_attr( $s['height'] ); ?>">
						<p class="description"><?php esc_html_e( 'How tall the builder is on the page, such as 85vh (85% of the window) or 900px.', 'meeting-schedule' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
		<h2><?php esc_html_e( 'Different settings on one page', 'meeting-schedule' ); ?></h2>
		<p><?php echo wp_kses( __( 'Each shortcode can override these: <code>[meeting_schedule server="https://example.org/main_server/" services="12,34" height="900px"]</code>', 'meeting-schedule' ), array( 'code' => array() ) ); ?></p>
	</div>
	<?php
}

function meeting_schedule_action_links( $links ) {
	$url = admin_url( 'options-general.php?page=meeting-schedule' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'meeting-schedule' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'meeting_schedule_action_links' );
