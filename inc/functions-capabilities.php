<?php
/**
 * Functions related to capabilities.
 */

# Register capabilities.
add_action( 'init',                  'memberscontrol_register_caps',         95 );
add_action( 'memberscontrol_register_caps', 'memberscontrol_register_default_caps', 5  );

# Disables the old user levels from capabilities array.
add_filter( 'memberscontrol_get_capabilities', 'memberscontrol_remove_old_levels'  );
add_filter( 'memberscontrol_get_capabilities', 'memberscontrol_remove_hidden_caps' );

/**
 * Fires the action hook for registering capabilities.
 */
function memberscontrol_register_caps() {

	do_action( 'memberscontrol_register_caps' );

	// The following is a quick way to register capabilities that technically
	// exist (i.e., caps that have been added to a role).  These are caps that
	// we don't know about because they haven't been registered.

	$role_caps    = array_values( memberscontrol_get_role_capabilities() );
	$unregistered = array_diff( $role_caps, array_keys( memberscontrol_get_caps() ) );

	foreach ( $unregistered as $cap )
		memberscontrol_register_cap( $cap, array( 'label' => $cap ) );

}

/**
 * Registers all of our default caps.  In particular, the plugin registers its own caps plus core
 * WP's caps.
 */
function memberscontrol_register_default_caps() {

	$caps = array();

	// General caps.
	$caps['edit_dashboard']    = array( 'label' => __( 'Edit Dashboard',    'memberscontrol' ), 'group' => 'general' );
	$caps['edit_files']        = array( 'label' => __( 'Edit Files',        'memberscontrol' ), 'group' => 'general' );
	$caps['export']            = array( 'label' => __( 'Export',            'memberscontrol' ), 'group' => 'general' );
	$caps['import']            = array( 'label' => __( 'Import',            'memberscontrol' ), 'group' => 'general' );
	$caps['manage_links']      = array( 'label' => __( 'Manage Links',      'memberscontrol' ), 'group' => 'general' );
	$caps['manage_options']    = array( 'label' => __( 'Manage Options',    'memberscontrol' ), 'group' => 'general' );
	$caps['moderate_comments'] = array( 'label' => __( 'Moderate Comments', 'memberscontrol' ), 'group' => 'general' );
	$caps['read']              = array( 'label' => __( 'Read',              'memberscontrol' ), 'group' => 'general' );
	$caps['unfiltered_html']   = array( 'label' => __( 'Unfiltered HTML',   'memberscontrol' ), 'group' => 'general' );
	$caps['update_core']       = array( 'label' => __( 'Update Core',       'memberscontrol' ), 'group' => 'general' );

	// Post caps.
	$caps['delete_others_posts']    = array( 'label' => __( "Delete Others' Posts",   'memberscontrol' ), 'group' => 'type-post' );
	$caps['delete_posts']           = array( 'label' => __( 'Delete Posts',           'memberscontrol' ), 'group' => 'type-post' );
	$caps['delete_private_posts']   = array( 'label' => __( 'Delete Private Posts',   'memberscontrol' ), 'group' => 'type-post' );
	$caps['delete_published_posts'] = array( 'label' => __( 'Delete Published Posts', 'memberscontrol' ), 'group' => 'type-post' );
	$caps['edit_others_posts']      = array( 'label' => __( "Edit Others' Posts",     'memberscontrol' ), 'group' => 'type-post' );
	$caps['edit_posts']             = array( 'label' => __( 'Edit Posts',             'memberscontrol' ), 'group' => 'type-post' );
	$caps['edit_private_posts']     = array( 'label' => __( 'Edit Private Posts',     'memberscontrol' ), 'group' => 'type-post' );
	$caps['edit_published_posts']   = array( 'label' => __( 'Edit Published Posts',   'memberscontrol' ), 'group' => 'type-post' );
	$caps['publish_posts']          = array( 'label' => __( 'Publish Posts',          'memberscontrol' ), 'group' => 'type-post' );
	$caps['read_private_posts']     = array( 'label' => __( 'Read Private Posts',     'memberscontrol' ), 'group' => 'type-post' );

	// Page caps.
	$caps['delete_others_pages']    = array( 'label' => __( "Delete Others' Pages",   'memberscontrol' ), 'group' => 'type-page' );
	$caps['delete_pages']           = array( 'label' => __( 'Delete Pages',           'memberscontrol' ), 'group' => 'type-page' );
	$caps['delete_private_pages']   = array( 'label' => __( 'Delete Private Pages',   'memberscontrol' ), 'group' => 'type-page' );
	$caps['delete_published_pages'] = array( 'label' => __( 'Delete Published Pages', 'memberscontrol' ), 'group' => 'type-page' );
	$caps['edit_others_pages']      = array( 'label' => __( "Edit Others' Pages",     'memberscontrol' ), 'group' => 'type-page' );
	$caps['edit_pages']             = array( 'label' => __( 'Edit Pages',             'memberscontrol' ), 'group' => 'type-page' );
	$caps['edit_private_pages']     = array( 'label' => __( 'Edit Private Pages',     'memberscontrol' ), 'group' => 'type-page' );
	$caps['edit_published_pages']   = array( 'label' => __( 'Edit Published Pages',   'memberscontrol' ), 'group' => 'type-page' );
	$caps['publish_pages']          = array( 'label' => __( 'Publish Pages',          'memberscontrol' ), 'group' => 'type-page' );
	$caps['read_private_pages']     = array( 'label' => __( 'Read Private Pages',     'memberscontrol' ), 'group' => 'type-page' );

	// Attachment caps.
	$caps['upload_files'] = array( 'label' => __( 'Upload Files', 'memberscontrol' ), 'group' => 'type-attachment' );

	// Taxonomy caps.
	$caps['manage_categories'] = array( 'label' => __( 'Manage Categories', 'memberscontrol' ), 'group' => 'taxonomy' );

	// Theme caps.
	$caps['delete_themes']      = array( 'label' => __( 'Delete Themes',      'memberscontrol' ), 'group' => 'theme' );
	$caps['edit_theme_options'] = array( 'label' => __( 'Edit Theme Options', 'memberscontrol' ), 'group' => 'theme' );
	$caps['edit_themes']        = array( 'label' => __( 'Edit Themes',        'memberscontrol' ), 'group' => 'theme' );
	$caps['install_themes']     = array( 'label' => __( 'Install Themes',     'memberscontrol' ), 'group' => 'theme' );
	$caps['switch_themes']      = array( 'label' => __( 'Switch Themes',      'memberscontrol' ), 'group' => 'theme' );
	$caps['update_themes']      = array( 'label' => __( 'Update Themes',      'memberscontrol' ), 'group' => 'theme' );

	// Plugin caps.
	$caps['activate_plugins'] = array( 'label' => __( 'Activate Plugins', 'memberscontrol' ), 'group' => 'plugin' );
	$caps['delete_plugins']   = array( 'label' => __( 'Delete Plugins',   'memberscontrol' ), 'group' => 'plugin' );
	$caps['edit_plugins']     = array( 'label' => __( 'Edit Plugins',     'memberscontrol' ), 'group' => 'plugin' );
	$caps['install_plugins']  = array( 'label' => __( 'Install Plugins',  'memberscontrol' ), 'group' => 'plugin' );
	$caps['update_plugins']   = array( 'label' => __( 'Update Plugins',   'memberscontrol' ), 'group' => 'plugin' );

	// User caps.
	$caps['create_roles']  = array( 'label' => __( 'Create Roles',  'memberscontrol' ), 'group' => 'user' );
	$caps['create_users']  = array( 'label' => __( 'Create Users',  'memberscontrol' ), 'group' => 'user' );
	$caps['delete_roles']  = array( 'label' => __( 'Delete Roles',  'memberscontrol' ), 'group' => 'user' );
	$caps['delete_users']  = array( 'label' => __( 'Delete Users',  'memberscontrol' ), 'group' => 'user' );
	$caps['edit_roles']    = array( 'label' => __( 'Edit Roles',    'memberscontrol' ), 'group' => 'user' );
	$caps['edit_users']    = array( 'label' => __( 'Edit Users',    'memberscontrol' ), 'group' => 'user' );
	$caps['list_roles']    = array( 'label' => __( 'List Roles',    'memberscontrol' ), 'group' => 'user' );
	$caps['list_users']    = array( 'label' => __( 'List Users',    'memberscontrol' ), 'group' => 'user' );
	$caps['promote_users'] = array( 'label' => __( 'Promote Users', 'memberscontrol' ), 'group' => 'user' );
	$caps['remove_users']  = array( 'label' => __( 'Remove Users',  'memberscontrol' ), 'group' => 'user' );

	// Custom caps.
	$caps['restrict_content'] = array( 'label' => __( 'Restrict Content', 'memberscontrol' ), 'group' => 'custom' );

	// Register each of the capabilities.
	foreach ( $caps as $name => $args )
		memberscontrol_register_cap( $name, $args );

	// === Category and Tag caps. ===
	// These are mapped to `manage_categories` in a default WP install.  However, it's possible
	// for another plugin to map these differently and handle them correctly.  So, we're only
	// going to register the caps if they've been assigned to a role.  There's no other way
	// to reliably detect if they've been mapped.

	$role_caps = array_values( memberscontrol_get_role_capabilities() );
	$tax_caps  = array();

	$tax_caps['assign_categories'] = array( 'label' => __( 'Assign Categories', 'memberscontrol' ), 'group' => 'taxonomy' );
	$tax_caps['edit_categories']   = array( 'label' => __( 'Edit Categories',   'memberscontrol' ), 'group' => 'taxonomy' );
	$tax_caps['delete_categories'] = array( 'label' => __( 'Delete Categories', 'memberscontrol' ), 'group' => 'taxonomy' );
	$tax_caps['assign_post_tags']  = array( 'label' => __( 'Assign Post Tags',  'memberscontrol' ), 'group' => 'taxonomy' );
	$tax_caps['edit_post_tags']    = array( 'label' => __( 'Edit Post Tags',    'memberscontrol' ), 'group' => 'taxonomy' );
	$tax_caps['delete_post_tags']  = array( 'label' => __( 'Delete Post Tags',  'memberscontrol' ), 'group' => 'taxonomy' );
	$tax_caps['manage_post_tags']  = array( 'label' => __( 'Manage Post Tags',  'memberscontrol' ), 'group' => 'taxonomy' );

	foreach ( $tax_caps as $tax_cap => $args ) {

		if ( in_array( $tax_cap, $role_caps ) )
			memberscontrol_register_cap( $tax_cap, $args );
	}
}

