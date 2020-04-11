<?php
/**
 * Shortcodes for use within posts and other shortcode-aware areas.
 */

# Add shortcodes.
add_action( 'init', 'memberscontrol_register_shortcodes' );

/**
 * Registers shortcodes.
 */
function memberscontrol_register_shortcodes() {

	// Add the `[memberscontrol_login_form]` shortcode.
	add_shortcode( 'memberscontrol_login_form', 'memberscontrol_login_form_shortcode' );
	add_shortcode( 'login-form',         'memberscontrol_login_form_shortcode' ); // @deprecated 1.0.0

	// Add the `[memberscontrol_access]` shortcode.
	add_shortcode( 'memberscontrol_access', 'memberscontrol_access_check_shortcode' );
	add_shortcode( 'access',         'memberscontrol_access_check_shortcode' ); // @deprecated 1.0.0

	// Add the `[memberscontrol_feed]` shortcode.
	add_shortcode( 'memberscontrol_feed', 'memberscontrol_feed_shortcode' );
	add_shortcode( 'feed',         'memberscontrol_feed_shortcode' ); // @deprecated 1.0.0

	// Add the `[memberscontrol_logged_in]` shortcode.
	add_shortcode( 'memberscontrol_logged_in', 'memberscontrol_is_user_logged_in_shortcode' );
	add_shortcode( 'is_user_logged_in', 'memberscontrol_is_user_logged_in_shortcode' ); // @deprecated 1.0.0

	// Add the `[memberscontrol_not_logged_in]` shortcode.
	add_shortcode( 'memberscontrol_not_logged_in', 'memberscontrol_not_logged_in_shortcode' );

	// @deprecated 0.2.0.
	add_shortcode( 'get_avatar', 'memberscontrol_get_avatar_shortcode' );
	add_shortcode( 'avatar',     'memberscontrol_get_avatar_shortcode' );
}

/**
 * Displays content if the user viewing it is currently logged in. This also blocks content
 * from showing in feeds.
 */
function memberscontrol_is_user_logged_in_shortcode( $attr, $content = null ) {

	return is_feed() || ! is_user_logged_in() || is_null( $content ) ? '' : do_shortcode( $content );
}

/**
 * Displays content if the user viewing it is not currently logged in.
 */
function memberscontrol_not_logged_in_shortcode( $attr, $content = null ) {

	return is_user_logged_in() || is_null( $content ) ? '' : do_shortcode( $content );
}

/**
 * Content that should only be shown in feed readers.  Can be useful for displaying
 * feed-specific items.
 */
function memberscontrol_feed_shortcode( $attr, $content = null ) {

	return ! is_feed() || is_null( $content ) ? '' : do_shortcode( $content );
}

/**
 * Provide/restrict access to specific roles or capabilities. This content should not be shown
 * in feeds.  Note that capabilities are checked first.  If a capability matches, any roles
 * added will *not* be checked.  Users should choose between using either capabilities or roles
 * for the check rather than both.  The best option is to always use a capability.
 */
function memberscontrol_access_check_shortcode( $attr, $content = null ) {

	// If there's no content or if viewing a feed, return an empty string.
	if ( is_null( $content ) || is_feed() )
		return '';

	$user_can = false;

	// Set up the default attributes.
	$defaults = array(
		'capability' => '',  // Single capability or comma-separated multiple capabilities.
		'role'       => '',  // Single role or comma-separated multiple roles.
		'user_id'    => '',  // Single user ID or comma-separated multiple IDs.
		'user_name'  => '',  // Single user name or comma-separated multiple names.
		'user_email' => '',  // Single user email or comma-separated multiple emails.
		'operator'   => 'or' // Only the `!` operator is supported for now.  Everything else falls back to `or`.
	);

	// Merge the input attributes and the defaults.
	$attr = shortcode_atts( $defaults, $attr, 'memberscontrol_access' );

	// Get the operator.
	$operator = strtolower( $attr['operator'] );

	// If the current user has the capability, show the content.
	if ( $attr['capability'] ) {

		// Get the capabilities.
		$caps = explode( ',', $attr['capability'] );

		if ( '!' === $operator )
			return memberscontrol_current_user_can_any( $caps ) ? '' : do_shortcode( $content );

		return memberscontrol_current_user_can_any( $caps ) ? do_shortcode( $content ) : '';
	}

	// If the current user has the role, show the content.
	if ( $attr['role'] ) {

		// Get the roles.
		$roles = explode( ',', $attr['role'] );

		if ( '!' === $operator )
			return memberscontrol_current_user_has_role( $roles ) ? '' : do_shortcode( $content );

		return memberscontrol_current_user_has_role( $roles ) ? do_shortcode( $content ) : '';
	}

	$user_id = 0;
	$user_name = $user_email = '';

	if ( is_user_logged_in() ) {

		$user       = wp_get_current_user();
		$user_id    = get_current_user_id();
		$user_name  = $user->user_login;
		$user_email = $user->user_email;
	}

	// If the current user has one of the user ids.
	if ( $attr['user_id'] ) {

		// Get the user IDs.
		$ids = array_map( 'trim', explode( ',', $attr['user_id'] ) );

		if ( '!' === $operator ) {
			return in_array( $user_id, $ids ) ? '' : do_shortcode( $content );
		}

		return in_array( $user_id, $ids ) ? do_shortcode( $content ) : '';
	}

	// If the current user has one of the user names.
	if ( $attr['user_name'] ) {

		// Get the user names.
		$names = array_map( 'trim', explode( ',', $attr['user_name'] ) );

		if ( '!' === $operator ) {
			return in_array( $user_name, $names ) ? '' : do_shortcode( $content );
		}

		return in_array( $user_name, $names ) ? do_shortcode( $content ) : '';
	}

	// If the current user has one of the user emails.
	if ( $attr['user_email'] ) {

		// Get the user emails.
		$emails = array_map( 'trim', explode( ',', $attr['user_email'] ) );

		if ( '!' === $operator ) {
			return in_array( $user_email, $emails ) ? '' : do_shortcode( $content );
		}

		return in_array( $user_email, $emails ) ? do_shortcode( $content ) : '';
	}

	// Return an empty string if we've made it to this point.
	return '';
}

/**
 * Displays a login form.
 */
function memberscontrol_login_form_shortcode() {

	return wp_login_form( array( 'echo' => false ) );
}
