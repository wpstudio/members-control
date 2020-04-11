<?php
/**
 * Capability section class for use in the edit capabilities tabs.
 */

namespace MembersControl\Admin;

/**
 * Cap section class.
 */
final class Cap_Section {

	public $manager;

	public $section = '';

	public $icon = 'dashicons-admin-generic';

	public $label = '';

	public $json = array();

	public function __construct( $manager, $section, $args = array() ) {

		foreach ( array_keys( get_object_vars( $this ) ) as $key ) {

			if ( isset( $args[ $key ] ) )
				$this->$key = $args[ $key ];
		}

		$this->manager = $manager;
		$this->section = $section;
	}

	public function json() {
		$this->to_json();
		return $this->json;
	}

	public function to_json() {

		// Is the role editable?
		$is_editable = $this->manager->role ? memberscontrol_is_role_editable( $this->manager->role->name ) : true;

		// Set up the ID and class.
		$this->json['id']    = $this->section;
		$this->json['class'] = 'memberscontrol-tab-content' . ( $is_editable ? ' editable-role' : '' );
	}
}
