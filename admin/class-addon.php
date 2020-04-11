<?php
/**
 * Class for handling an add-on object.
 */

namespace MembersControl;

/**
 * Add-on object class.
 */
final class Addon {

	public $name = '';

	public $title = '';

	public $excerpt = '';

	public $url = 'https://wpstudio.com/plugins/members-control';

	public $download_url = '';

	public $purchase_url = '';

	public $icon_url = '';

	public $author_url = '';

	public $author_name = '';

	public $rating = '';

	public $rating_count = 0;

	public $install_count = 0;

	public $is_memberpress = false;

	public function __toString() {
		return $this->name;
	}

	/**
	 * Register a new object.
	 */
	public function __construct( $name, $args = array() ) {

		foreach ( array_keys( get_object_vars( $this ) ) as $key ) {

			if ( isset( $args[ $key ] ) ) {
				$this->$key = $args[ $key ];
			}
		}

		$this->name = sanitize_key( $name );

		if ( ! $this->icon_url ) {
			$this->icon_url = members_plugin()->uri . 'img/icon-addon.png';
		}
	}
}
