<?php
/**
 * Roles admin screen.
 */

namespace MembersControl\Admin;

/**
 * Class that displays the roles admin screen and handles requests for that page.
 */
final class Roles {

	public function __construct() {

		// Set up some page options for the current screen.
		add_action( 'current_screen', array( $this, 'current_screen' ) );

		// Set up the role list table columns.
		add_filter( 'manage_memberscontrol_page_roles_columns', array( $this, 'manage_roles_columns' ), 5 );

		// Add help tabs.
		add_action( 'memberscontrol_load_manage_roles', array( $this, 'add_help_tabs' ) );
	}

	public function current_screen( $screen ) {

		if ( 'memberscontrol_page_roles' === $screen->id )
			$screen->add_option( 'per_page', array( 'default' => 20 ) );
	}

	public function manage_roles_columns( $columns ) {

		$columns = array(
			'cb'            => '<input type="checkbox" />',
			'title'         => esc_html__( 'Role Name', 'memberscontrol' ),
			'role'          => esc_html__( 'Role',      'memberscontrol' ),
			'users'         => esc_html__( 'Users',     'memberscontrol' ),
			'granted_caps'  => esc_html__( 'Granted',   'memberscontrol' ),
			'denied_caps'   => esc_html__( 'Denied',    'memberscontrol' )
		);

		return apply_filters( 'memberscontrol_manage_roles_columns', $columns );
	}

	/**
	 * Runs on the `load-{$page}` hook.  This is the handler for form submissions and requests.
	 */
	public function load() {

		// Get the current action if sent as request.
		$action = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : false;

		// Get the current action if posted.
		if ( ( isset( $_POST['action'] ) && 'delete' == $_POST['action'] ) || ( isset( $_POST['action2'] ) && 'delete' == $_POST['action2'] ) )
			$action = 'bulk-delete';

		// Bulk delete role handler.
		if ( 'bulk-delete' === $action ) {

			// If roles were selected, let's delete some roles.
			if ( current_user_can( 'delete_roles' ) && isset( $_POST['roles'] ) && is_array( $_POST['roles'] ) ) {

				// Verify the nonce. Nonce created via `WP_List_Table::display_tablenav()`.
				check_admin_referer( 'bulk-roles' );

				// Loop through each of the selected roles.
				foreach ( $_POST['roles'] as $role ) {

					$role = memberscontrol_sanitize_role( $role );

					if ( memberscontrol_role_exists( $role ) )
						memberscontrol_delete_role( $role );
				}

				// Add roles deleted message.
				add_settings_error( 'memberscontrol_roles', 'roles_deleted', esc_html__( 'Selected roles deleted.', 'memberscontrol' ), 'updated' );
			}

		// Delete single role handler.
		} else if ( 'delete' === $action ) {

			// Make sure the current user can delete roles.
			if ( current_user_can( 'delete_roles' ) ) {

				// Verify the referer.
				check_admin_referer( 'delete_role', 'memberscontrol_delete_role_nonce' );

				// Get the role we want to delete.
				$role = memberscontrol_sanitize_role( $_GET['role'] );

				// Check that we have a role before attempting to delete it.
				if ( memberscontrol_role_exists( $role ) ) {

					// Add role deleted message.
					add_settings_error( 'memberscontrol_roles', 'role_deleted', sprintf( esc_html__( '%s role deleted.', 'memberscontrol' ), memberscontrol_get_role( $role )->get( 'label' ) ), 'updated' );

					// Delete the role.
					memberscontrol_delete_role( $role );
				}
			}
		}

		// Load page hook.
		do_action( 'memberscontrol_load_manage_roles' );
	}

	/**
	 * Enqueue scripts/styles.
	 */
	public function enqueue() {

		wp_enqueue_style(  'memberscontrol-admin'     );
		wp_enqueue_script( 'memberscontrol-edit-role' );
	}

