<?php
/**
 * Plugin Activator.
 *
 * Runs the plugin activation routine.
 */

namespace MembersControl\Integration\ACF;

/**
 * Activator class.
class Activator {

	/**
	 * Runs necessary code when first activating the plugin.
	 */
	public static function activate() {

		// Get the administrator role.
		$role = get_role( 'administrator' );

		// If the administrator role exists, add required capabilities
		// for the plugin.
		if ( ! empty( $role ) ) {

			$role->add_cap( 'manage_acf'                     );
			$role->add_cap( 'edit_acf_field_groups'          );
			$role->add_cap( 'edit_others_acf_field_groups'   );
			$role->add_cap( 'delete_acf_field_groups'        );
			$role->add_cap( 'delete_others_acf_field_groups' );
		}
	}
}
