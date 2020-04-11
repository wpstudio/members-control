<?php
/**
 * Handles the general settings view.
 */

namespace MembersControl\Admin;

/**
 * Sets up and handles the general settings view.
 */
class View_General extends View {

	public $settings = array();

	public function enqueue() {

		wp_enqueue_script( 'memberscontrol-settings' );
	}

	function register_settings() {

		// Get the current plugin settings w/o the defaults.
		$this->settings = get_option( 'memberscontrol_settings' );

		// Register the setting.
		register_setting( 'memberscontrol_settings', 'memberscontrol_settings', array( $this, 'validate_settings' ) );

		/* === Settings Sections === */

		// Add settings sections.
		add_settings_section( 'roles_caps',          esc_html__( 'Roles and Capabilities', 'memberscontrol' ), array( $this, 'section_roles_caps' ), 'memberscontrol-settings' );
		add_settings_section( 'content_permissions', esc_html__( 'Content Permissions',    'memberscontrol' ), '__return_false',                     'memberscontrol-settings' );
		add_settings_section( 'private_site',        esc_html__( 'Private Site',           'memberscontrol' ), '__return_false',                     'memberscontrol-settings' );

		/* === Settings Fields === */

		// Role manager fields.
		add_settings_field( 'enable_role_manager',  esc_html__( 'Role Manager',        'memberscontrol' ), array( $this, 'field_enable_role_manager'  ), 'memberscontrol-settings', 'roles_caps' );
		add_settings_field( 'enable_multi_roles',   esc_html__( 'Multiple User Roles', 'memberscontrol' ), array( $this, 'field_enable_multi_roles'   ), 'memberscontrol-settings', 'roles_caps' );
		add_settings_field( 'explicit_denied_caps', esc_html__( 'Capabilities',        'memberscontrol' ), array( $this, 'field_explicit_denied_caps' ), 'memberscontrol-settings', 'roles_caps' );

		// Content permissions fields.
		add_settings_field( 'enable_content_permissions', esc_html__( 'Enable Permissions', 'memberscontrol' ), array( $this, 'field_enable_content_permissions' ), 'memberscontrol-settings', 'content_permissions' );
		add_settings_field( 'content_permissions_error',  esc_html__( 'Error Message',      'memberscontrol' ), array( $this, 'field_content_permissions_error'  ), 'memberscontrol-settings', 'content_permissions' );

		// Private site fields.
		add_settings_field( 'enable_private_site', esc_html__( 'Enable Private Site', 'memberscontrol' ), array( $this, 'field_enable_private_site' ), 'memberscontrol-settings', 'private_site' );
		add_settings_field( 'private_rest_api',    esc_html__( 'REST API',            'memberscontrol' ), array( $this, 'field_private_rest_api'    ), 'memberscontrol-settings', 'private_site' );
		add_settings_field( 'enable_private_feed', esc_html__( 'Disable Feed',        'memberscontrol' ), array( $this, 'field_enable_private_feed' ), 'memberscontrol-settings', 'private_site' );
		add_settings_field( 'private_feed_error',  esc_html__( 'Feed Error Message',  'memberscontrol' ), array( $this, 'field_private_feed_error'  ), 'memberscontrol-settings', 'private_site' );
	}

	/**
	 * Validates the plugin settings.
	 */
	function validate_settings( $settings ) {

		// Validate true/false checkboxes.
		$settings['role_manager']         = ! empty( $settings['role_manager'] )         ? true : false;
		$settings['explicit_denied_caps'] = ! empty( $settings['explicit_denied_caps'] ) ? true : false;
		$settings['show_human_caps']      = ! empty( $settings['show_human_caps'] )      ? true : false;
		$settings['multi_roles']          = ! empty( $settings['multi_roles'] )          ? true : false;
		$settings['content_permissions']  = ! empty( $settings['content_permissions'] )  ? true : false;
		$settings['private_blog']         = ! empty( $settings['private_blog'] )         ? true : false;
		$settings['private_rest_api']     = ! empty( $settings['private_rest_api'] )     ? true : false;
		$settings['private_feed']         = ! empty( $settings['private_feed'] )         ? true : false;

		// Kill evil scripts.
		$settings['content_permissions_error'] = stripslashes( wp_filter_post_kses( addslashes( $settings['content_permissions_error'] ) ) );
		$settings['private_feed_error']        = stripslashes( wp_filter_post_kses( addslashes( $settings['private_feed_error']        ) ) );

		// Return the validated/sanitized settings.
		return $settings;
	}

