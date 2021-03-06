<?php
/**
 * Handles permissions for post content, post excerpts, and post comments.  This is based on whether a user
 * has permission to view a post according to the settings provided by the plugin.
 */

# Enable the content permissions features.
add_action( 'after_setup_theme', 'memberscontrol_enable_content_permissions', 0 );

/**
 * Conditional check to determine if a post any permissions rules assigned
 * to it.
 */
function memberscontrol_has_post_permissions( $post_id = '' ) {

	return memberscontrol_has_post_roles( $post_id );
}

/**
 * Returns an array of the roles for a given post.
 */
function memberscontrol_get_post_roles( $post_id ) {
	return get_post_meta( $post_id, '_memberscontrol_access_role', false );
}

/**
 * Conditional check to determine if a post has roles assigned to it.
 */
function memberscontrol_has_post_roles( $post_id = '' ) {

	if ( ! $post_id )
		$post_id = get_the_ID();

	$roles = memberscontrol_get_post_roles( $post_id );

	return ! empty( $roles );
}

/**
 * Adds a single role to a post's access roles.
 */
function memberscontrol_add_post_role( $post_id, $role ) {

	return add_post_meta( $post_id, '_memberscontrol_access_role', $role, false );
}

/**
 * Removes a single role from a post's access roles.
 */
function memberscontrol_remove_post_role( $post_id, $role ) {

	return delete_post_meta( $post_id, '_memberscontrol_access_role', $role );
}

/**
 * Sets a post's access roles given an array of roles.
 */
function memberscontrol_set_post_roles( $post_id, $roles ) {
	global $wp_roles;

	// Get the current roles.
	$current_roles = get_post_meta( $post_id, '_memberscontrol_access_role', false );

	// Loop through new roles.
	foreach ( $roles as $role ) {

		// If new role is not already one of the current roles, add it.
		if ( ! in_array( $role, $current_roles ) )
			memberscontrol_add_post_role( $post_id, $role );
	}

	// Loop through all WP roles.
	foreach ( $wp_roles->role_names as $role => $name ) {

		// If the WP role is one of the current roles but not a new role, remove it.
		if ( ! in_array( $role, $roles ) && in_array( $role, $current_roles ) )
			memberscontrol_remove_post_role( $post_id, $role );
	}
}

/**
 * Deletes all of a post's access roles.
 */
function memberscontrol_delete_post_roles( $post_id ) {

	return delete_post_meta( $post_id, '_memberscontrol_access_role' );
}

/**
 * Adds required filters for the content permissions feature if it is active.
 */
function memberscontrol_enable_content_permissions() {
	global $wp_embed;

	// Only add filters if the content permissions feature is enabled and we're not in the admin.
	if ( memberscontrol_content_permissions_enabled() && !is_admin() ) {

		// Filter the content and exerpts.
		add_filter( 'the_content',      'memberscontrol_content_permissions_protect', 95 );
		add_filter( 'get_the_excerpt',  'memberscontrol_content_permissions_protect', 95 );
		add_filter( 'the_excerpt',      'memberscontrol_content_permissions_protect', 95 );
		add_filter( 'the_content_feed', 'memberscontrol_content_permissions_protect', 95 );
		add_filter( 'get_comment_text', 'memberscontrol_content_permissions_protect', 95 );

		// Filter the comments template to make sure comments aren't shown to users without access.
		add_filter( 'comments_template', 'memberscontrol_content_permissions_comments', 95 );

		// Use WP formatting filters on the post error message.
		add_filter( 'memberscontrol_post_error_message', array( $wp_embed, 'run_shortcode' ),   5 );
		add_filter( 'memberscontrol_post_error_message', array( $wp_embed, 'autoembed'     ),   5 );
		add_filter( 'memberscontrol_post_error_message',                   'wptexturize',       10 );
		add_filter( 'memberscontrol_post_error_message',                   'convert_smilies',   15 );
		add_filter( 'memberscontrol_post_error_message',                   'convert_chars',     20 );
		add_filter( 'memberscontrol_post_error_message',                   'wpautop',           25 );
		add_filter( 'memberscontrol_post_error_message',                   'do_shortcode',      30 );
		add_filter( 'memberscontrol_post_error_message',                   'shortcode_unautop', 35 );
	}
}

