<?php
/**
 * Publish/Update role meta box.
 */
namespace MembersControl\Admin;

/**
 * Class to handle the role meta box edit/new role screen.
 */
final class Meta_Box_Publish_Role {

	private static $instance;

	protected function __construct() {

		add_action( 'memberscontrol_load_role_edit', array( $this, 'load' ) );
		add_action( 'memberscontrol_load_role_new',  array( $this, 'load' ) );
	}

	public function load() {

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}

	public function add_meta_boxes( $screen_id ) {

		add_meta_box( 'submitdiv', esc_html__( 'Role', 'memberscontrol' ), array( $this, 'meta_box' ), $screen_id, 'side', 'high' );
	}

	public function meta_box( $role ) {

		// Set up some defaults for new roles.
		$is_editable = true;
		$user_count  = 0;
		$grant_count = 0;
		$deny_count  = 0;

		// If we're editing a role, overwrite the defaults.
		if ( $role ) {
			$is_editable = memberscontrol_is_role_editable( $role->name );
			$user_count  = memberscontrol_get_role_user_count( $role->name );
			$grant_count = memberscontrol_get_role_granted_cap_count( $role->name );
			$deny_count  = memberscontrol_get_role_denied_cap_count( $role->name );
		} ?>

		<div class="submitbox" id="submitpost">

			<div id="misc-publishing-actions">

				<div class="misc-pub-section misc-pub-section-users">
					<i class="dashicons dashicons-admin-users"></i>
					<?php if ( 0 < $user_count && current_user_can( 'list_users' ) ) : ?>

						<a href="<?php echo esc_url( add_query_arg( 'role', $role->name, admin_url( 'users.php' ) ) ); ?>"><?php echo esc_html(
							sprintf(
								_n( '%s User', '%s Users', absint( $user_count ), 'memberscontrol' ),
								number_format_i18n( $user_count )
							)
						); ?></a>

					<?php else : ?>
						<?php esc_html_e( 'Users:', 'memberscontrol' ); ?>
						<strong class="user-count"><?php echo number_format_i18n( $user_count ); ?></strong>
					<?php endif; ?>
				</div>

				<div class="misc-pub-section misc-pub-section-granted">
					<i class="dashicons dashicons-yes"></i>
					<?php esc_html_e( 'Granted:', 'memberscontrol' ); ?>
					<strong class="granted-count"><?php echo number_format_i18n( $grant_count ); ?></strong>
				</div>

				<div class="misc-pub-section misc-pub-section-denied">
					<i class="dashicons dashicons-no"></i>
					<?php esc_html_e( 'Denied:', 'memberscontrol' ); ?>
					<strong class="denied-count"><?php echo number_format_i18n( $deny_count ); ?></strong>
				</div>

			</div><!-- #misc-publishing-actions -->

			<div id="major-publishing-actions">

				<div id="delete-action">

					<?php if ( $is_editable && $role ) : ?>
						<a class="submitdelete deletion memberscontrol-delete-role-link" href="<?php echo esc_url( memberscontrol_get_delete_role_url( $role->name ) ); ?>"><?php echo esc_html_x( 'Delete', 'delete role', 'memberscontrol' ); ?></a>
					<?php endif; ?>
				</div>

				<div id="publishing-action">

					<?php if ( $is_editable ) : ?>
						<?php submit_button( $role ? esc_attr__( 'Update', 'memberscontrol' ) : esc_attr__( 'Add Role', 'memberscontrol' ), 'primary', 'publish', false, array( 'id' => 'publish' ) ); ?>
					<?php endif; ?>

				</div><!-- #publishing-action -->

				<div class="clear"></div>

			</div><!-- #major-publishing-actions -->

		</div><!-- .submitbox -->
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

Meta_Box_Publish_Role::get_instance();
