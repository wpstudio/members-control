<?php

namespace MembersControl\CategoryAndTagCaps;

use MembersControl\CategoryAndTagCaps\Activator;

// Don't execute code if file is accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Registers the plugin activation callback.
 */
register_activation_hook( __FILE__, function() {
	require_once 'src/Activator.php';
	Activator::activate();
} );

# Load plugin files.
require_once 'src/functions-filters.php';
