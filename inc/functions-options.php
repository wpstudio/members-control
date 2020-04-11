<?php
/**
 * Functions for handling plugin options.
 */

/**
 * Conditional check to see if the role manager is enabled.
 */
function memberscontrol_role_manager_enabled() {

	return apply_filters( 'memberscontrol_role_manager_enabled', memberscontrol_get_setting( 'role_manager' ) );
}

/**
 * Conditional check to see if denied capabilities should overrule granted capabilities when
 * a user has multiple roles with conflicting cap definitions.
 */
function memberscontrol_explicitly_deny_caps() {

	return apply_filters( 'memberscontrol_explicitly_deny_caps', memberscontrol_get_setting( 'explicit_denied_caps' ) );
}

/**
 * Whether to show human-readable caps.
 */
function memberscontrol_show_human_caps() {

	return apply_filters( 'memberscontrol_show_human_caps', memberscontrol_get_setting( 'show_human_caps' ) );
}

/**
 * Conditional check to see if the role manager is enabled.
 */
function memberscontrol_multiple_user_roles_enabled() {

	return apply_filters( 'memberscontrol_multiple_roles_enabled', memberscontrol_get_setting( 'multi_roles' ) );
}

/**
 * Conditional check to see if content permissions are enabled.
 */
function memberscontrol_content_permissions_enabled() {

	return apply_filters( 'memberscontrol_content_permissions_enabled', memberscontrol_get_setting( 'content_permissions' ) );
}

/**
 * Conditional check to see if login widget is enabled.
 */
function memberscontrol_login_widget_enabled() {

	return apply_filters( 'memberscontrol_login_widget_enabled', true );
}

/**
 * Conditional check to see if users widget is enabled.
 */
function memberscontrol_users_widget_enabled() {

	return apply_filters( 'memberscontrol_users_widget_enabled', true );
}

/**
 * Gets a setting from from the plugin settings in the database.
 */
function memberscontrol_get_setting( $option = '' ) {

	$defaults = memberscontrol_get_default_settings();

	$settings = wp_parse_args( get_option( 'memberscontrol_settings', $defaults ), $defaults );

	return isset( $settings[ $option ] ) ? $settings[ $option ] : false;
}

/**
 * Returns an array of the default plugin settings.
 */
function memberscontrol_get_default_settings() {

	return array(

		// @since 0.1.0
		'role_manager'        => 1,
		'content_permissions' => 1,
		'private_blog'        => 0,

		// @since 0.2.0
		'private_feed'              => 0,
		'content_permissions_error' => esc_html__( 'Sorry, but you do not have permission to view this content.', 'memberscontrol' ),
		'private_feed_error'        => esc_html__( 'You must be logged into the site to view this content.',      'memberscontrol' ),

		// @since 1.0.0
		'explicit_denied_caps' => true,
		'multi_roles'          => true,

		// @since 2.0.0
		'show_human_caps'      => true,
		'private_rest_api'     => false,
	);
}
