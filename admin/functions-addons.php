<?php
/**
 * Functions for handling add-on plugin registration and integration for the Add-Ons
 * view on the settings screen.
 */

# Register addons.
add_action( 'memberscontrol_register_addons', 'memberscontrol_register_default_addons', 5 );

/**
 * Registers any addons stored globally with WordPress.
 */
function memberscontrol_register_default_addons() {

	$data = include memberscontrol_plugin()->dir . 'admin/config/addons.php';

	// If we have an array of data, let's roll.
	if ( ! empty( $data ) && is_array( $data ) ) {

		foreach ( $data as $addon => $options ) {
			memberscontrol_register_addon( $addon, $options );
		}
	}
}

/**
 * Returns the instance of the addon registry.
 */
function memberscontrol_addon_registry() {

	return \MembersControl\Registry::get_instance( 'addon' );
}

/**
 * Returns all registered addons.
 */
function memberscontrol_get_addons() {

	return memberscontrol_addon_registry()->get_collection();
}

/**
 * Registers a addon.
 */
function memberscontrol_register_addon( $name, $args = array() ) {

	memberscontrol_addon_registry()->register( $name, new \MembersControl\Addon( $name, $args ) );
}

/**
 * Unregisters a addon.
 */
function memberscontrol_unregister_addon( $name ) {

	memberscontrol_addon_registry()->unregister( $name );
}

/**
 * Returns a addon object.
 */
function memberscontrol_get_addon( $name ) {

	return memberscontrol_addon_registry()->get( $name );
}

/**
 * Checks if a addon object exists.
 */
function memberscontrol_addon_exists( $name ) {

	return memberscontrol_addon_registry()->exists( $name );
}