	/**
	 * Displays the page content.
	 */
	public function page() {

		require_once( memberscontrol_plugin()->dir . 'admin/class-role-list-table.php' ); ?>

		<div class="wrap">

			<h1>
				<?php esc_html_e( 'Roles', 'memberscontrol' ); ?>

				<?php if ( current_user_can( 'create_roles' ) ) : ?>
					<a href="<?php echo esc_url( memberscontrol_get_new_role_url() ); ?>" class="page-title-action"><?php echo esc_html_x( 'Add New', 'role', 'memberscontrol' ); ?></a>
				<?php endif; ?>
			</h1>

			<?php settings_errors( 'memberscontrol_roles' ); ?>

			<div id="poststuff">

				<form id="roles" action="<?php echo esc_url( memberscontrol_get_edit_roles_url() ); ?>" method="post">

					<?php $table = new Role_List_Table(); ?>
					<?php $table->prepare_items(); ?>
					<?php $table->display(); ?>

				</form><!-- #roles -->

			</div><!-- #poststuff -->

		</div><!-- .wrap -->
	<?php }

	/**
	 * Adds help tabs.
	 */
	public function add_help_tabs() {

		// Get the current screen.
		$screen = get_current_screen();

		// Add overview help tab.
		$screen->add_help_tab(
			array(
				'id'       => 'overview',
				'title'    => esc_html__( 'Overview', 'memberscontrol' ),
				'callback' => array( $this, 'help_tab_overview' )
			)
		);

		// Add screen content help tab.
		$screen->add_help_tab(
			array(
				'id'       => 'screen-content',
				'title'    => esc_html__( 'Screen Content', 'memberscontrol' ),
				'callback' => array( $this, 'help_tab_screen_content' )
			)
		);

		// Add available actions help tab.
		$screen->add_help_tab(
			array(
				'id'       => 'row-actions',
				'title'    => esc_html__( 'Available Actions', 'memberscontrol' ),
				'callback' => array( $this, 'help_tab_row_actions' )
			)
		);

		// Add bulk actions help tab.
		$screen->add_help_tab(
			array(
				'id'       => 'bulk-actions',
				'title'    => esc_html__( 'Bulk Actions', 'memberscontrol' ),
				'callback' => array( $this, 'help_tab_bulk_actions' )
			)
		);

		// Set the help sidebar.
		$screen->set_help_sidebar( memberscontrol_get_help_sidebar_text() );
	}

	/**
	 * Overview help tab callback function.
	 */
	public function help_tab_overview() { ?>

		<p>
			<?php esc_html_e( 'This screen provides access to all of your user roles. Roles are a method of grouping users. They are made up of capabilities (caps), which give permission to users to perform specific actions on the site.' ); ?>
		<p>
	<?php }

	/**
	 * Screen content help tab callback function.
	 */
	public function help_tab_screen_content() { ?>

		<p>
			<?php esc_html_e( 'You can customize the display of this screen&#8216;s contents in a number of ways:', 'memberscontrol' ); ?>
		</p>

		<ul>
			<li><?php esc_html_e( 'You can hide/display columns based on your needs and decide how many roles to list per screen using the Screen Options tab.', 'memberscontrol' ); ?></li>
			<li><?php esc_html_e( 'You can filter the list of roles by types using the text links in the upper left. The default view is to show all roles.', 'memberscontrol' ); ?></li>
		</ul>
	<?php }

	/**
	 * Row actions help tab callback function.
	 */
	public function help_tab_row_actions() { ?>

		<p>
			<?php esc_html_e( 'Hovering over a row in the roles list will display action links that allow you to manage your role. You can perform the following actions:', 'memberscontrol' ); ?>
		</p>

		<ul>
			<li><?php _e( '<strong>Edit</strong> takes you to the editing screen for that role. You can also reach that screen by clicking on the role name.', 'memberscontrol' ); ?></li>
			<li><?php _e( '<strong>Delete</strong> removes your role from this list and permanently deletes it.', 'memberscontrol' ); ?></li>
			<li><?php _e( '<strong>Clone</strong> copies the role and takes you to the new role screen to further edit it.', 'memberscontrol' ); ?></li>
			<li><?php _e( '<strong>Users</strong> takes you to the users screen and lists the users that have that role.', 'memberscontrol' ); ?></li>
		</ul>
	<?php }

	/**
	 * Bulk actions help tab callback function.
	 */
	public function help_tab_bulk_actions() { ?>

		<p>
			<?php esc_html_e( 'You can permanently delete multiple roles at once. Select the roles you want to act on using the checkboxes, then select the action you want to take from the Bulk Actions menu and click Apply.', 'memberscontrol' ); ?>
		</p>
	<?php }
}
