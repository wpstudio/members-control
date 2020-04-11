<?php
/**
 * Handles the edit role screen.
 */

namespace MembersControl\Admin;

/**
 * Class that displays the edit role screen and handles the form submissions for that page.
 */
final class Role_Edit {

	protected $role;

	protected $members_role;

	protected $is_editable = true;

	protected $capabilities = array();

	protected $role_updated = false;

	public function __construct() {

		// Add help tabs.
		add_action( 'memberscontrol_load_role_edit', array( $this, 'add_help_tabs' ) );
	}

	public function load() {

		// If the current user can't edit roles, don't proceed.
		if ( ! current_user_can( 'edit_roles' ) )
			wp_die( esc_html__( 'Whoah, partner!', 'memberscontrol' ) );

		// Get the current role object to edit.
		$this->role = get_role( members_sanitize_role( $_GET['role'] ) );

		// If we don't have a real role, die.
		if ( is_null( $this->role ) )
			wp_die( esc_html__( 'The requested role to edit does not exist.', 'memberscontrol' ) );

		$this->memberscontrol_role = memberscontrol_get_role( $this->role->name );

		// Get all the capabilities.
		$this->capabilities = memberscontrol_get_capabilities();

		// Add all caps from the cap groups.
		foreach ( memberscontrol_get_cap_groups() as $group )
			$this->capabilities = array_merge( $this->capabilities, $group->caps );

		// Make sure we have a unique array of caps.
		$this->capabilities = array_unique( $this->capabilities );

		// Is the role editable?
		$this->is_editable = memberscontrol_is_role_editable( $this->role->name );

		// Check if the form has been submitted.
		if ( $this->is_editable && isset( $_POST['memberscontrol_edit_role_nonce'] ) ) {

			// Verify the nonce.
			check_admin_referer( 'edit_role', 'memberscontrol_edit_role_nonce' );

			// Get the granted and denied caps.
			$grant_caps = ! empty( $_POST['grant-caps'] ) ? memberscontrol_remove_hidden_caps( array_unique( $_POST['grant-caps'] ) ) : array();
			$deny_caps  = ! empty( $_POST['deny-caps'] )  ? memberscontrol_remove_hidden_caps( array_unique( $_POST['deny-caps']  ) ) : array();

			// Get the new (custom) granted and denied caps.
			$grant_new_caps = ! empty( $_POST['grant-new-caps'] ) ? memberscontrol_remove_hidden_caps( array_unique( $_POST['grant-new-caps'] ) ) : array();
			$deny_new_caps  = ! empty( $_POST['deny-new-caps'] )  ? memberscontrol_remove_hidden_caps( array_unique( $_POST['deny-new-caps']  ) ) : array();

			// Get the all and custom cap group objects.
			$all_group    = memberscontrol_get_cap_group( 'all'    );
			$custom_group = memberscontrol_get_cap_group( 'custom' );

			// New caps to push to cap groups on update.
			$push_caps = array();

			// Set the $role_updated variable to true.
			$this->role_updated = true;

			// Loop through all available capabilities.
			foreach ( $this->capabilities as $cap ) {

				// Get the posted capability.
				$grant_this_cap = in_array( $cap, $grant_caps );
				$deny_this_cap  = in_array( $cap, $deny_caps  );

				// Does the role have the cap?
				$is_granted_cap = $this->role->has_cap( $cap );
				$is_denied_cap  = isset( $this->role->capabilities[ $cap ] ) && false === $this->role->capabilities[ $cap ];

				if ( $grant_this_cap && ! $is_granted_cap )
					$this->role->add_cap( $cap );

				else if ( $deny_this_cap && ! $is_denied_cap )
					$this->role->add_cap( $cap, false );

				else if ( ! $grant_this_cap && $is_granted_cap )
					$this->role->remove_cap( $cap );

				else if ( ! $deny_this_cap && $is_denied_cap )
					$this->role->remove_cap( $cap );

			} // End loop through existing capabilities.

			// Loop through the custom granted caps.
			foreach ( $grant_new_caps as $grant_new_cap ) {

				$_cap = memberscontrol_sanitize_cap( $grant_new_cap );

				// If not an existing cap, add it.
				if ( 'do_not_allow' !== $_cap && ! in_array( $_cap, $this->capabilities ) ) {
					$this->role->add_cap( $_cap );

					$push_caps[] = $_cap;
				}
			}

			// Loop through the custom denied caps.
			foreach ( $deny_new_caps as $deny_new_cap ) {

				$_cap = memberscontrol_sanitize_cap( $deny_new_cap );

				// If not a granted cap and not an existing cap, add it.
				if ( 'do_not_allow' !== $_cap && ! in_array( $_cap, $this->capabilities ) && ! in_array( $_cap, $grant_new_caps ) ) {
					$this->role->add_cap( $_cap, false );

					$push_caps[] = $_cap;
				}
			}

			// If there are new caps, add them to the all and custom groups.
			if ( $push_caps ) {

				if ( $all_group ) {
					$all_group->caps[] = $_cap;
					sort( $all_group->caps );
				}

				if ( $custom_group ) {
					$custom_group->caps[] = $_cap;
					sort( $custom_group->caps );
				}
			}

			// Add the updated role to the role registry.
			memberscontrol_unregister_role( $this->role->name );

			memberscontrol_register_role(
				$this->role->name,
				array(
					'label' => $this->memberscontrol_role->get( 'label' ),
					'caps'  => $this->role->capabilities
				)
			);

			// Reset the Members role object.
			$this->memberscontrol_role = memberscontrol_get_role( $this->role->name );

			// Action hook for when a role is updated.
			do_action( 'memberscontrol_role_updated', $this->role->name );

		} // End check for form submission.

		// If successful update.
		if ( $this->role_updated )
			add_settings_error( 'memberscontrol_edit_role', 'role_updated', sprintf( esc_html__( '%s role updated.', 'memberscontrol' ), memberscontrol_get_role( $this->role->name )->get( 'label' ) ), 'updated' );

		// If the role is not editable.
		if ( ! $this->is_editable )
			add_settings_error( 'memberscontrol_edit_role', 'role_uneditable', sprintf( esc_html__( 'The %s role is not editable. This means that it is most likely added via another plugin for a special use or that you do not have permission to edit it.', 'memberscontrol' ), memberscontrol_get_role( $this->role->name )->get( 'label' ) ) );

		// If editing the core administrator role.
		if ( 'administrator' === $this->role->name )
			add_settings_error( 'memberscontrol_edit_role', 'role_is_admin', sprintf( esc_html__( 'The %s role is typically the most important role on the site. Please take extreme caution that you do not inadvertently remove necessary capabilities.', 'memberscontrol' ), memberscontrol_get_role( $this->role->name )->get( 'label' ) ) );

		// If a new role was added (redirect from new role screen).
		if ( isset( $_GET['message'] ) && 'role_added' === $_GET['message'] )
			add_settings_error( 'memberscontrol_edit_role', 'role_added', sprintf( esc_html__( 'The %s role has been created.', 'memberscontrol' ), memberscontrol_get_role( $this->role->name )->get( 'label' ) ), 'updated' );

		// Load page hook.
		do_action( 'memberscontrol_load_role_edit' );

		// Hook for adding in meta boxes.
		do_action( 'add_meta_boxes_' . get_current_screen()->id, $this->role->name );
		do_action( 'add_meta_boxes',   get_current_screen()->id, $this->role->name );

		// Add layout screen option.
		add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );
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
	 * Displays the page content.
	 */
	public function page() { ?>

		<div class="wrap">

			<h1>
				<?php esc_html_e( 'Edit Role', 'memberscontrol' ); ?>

				<?php if ( current_user_can( 'create_roles' ) ) : ?>
					<?php printf( '<a class="page-title-action" href="%s">%s</a>', esc_url( memberscontrol_get_new_role_url() ), esc_html_x( 'Add New', 'role', 'memberscontrol' ) ); ?>
				<?php endif; ?>
			</h1>

			<?php settings_errors( 'memberscontrol_edit_role' ); ?>

			<div id="poststuff">

				<form name="form0" method="post" action="<?php echo esc_url( memberscontrol_get_edit_role_url( $this->role->name ) ); ?>">

					<?php wp_nonce_field( 'edit_role', 'memberscontrol_edit_role_nonce' ); ?>

					<div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? 1 : 2; ?>">

						<div id="post-body-content">

							<div id="titlediv" class="memberscontrol-title-div">

								<div id="titlewrap">
									<span class="screen-reader-text"><?php esc_html_e( 'Role Name', 'memberscontrol' ); ?></span>
									<input type="text" disabled="disabled" readonly="readonly" value="<?php echo esc_attr( memberscontrol_get_role( $this->role->name )->get( 'label' ) ); ?>" />
								</div><!-- #titlewrap -->

								<div class="inside">
									<div id="edit-slug-box">
										<strong><?php esc_html_e( 'Role:', 'memberscontrol' ); ?></strong> <?php echo esc_attr( $this->role->name ); ?> <!-- edit box -->
									</div>
								</div><!-- .inside -->

							</div><!-- .memberscontrol-title-div -->

							<?php $cap_tabs = new Cap_Tabs( $this->role->name ); ?>
							<?php $cap_tabs->display(); ?>

						</div><!-- #post-body-content -->

						<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
						<?php wp_nonce_field( 'meta-box-order',  'meta-box-order-nonce', false ); ?>

						<div id="postbox-container-1" class="postbox-container side">

							<?php do_meta_boxes( get_current_screen()->id, 'side', $this->role ); ?>

						</div><!-- .post-box-container -->

					</div><!-- #post-body -->
				</form>

			</div><!-- #poststuff -->

		</div><!-- .wrap -->
	<?php }
}