	/**
	 * Role/Caps section callback.
	 */
	public function section_roles_caps() { ?>

		<p class="description">
			<?php esc_html_e( 'Your roles and capabilities will not revert back to their previous settings after deactivating or uninstalling this plugin, so use this feature wisely.', 'memberscontrol' ); ?>
		</p>
	<?php }

	/**
	 * Role manager field callback.
	 */
	public function field_enable_role_manager() { ?>

		<label>
			<input type="checkbox" name="memberscontrol_settings[role_manager]" value="true" <?php checked( memberscontrol_role_manager_enabled() ); ?> />
			<?php esc_html_e( 'Enable the role manager.', 'memberscontrol' ); ?>
		</label>
	<?php }

	/**
	 * Explicit denied caps field callback.
	 */
	public function field_explicit_denied_caps() { ?>

		<fieldset>

			<p>
				<label>
					<input type="checkbox" name="memberscontrol_settings[explicit_denied_caps]" value="true" <?php checked( memberscontrol_explicitly_deny_caps() ); ?> />
					<?php esc_html_e( 'Denied capabilities should always overrule granted capabilities.', 'memberscontrol' ); ?>
				</label>
			</p>

			<p>
				<label>
					<input type="checkbox" name="memberscontrol_settings[show_human_caps]" value="true" <?php checked( memberscontrol_show_human_caps() ); ?> />
					<?php esc_html_e( 'Show human-readable capabilities when possible.', 'memberscontrol' ); ?>
				</label>
			</p>

		</fieldset>
	<?php }

	/**
	 * Multiple roles field callback.
	 */
	public function field_enable_multi_roles() { ?>

		<label>
			<input type="checkbox" name="memberscontrol_settings[multi_roles]" value="true" <?php checked( memberscontrol_multiple_user_roles_enabled() ); ?> />
			<?php esc_html_e( 'Allow users to be assigned more than a single role.', 'memberscontrol' ); ?>
		</label>
	<?php }

	/**
	 * Enable content permissions field callback.
	 */
	public function field_enable_content_permissions() { ?>

		<label>
			<input type="checkbox" name="memberscontrol_settings[content_permissions]" value="true" <?php checked( memberscontrol_content_permissions_enabled() ); ?> />
			<?php esc_html_e( 'Enable the content permissions feature.', 'memberscontrol' ); ?>
		</label>
	<?php }

	/**
	 * Content permissions error message field callback.
	 */
	public function field_content_permissions_error() {

		wp_editor(
			memberscontrol_get_setting( 'content_permissions_error' ),
			'memberscontrol_settings_content_permissions_error',
			array(
				'textarea_name'    => 'memberscontrol_settings[content_permissions_error]',
				'drag_drop_upload' => true,
				'editor_height'    => 250
			)
		);
	}

	/**
	 * Enable private site field callback.
	 */
	public function field_enable_private_site() { ?>

		<label>
			<input type="checkbox" name="memberscontrol_settings[private_blog]" value="true" <?php checked( memberscontrol_is_private_blog() ); ?> />
			<?php esc_html_e( 'Redirect all logged-out users to the login page before allowing them to view the site.', 'memberscontrol' ); ?>
		</label>
	<?php }

	/**
	 * Enable private REST API field callback.
	 */
	public function field_private_rest_api() { ?>

		<label>
			<input type="checkbox" name="memberscontrol_settings[private_rest_api]" value="true" <?php checked( memberscontrol_is_private_rest_api() ); ?> />
			<?php esc_html_e( 'Require authentication for access to the REST API.', 'memberscontrol' ); ?>
		</label>
	<?php }

	/**
	 * Enable private feed field callback.
	 */
	public function field_enable_private_feed() { ?>

		<label>
			<input type="checkbox" name="memberscontrol_settings[private_feed]" value="true" <?php checked( memberscontrol_is_private_feed() ); ?> />
			<?php esc_html_e( 'Show error message for feed items.', 'memberscontrol' ); ?>
		</label>
	<?php }

