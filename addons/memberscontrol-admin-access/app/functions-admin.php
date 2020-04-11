<?php
/**
 * Admin functions.
 */

namespace MembersControl\AddOns\AdminAccess;

# Redirect users without access.
add_action( 'admin_init', __NAMESPACE__ . '\access_check', 0 );

# Register custom settings views.
add_action( 'memberscontrol_register_settings_views', __NAMESPACE__ . '\register_views' );

/**
 * Checks if the current user has access to the admin.  If not, it redirects them.
 */
function access_check() {

	if ( ! current_user_has_access() && ! wp_doing_ajax() ) {
		wp_redirect( esc_url_raw( get_redirect_url() ) );
		exit;
	}

	// Override WooCommerce's admin redirect.
	add_filter( 'woocommerce_prevent_admin_access', '__return_false' );
}

/**
 * Registers custom settings views with the Members plugin.
 */
function register_views( $manager ) {

	// Bail if not on the settings screen.
	if ( 'memberscontrol-settings' !== $manager->name )
		return;

	require_once( app()->dir . 'app/class-view-settings.php' );

	// Register a view for the plugin settings.
	$manager->register_view(
		new View_Settings(
			'memberscontrol_admin_access',
			[
				'label'    => esc_html__( 'Admin Access', 'memberscontrol' ),
				'priority' => 15
			]
		)
	);
}
