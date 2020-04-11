<?php
/**
 * Primary class for setting up the plugin.
 */
namespace MembersControl\AddOns\AdminAccess;

/**
 * Application class.
 */
class App {

	public $dir = '';

	public $namespace = '';

	public function __construct( array $args = [] ) {

		foreach ( array_keys( get_object_vars( $this ) ) as $key ) {

			if ( isset( $args[ $key ] ) )
				$this->$key = $args[ $key ];
		}
	}
}
