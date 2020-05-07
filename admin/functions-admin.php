<?php
/**
 * General admin functionality.
 */

# Register scripts/styles.
add_action( 'admin_enqueue_scripts', 'memberscontrol_admin_register_scripts', 0 );
add_action( 'admin_enqueue_scripts', 'memberscontrol_admin_register_styles',  0 );

/**
 * Get an Underscore JS template.
 */
function memberscontrol_get_underscore_template( $name ) {
	require_once( memberscontrol_plugin()->dir . "admin/tmpl/{$name}.php" );
}

/**
 * Registers custom plugin scripts.
 */
function memberscontrol_admin_register_scripts() {

	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_register_script( 'memberscontrol-settings',  memberscontrol_plugin()->uri . "js/settings{$min}.js",  array( 'jquery'  ), '', true );
	wp_register_script( 'memberscontrol-edit-post', memberscontrol_plugin()->uri . "js/edit-post{$min}.js", array( 'jquery'  ), '', true );
	wp_register_script( 'memberscontrol-edit-role', memberscontrol_plugin()->uri . "js/edit-role{$min}.js", array( 'postbox', 'wp-util' ), '', true );

	// Localize our script with some text we want to pass in.
	$i18n = array(
		'button_role_edit' => esc_html__( 'Edit',                'memberscontrol' ),
		'button_role_ok'   => esc_html__( 'OK',                  'memberscontrol' ),
		'label_grant_cap'  => esc_html__( 'Grant %s capability', 'memberscontrol' ),
		'label_deny_cap'   => esc_html__( 'Deny %s capability',  'memberscontrol' ),
		'ays_delete_role'  => esc_html__( 'Are you sure you want to delete this role? This is a permanent action and cannot be undone.', 'memberscontrol' ),
		'hidden_caps'      => memberscontrol_get_hidden_caps()
	);

	wp_localize_script( 'memberscontrol-edit-role', 'memberscontrol_i18n', $i18n );
}

/**
 * Registers custom plugin scripts.
 */
function memberscontrol_admin_register_styles() {

	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_register_style( 'memberscontrol-admin', memberscontrol_plugin()->uri . "css/admin{$min}.css" );
}

/**
 * Function for safely deleting a role and transferring the deleted role's users to the default
 * role.  Note that this function can be extremely intensive.  Whenever a role is deleted, it's
 * best for the site admin to assign the user's of the role to a different role beforehand.
 */
function memberscontrol_delete_role( $role ) {

	// Get the default role.
	$default_role = get_option( 'default_role' );

	// Don't delete the default role. Site admins should change the default before attempting to delete the role.
	if ( $role == $default_role )
		return;

	// Get all users with the role to be deleted.
	$users = get_users( array( 'role' => $role ) );

	// Check if there are any users with the role we're deleting.
	if ( is_array( $users ) ) {

		// If users are found, loop through them.
		foreach ( $users as $user ) {

			// If the user has the role and no other roles, set their role to the default.
			if ( $user->has_cap( $role ) && 1 >= count( $user->roles ) )
				$user->set_role( $default_role );

			// Else, remove the role.
			else if ( $user->has_cap( $role ) )
				$user->remove_role( $role );
		}
	}

	// Remove the role.
	remove_role( $role );

	// Remove the role from the role factory.
	memberscontrol_unregister_role( $role );
}

/**
 * Returns an array of all the user meta keys in the $wpdb->usermeta table.
 */
function memberscontrol_get_user_meta_keys() {
	global $wpdb;

	return $wpdb->get_col( "SELECT meta_key FROM $wpdb->usermeta GROUP BY meta_key ORDER BY meta_key" );
}

add_action( 'admin_enqueue_scripts', 'memberscontrol_add_pointers' );
/**
 * Adds helper pointers to the admin
 */
function memberscontrol_add_pointers() {

	$pointers = apply_filters( 'memberscontrol_admin_pointers', array() );

	if ( empty( $pointers ) ) {
		return;
	}

	// Get dismissed pointers
	$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
	$valid_pointers =array();
 
	// Check pointers and remove dismissed ones.
	foreach ( $pointers as $pointer_id => $pointer ) {
 
		// Sanity check
		if ( in_array( $pointer_id, $dismissed ) || empty( $pointer )  || empty( $pointer_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) ) {
			continue;
		}
 
		$pointer['pointer_id'] = $pointer_id;
 
		$valid_pointers['pointers'][] =  $pointer;
	}
 
	if ( empty( $valid_pointers ) ) {
		return;
	}
 
	wp_enqueue_style( 'wp-pointer' );
	wp_enqueue_script( 'memberscontrol-pointers', memberscontrol_plugin()->uri . '/js/memberscontrol-pointers.min.js', array( 'wp-pointer' ) );
	wp_localize_script( 'memberscontrol-pointers', 'memberscontrolPointers', $valid_pointers );
}

add_filter( 'memberscontrol_admin_pointers', 'memberscontrol_fork_helper_pointer' );

function memberscontrol_fork_helper_pointer( $pointers ) {
	ob_start();
	?>
	<h3><?php _e( 'Welcome to MembersControl 1.0!', 'memberscontrol' ); ?></h3>
	<p><?php _e( 'This plug is a fork of the incredible Members plugin by Justin Tadlock.', 'memberscontrol' ); ?></p>
	<p><?php _e( 'At the end of 2019, Justin sold the plugin to the MemberPress team, and they added upsells.', 'memberscontrol' ); ?></p>
	<p><?php _e( 'This fork removes all upsells and restores Justin\'s plugin to its proper functionality.', 'memberscontrol' ); ?></p>
	<?php
	$content = ob_get_clean();
    $pointers['members_fork'] = array(
        'target' => '#toplevel_page_members',
        'options' => array(
            'content' => $content,
            'position' => array( 
            	'edge' => 'left', 
            	'align' => 'center' 
            )
        )
    );
    return $pointers;
}
