<?php
/**
 * Integration Class.
 */

namespace MembersControl\BlockPermissions;

use function memberscontrol_register_cap;

/**
 * Integration component class.
 */
class Integration {

	public function boot() {
		add_action( 'memberscontrol_register_caps', [ $this, 'registerCaps' ] );
	}

	public function registerCaps() {

		if ( function_exists( 'memberscontrol_register_cap' ) ) {

			memberscontrol_register_cap( 'assign_block_permissions', [
				'label'       => __( 'Assign Block Permissions', 'memberscontrol' ),
				'description' => __( 'Allows users to assign block permissions inside of the block editor.', 'memberscontrol' ),
				'group'       => 'type-wp_block'
			] );
		}
	}
}
