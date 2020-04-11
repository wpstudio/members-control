<?php
/**
 * Role management.  This is the base class for the Roles and Edit Role screens.
 */

namespace MembersControl\Admin;

/**
 * Role management class.
 */
final class Manage_Roles {

	private static $instance;

	public $page = '';

	public $page_obj = '';

	public function __construct() {

		// If the role manager is active.
		if ( memberscontrol_role_manager_enabled() ) {
			add_action( 'admin_menu', array( $this, 'add_admin_page' ), 15 );
		}
	}

	public function add_admin_page() {

		// The "Roles" page should be shown for anyone that has the 'list_roles', 'edit_roles', or
		// 'delete_roles' caps, so we're checking against all three.
		$edit_roles_cap = 'list_roles';

		// If the current user can 'edit_roles'.
		if ( current_user_can( 'edit_roles' ) )
			$edit_roles_cap = 'edit_roles';

		// If the current user can 'delete_roles'.
		elseif ( current_user_can( 'delete_roles' ) )
			$edit_roles_cap = 'delete_roles';

		// Get the page title.
		$title = esc_html__( 'Roles', 'members' );

		if ( isset( $_GET['action'] ) && 'edit' === $_GET['action'] && isset( $_GET['role'] ) )
			$title = esc_html__( 'Edit Role', 'memberscontrol' );

		// Create the Manage Roles page.
		$this->page = add_submenu_page( 'memberscontrol', $title, esc_html__( 'Roles', 'memberscontrol' ), $edit_roles_cap, 'roles', array( $this, 'page' ) );

		// Let's roll if we have a page.
		if ( $this->page ) {

			// If viewing the edit role page.
			if ( isset( $_REQUEST['action'] ) && 'edit' === $_REQUEST['action'] && current_user_can( 'edit_roles' ) )
				$this->page_obj = new Role_Edit();

			// If viewing the role list page.
			else
				$this->page_obj = new Roles();

			// Load actions.
			add_action( "load-{$this->page}", array( $this, 'load' ) );

			// Load scripts/styles.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		}
	}

	/**
	 * Checks posted data on load and performs actions if needed.
	 */
	public function load() {

		if ( method_exists( $this->page_obj, 'load' ) )
			$this->page_obj->load();
	}

	/**
	 * Loads necessary scripts/styles.
	 */
	public function enqueue( $hook_suffix ) {

		if ( $this->page === $hook_suffix && method_exists( $this->page_obj, 'enqueue' ) )
			$this->page_obj->enqueue();
	}

	/**
	 * Outputs the page.
	 */
	public function page() {

		if ( method_exists( $this->page_obj, 'page' ) )
			$this->page_obj->page();
	}

	/**
	 * Returns the instance.
	 */
	public static function get_instance() {

		if ( ! self::$instance )
			self::$instance = new self;

		return self::$instance;
	}
}

Manage_Roles::get_instance();
