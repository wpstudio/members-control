<?php
/**
 * Handles the new role screen.
 */

namespace MembersControl\Admin;

/**
 * Class that displays the new role screen and handles the form submissions for that page.
 */
final class Role_New {

	private static $instance;

	public $page = '';

	public $role = '';

	public $role_name = '';

	public $capabilities = array();

	public $is_clone = false;

	public $clone_role = '';

	public function __construct() {

		// If the role manager is active.
		if ( memberscontrol_role_manager_enabled() ) {
			add_action( 'admin_menu', array( $this, 'add_admin_page' ) );
			//add_action( 'admin_menu', array( $this, 'add_submenu_admin_page' ), 20 );
		}
	}

	public function add_admin_page() {

		$this->page = add_menu_page( esc_html__( 'MembersControl', 'memberscontrol' ), esc_html__( 'MembersControl', 'memberscontrol' ), 'create_roles', 'memberscontrol', array( $this, 'page' ), 'data:image/svg+xml;base64,' . base64_encode( '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="users-cog" class="svg-inline--fa fa-users-cog fa-w-20" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="currentColor" d="M610.5 341.3c2.6-14.1 2.6-28.5 0-42.6l25.8-14.9c3-1.7 4.3-5.2 3.3-8.5-6.7-21.6-18.2-41.2-33.2-57.4-2.3-2.5-6-3.1-9-1.4l-25.8 14.9c-10.9-9.3-23.4-16.5-36.9-21.3v-29.8c0-3.4-2.4-6.4-5.7-7.1-22.3-5-45-4.8-66.2 0-3.3.7-5.7 3.7-5.7 7.1v29.8c-13.5 4.8-26 12-36.9 21.3l-25.8-14.9c-2.9-1.7-6.7-1.1-9 1.4-15 16.2-26.5 35.8-33.2 57.4-1 3.3.4 6.8 3.3 8.5l25.8 14.9c-2.6 14.1-2.6 28.5 0 42.6l-25.8 14.9c-3 1.7-4.3 5.2-3.3 8.5 6.7 21.6 18.2 41.1 33.2 57.4 2.3 2.5 6 3.1 9 1.4l25.8-14.9c10.9 9.3 23.4 16.5 36.9 21.3v29.8c0 3.4 2.4 6.4 5.7 7.1 22.3 5 45 4.8 66.2 0 3.3-.7 5.7-3.7 5.7-7.1v-29.8c13.5-4.8 26-12 36.9-21.3l25.8 14.9c2.9 1.7 6.7 1.1 9-1.4 15-16.2 26.5-35.8 33.2-57.4 1-3.3-.4-6.8-3.3-8.5l-25.8-14.9zM496 368.5c-26.8 0-48.5-21.8-48.5-48.5s21.8-48.5 48.5-48.5 48.5 21.8 48.5 48.5-21.7 48.5-48.5 48.5zM96 224c35.3 0 64-28.7 64-64s-28.7-64-64-64-64 28.7-64 64 28.7 64 64 64zm224 32c1.9 0 3.7-.5 5.6-.6 8.3-21.7 20.5-42.1 36.3-59.2 7.4-8 17.9-12.6 28.9-12.6 6.9 0 13.7 1.8 19.6 5.3l7.9 4.6c.8-.5 1.6-.9 2.4-1.4 7-14.6 11.2-30.8 11.2-48 0-61.9-50.1-112-112-112S208 82.1 208 144c0 61.9 50.1 112 112 112zm105.2 194.5c-2.3-1.2-4.6-2.6-6.8-3.9-8.2 4.8-15.3 9.8-27.5 9.8-10.9 0-21.4-4.6-28.9-12.6-18.3-19.8-32.3-43.9-40.2-69.6-10.7-34.5 24.9-49.7 25.8-50.3-.1-2.6-.1-5.2 0-7.8l-7.9-4.6c-3.8-2.2-7-5-9.8-8.1-3.3.2-6.5.6-9.8.6-24.6 0-47.6-6-68.5-16h-8.3C179.6 288 128 339.6 128 403.2V432c0 26.5 21.5 48 48 48h255.4c-3.7-6-6.2-12.8-6.2-20.3v-9.2zM173.1 274.6C161.5 263.1 145.6 256 128 256H64c-35.3 0-64 28.7-64 64v32c0 17.7 14.3 32 32 32h65.9c6.3-47.4 34.9-87.3 75.2-109.4z"></path></svg>' ) );

		// We don't need to have a "Members" link in the submenu, so this removes it
		add_submenu_page( 'memberscontrol', '', '', 'create_roles', 'memberscontrol', array( $this, 'page' ) );
		remove_submenu_page( 'memberscontrol', 'memberscontrol' );

		// Let's roll if we have a page.
		if ( $this->page ) {

			add_action( "load-{$this->page}", array( $this, 'load'          ) );
			add_action( "load-{$this->page}", array( $this, 'add_help_tabs' ) );
		}
	}

