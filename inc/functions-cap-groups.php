<?php
/**
 * Capability groups API. Offers a standardized method for creating capability groups.
 */

# Registers default groups.
add_action( 'init',                        'memberscontrol_register_cap_groups',         95 );
add_action( 'memberscontrol_register_cap_groups', 'memberscontrol_register_default_cap_groups',  5 );

/**
 * Fires the cap group registration action hook.
 */
function memberscontrol_register_cap_groups() {

	// Hook for registering cap groups. Plugins should always register on this hook.
	do_action( 'memberscontrol_register_cap_groups' );
}

/**
 * Registers the default cap groups.
 */
function memberscontrol_register_default_cap_groups() {

	// Registers the general group.
	memberscontrol_register_cap_group( 'general',
		array(
			'label'    => esc_html__( 'General', 'memberscontrol' ),
			'icon'     => 'dashicons-wordpress',
			'priority' => 5
		)
	);

	// Loop through every custom post type.
	foreach ( get_post_types( array(), 'objects' ) as $type ) {

		// Skip revisions and nave menu items.
		if ( in_array( $type->name, array( 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset' ) ) )
			continue;

		// Get the caps for the post type.
		$has_caps = memberscontrol_get_post_type_group_caps( $type->name );

		// Skip if the post type doesn't have caps.
		if ( empty( $has_caps ) )
			continue;

		// Set the default post type icon.
		$icon = $type->hierarchical ? 'dashicons-admin-page' : 'dashicons-admin-post';

		// Get the post type icon.
		if ( is_string( $type->menu_icon ) && preg_match( '/dashicons-/i', $type->menu_icon ) )
			$icon = $type->menu_icon;

		else if ( 'attachment' === $type->name )
			$icon = 'dashicons-admin-media';

		else if ( 'download' === $type->name )
			$icon = 'dashicons-download'; // EDD

		else if ( 'product' === $type->name )
			$icon = 'dashicons-cart';

		// Register the post type cap group.
		memberscontrol_register_cap_group( "type-{$type->name}",
			array(
				'label'    => $type->labels->name,
				'caps'     => $has_caps,
				'icon'     => $icon,
				'priority' => 10
			)
		);
	}

	// Register the taxonomy group.
	memberscontrol_register_cap_group( 'taxonomy',
		array(
			'label'      => esc_html__( 'Taxonomies', 'memberscontrol' ),
			'caps'       => memberscontrol_get_taxonomy_group_caps(),
			'icon'       => 'dashicons-tag',
			'diff_added' => true,
			'priority'   => 15
		)
	);

	// Register the theme group.
	memberscontrol_register_cap_group( 'theme',
		array(
			'label'    => esc_html__( 'Appearance', 'memberscontrol' ),
			'icon'     => 'dashicons-admin-appearance',
			'priority' => 20
		)
	);

	// Register the plugin group.
	memberscontrol_register_cap_group( 'plugin',
		array(
			'label'    => esc_html__( 'Plugins', 'memberscontrol' ),
			'icon'     => 'dashicons-admin-plugins',
			'priority' => 25
		)
	);

	// Register the user group.
	memberscontrol_register_cap_group( 'user',
		array(
			'label'    => esc_html__( 'Users', 'memberscontrol' ),
			'icon'     => 'dashicons-admin-users',
			'priority' => 30
		)
	);

	// Register the custom group.
	memberscontrol_register_cap_group( 'custom',
		array(
			'label'      => esc_html__( 'Custom', 'memberscontrol' ),
			'caps'       => memberscontrol_get_capabilities(),
			'icon'       => 'dashicons-admin-generic',
			'diff_added' => true,
			'priority'   => 995
		)
	);
}

/**
 * Returns the instance of cap group registry.
 */
function memberscontrol_cap_group_registry() {

	return \MembersControl\Registry::get_instance( 'cap_group' );
}

/**
 * Function for registering a cap group.
 */
function memberscontrol_register_cap_group( $name, $args = array() ) {

	memberscontrol_cap_group_registry()->register( $name, new \MembersControl\Cap_Group( $name, $args ) );
}

/**
 * Unregisters a group.
 */
function memberscontrol_unregister_cap_group( $name ) {

	memberscontrol_cap_group_registry()->unregister( $name );
}

/**
 * Checks if a group exists.
 */
function memberscontrol_cap_group_exists( $name ) {

	return memberscontrol_cap_group_registry()->exists( $name );
}

/**
 * Returns an array of registered group objects.
 */
function memberscontrol_get_cap_groups() {

	return memberscontrol_cap_group_registry()->get_collection();
}

/**
 * Returns a group object if it exists.  Otherwise, `FALSE`.
 */
function memberscontrol_get_cap_group( $name ) {

	return memberscontrol_cap_group_registry()->get( $name );
}

/**
 * Returns the caps for a specific post type capability group.
 */
function memberscontrol_get_post_type_group_caps( $post_type = 'post' ) {

	// Get the post type caps.
	$caps = (array) get_post_type_object( $post_type )->cap;

	// remove meta caps.
	unset( $caps['edit_post']   );
	unset( $caps['read_post']   );
	unset( $caps['delete_post'] );

	// Get the cap names only.
	$caps = array_values( $caps );

	// If this is not a core post/page post type.
	if ( ! in_array( $post_type, array( 'post', 'page' ) ) ) {

		// Get the post and page caps.
		$post_caps = array_values( (array) get_post_type_object( 'post' )->cap );
		$page_caps = array_values( (array) get_post_type_object( 'page' )->cap );

		// Remove post/page caps from the current post type caps.
		$caps = array_diff( $caps, $post_caps, $page_caps );
	}

	// If attachment post type, add the `unfiltered_upload` cap.
	if ( 'attachment' === $post_type )
		$caps[] = 'unfiltered_upload';

	$registered_caps = array_keys( wp_list_filter( memberscontrol_get_caps(), array( 'group' => "type-{$post_type}" ) ) );

	if ( $registered_caps )
		array_merge( $caps, $registered_caps );

	// Make sure there are no duplicates and return.
	return array_unique( $caps );
}

/**
 * Returns the caps for the taxonomy capability group.
 */
function memberscontrol_get_taxonomy_group_caps() {

	$do_not_add = array(
		'assign_categories',
		'edit_categories',
		'delete_categories',
		'assign_post_tags',
		'edit_post_tags',
		'delete_post_tags',
		'manage_post_tags'
	);

	$taxi = get_taxonomies( array(), 'objects' );

	$caps = array();

	foreach ( $taxi as $tax )
		$caps = array_merge( $caps, array_values( (array) $tax->cap ) );

	$registered_caps = array_keys( wp_list_filter( memberscontrol_get_caps(), array( 'group' => 'taxonomy' ) ) );

	if ( $registered_caps )
		array_merge( $caps, $registered_caps );

	return array_diff( array_unique( $caps ), $do_not_add );
}

/**
 * Returns the caps for the custom capability group.
 */
function memberscontrol_get_custom_group_caps() {

	$caps = memberscontrol_get_capabilities();

	$registered_caps = array_keys( wp_list_filter( memberscontrol_get_caps(), array( 'group' => 'custom' ) ) );

	if ( $registered_caps )
		array_merge( $caps, $registered_caps );

	return array_unique( $caps );
}
