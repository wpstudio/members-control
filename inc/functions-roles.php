<?php
/**
 * Role-related functions that extend the built-in WordPress Roles API.
 */

# Register roles.
add_action( 'wp_roles_init',          'memberscontrol_register_roles',         95 );
add_action( 'memberscontrol_register_roles', 'memberscontrol_register_default_roles',  5 );

/**
 * Fires the role registration action hook.
 */
function memberscontrol_register_roles( $wp_roles ) {

	do_action( 'memberscontrol_register_roles', $wp_roles );
}

/**
 * Registers any roles stored globally with WordPress.
 */
function memberscontrol_register_default_roles( $wp_roles ) {

	foreach ( $wp_roles->roles as $name => $object ) {

		$args = array(
			'label' => $object['name'],
			'caps'  => $object['capabilities']
		);

		memberscontrol_register_role( $name, $args );
	}

	// Unset any roles that were registered previously but are not currently available.
	foreach ( memberscontrol_get_roles() as $role ) {

		if ( ! isset( $wp_roles->roles[ $role->name ] ) )
			memberscontrol_unregister_role( $role->name );
	}
}

/**
 * Returns the instance of the role registry.
 */
function memberscontrol_role_registry() {

	return \MembersControl\Registry::get_instance( 'role' );
}

/**
 * Returns all registered roles.
 */
function memberscontrol_get_roles() {

	return memberscontrol_role_registry()->get_collection();
}

/**
 * Registers a role.
 */
function memberscontrol_register_role( $name, $args = array() ) {

	memberscontrol_role_registry()->register( $name, new \MembersControl\Role( $name, $args ) );
}

/**
 * Unregisters a role.
 */
function memberscontrol_unregister_role( $name ) {

	memberscontrol_role_registry()->unregister( $name );
}

/**
 * Returns a role object.
 */
function memberscontrol_get_role( $name ) {

	return memberscontrol_role_registry()->get( $name );
}

/**
 * Checks if a role object exists.
 */
function memberscontrol_role_exists( $name ) {

	return memberscontrol_role_registry()->exists( $name );
}

/* ====== Multiple Role Functions ====== */

/**
 * Returns an array of editable roles.
 */
function memberscontrol_get_editable_roles() {
	global $wp_roles;

	$editable = function_exists( 'get_editable_roles' ) ? get_editable_roles() : apply_filters( 'editable_roles', $wp_roles->roles );

	return array_keys( $editable );
}

/**
 * Returns an array of uneditable roles.
 */
function memberscontrol_get_uneditable_roles() {

	return array_diff( array_keys( memberscontrol_get_roles() ), memberscontrol_get_editable_roles() );
}

/**
 * Returns an array of core WP roles.  Note that we remove any that are not registered.
 */
function memberscontrol_get_wordpress_roles() {

	$roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );

	return array_intersect( $roles, array_keys( memberscontrol_get_roles() ) );
}

/**
 * Returns an array of the roles that have users.
 */
function memberscontrol_get_active_roles() {

	$has_users = array();

	foreach ( memberscontrol_get_role_user_count() as $role => $count ) {

		if ( 0 < $count )
			$has_users[] = $role;
	}

	return $has_users;
}

/**
 * Returns an array of the roles that have no users.
 */
function memberscontrol_get_inactive_roles() {

	return array_diff( array_keys( memberscontrol_get_roles() ), memberscontrol_get_active_roles() );
}

/**
 * Returns a count of all the available roles for the site.
 */
function memberscontrol_get_role_count() {

	return count( $GLOBALS['wp_roles']->role_names );
}

/* ====== Single Role Functions ====== */

/**
 * Sanitizes a role name.  This is a wrapper for the `sanitize_key()` WordPress function.  Only
 * alphanumeric characters and underscores are allowed.  Hyphens are also replaced with underscores.
 */
function memberscontrol_sanitize_role( $role ) {

	$_role = strtolower( $role );
	$_role = preg_replace( '/[^a-z0-9_\-\s]/', '', $_role );

	return apply_filters( 'memberscontrol_sanitize_role', str_replace( ' ', '_', $_role ), $role );
}

/**
 * WordPress provides no method of translating custom roles other than filtering the
 * `translate_with_gettext_context` hook, which is very inefficient and is not the proper
 * method of translating.  This is a method that allows plugin authors to hook in and add
 * their own translations.
 *
 * Note the core WP `translate_user_role()` function only translates core user roles.
 */
