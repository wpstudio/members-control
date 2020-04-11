<?php
/**
 * Role Functions.
 */
namespace MembersControl\Integration\ACF;

use function memberscontrol_get_roles;
use function memberscontrol_role_exists;

# Don't execute code if file is accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Returns an array of the ACF plugin roles.
 */
function acf_roles() {

	$roles = [];

	// Add any roles that have any of the ACF capabilities to the group.
	$role_objects = memberscontrol_get_roles();

	$acf_caps = array_keys( acf_caps() );

	foreach ( $role_objects as $role ) {

		if ( 0 < count( array_intersect( $acf_caps, (array) $role->get( 'granted_caps' ) ) ) ) {
			$roles[] = $role->get( 'name' );
		}
	}

	return $roles;
}
