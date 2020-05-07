<?php
/**
 * Plugin Activator.
 */

namespace MembersControl\BlockPermissions;

/**
 * Activator class.
 */
class Activator {

	public static function activate() {

		// Get the administrator role.
		$role = get_role( 'administrator' );

		// If the administrator role exists, add required capabilities
		// for the plugin.
		if ( ! empty( $role ) ) {
			$role->add_cap( 'assign_block_permissions' );
		}
	}
}
