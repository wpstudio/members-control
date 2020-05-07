<?php
/**
 * Base class for creating custom settings views.
 */

namespace MembersControl\Admin;

/**
 * Settings view base class.
 */
abstract class View {

	public $name = '';

	public $label = '';

	public $priority = 10;

	public $capability = 'manage_options';

	public function __toString() {
		return $this->name;
	}

	public function __construct( $name, $args = array() ) {

		foreach ( array_keys( get_object_vars( $this ) ) as $key ) {

			if ( isset( $args[ $key ] ) )
				$this->$key = $args[ $key ];
		}

		$this->name = sanitize_key( $name );
	}

	public function load() {}

	public function enqueue() {}

	public function register_settings() {}

	public function add_help_tabs() {}

	public function template() {}

	public function check_capabilities() {

		if ( $this->capability && ! call_user_func_array( 'current_user_can', (array) $this->capability ) )
			return false;

		return true;
	}
}
