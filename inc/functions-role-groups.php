<?php
/**
 * Role groups API. Offers a standardized method for creating role groups.
 */

# Registers default groups.
add_action( 'init',                         'memberscontrol_register_role_groups',         95 );
add_action( 'memberscontrol_register_role_groups', 'memberscontrol_register_default_role_groups',  5 );

/**
 * Fires the role group registration action hook.
 */
function memberscontrol_register_role_groups() {

	do_action( 'memberscontrol_register_role_groups' );
}


/**
 * Registers the default role groups.
 */
function memberscontrol_register_default_role_groups() {

	// Register the WordPress group.
	memberscontrol_register_role_group( 'wordpress',
		array(
			'label'       => esc_html__( 'WordPress', 'memberscontrol' ),
			'label_count' => _n_noop( 'WordPress %s', 'WordPress %s', 'memberscontrol' ),
			'roles'       => memberscontrol_get_wordpress_roles(),
		)
	);
}

/**
 * Returns the instance of the role group registry.
 */
function memberscontrol_role_group_registry() {

	return \MembersControl\Registry::get_instance( 'role_group' );
}

/**
 * Function for registering a role group.
 */
function memberscontrol_register_role_group( $name, $args = array() ) {

	memberscontrol_role_group_registry()->register( $name, new \MembersControl\Role_Group( $name, $args ) );
}

/**
 * Unregisters a group.
 */
function memberscontrol_unregister_role_group( $name ) {

	memberscontrol_role_group_registry()->unregister( $name );
}

/**
 * Checks if a group exists.
 */
function memberscontrol_role_group_exists( $name ) {

	return memberscontrol_role_group_registry()->exists( $name );
}

/**
 * Returns an array of registered group objects.
 */
function memberscontrol_get_role_groups() {

	return memberscontrol_role_group_registry()->get_collection();
}

/**
 * Returns a group object if it exists.  Otherwise, `FALSE`.
 */
function memberscontrol_get_role_group( $name ) {

	return memberscontrol_role_group_registry()->get( $name );
}
