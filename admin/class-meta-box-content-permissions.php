<?php
/**
 * Content permissions meta box.
 */

namespace MembersControl\Admin;

/**
 * Class to handle the content permissios meta box and saving the meta.
 */
final class Meta_Box_Content_Permissions {

	private static $instance;

	public $is_new_post = false;

	protected function __construct() {

		// If content permissions is disabled, bail.
		if ( ! memberscontrol_content_permissions_enabled() )
			return;

		add_action( 'load-post.php',     array( $this, 'load' ) );
		add_action( 'load-post-new.php', array( $this, 'load' ) );
	}

	 */
	public function load() {

		// Make sure meta box is allowed for this post type.
		if ( ! $this->maybe_enable() )
			return;

		// Is this a new post?
		$this->is_new_post = 'load-post-new.php' === current_action();

		// Enqueue scripts/styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		// Add custom meta boxes.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		// Save metadata on post save.
		add_action( 'save_post', array( $this, 'update' ), 10, 2 );
	}

	/**
	 * Enqueues scripts styles.
	 */
	 public function enqueue() {

		wp_enqueue_script( 'memberscontrol-edit-post' );
		wp_enqueue_style( 'memberscontrol-admin' );
	}

	/**
	 * Adds the meta box.
	 */
	public function add_meta_boxes( $post_type ) {

		// If the current user can't restrict content, bail.
		if ( ! current_user_can( 'restrict_content' ) )
			return;

		// Add the meta box.
		add_meta_box( 'memberscontrol-cp', esc_html__( 'Content Permissions', 'memberscontrol' ), array( $this, 'meta_box' ), $post_type, 'advanced', 'high' );
	}

	/**
	 * Checks if Content Permissions should appear for the given post type.
	 */
	public function maybe_enable() {

		// Get the post type object.
		$type = get_post_type_object( get_current_screen()->post_type );

		// Only enable for public post types and non-attachments by default.
		$enable = 'attachment' !== $type->name && $type->public;

		return apply_filters( "memberscontrol_enable_{$type->name}_content_permissions", $enable );
	}

	/**
	 * Outputs the meta box HTML.
	 */
	public function meta_box( $post ) {
		global $wp_roles;

		// Get roles and sort.
		 $_wp_roles = $wp_roles->role_names;
		asort( $_wp_roles );

		// Get the roles saved for the post.
		$roles = get_post_meta( $post->ID, '_memberscontrol_access_role', false );

		if ( ! $roles && $this->is_new_post )
			$roles = apply_filters( 'memberscontrol_default_post_roles', array(), $post->ID );

		// Convert old post meta to the new system if no roles were found.
		if ( empty( $roles ) )
			$roles = memberscontrol_convert_old_post_meta( $post->ID );

		// Nonce field to validate on save.
		wp_nonce_field( 'memberscontrol_cp_meta_nonce', 'memberscontrol_cp_meta' );

		// Hook for firing at the top of the meta box.
		do_action( 'memberscontrol_cp_meta_box_before', $post ); ?>

		<div class="memberscontrol-tabs members-cp-tabs">

			<ul class="memberscontrol-tab-nav">
				<li class="memberscontrol-tab-title">
					<a href="#memberscontrol-tab-cp-roles">
						<i class="dashicons dashicons-groups"></i>
						<span class="label"><?php esc_html_e( 'Roles', 'memberscontrol' ); ?></span>
					</a>
				</li>
				<li class="memberscontrol-tab-title">
					<a href="#memberscontrol-tab-cp-message">
						<i class="dashicons dashicons-edit"></i>
						<span class="label"><?php esc_html_e( 'Error Message', 'memberscontrol' ); ?></span>
					</a>
				</li>
			</ul>

			<div class="memberscontrol-tab-wrap">

				<div id="memberscontrol-tab-cp-roles" class="memberscontrol-tab-content">

					<span class="memberscontrol-tabs-label">
						<?php esc_html_e( 'Limit access to the content to users of the selected roles.', 'memberscontrol' ); ?>
					</span>

					<div class="memberscontrol-cp-role-list-wrap">

						<ul class="memberscontrol-cp-role-list">

						<?php foreach ( $_wp_roles as $role => $name ) : ?>
							<li>
								<label>
									<input type="checkbox" name="memberscontrol_access_role[]" <?php checked( is_array( $roles ) && in_array( $role, $roles ) ); ?> value="<?php echo esc_attr( $role ); ?>" />
									<?php echo esc_html( memberscontrol_translate_role( $role ) ); ?>
								</label>
							</li>
						<?php endforeach; ?>

						</ul>
					</div>

					<span class="memberscontrol-tabs-description">
						<?php printf( esc_html__( 'If no roles are selected, everyone can view the content. The author, any users who can edit the content, and users with the %s capability can view the content regardless of role.', 'memberscontrol' ), '<code>restrict_content</code>' ); ?>
					</span>

				</div>

				<div id="memberscontrol-tab-cp-message" class="memberscontrol-tab-content">

					<?php wp_editor(
						get_post_meta( $post->ID, '_memberscontrol_access_error', true ),
						'memberscontrol_access_error',
						array(
							'drag_drop_upload' => true,
							'editor_height'    => 200
						)
					); ?>

				</div>

			</div><!-- .memberscontrol-tab-wrap -->

		</div><!-- .memberscontrol-tabs --><?php

		// Hook that fires at the end of the meta box.
		do_action( 'memberscontrol_cp_meta_box_after', $post );
	}

	/**
	 * Saves the post meta.
	 */
	public function update( $post_id, $post = '' ) {

		$do_autosave = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );

		if ( $do_autosave || $is_autosave || $is_revision )
			return;

		// Fix for attachment save issue in WordPress 3.5.
		// @link http://core.trac.wordpress.org/ticket/21963
		if ( ! is_object( $post ) )
			$post = get_post();

		// Verify the nonce.
		if ( ! isset( $_POST['memberscontrol_cp_meta'] ) || ! wp_verify_nonce( $_POST['memberscontrol_cp_meta'], 'memberscontrol_cp_meta_nonce' ) )
			return;

		/* === Roles === */

		// Get the current roles.
		$current_roles = memberscontrol_get_post_roles( $post_id );

		// Get the new roles.
		$new_roles = isset( $_POST['memberscontrol_access_role'] ) ? $_POST['memberscontrol_access_role'] : '';

		// If we have an array of new roles, set the roles.
		if ( is_array( $new_roles ) )
			memberscontrol_set_post_roles( $post_id, array_map( 'memberscontrol_sanitize_role', $new_roles ) );

		// Else, if we have current roles but no new roles, delete them all.
		elseif ( !empty( $current_roles ) )
			memberscontrol_delete_post_roles( $post_id );

		/* === Error Message === */

		// Get the old access message.
		$old_message = memberscontrol_get_post_access_message( $post_id );

		// Get the new message.
		$new_message = isset( $_POST['memberscontrol_access_error'] ) ? wp_kses_post( wp_unslash( $_POST['memberscontrol_access_error'] ) ) : '';

		// If we have don't have a new message but do have an old one, delete it.
		if ( '' == $new_message && $old_message )
			memberscontrol_delete_post_access_message( $post_id );

		// If the new message doesn't match the old message, set it.
		else if ( $new_message !== $old_message )
			memberscontrol_set_post_access_message( $post_id, $new_message );
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

Meta_Box_Content_Permissions::get_instance();