/**
 * Returns the instance of the capability registry.
 */
function memberscontrol_capability_registry() {

	return \MembersControl\Registry::get_instance( 'cap' );
}

/**
 * Returns all registered caps.
 */
function memberscontrol_get_caps() {

	return memberscontrol_capability_registry()->get_collection();
}

/**
 * Registers a capability.
 */
function memberscontrol_register_cap( $name, $args = array() ) {

	memberscontrol_capability_registry()->register( $name, new \MembersControl\Capability( $name, $args ) );
}

/**
 * Unregisters a capability.
 */
function memberscontrol_unregister_cap( $name ) {

	memberscontrol_capability_registry()->unregister( $name );
}

/**
 * Returns a capability object.
 */
function memberscontrol_get_cap( $name ) {

	return memberscontrol_capability_registry()->get( $name );
}

/**
 * Checks if a capability object exists.
 */
function memberscontrol_cap_exists( $name ) {

	return memberscontrol_capability_registry()->exists( $name );
}

/**
 * Function for sanitizing a capability.
 */
function memberscontrol_sanitize_cap( $cap ) {

	return apply_filters( 'memberscontrol_sanitize_cap', sanitize_key( $cap ) );
}

/**
 * Checks if a capability is editable.  A capability is editable if it's not one of the core WP
 * capabilities and doesn't belong to an uneditable role.
 */
