<?php
/**
 * Loads and enables the widgets for the plugin.
 */

# Hook widget registration to the 'widgets_init' hook.
add_action( 'widgets_init', 'memberscontrol_register_widgets' );

/**
 * Registers widgets for the plugin.
 */
function memberscontrol_register_widgets() {

	// If the login form widget is enabled.
	if ( memberscontrol_login_widget_enabled() ) {

		require_once( memberscontrol_plugin()->dir . 'inc/class-widget-login.php' );

		register_widget( '\MembersControl\Widget_Login' );
	}

	// If the users widget is enabled.
	if ( memberscontrol_users_widget_enabled() ) {

		require_once( memberscontrol_plugin()->dir . 'inc/class-widget-users.php' );

		register_widget( '\MembersControl\Widget_Users' );
	}
}
