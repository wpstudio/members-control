<?php
/**
 * Outputs a custom settings view under "Admin Access" on the Members plugin
 * settings page.
 */

namespace MembersControl\AddOns\AdminAccess;

use MembersControl\Admin\View;

/**
 * Sets up and handles the general settings view.
 */
class View_Settings extends View {

	function register_settings() {

		// Register the setting.
		register_setting( 'memberscontrol_admin_access_settings', 'memberscontrol_admin_access_settings', array( $this, 'validate_settings' ) );

		/* === Settings Sections === */

		add_settings_section( 'general', esc_html__( 'Admin Access', 'memberscontrol' ), array( $this, 'section_general' ), app()->namespace . '/settings' );

		/* === Settings Fields === */

		add_settings_field( 'select_roles', esc_html__( 'Select Roles', 'memberscontrol' ), array( $this, 'field_select_roles' ), app()->namespace . '/settings', 'general' );
		add_settings_field( 'redirect',     esc_html__( 'Redirect',     'memberscontrol' ), array( $this, 'field_redirect'     ), app()->namespace . '/settings', 'general' );
		add_settings_field( 'toolbar',      esc_html__( 'Toolbar',      'memberscontrol' ), array( $this, 'field_toolbar'      ), app()->namespace . '/settings', 'general' );
	}

	function validate_settings( $settings ) {

		// Validate selected roles.
		//
		// Note that it's possible for `$settings['roles']` to not be set
		// when no roles at all are selected.

		if ( empty( $settings['roles'] ) ) {
			$settings['roles'] = array();
		}

		foreach ( $settings['roles'] as $key => $role ) {

			if ( ! memberscontrol_role_exists( $role ) )
				unset( $settings['roles'][ $key ] );
		}

		// Escape URLs.
		$settings['redirect_url'] = esc_url_raw( $settings['redirect_url'] );

		if ( ! $settings['redirect_url'] )
			$settings['redirect_url'] = esc_url_raw( home_url() );

		// Handle checkboxes.
		$settings['disable_toolbar'] = ! empty( $settings['disable_toolbar'] ) ? true : false;

		return $settings;
	}

	public function section_general() { ?>

		<p class="description">
			<?php esc_html_e( 'Control admin access by user role.', 'memberscontrol' ); ?>
		</p>
	<?php }

	public function field_select_roles() { ?>

		<p class="description">
			<?php esc_html_e( 'Select which roles should have admin access.', 'memberscontrol' ); ?>
		</p>

		<div class="wp-tab-panel">

		<ul>
			<?php foreach ( memberscontrol_get_roles() as $role ) :

				$disabled = in_array( $role->name, get_roles_with_permanent_access() ); ?>

				<li>
					<label>
						<?php if ( ! $disabled ) : ?>

							<input type="checkbox" name="memberscontrol_admin_access_settings[roles][]" value="<?php echo esc_attr( $role->name ); ?>" <?php checked( role_has_access( $role->name ) ); ?> />

						<?php else : ?>

							<input readonly="readonly" disabled="disabled" type="checkbox" name="memberscontrol_admin_access_settings[roles][]" value="<?php echo esc_attr( $role->name ); ?>" <?php checked( role_has_access( $role->name ) ); ?> />

						<?php endif; ?>

						<?php echo esc_html( $role->label ); ?>
					</label>
				</li>

			<?php endforeach; ?>
		</ul>

		</div><!-- .wp-tab-panel -->
	<?php }

	/**
	 * Outputs the redirect URL field.
	 */
	public function field_redirect() { ?>

		<p>
			<label>
				<?php esc_html_e( 'Redirect users without access to:', 'memberscontrol' ); ?>

				<input type="url" name="memberscontrol_admin_access_settings[redirect_url]" value="<?php echo esc_attr( get_redirect_url() ); ?>" />
			</label>
		</p>
	<?php }

	/**
	 * The toolbar field callback.
	 */
	public function field_toolbar() { ?>

		<p>
			<label>
				<input type="checkbox" name="memberscontrol_admin_access_settings[disable_toolbar]" value="1" <?php checked( disable_toolbar() ); ?> />

				<?php esc_html_e( 'Disable toolbar on the front end for users without admin access.', 'memberscontrol' ); ?>
			</label>
		</p>
	<?php }

	/**
	 * Renders the settings page.
	 */
	public function template() { ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'memberscontrol_admin_access_settings' ); ?>
			<?php do_settings_sections( app()->namespace . '/settings' ); ?>
			<?php submit_button( esc_attr__( 'Update Settings', 'memberscontrol' ), 'primary' ); ?>
		</form>

	<?php }
}
