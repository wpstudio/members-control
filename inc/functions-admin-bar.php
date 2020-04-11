<?php
/**
 * Functions for modifying the WordPress admin bar.
 */

# Hook the members admin bar to 'wp_before_admin_bar_render'.
add_action( 'wp_before_admin_bar_render', 'memberscontrol_admin_bar' );

/**
 * Adds new menu items to the WordPress admin bar.
 */
function memberscontrol_admin_bar() {
	global $wp_admin_bar;

	// Check if the current user can 'create_roles'.
	if ( current_user_can( 'create_roles' ) ) {

		// Add a 'Role' menu item as a sub-menu item of the new content menu.
		$wp_admin_bar->add_menu(
			array(
				'id'     => 'memberscontrol-new-role',
				'parent' => 'new-content',
				'title'  => esc_attr__( 'Role', 'memberscontrol' ),
				'href'   => esc_url( memberscontrol_get_new_role_url() )
			)
		);
	}
}