	/**
	 * Private feed error message field callback.
	 */
	public function field_private_feed_error() {

		wp_editor(
			memberscontrol_get_setting( 'private_feed_error' ),
			'memberscontrol_settings_private_feed_error',
			array(
				'textarea_name'    => 'memberscontrol_settings[private_feed_error]',
				'drag_drop_upload' => true,
				'editor_height'    => 250
			)
		);
	}

	/**
	 * Renders the settings page.
	 */
	public function template() { ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'memberscontrol_settings' ); ?>
			<?php do_settings_sections( 'memberscontrol-settings' ); ?>
			<?php submit_button( esc_attr__( 'Update Settings', 'memberscontrol' ), 'primary' ); ?>
		</form>

	<?php }

	/**
	 * Adds help tabs.
	 */
	public function add_help_tabs() {

		// Get the current screen.
		$screen = get_current_screen();

		// Roles/Caps help tab.
		$screen->add_help_tab(
			array(
				'id'       => 'roles-caps',
				'title'    => esc_html__( 'Role and Capabilities', 'memberscontrol' ),
				'callback' => array( $this, 'help_tab_roles_caps' )
			)
		);

		// Content Permissions help tab.
		$screen->add_help_tab(
			array(
				'id'       => 'content-permissions',
				'title'    => esc_html__( 'Content Permissions', 'memberscontrol' ),
				'callback' => array( $this, 'help_tab_content_permissions' )
			)
		);

		// Widgets help tab.
		$screen->add_help_tab(
			array(
				'id'       => 'sidebar-widgets',
				'title'    => esc_html__( 'Sidebar Widgets', 'memberscontrol' ),
				'callback' => array( $this, 'help_tab_sidebar_widgets' )
			)
		);

		// Private Site help tab.
		$screen->add_help_tab(
			array(
				'id'       => 'private-site',
				'title'    => esc_html__( 'Private Site', 'memberscontrol' ),
				'callback' => array( $this, 'help_tab_private_site' )
			)
		);

		// Set the help sidebar.
		$screen->set_help_sidebar( memberscontrol_get_help_sidebar_text() );
	}

	/**
	 * Displays the roles/caps help tab.
	 */
	public function help_tab_roles_caps() { ?>

		<p>
			<?php esc_html_e( 'The role manager allows you to manage roles on your site by giving you the ability to create, edit, and delete any role. Note that changes to roles do not change settings for the Members plugin. You are literally changing data in your WordPress database. This plugin feature merely provides an interface for you to make these changes.', 'memberscontrol' ); ?>
		</p>

		<p>
			<?php esc_html_e( 'The multiple user roles feature allows you to assign more than one role to each user from the edit user screen.', 'memberscontrol' ); ?>
		</p>

		<p>
			<?php esc_html_e( 'Tick the checkbox for denied capabilities to always take precedence over granted capabilities when there is a conflict. This is only relevant when using multiple roles per user.', 'memberscontrol' ); ?>
		</p>

		<p>
			<?php esc_html_e( 'Tick the checkbox to show human-readable capabilities when possible. Note that custom capabilities and capabilities from third-party plugins will show the machine-readable capability name unless they are registered.', 'memberscontrol' ); ?>
		</p>
	<?php }

	/**
	 * Displays the content permissions help tab.
	 */
	public function help_tab_content_permissions() { ?>

		<p>
			<?php printf( esc_html__( "The content permissions features adds a meta box to the edit post screen that allows you to grant permissions for who can read the post content based on the user's role. Only users of roles with the %s capability will be able to use this component.", 'memberscontrol' ), '<code>restrict_content</code>' ); ?>
		</p>
	<?php }

	/**
	 * Displays the sidebar widgets help tab.
	 */
	public function help_tab_sidebar_widgets() { ?>

		<p>
			<?php esc_html_e( "The sidebar widgets feature adds additional widgets for use in your theme's sidebars.", 'memberscontrol' ); ?>
		</p>
	<?php }

	/**
	 * Displays the private site help tab.
	 */
	public function help_tab_private_site() { ?>

		<p>
			<?php esc_html_e( 'The private site feature redirects all users who are not logged into the site to the login page, creating an entirely private site. You may also replace your feed content with a custom error message.', 'memberscontrol' ); ?>
		</p>
	<?php }
}
