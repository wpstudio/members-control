<?php
/**
 * Creates a new role object.  This is an extension of the core `get_role()` functionality.  It's
 * just been beefed up a bit to provide more useful info for our plugin.
 *
 */

namespace MembersControl;

/**
 * Role class.
 */
class Role {

	public $name = '';

	public $label = '';

	public $group = '';

	public $has_caps = false;

	public $granted_cap_count = 0;

	public $denied_cap_count = 0;

	public $caps = array();

	public $granted_caps = array();

	public $denied_caps = array();

	public function __toString() {
		return $this->name;
	}

	/**
	 * Creates a new role object.
	 *
	 * @param  string  $role
	 * @param  array   $args
	 * @return void
	 */
	public function __construct( $name, $args = array() ) {

		foreach ( array_keys( get_object_vars( $this ) ) as $key ) {

			if ( isset( $args[ $key ] ) )
				$this->$key = $args[ $key ];
		}

		$this->name = memberscontrol_sanitize_role( $name );

		if ( $this->caps ) {

			// Validate cap values as booleans in case they are stored as strings.
			$this->caps = array_map( 'memberscontrol_validate_boolean', $this->caps );

			// Get granted and denied caps.
			$this->granted_caps = array_keys( $this->caps, true  );
			$this->denied_caps  = array_keys( $this->caps, false );

			// Remove user levels from granted/denied caps.
			$this->granted_caps = memberscontrol_remove_old_levels( $this->granted_caps );
			$this->denied_caps  = memberscontrol_remove_old_levels( $this->denied_caps  );

			// Remove hidden caps from granted/denied caps.
			$this->granted_caps = memberscontrol_remove_hidden_caps( $this->granted_caps );
			$this->denied_caps  = memberscontrol_remove_hidden_caps( $this->denied_caps  );

			// Set the cap count.
			$this->granted_cap_count = count( $this->granted_caps );
			$this->denied_cap_count  = count( $this->denied_caps  );

			// Check if we have caps.
			$this->has_caps = 0 < $this->granted_cap_count;
		}
	}

	/**
	 * Magic method for getting media object properties.  Let's keep from failing if a theme
	 * author attempts to access a property that doesn't exist.
	 * @param  string  $property
	 * @return mixed
	 */
	public function get( $property ) {

		if ( 'label' === $property )
			return memberscontrol_translate_role( $this->name );

		return isset( $this->$property ) ? $this->$property : false;
	}
}
