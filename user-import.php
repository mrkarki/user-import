<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.nabinkarki2.com.np
 * @since             1.0.0
 * @package           User_Import
 *
 * @wordpress-plugin
 * Plugin Name:       User Import
 * Plugin URI:        https://www.nabinkarki2.com.np
 * Description:       This plugin enables the CSV upload to insert users
 * Version:           1.0.0
 * Author:            Nabin karki
 * Author URI:        https://www.nabinkarki2.com.np
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       user-import
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'USER_IMPORT_VERSION', '1.0.0' );

define('PLUGIN_PATH',plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-user-import-activator.php
 */
function activate_user_import() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-user-import-activator.php';
	User_Import_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-user-import-deactivator.php
 */
function deactivate_user_import() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-user-import-deactivator.php';
	User_Import_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_user_import' );
register_deactivation_hook( __FILE__, 'deactivate_user_import' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-user-import.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_user_import() {

	$plugin = new User_Import();
	$plugin->run();

}
run_user_import();
