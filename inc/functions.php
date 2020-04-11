<?php
/**
 * General functions file for the plugin.
 */

/**
 * Validates a value as a boolean.  This way, strings such as "true" or "false" will be converted
 * to their correct boolean values.
 */
function memberscontrol_validate_boolean( $val ) {

	return filter_var( $val, FILTER_VALIDATE_BOOLEAN );
}


/**
 * Helper function for sorting objects by priority.
 */
function memberscontrol_priority_sort( $a, $b ) {

	return $a->priority - $b->priority;
}
