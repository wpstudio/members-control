<?php
/**
 * Capability Functions.
 */

namespace MembersControl\Integration\ACF;

# Don't execute code if file is accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Returns an array of the ACF plugin capabilities.
 */
function acf_caps() {

	return [
		'manage_acf'   => [
			'label'       => __( 'Manage Advanced Custom Fields', 'memberscontrol' ),
			'description' => __( 'Allows access to settings and tools for the Advanced Custom Fields plugin and may be required to access some third-party add-ons.', 'memberscontrol' )
		],

		'edit_acf_field_groups' => [
			'label'       => __( 'Edit Field Groups',   'memberscontrol' ),
			'description' => sprintf(
				// Translators: %s is a capability name.
				__( "Allows users to edit field groups. May need to be combined with other %s capabilities, depending on the scenario.", 'memberscontrol' ),
				'<code>edit_*_acf_field_groups</code>'
			)
		],

		'edit_others_acf_field_groups'   => [
			'label'       => __( "Edit Others' Field Groups", 'memberscontrol' ),
			'description' => __( "Allows users to edit others user's field groups.", 'memberscontrol' )
		],

		'delete_acf_field_groups'           => [
			'label'       => __( 'Delete Field Groups',   'memberscontrol' ),
			'description' => sprintf(
				// Translators: %s is a capability name.
				__( "Allows users to delete field groups. May need to be combined with other %s capabilities, depending on the scenario.", 'memberscontrol' ),
				'<code>delete_*_acf_field_groups</code>'
			)
		],

		'delete_others_acf_field_groups' => [
			'label'       => __( "Delete Others' Field Groups", 'memberscontrol' ),
			'description' => __( "Allows users to delete other user's field groups.", 'memberscontrol' )
		]
	];
}