function memberscontrol_is_cap_editable( $cap ) {

	$uneditable = array_keys( memberscontrol_get_uneditable_roles() );

	return ! in_array( $cap, memberscontrol_get_wp_capabilities() ) && ! array_intersect( $uneditable, memberscontrol_get_cap_roles( $cap ) );
}

/**
 * Returns an array of roles that have a capability.
 *
 */
function memberscontrol_get_cap_roles( $cap ) {
	global $wp_roles;

	$_roles = array();

	foreach ( $wp_roles->role_objects as $role ) {

		if ( $role->has_cap( $cap ) )
			$_roles[] = $role->name;
	}

	return $_roles;
}

/**
 * The function that makes this plugin what it is.  It returns all of our capabilities in a
 * nicely-formatted, alphabetized array with no duplicate capabilities.  It pulls from three
 * different functions to make sure we get all of the capabilities that we need for use in the
 * plugin components.
 */
function memberscontrol_get_capabilities() {

	// Apply filters to the array of capabilities.
	$capabilities = apply_filters( 'memberscontrol_get_capabilities', array_keys( memberscontrol_get_caps() ) );

	// Sort the capabilities alphabetically.
	sort( $capabilities );

	// Discard duplicates and return.
	return array_unique( $capabilities );
}

/**
 * Gets an array of capabilities according to each user role.  Each role will return its caps,
 * which are then added to the overall `$capabilities` array.
 *
 * Note that if no role has the capability, it technically no longer exists.  Since this could be
 * a problem with folks accidentally deleting the default WordPress capabilities, the
 * `memberscontrol_get_plugin_capabilities()` will return all the defaults.
 */
function memberscontrol_get_role_capabilities() {
	global $wp_roles;

	// Set up an empty capabilities array.
	$capabilities = array();

	// Loop through each role object because we need to get the caps.
	foreach ( $wp_roles->role_objects as $key => $role ) {

		// Make sure that the role has caps.
		if ( is_array( $role->capabilities ) ) {

			// Add each of the role's caps (both granted and denied) to the array.
			foreach ( $role->capabilities as $cap => $grant )
				$capabilities[ $cap ] = $cap;
		}
	}

	// Return the capabilities array, making sure there are no duplicates.
	return array_unique( $capabilities );
}

