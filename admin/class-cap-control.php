<?php
/**
 * Capability control class for use in the edit capabilities tabs.
 */

namespace MembersControl\Admin;

/**
 * Cap control class.
 */
final class Cap_Control {

	public $manager;

	public $cap = '';

	public $section = '';

	public $json = array();

	public function __construct( $manager, $cap, $args = array() ) {

		foreach ( array_keys( get_object_vars( $this ) ) as $key ) {

			if ( isset( $args[ $key ] ) )
				$this->$key = $args[ $key ];
		}

		$this->manager = $manager;
		$this->cap     = $cap;
	}

	public function json() {
		$this->to_json();
		return $this->json;
	}

	public function to_json() {

		// Is the role editable?
		$is_editable = $this->manager->role ? memberscontrol_is_role_editable( $this->manager->role->name ) : true;

		// Get the current capability.
		$this->json['cap'] = $this->cap;

		// Add the section ID.
		$this->json['section'] = $this->section;

		// If the cap is not editable, the inputs should be read-only.
		$this->json['readonly'] = $is_editable ? '' : ' disabled="disabled" readonly="readonly"';

		// Set up the input labels.
		$this->json['label'] = array(
			'cap'   => memberscontrol_show_human_caps() && memberscontrol_cap_exists( $this->cap ) ? memberscontrol_get_cap( $this->cap )->label : $this->cap,
			'grant' => sprintf( esc_html__( 'Grant %s capability', 'memberscontrol' ), "<code>{$this->cap}</code>" ),
			'deny'  => sprintf( esc_html__( 'Deny %s capability',  'memberscontrol' ), "<code>{$this->cap}</code>" )
		);

		// Set up the input `name` attributes.
		$this->json['name'] = array(
			'grant' => 'grant-caps[]',
			'deny'  => 'deny-caps[]'
		);

		// Is this a granted or denied cap?
		$this->json['is_granted_cap'] = isset( $this->manager->has_caps[ $this->cap ] ) && $this->manager->has_caps[ $this->cap ];
		$this->json['is_denied_cap']  = isset( $this->manager->has_caps[ $this->cap ] ) && false === $this->manager->has_caps[ $this->cap ];
	}
}
