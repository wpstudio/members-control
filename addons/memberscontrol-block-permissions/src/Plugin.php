<?php
/**
 * Primary plugin class.
 */

namespace MembersControl\BlockPermissions;

/**
 * Plugin class.
 */
class Plugin {

	/**
	 * Stores the plugin directory path.
	 */
	protected $path;

	protected $uri;

	private $mix = [];

	protected $components = [];

	public function __construct( $path, $uri ) {

		$this->path = untrailingslashit( $path );
		$this->uri  = untrailingslashit( $uri );

		$this->registerDefaultComponents();
	}

	public function boot() {

		// Bootstrap components.
		foreach ( $this->components as $component ) {
			$component->boot();
		}
	}

	public function path( $file = '' ) {

		$file = ltrim( $file, '/' );

		return $file ? $this->path . "/{$file}" : $this->path;
	}

	public function uri( $file = '' ) {

		$file = ltrim( $file, '/' );

		return $file ? $this->uri . "/{$file}" : $this->uri;
	}

	function asset( $path ) {

		if ( ! $this->mix ) {
			$file      = $this->path( 'public/mix-manifest.json' );
			$this->mix = (array) json_decode( file_get_contents( $file ), true );
		}

		// Make sure to trim any slashes from the front of the path.
		$path = '/' . ltrim( $path, '/' );

		if ( $this->mix && isset( $this->mix[ $path ] ) ) {
			$path = $this->mix[ $path ];
		}

		return $this->uri( 'public' . $path );
	}

	protected function registerDefaultComponents() {

		$components = [
			Block::class,
			Editor::class,
			Integration::class
		];

		foreach ( $components as $component ) {
			$this->registerComponent( $component );
		}
	}

	public function getComponent( $abstract ) {
		return $this->components[ $abstract ];
	}

	protected function registerComponent( $abstract ) {
		$this->components[ $abstract ] = new $abstract();
	}
}
