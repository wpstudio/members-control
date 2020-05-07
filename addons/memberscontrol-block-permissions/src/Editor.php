<?php
/**
 * Editor Class.
 */

namespace MembersControl\BlockPermissions;

/**
 * Editor component class.
 */
class Editor {

	public function boot() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue'] );
	}

	public function enqueue() {

		wp_enqueue_script(
			'memberscontrol-block-permissions-editor',
			plugin()->asset( 'js/editor.js' ),
			[
				'lodash',
				'wp-block-editor',
				'wp-compose',
				'wp-components',
				'wp-element',
				'wp-hooks'
			],
			null,
			true
		);

		wp_localize_script(
			'memberscontrol-block-permissions-editor',
			'memberscontrolBlockPermissions',
			$this->jsonData()
		);

		wp_enqueue_style(
			'memberscontrol-block-permissions-editor',
			plugin()->asset( 'css/editor.css' ),
		 	[],
			null
		);
	}

	private function jsonData() {

		$labels = [
			'controls' => [],
			'notices'  => []
		];

		$labels['panel'] =  __( 'Permissions', 'memberscontrol' );

		$labels['controls']['cap'] = [
			'label' => __( 'Capability', 'memberscontrol' )
		];

		$labels['controls']['condition'] = [
			'label' => __( 'Condition', 'memberscontrol' ),
			'options' => [
				'default' => __( 'Show block to everyone',   'memberscontrol' ),
				'show'    => __( 'Show block to selected',   'memberscontrol' ),
				'hide'    => __( 'Hide block from selected', 'memberscontrol' )
			]
		];

		$labels['controls']['message'] = [
			'label' => __( 'Error Message', 'memberscontrol' ),
			'help'  => __( 'Optionally display an error message for users who cannot see this block.', 'memberscontrol' )
		];

		$labels['controls']['roles'] = [
			'label' => __( 'User Roles', 'memberscontrol' )
		];

		$labels['controls']['type'] = [
			'label' => __( 'Type', 'memberscontrol' ),
			'options' => [
				'userStatus' 		=> __( 'User Status', 'memberscontrol' ),
				'role'       		=> __( 'User Role',   'memberscontrol' ),
				'cap'        		=> __( 'Capability',  'memberscontrol' ),
				'paidMembership'	=> __( 'Paid Membership',  'memberscontrol' ),
				'contentRule'		=> __( 'Content Protection Rule',  'memberscontrol' )
			]
		];

		$labels['controls']['userStatus'] = [
			'label' => __( 'User Status', 'memberscontrol' ),
			'options' => [
				'loggedIn'  => __( 'Logged In',  'memberscontrol' ),
				'loggedOut' => __( 'Logged Out', 'memberscontrol' )
			]
		];

		$labels['notices']['notAllowed'] = __( 'Your user account does not have access to assign permissions to this block.', 'memberscontrol' );

		$data = [
			'roles'                    => [],
			'labels'                   => $labels,
			'userCanAssignPermissions' => current_user_can( 'assign_block_permissions' )
		];

		$_roles = wp_roles()->roles;
		ksort( $_roles );

		foreach ( $_roles as $role => $args ) {
			$data['roles'][] = [
				'name'  => $role,
				'label' => $args['name']
			];
		}

		return $data;
	}
}
