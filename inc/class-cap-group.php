<?php
/**
 * Class for handling a capability group object.
 */
namespace MembersControl;

/**
 * Capability group object class.
 */
final class Cap_Group {

	public $name = '';

	public $label = '';

	public $icon = 'dashicons-admin-generic';

	public $caps = array();

	public $priority = 10;

	public $diff_added = false;

	public function __toString() {
		return $this->name;
	}

	/**
	 * Register a new object.
	 *
	 * @access public
	 * @param  string  $name
	 * @param  array   $args  {
	 *     @type string  $label        Internationalized text label.
	 *     @type string  $icon         Dashicon icon in the form of `dashicons-icon-name`.
	 *     @type array   $caps         Array of capabilities in the group.
	 *     @type bool    $merge_added  Whether to merge this caps into the added caps array.
	 *     @type bool    $diff_added   Whether to remove previously-added caps from this group.
	 * }
	 * @return void
	 */
	public function __construct( $name, $args = array() ) {

		foreach ( array_keys( get_object_vars( $this ) ) as $key ) {

			if ( isset( $args[ $key ] ) )
				$this->$key = $args[ $key ];
		}

		$this->name = sanitize_key( $name );

		$registered_caps = array_keys( wp_list_filter( memberscontrol_get_caps(), array( 'group' => $this->name ) ) );

		$this->caps = array_unique( array_merge( $this->caps, $registered_caps ) );

		$this->caps = memberscontrol_remove_hidden_caps( $this->caps );
	}
}