	/**
	 * Adds the "Add New Role" submenu page to the admin.
	 
	public function add_submenu_admin_page() {
		add_submenu_page( 'members', esc_html_x( 'Add New Role', 'admin screen', 'memberscontrol' ), esc_html_x( 'Add New Role', 'admin screen', 'members' ), 'create_roles', 'members', array( $this, 'page' ) );
	}
	*/
	/**
	 * Checks posted data on load and performs actions if needed.
	 */
	public function load() {

		// Are we cloning a role?
		$this->is_clone = isset( $_GET['clone'] ) && memberscontrol_role_exists( $_GET['clone'] );

		if ( $this->is_clone ) {

			// Override the default new role caps.
			add_filter( 'memberscontrol_new_role_default_caps', array( $this, 'clone_default_caps' ), 15 );

			// Set the clone role.
			$this->clone_role = memberscontrol_sanitize_role( $_GET['clone'] );
		}

		// Check if the current user can create roles and the form has been submitted.
		if ( current_user_can( 'create_roles' ) && isset( $_POST['memberscontrol_new_role_nonce'] ) ) {

			// Verify the nonce.
			check_admin_referer( 'new_role', 'memberscontrol_new_role_nonce' );

			// Set up some variables.
			$this->capabilities = array();
			$new_caps           = array();
			$is_duplicate       = false;

			// Get all the capabilities.
			$_m_caps = memberscontrol_get_capabilities();

			// Add all caps from the cap groups.
			foreach ( memberscontrol_get_cap_groups() as $group )
				$_m_caps = array_merge( $_m_caps, $group->caps );

			// Make sure we have a unique array of caps.
			$_m_caps = array_unique( $_m_caps );

			// Check if any capabilities were selected.
			if ( isset( $_POST['grant-caps'] ) || isset( $_POST['deny-caps'] ) ) {

				$grant_caps = ! empty( $_POST['grant-caps'] ) ? memberscontrol_remove_hidden_caps( array_unique( $_POST['grant-caps'] ) ) : array();
				$deny_caps  = ! empty( $_POST['deny-caps'] )  ? memberscontrol_remove_hidden_caps( array_unique( $_POST['deny-caps']  ) ) : array();

				foreach ( $_m_caps as $cap ) {

					if ( in_array( $cap, $grant_caps ) )
						$new_caps[ $cap ] = true;

					else if ( in_array( $cap, $deny_caps ) )
						$new_caps[ $cap ] = false;
				}
			}

			$grant_new_caps = ! empty( $_POST['grant-new-caps'] ) ? memberscontrol_remove_hidden_caps( array_unique( $_POST['grant-new-caps'] ) ) : array();
			$deny_new_caps  = ! empty( $_POST['deny-new-caps'] )  ? memberscontrol_remove_hidden_caps( array_unique( $_POST['deny-new-caps']  ) ) : array();

			foreach ( $grant_new_caps as $grant_new_cap ) {

				$_cap = memberscontrol_sanitize_cap( $grant_new_cap );

				if ( ! in_array( $_cap, $_m_caps ) )
					$new_caps[ $_cap ] = true;
			}

			foreach ( $deny_new_caps as $deny_new_cap ) {

				$_cap = memberscontrol_sanitize_cap( $deny_new_cap );

				if ( ! in_array( $_cap, $_m_caps ) )
					$new_caps[ $_cap ] = false;
			}

			// Sanitize the new role name/label. We just want to strip any tags here.
			if ( ! empty( $_POST['role_name'] ) )
				$this->role_name = wp_strip_all_tags( wp_unslash( $_POST['role_name'] ) );

			// Sanitize the new role, removing any unwanted characters.
			if ( ! empty( $_POST['role'] ) )
				$this->role = memberscontrol_sanitize_role( $_POST['role'] );

			else if ( $this->role_name )
				$this->role = memberscontrol_sanitize_role( $this->role_name );

			// Is duplicate?
			if ( memberscontrol_role_exists( $this->role ) )
				$is_duplicate = true;

			// Add a new role with the data input.
			if ( $this->role && $this->role_name && ! $is_duplicate ) {

				add_role( $this->role, $this->role_name, $new_caps );

				// Action hook for when a role is added.
				do_action( 'memberscontrol_role_added', $this->role );

				// If the current user can edit roles, redirect to edit role screen.
				if ( current_user_can( 'edit_roles' ) ) {
					wp_redirect( esc_url_raw( add_query_arg( 'message', 'role_added', memberscontrol_get_edit_role_url( $this->role ) ) ) );
 					exit;
				}

				// Add role added message.
				add_settings_error( 'memberscontrol_role_new', 'role_added', sprintf( esc_html__( 'The %s role has been created.', 'memberscontrol' ), $this->role_name ), 'updated' );
			}

			// If there are new caps, let's assign them.
			if ( ! empty( $new_caps ) )
				$this->capabilities = $new_caps;

			// Add error if there's no role.
			if ( ! $this->role )
				add_settings_error( 'memberscontrol_role_new', 'no_role', esc_html__( 'You must enter a valid role.', 'memberscontrol' ) );

			// Add error if this is a duplicate role.
			if ( $is_duplicate )
				add_settings_error( 'memberscontrol_role_new', 'duplicate_role', sprintf( esc_html__( 'The %s role already exists.', 'memberscontrol' ), $this->role ) );

			// Add error if there's no role name.
			if ( ! $this->role_name )
				add_settings_error( 'memberscontrol_role_new', 'no_role_name', esc_html__( 'You must enter a valid role name.', 'memberscontrol' ) );
		}

		// If we don't have caps yet, get the new role default caps.
		if ( empty( $this->capabilities ) )
			$this->capabilities = memberscontrol_new_role_default_caps();

		// Load page hook.
		do_action( 'memberscontrol_load_role_new' );

		// Hook for adding in meta boxes.
		do_action( 'add_meta_boxes_' . get_current_screen()->id, '' );
		do_action( 'add_meta_boxes',   get_current_screen()->id, '' );

		// Add layout screen option.
		add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );

