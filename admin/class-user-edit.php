<?php
/**
 * Handles custom functionality on the edit user screen, such as multiple user roles.
 */

namespace MembersControl\Admin;

/**
 * Edit user screen class.
 */
final class User_Edit {

	private static $instance;

	public function __construct() {

		// If multiple roles per user is not enabled, bail.
		if ( ! memberscontrol_multiple_user_roles_enabled() )
			return;

		// Only run our customization on the 'user-edit.php' page in the admin.
		add_action( 'load-user-edit.php', array( $this, 'load_user_edit' ) );
	}

	public function load_user_edit() {

		// Handle scripts and styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_footer',          array( $this, 'print_scripts' ), 25 );
		add_action( 'admin_head',            array( $this, 'print_styles' ) );

		add_action( 'show_user_profile', array( $this, 'profile_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'profile_fields' ) );

		// Must use `profile_update` to change role. Otherwise, WP will wipe it out.
		add_action( 'profile_update',  array( $this, 'role_update' ), 10, 2 );
	}

	public function profile_fields( $user ) {
		global $wp_roles;

		if ( ! current_user_can( 'promote_users' ) || ! current_user_can( 'edit_user', $user->ID ) )
			return;

		$user_roles = (array) $user->roles;

		$roles = memberscontrol_get_roles();

		ksort( $roles );

		wp_nonce_field( 'new_user_roles', 'memberscontrol_new_user_roles_nonce' ); ?>

		<h2><?php esc_html_e( 'Roles', 'memberscontrol' ); ?></h2>

		<table class="form-table">

			<tr>
				<th><?php esc_html_e( 'User Roles', 'memberscontrol' ); ?></th>

				<td>
					<div class="wp-tab-panel">
						<ul>
						<?php foreach ( $roles as $role ) : ?>

							<?php if ( memberscontrol_is_role_editable( $role->name ) ) :?>
							<li>
								<label>
									<input type="checkbox" name="memberscontrol_user_roles[]" value="<?php echo esc_attr( $role->name ); ?>" <?php checked( in_array( $role->name, $user_roles ) ); ?> />
									<?php echo esc_html( $role->get( 'label' ) ); ?>
								</label>
							</li>
							<?php endif; ?>

						<?php endforeach; ?>
						</ul>
					</div>
				</td>
			</tr>

		</table>
	<?php }

	/**
	 * Callback function for handling user role changes.  Note that we needed to execute this function
	 * on a different hook, `profile_update`.  Using the normal hooks on the edit user screen won't work
	 * because WP will wipe out the role.
	 */
	public function role_update( $user_id, $old_user_data ) {

		// If the current user can't promote users or edit this particular user, bail.
		if ( ! current_user_can( 'promote_users' ) || ! current_user_can( 'edit_user', $user_id ) )
			return;

		// Is this a role change?
		if ( ! isset( $_POST['memberscontrol_new_user_roles_nonce'] ) || ! wp_verify_nonce( $_POST['memberscontrol_new_user_roles_nonce'], 'new_user_roles' ) )
			return;

		// Create a new user object.
		//$user = new WP_User( $user_id );

		// If we have an array of roles.
		if ( ! empty( $_POST['memberscontrol_user_roles'] ) ) {

			// Get the current user roles.
			$old_roles = (array) $old_user_data->roles;

			// Sanitize the posted roles.
			$new_roles = array_map( 'memberscontrol_sanitize_role', $_POST['memberscontrol_user_roles'] );

			// Loop through the posted roles.
			foreach ( $new_roles as $new_role ) {

				// If the user doesn't already have the role, add it.
				if ( memberscontrol_is_role_editable( $new_role ) && ! in_array( $new_role, (array) $old_user_data->roles ) )
					$old_user_data->add_role( $new_role );
			}

			// Loop through the current user roles.
			foreach ( $old_roles as $old_role ) {

				// If the role is editable and not in the new roles array, remove it.
				if ( memberscontrol_is_role_editable( $old_role ) && ! in_array( $old_role, $new_roles ) )
					$old_user_data->remove_role( $old_role );
			}

		// If the posted roles are empty.
		} else {

			// Loop through the current user roles.
			foreach ( (array) $old_user_data->roles as $old_role ) {

				// Remove the role if it is editable.
				if ( memberscontrol_is_role_editable( $old_role ) )
					$old_user_data->remove_role( $old_role );
			}
		}
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue() {

		wp_enqueue_script( 'jquery' );
	}

	/**
	 * Enqueue the plugin admin CSS.
	 */
	public function print_scripts() { ?>

		<script>
		jQuery( document ).ready( function() {

			jQuery( '.user-role-wrap' ).remove();
		} );
		</script>

	<?php }

	/**
	 * Enqueue the plugin admin CSS.
	 */
	public function print_styles() { ?>

		<style type="text/css">.user-role-wrap{ display: none !important; }</style>

	<?php }

	/**
	 * Returns the instance.
	 */
	public static function get_instance() {

		if ( ! self::$instance )
			self::$instance = new self;

		return self::$instance;
	}
}

User_Edit::get_instance();
