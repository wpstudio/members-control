<?php
/**
 * Handles settings functionality.
 */

# Register settings views.
add_action( 'memberscontrol_register_settings_views', 'memberscontrol_register_default_settings_views', 5 );

/**
 * Registers the plugin's built-in settings views.
 */
function memberscontrol_register_default_settings_views( $manager ) {

	// Bail if not on the settings screen.
	if ( 'memberscontrol-settings' !== $manager->name )
		return;

	// Register general settings view (default view).
	$manager->register_view(
		new \MembersControl\Admin\View_General(
			'general',
			array(
				'label'    => esc_html__( 'General', 'memberscontrol' ),
				'priority' => 0
			)
		)
	);

	// Register add-ons view.
	$manager->register_view(
		new \MembersControl\Admin\View_Addons(
			'add-ons',
			array(
				'label'    => esc_html__( 'Add-Ons', 'memberscontrol' ),
				'priority' => 95
			)
		)
	);
}

/**
 * Conditional function to check if on the plugin's settings page.
 */
function memberscontrol_is_settings_page() {

	$screen = get_current_screen();

	return is_object( $screen ) && 'memberscontrol_page_members-settings' === $screen->id;
}

/**
 * Conditional function to check if an add-on is active.
 */
function memberscontrol_is_addon_active( $addon ) {
	return in_array( $addon, get_option( 'memberscontrol_active_addons', array() ) );
}

/**
 * Returns the URL to the settings page.
 */
function memberscontrol_get_settings_page_url() {

	return add_query_arg( array( 'page' => 'memberscontrol-settings' ), admin_url( 'options-general.php' ) );
}

/**
 * Returns the URL to a settings view page.
 */
function memberscontrol_get_settings_view_url( $view ) {

	return add_query_arg( array( 'view' => sanitize_key( $view ) ), memberscontrol_get_settings_page_url() );
}

/**
 * Returns the current settings view name.
 */
function memberscontrol_get_current_settings_view() {

	if ( ! memberscontrol_is_settings_page() )
		return '';

	return isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'general';
}

/**
 * Conditional function to check if on a specific settings view page.
 */
function memberscontrol_is_settings_view( $view = '' ) {

	return memberscontrol_is_settings_page() && $view === memberscontrol_get_current_settings_view();
}