		// Load scripts/styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Adds help tabs.
	 */
	public function add_help_tabs() {

		// Get the current screen.
		$screen = get_current_screen();

		// Add help tabs.
		$screen->add_help_tab( memberscontrol_get_edit_role_help_overview_args()   );
		$screen->add_help_tab( memberscontrol_get_edit_role_help_role_name_args()  );
		$screen->add_help_tab( memberscontrol_get_edit_role_help_edit_caps_args()  );
		$screen->add_help_tab( memberscontrol_get_edit_role_help_custom_cap_args() );

		// Set the help sidebar.
		$screen->set_help_sidebar( memberscontrol_get_help_sidebar_text() );
	}

	/**
	 * Enqueue scripts/styles.
	 */
	public function enqueue() {

		wp_enqueue_style(  'memberscontrol-admin'     );
		wp_enqueue_script( 'memberscontrol-edit-role' );
	}

	/**
	 * Outputs the page.
	 */
	public function page() { ?>

		<div class="wrap">

			<h1><?php ! $this->is_clone ? esc_html_e( 'Add New Role', 'memberscontrol' ) : esc_html_e( 'Clone Role', 'memberscontrol' ); ?></h1>

			<?php settings_errors( 'memberscontrol_role_new' ); ?>

			<div id="poststuff">

				<form name="form0" method="post" action="<?php echo esc_url( memberscontrol_get_new_role_url() ); ?>">

					<?php wp_nonce_field( 'new_role', 'memberscontrol_new_role_nonce' ); ?>

					<div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? 1 : 2; ?>">

						<div id="post-body-content">

							<div id="titlediv" class="memberscontrol-title-div">

								<div id="titlewrap">
									<span class="screen-reader-text"><?php esc_html_e( 'Role Name', 'memberscontrol' ); ?></span>
									<input type="text" name="role_name" value="<?php echo ! $this->role && $this->clone_role ? esc_attr( sprintf( __( '%s Clone', 'memberscontrol' ), memberscontrol_get_role( $this->clone_role )->get( 'label' ) ) ) : esc_attr( $this->role_name ); ?>" placeholder="<?php esc_attr_e( 'Enter role name', 'memberscontrol' ); ?>" />
								</div><!-- #titlewrap -->

								<div class="inside">
									<div id="edit-slug-box">
										<strong><?php esc_html_e( 'Role:', 'memberscontrol' ); ?></strong> <span class="role-slug"><?php echo ! $this->role && $this->clone_role ? esc_attr( "{$this->clone_role}_clone" ) : esc_attr( $this->role ); ?></span> <!-- edit box -->
										<input type="text" name="role" value="<?php echo memberscontrol_sanitize_role( $this->role ); ?>" />
										<button type="button" class="role-edit-button button button-small closed"><?php esc_html_e( 'Edit', 'memberscontrol' ); ?></button>
									</div>
								</div><!-- .inside -->

							</div><!-- .memberscontrol-title-div -->

							<?php $cap_tabs = new Cap_Tabs( '', $this->capabilities ); ?>
							<?php $cap_tabs->display(); ?>

						</div><!-- #post-body-content -->

						<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
						<?php wp_nonce_field( 'meta-box-order',  'meta-box-order-nonce', false ); ?>

						<div id="postbox-container-1" class="postbox-container side">

							<?php do_meta_boxes( get_current_screen()->id, 'side', '' ); ?>

						</div><!-- .post-box-container -->

					</div><!-- #post-body -->
				</form>

			</div><!-- #poststuff -->

		</div><!-- .wrap -->

	<?php }

	/**
	 * Filters the new role default caps in the case that we're cloning a role.
	 */
	public function clone_default_caps( $capabilities ) {

		if ( $this->is_clone ) {

			$role = get_role( $this->clone_role );

			if ( $role && isset( $role->capabilities ) && is_array( $role->capabilities ) )
				$capabilities = $role->capabilities;
		}

		return $capabilities;
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

Role_New::get_instance();