/**
 * Checks if a specific capability has been given to at least one role. If it has, return true.
 * Else, return false.
 */
function memberscontrol_check_for_cap( $cap = '' ) {

	// Without a capability, we have nothing to check for.  Just return false.
	if ( ! $cap )
		return false;

	// Check if the cap is assigned to any role.
	return in_array( $cap, memberscontrol_get_role_capabilities() );
}

/**
 * Return an array of capabilities that are not allowed on this installation.
 */
function memberscontrol_get_hidden_caps() {

	$caps = array();

	// This is always a hidden cap and should never be added to the caps list.
	$caps[] = 'do_not_allow';

	// Network-level caps.
	// These shouldn't show on single-site installs anyway.
	// On multisite installs, they should be handled by a network-specific role manager.
	$caps[] = 'create_sites';
	$caps[] = 'delete_sites';
	$caps[] = 'manage_network';
	$caps[] = 'manage_sites';
	$caps[] = 'manage_network_users';
	$caps[] = 'manage_network_plugins';
	$caps[] = 'manage_network_themes';
	$caps[] = 'manage_network_options';
	$caps[] = 'upgrade_network';

	// This cap is needed on single site to set up a multisite network.
	if ( is_multisite() )
		$caps[] = 'setup_network';

	// Unfiltered uploads.
	if ( is_multisite() || ! defined( 'ALLOW_UNFILTERED_UPLOADS' ) || ! ALLOW_UNFILTERED_UPLOADS )
		$caps[] = 'unfiltered_upload';

	// Unfiltered HTML.
	if ( is_multisite() || ( defined( 'DISALLOW_UNFILTERED_HTML' ) && DISALLOW_UNFILTERED_HTML ) )
		$caps[] = 'unfiltered_html';

	// File editing.
	if ( is_multisite() || ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) ) {
		$caps[] = 'edit_files';
		$caps[] = 'edit_plugins';
		$caps[] = 'edit_themes';
	}

	// File mods.
	if ( is_multisite() || ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) ) {
		$caps[] = 'edit_files';
		$caps[] = 'edit_plugins';
		$caps[] = 'edit_themes';
		$caps[] = 'update_plugins';
		$caps[] = 'delete_plugins';
		$caps[] = 'install_plugins';
		$caps[] = 'upload_plugins';
		$caps[] = 'update_themes';
		$caps[] = 'delete_themes';
		$caps[] = 'install_themes';
		$caps[] = 'upload_themes';
		$caps[] = 'update_core';
	}

	return array_unique( $caps );
}

/**
 * Get rid of hidden capabilities.
 */
function memberscontrol_remove_hidden_caps( $caps ) {

	return apply_filters( 'memberscontrol_remove_hidden_caps', true ) ? array_diff( $caps, memberscontrol_get_hidden_caps() ) : $caps;
}

/**
 * Old WordPress levels system.  This is mostly useful for filtering out the levels when shown
 * in admin screen.  Plugins shouldn't rely on these levels to create permissions for users.
 * They should move to the newer system of checking for a specific capability instead.
 */
function memberscontrol_get_old_levels() {

	return array(
		'level_0',
		'level_1',
		'level_2',
		'level_3',
		'level_4',
		'level_5',
		'level_6',
		'level_7',
		'level_8',
		'level_9',
		'level_10'
	);
}

/**
 * Get rid of levels since these are mostly useless in newer versions of WordPress.  Devs should
 * add the `__return_false` filter to the `memberscontrol_remove_old_levels` hook to utilize user levels.
 */
function memberscontrol_remove_old_levels( $caps ) {

	return apply_filters( 'memberscontrol_remove_old_levels', true ) ? array_diff( $caps, memberscontrol_get_old_levels() ) : $caps;
}

/**
 * Returns an array of capabilities that should be set on the New Role admin screen.  By default,
 * the only capability checked is 'read' because it's needed for users of the role to view their
 * profile in the admin.
 */
function memberscontrol_new_role_default_capabilities() {

	return apply_filters( 'memberscontrol_new_role_default_capabilities', array( 'read' ) );
}

/**
 * Returns an array of capabilities that should be set on the New Role admin screen.  By default,
 * the only capability checked is 'read' because it's needed for users of the role to view their
 * profile in the admin.
 */
function memberscontrol_new_role_default_caps() {

	return apply_filters( 'memberscontrol_new_role_default_caps', array( 'read' => true ) );
}
