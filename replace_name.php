<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              WebDuck
 * @since             1.0.0
 * @package           Replace_name
 *
 * @wordpress-plugin
 * Plugin Name:       display_name_replace
 * Plugin URI:        replace_name
 * Description:       description_replace.
 * Version:           1.0.0
 * Author:            WebDuck
 * Author URI:        WebDuck
 * License:           All rights reserved © WebDuck 2020
 * License URI:       http://webduck.co.il
 * Text Domain:       replace_name
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
define( 'REPLACE_NAME_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-replace_name-activator.php
 */
function activate_replace_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-replace_name-activator.php';
	Replace_name_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-replace_name-deactivator.php
 */
function deactivate_replace_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-replace_name-deactivator.php';
	Replace_name_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_replace_name' );
register_deactivation_hook( __FILE__, 'deactivate_replace_name' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-replace_name.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_replace_name() {

	$plugin = new Replace_name();
	$plugin->run();

}
run_replace_name();
