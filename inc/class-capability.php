<?php
/**
 * Creates a new capability object.
 */

namespace MembersControl;

/**
 * Capability class.
 */
class Capability {

	public $name = '';

	public $label = '';

	public $group = '';

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
}
