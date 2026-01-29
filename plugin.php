<?php
/**
 * Plugin Name: Breakdance Icon Fix
 * Plugin URI: https://wpsix.com/plugins/breakdance-icon-fix/
 * Description: Fix for the Breakdance Icons plugin
 * Version: 1.0.0
 * Author: Bence Boruzs
 * Author URI: https://wpsix.com/
 * License: GPL2
 * Text Domain: breakdance-icon-fix
 */

use function Breakdance\Util\getDirectoryPathRelativeToPluginFolder;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

define( 'BREAKDANCE_ICON_FIX_VERSION', '1.0.0' );

add_action('breakdance_loaded', function () {
    \Breakdance\ElementStudio\registerSaveLocation(
        getDirectoryPathRelativeToPluginFolder( __DIR__ ) . '/macros',
        'BreakdanceIconFix',
        'macro',
        'Breakdance Icon Fix Macros',
        true,
    );
}, 11 );
