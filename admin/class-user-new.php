<?php
/**
 * Handles custom functionality on the new user screen, such as multiple user roles.
 */

namespace MembersControl\Admin;

/**
 * Edit user screen class.
 */
final class User_New {

	private static $instance;

	private function __construct() {}

	private function setup_actions() {

		// If multiple roles per user is not enabled, bail.
		//
		// @since 2.0.1 Added a check to not run on multisite.
		// @link https://github.com/justintadlock/members/issues/153
		if ( ! memberscontrol_multiple_user_roles_enabled() || is_multisite() )
			return;

		// Only run our customization on the 'user-edit.php' page in the admin.
		add_action( 'load-user-new.php', array( $this, 'load' ) );

		// Sets the new user's roles.
		add_action( 'user_register', array( $this, 'user_register' ), 5 );
	}

	public function load() {

		// Adds the profile fields.
		add_action( 'user_new_form', array( $this, 'profile_fields' ) );

		// Handle scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_footer',          array( $this, 'print_scripts' ), 25 );
	}

	public function profile_fields() {

		if ( ! current_user_can( 'promote_users' ) )
			return;

		// Get the default user roles.
		$new_user_roles = apply_filters( 'memberscontrol_default_user_roles', array( get_option( 'default_role' ) ) );

		// If the form was submitted but didn't go through, get the posted roles.
		if ( isset( $_POST['createuser'] ) && ! empty( $_POST['memberscontrol_user_roles'] ) )
			$new_user_roles = array_map( 'memberscontrol_sanitize_role', $_POST['memberscontrol_user_roles'] );

		$roles = memberscontrol_get_roles();

		ksort( $roles );

		wp_nonce_field( 'new_user_roles', 'memberscontrol_new_user_roles_nonce' ); ?>

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
									<input type="checkbox" name="memberscontrol_user_roles[]" value="<?php echo esc_attr( $role->name ); ?>" <?php checked( in_array( $role->name, $new_user_roles ) ); ?> />
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
	 * Handles the new user's roles once the form has been submitted.
	 */
	public function user_register( $user_id ) {

		// If the current user can't promote users or edit this particular user, bail.
		if ( ! current_user_can( 'promote_users' ) )
			return;

		// Is this a role change?
		if ( ! isset( $_POST['memberscontrol_new_user_roles_nonce'] ) || ! wp_verify_nonce( $_POST['memberscontrol_new_user_roles_nonce'], 'new_user_roles' ) )
			return;

		// Create a new user object.
		$user = new \WP_User( $user_id );

		// If we have an array of roles.
		if ( ! empty( $_POST['memberscontrol_user_roles'] ) ) {

			// Get the current user roles.
			$old_roles = (array) $user->roles;

			// Sanitize the posted roles.
			$new_roles = array_map( 'memberscontrol_sanitize_role', $_POST['memberscontrol_user_roles'] );

			// Loop through the posted roles.
			foreach ( $new_roles as $new_role ) {

				// If the user doesn't already have the role, add it.
				if ( memberscontrol_is_role_editable( $new_role ) && ! in_array( $new_role, (array) $user->roles ) )
					$user->add_role( $new_role );
			}

			// Loop through the current user roles.
			foreach ( $old_roles as $old_role ) {

				// If the role is editable and not in the new roles array, remove it.
				if ( memberscontrol_is_role_editable( $old_role ) && ! in_array( $old_role, $new_roles ) )
					$user->remove_role( $old_role );
			}

		// If the posted roles are empty.
		} else {

			// Loop through the current user roles.
			foreach ( (array) $user->roles as $old_role ) {

				// Remove the role if it is editable.
				if ( memberscontrol_is_role_editable( $old_role ) )
					$user->remove_role( $old_role );
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

			var roles_dropdown = jQuery('select#role');
			roles_dropdown.closest( 'tr' ).remove();
		} );
		</script>

	<?php }

	/**
	 * Returns the instance.
	 */
	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new self;

			self::$instance->setup_actions();
		}

		return self::$instance;
	}
}

User_New::get_instance();