function memberscontrol_translate_role( $role ) {
	global $wp_roles;

	return memberscontrol_translate_role_hook( $wp_roles->role_names[ $role ], $role );
}

/**
 * Hook for translating user roles. I needed to separate this from the primary
 * `members_translate_role()` function in case `$wp_roles` was not yet available
 * but both the role and role label were.
 */
function memberscontrol_translate_role_hook( $label, $role ) {

	return apply_filters( 'memberscontrol_translate_role', translate_user_role( $label ), $role );
}

/**
 * Conditional tag to check if a role has any users.
 */
function memberscontrol_role_has_users( $role ) {

	return in_array( $role, memberscontrol_get_active_roles() );
}

/**
 * Conditional tag to check if a role has any capabilities.
 */
function memberscontrol_role_has_caps( $role ) {

	return memberscontrol_get_role( $role )->has_caps;
}

/**
 * Counts the number of users for all roles on the site and returns this as an array.  If
 * the `$role` parameter is given, the return value will be the count just for that particular role.
 */
function memberscontrol_get_role_user_count( $role = '' ) {

	// If the count is not already set for all roles, let's get it.
	if ( empty( memberscontrol_plugin()->role_user_count ) ) {

		// Count users.
		$user_count = count_users();

		// Loop through the user count by role to get a count of the users with each role.
		foreach ( $user_count['avail_roles'] as $_role => $count )
			memberscontrol_plugin()->role_user_count[ $_role ] = $count;
	}

	// Return the role count.
	if ( $role )
		return isset( memberscontrol_plugin()->role_user_count[ $role ] ) ? memberscontrol_plugin()->role_user_count[ $role ] : 0;

	// If the `$role` parameter wasn't passed into this function, return the array of user counts.
	return memberscontrol_plugin()->role_user_count;
}

/**
 * Returns the number of granted capabilities that a role has.
 */
function memberscontrol_get_role_granted_cap_count( $role ) {

	return memberscontrol_get_role( $role )->granted_cap_count;
}

/**
 * Returns the number of denied capabilities that a role has.
 */
function memberscontrol_get_role_denied_cap_count( $role ) {

	return memberscontrol_get_role( $role )->denied_cap_count;
}

/**
 * Conditional tag to check whether a role can be edited.
 */
function memberscontrol_is_role_editable( $role ) {

	return in_array( $role, memberscontrol_get_editable_roles() );
}

/**
 * Conditional tag to check whether a role is a core WordPress role.
 */
function memberscontrol_is_wordpress_role( $role ) {

	return in_array( $role, array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' ) );
}

/* ====== URLs ====== */

/**
 * Returns the URL for the add-new role admin screen.
 */
function memberscontrol_get_new_role_url() {

	return add_query_arg( 'page', 'memberscontrol', admin_url( 'admin.php' ) );
}

/**
 * Returns the URL for the clone role admin screen.
 */
function memberscontrol_get_clone_role_url( $role ) {

	return add_query_arg( 'clone', $role, memberscontrol_get_new_role_url() );
}

/**
 * Returns the URL for the edit roles admin screen.
 */
function memberscontrol_get_edit_roles_url() {

	return add_query_arg( 'page', 'roles', admin_url( 'admin.php?page=roles' ) );
}

/**
 * Returns the URL for the edit "mine" roles admin screen.
 */
function memberscontrol_get_role_view_url( $view ) {

	return add_query_arg( 'view', $view, memberscontrol_get_edit_roles_url() );
}

/**
 * Returns the URL for the edit role admin screen.
 */
function memberscontrol_get_edit_role_url( $role ) {

	return add_query_arg( array( 'action' => 'edit', 'role' => $role ), memberscontrol_get_edit_roles_url() );
}

/**
 * Returns the URL to permanently delete a role (edit roles screen).
 */
function memberscontrol_get_delete_role_url( $role ) {

	$url = add_query_arg( array( 'action' => 'delete', 'role' => $role ), memberscontrol_get_edit_roles_url() );

	return wp_nonce_url( $url, 'delete_role', 'memberscontrol_delete_role_nonce' );
}

/**
 * Returns the URL for the users admin screen specific to a role.
 */
function memberscontrol_get_role_users_url( $role ) {

	return admin_url( add_query_arg( 'role', $role, 'users.php' ) );
}
