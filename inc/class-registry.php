<?php
/**
 * Registry class for storing collections of data.
 */

namespace MembersControl;

/**
 * Base registry class.
 */
class Registry {

	private static $instances = array();

	protected $collection = array();

	protected function __construct() {}

	private function __clone() {}

	private function __wakeup() {}

	/**
	 * Register an item.
	 *
	 * @param  string  $name
	 * @param  mixed   $value
	 * @return void
	 */
	public function register( $name, $value ) {

		if ( ! $this->exists( $name ) )
			$this->collection[ $name ] = $value;
	}

	/**
	 * Unregisters an item.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function unregister( $name ) {

		if ( $this->exists( $name ) )
			unset( $this->collection[ $name ] );
	}

	/**
	 * Checks if an item exists.
	 *
	 * @param  string  $name
	 * @return bool
	 */
	public function exists( $name ) {

		return isset( $this->collection[ $name ] );
	}

	/**
	 * Returns an item.
	 *
	 * @param  string  $name
	 * @return mixed
	 */
	public function get( $name ) {

		return $this->exists( $name ) ? $this->collection[ $name ] : false;
	}

	/**
	 * Returns the entire collection.
	 */
	public function get_collection() {

		return $this->collection;
	}

	/**
	 * Returns the instance.
	 */
	final public static function get_instance( $name = '' ) {

		if ( ! isset( self::$instances[ $name ] ) )
			self::$instances[ $name ] = new static();

		return self::$instances[ $name ];
	}
}
