<?php
// Removes the saved schedule (and the 1.0 settings) when the plugin is deleted from the Plugins screen.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
delete_option( 'meeting_schedule_schedule' );
delete_option( 'meeting_schedule_settings' );
