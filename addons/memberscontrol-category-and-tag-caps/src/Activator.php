<?php
/**
 * Plugin Activator.
 */

namespace MembersControl\CategoryAndTagCaps;

/**
 * Activator class.
 */
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

			$role->add_cap( 'manage_categories' );
			$role->add_cap( 'assign_categories' );
			$role->add_cap( 'edit_categories'   );
			$role->add_cap( 'delete_categories' );

			$role->add_cap( 'manage_post_tags' );
			$role->add_cap( 'assign_post_tags' );
			$role->add_cap( 'edit_post_tags'   );
			$role->add_cap( 'delete_post_tags' );
		}
	}
}
