<?php
/*
 * Plugin Name: Members Control
 * Plugin URI: https://wpstudio.com/plugins/members-control
 * Description: A user and role management plugin that puts you in full control of your site's permissions. This plugin allows you to edit your roles and their capabilities, clone existing roles, assign multiple roles per user, block post content, or even make your site completely private. This is a fork from Justin Tadlock's amazing Members plugin.
 * Version: 1.0.0
 * Author: Benjamin
 * Author URI: https://b.enjam.in
 * Text Domain: memberscontrol
 * Domain Path: /lang
 * License: GPLv2 or later
*/
final class MembersControl_Plugin {
	// Minimum PHP version
	private $php_version = '5.3.0';

	public $dir = '';

	public $uri = '';

	public $role_user_count = array();

	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self;
			$instance->setup();
			$instance->includes();
			$instance->setup_actions();
		}
		return $instance;
	}

	private function __construct() {}

	public function __toString() {
		return 'memberscontrol';
	}

	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'You are not allowed to do this!', 'memberscontrol' ), '1.0.0' );
	}

	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'You are not allowed to do this!', 'memberscontrol' ), '1.0.0' );
	}

	public function __call( $method = '', $args = array() ) {
		_doing_it_wrong( "MembersControl_Plugin::{$method}", esc_html__( 'Method does not exist.', 'memberscontrol' ), '1.0.0' );
		unset( $method, $args );
		return null;
	}

	private function setup() {

		// Main plugin directory path and URI.
		$this->dir = trailingslashit( plugin_dir_path( __FILE__ ) );
		$this->uri  = trailingslashit( plugin_dir_url(  __FILE__ ) );
	}

	private function includes() {

		// Check if we meet the minimum PHP version.
		if ( version_compare( PHP_VERSION, $this->php_version, '<' ) ) {

			// Add admin notice.
			add_action( 'admin_notices', array( $this, 'php_admin_notice' ) );

			// Bail.
			return;
		}

		// Load class files.
		require_once( $this->dir . 'inc/class-capability.php' );
		require_once( $this->dir . 'inc/class-cap-group.php'  );
		require_once( $this->dir . 'inc/class-registry.php'   );
		require_once( $this->dir . 'inc/class-role-group.php' );
		require_once( $this->dir . 'inc/class-role.php'       );

		// Load includes files.
		require_once( $this->dir . 'inc/functions.php'                     );
		require_once( $this->dir . 'inc/functions-admin-bar.php'           );
		require_once( $this->dir . 'inc/functions-capabilities.php'        );
		require_once( $this->dir . 'inc/functions-cap-groups.php'          );
		require_once( $this->dir . 'inc/functions-content-permissions.php' );
		require_once( $this->dir . 'inc/functions-deprecated.php'          );
		require_once( $this->dir . 'inc/functions-options.php'             );
		require_once( $this->dir . 'inc/functions-private-site.php'        );
		require_once( $this->dir . 'inc/functions-roles.php'               );
		require_once( $this->dir . 'inc/functions-role-groups.php'         );
		require_once( $this->dir . 'inc/functions-shortcodes.php'          );
		require_once( $this->dir . 'inc/functions-users.php'               );
		require_once( $this->dir . 'inc/functions-widgets.php'             );

		// Load template files.
		require_once( $this->dir . 'inc/template.php' );

		// Load admin files.
		if ( is_admin() ) {

			// General admin functions.
			require_once( $this->dir . 'admin/functions-admin.php' );
			require_once( $this->dir . 'admin/functions-help.php'  );

			// Plugin settings.
			require_once( $this->dir . 'admin/class-settings.php' );

			// User management.
			require_once( $this->dir . 'admin/class-manage-users.php' );
			require_once( $this->dir . 'admin/class-user-edit.php'    );
			require_once( $this->dir . 'admin/class-user-new.php'     );

			// Edit posts.
			require_once( $this->dir . 'admin/class-meta-box-content-permissions.php' );

			// Role management.
			require_once( $this->dir . 'admin/class-manage-roles.php'          );
			require_once( $this->dir . 'admin/class-roles.php'                 );
			require_once( $this->dir . 'admin/class-role-edit.php'             );
			require_once( $this->dir . 'admin/class-role-new.php'              );
			require_once( $this->dir . 'admin/class-meta-box-publish-role.php' );
			require_once( $this->dir . 'admin/class-meta-box-custom-cap.php'   );

			// Edit capabilities tabs and groups.
			require_once( $this->dir . 'admin/class-cap-tabs.php'       );
			require_once( $this->dir . 'admin/class-cap-section.php'    );
			require_once( $this->dir . 'admin/class-cap-control.php'    );
		}

		$addons = get_option( 'memberscontrol_active_addons', array() );

		if ( ! empty( $addons ) ) {
			foreach ( $addons as $addon ) {
				if ( file_exists( __DIR__ . "/addons/{$addon}/addon.php" ) ) {
					include "addons/{$addon}/addon.php";
				}
			}
		}
	}

	private function setup_actions() {

		// Internationalize the text strings used.
	//	add_action( 'plugins_loaded', array( $this, 'i18n' ), 2 );

		// Migrate add-ons
		add_action( 'plugins_loaded', array( $this, 'migrate_addons' ) );

		// Register activation hook.
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
	}

	public function i18n() {

	//	load_plugin_textdomain( 'memberscontrol', false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) . 'lang' );
	}

	public function activation() {

		// Check PHP version requirements.
		if ( version_compare( PHP_VERSION, $this->php_version, '<' ) ) {

			// Make sure the plugin is deactivated.
			deactivate_plugins( plugin_basename( __FILE__ ) );

			// Add an error message and die.
			wp_die( $this->get_min_php_message() );
		}

		// Get the administrator role.
		$role = get_role( 'administrator' );

		// If the administrator role exists, add required capabilities for the plugin.
		if ( ! empty( $role ) ) {

			$role->add_cap( 'restrict_content' ); // Edit per-post content permissions.
			$role->add_cap( 'list_roles'       ); // View roles in backend.

			// Do not allow administrators to edit, create, or delete roles
			// in a multisite setup. Super admins should assign these manually.
			if ( ! is_multisite() ) {
				$role->add_cap( 'create_roles' ); // Create new roles.
				$role->add_cap( 'delete_roles' ); // Delete existing roles.
				$role->add_cap( 'edit_roles'   ); // Edit existing roles/caps.
			}
		}
	}

	private function get_min_php_message() {

		return sprintf(
			__( 'MembersControl requires PHP version %1$s. You are running version %2$s. Please upgrade and try again.', 'memberscontrol' ),
			$this->php_version,
			PHP_VERSION
		);
	}

	public function php_admin_notice() {

		// Output notice.
		printf(
			'<div class="notice notice-error is-dismissible"><p><strong>%s</strong></p></div>',
			esc_html( $this->get_min_php_message() )
		);

		// Make sure the plugin is deactivated.
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

	public function migrate_addons() {

		// Bail if we've already migrated the add-ons
		if ( ! empty( get_option( 'memberscontrol_addons_migrated' ) ) ) {
			return;
		}

		$addons = array();

		$plugins = array(
			'memberscontrol-acf-integration' => 'plugin.php',
			'memberscontrol-admin-access' => 'memberscontrol-admin-access.php',
			'memberscontrol-block-permissions' => 'plugin.php',
			'memberscontrol-category-and-tag-caps' => 'plugin.php',
			'memberscontrol-core-create-caps' => 'memberscontrol-core-create-caps.php',
			'memberscontrol-edd-integration' => 'plugin.php',
			'memberscontrol-givewp-integration' => 'plugin.php',
			'memberscontrol-meta-box-integration' => 'plugin.php',
			'memberscontrol-privacy-caps' => 'memberscontrol-privacy-caps.php',
			'memberscontrol-role-hierarchy' => 'memberscontrol-role-hierarchy.php',
			'memberscontrol-role-levels' => 'memberscontrol-role-levels.php',
			'memberscontrol-woocommerce-integration' => 'plugin.php'
		);

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		foreach ( $plugins as $dir => $file ) {
			if ( is_plugin_active( "{$dir}/{$file}" ) ) {

				// Deactive it
				deactivate_plugins( "{$dir}/{$file}", true );

				// Delete it
				delete_plugins( array( "{$dir}/{$file}" ) );

				// Make sure it's stored in our option for active add-ons
				$addons[] = $dir;
			}
		}

		if ( ! empty( $addons ) ) {
			update_option( 'memberscontrol_active_addons', $addons );
		}

		update_option( 'memberscontrol_addons_migrated', true );
	}

	/**
	 * We need a way to run an add-on's activation hook since the add-ons are no longer separate plugins.
	 */
	public function run_addon_activator( $addon ) {

		if ( file_exists( trailingslashit( __DIR__ ) . "addons/{$addon}/src/Activator.php" ) ) {
			
			// Require the add-on file
			include "addons/{$addon}/src/Activator.php";

			// Read the file contents into memory, and determine the namespace
			$contents = file_get_contents( trailingslashit( __DIR__ ) . "addons/{$addon}/src/Activator.php" );
			preg_match( '/[\r\n]namespace\W(.+);[\r\n]/', $contents, $matches );
			$namespace = $matches[1];
			// Run the activator
			if ( ! empty( $namespace ) ) {
				$namespace .= '\Activator';
				$namespace::activate();
			}
		}
	}

}

function memberscontrol_plugin() {
	return MembersControl_Plugin::get_instance();
}

// Let's roll!
memberscontrol_plugin();