/**
 * Denies/Allows access to view post content depending on whether a user has permission to
 * view the content.
 */
function memberscontrol_content_permissions_protect( $content ) {

	$post_id = get_the_ID();

	return memberscontrol_can_current_user_view_post( $post_id ) ? $content : memberscontrol_get_post_error_message( $post_id );
}

/**
 * Disables the comments template if a user doesn't have permission to view the post the
 * comments are associated with.
 */
function memberscontrol_content_permissions_comments( $template ) {

	// Check if the current user has permission to view the comments' post.
	if ( ! memberscontrol_can_current_user_view_post( get_the_ID() ) ) {

		// Look for a 'comments-no-access.php' template in the parent and child theme.
		$has_template = locate_template( array( 'comments-no-access.php' ) );

		// If the template was found, use it.  Otherwise, fall back to the Members comments.php template.
		$template = $has_template ? $has_template : memberscontrol_plugin()->dir . 'templates/comments.php';

		// Allow devs to overwrite the comments template.
		$template = apply_filters( 'memberscontrol_comments_template', $template );
	}

	// Return the comments template filename.
	return $template;
}

/**
 * Gets the error message to display for users who do not have access to view the given post.
 * The function first checks to see if a custom error message has been written for the
 * specific post.  If not, it loads the error message set on the plugins settings page.
 */
function memberscontrol_get_post_error_message( $post_id ) {

	// Get the error message for the specific post.
	$message = memberscontrol_get_post_access_message( $post_id );

	// Use default error message if we don't have one for the post.
	if ( ! $message )
		$message = memberscontrol_get_setting( 'content_permissions_error' );

	// Return the error message.
	return apply_filters( 'memberscontrol_post_error_message', sprintf( '<div class="memberscontrol-access-error">%s</div>', $message ) );
}

/**
 * Returns the post access message.
 */
function memberscontrol_get_post_access_message( $post_id ) {

	return get_post_meta( $post_id, '_memberscontrol_access_error', true );
}

/**
 * Sets the post access message.
 */
function memberscontrol_set_post_access_message( $post_id, $message ) {

	return update_post_meta( $post_id, '_memberscontrol_access_error', $message );
}

/**
 * Deletes the post access message.
 */
function memberscontrol_delete_post_access_message( $post_id ) {

	return delete_post_meta( $post_id, '_memberscontrol_access_error' );
}

/**
 * Converts the meta values of the old '_role' post meta key to the newer '_memberscontrol_access_role' meta
 * key.  The reason for this change is to avoid any potential conflicts with other plugins/themes.  We're
 * now using a meta key that is extremely specific to the Members plugin.
 */
function memberscontrol_convert_old_post_meta( $post_id ) {

	// Check if there are any meta values for the '_role' meta key.
	$old_roles = get_post_meta( $post_id, '_role', false );

	// If roles were found, let's convert them.
	if ( !empty( $old_roles ) ) {

		// Delete the old '_role' post meta.
		delete_post_meta( $post_id, '_role' );

		// Check if there are any roles for the '_memberscontrol_access_role' meta key.
		$new_roles = get_post_meta( $post_id, '_memberscontrol_access_role', false );

		// If new roles were found, don't do any conversion.
		if ( empty( $new_roles ) ) {

			// Loop through the old meta values for '_role' and add them to the new '_memberscontrol_access_role' meta key.
			foreach ( $old_roles as $role )
				add_post_meta( $post_id, '_memberscontrol_access_role', $role, false );

			// Return the array of roles.
			return $old_roles;
		}
	}

	// Return false if we get to this point.
	return false;
}
