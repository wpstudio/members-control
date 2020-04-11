<?php
/**
 * Class for handling a role group object.
 */

namespace MembersControl;

/**
 * Role group object class.
 */
final class Role_Group {

	public $name = '';

	public $label = '';

	public $label_count = '';

	public $roles = array();

	public $show_in_view_list = true;

	public function __toString() {
		return $this->name;
	}

	/**
	 * Register a new object.
	 *
	 * @param  string  $name
	 * @param  array   $args
	 * @return void
	 */
	public function __construct( $name, $args = array() ) {

		foreach ( array_keys( get_object_vars( $this ) ) as $key ) {

			if ( isset( $args[ $key ] ) )
				$this->$key = $args[ $key ];
		}

		$this->name = sanitize_key( $name );

		$registered_roles = array_keys( wp_list_filter( memberscontrol_get_roles(), array( 'group' => $this->name ) ) );

		$this->roles = array_unique( array_merge( $this->roles, $registered_roles ) );
	}
}
