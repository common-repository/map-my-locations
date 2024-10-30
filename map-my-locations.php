<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Map My Locations
 * Description:       Show your locations as pins on a map, Works with Google Maps or Mapbox
 * Version:           1.1
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       map-my-locations
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Current plugin version.
 */
define( 'MAP_MY_LOCATIONS_VERSION', '1.1' );

/**
 * The code that runs during plugin activation.
 */
function activate_map_my_locations() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-map-my-locations-activator.php';
    Map_My_Locations_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_map_my_locations() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-map-my-locations-deactivator.php';
    Map_My_Locations_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_map_my_locations' );
register_deactivation_hook( __FILE__, 'deactivate_map_my_locations' );

/**
 * The core plugin class that is used to define internationalization,
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-map-my-locations.php';

/**
 * Begins execution of the plugin.
 */
function run_map_my_locations() {

    $map_my_locations = new Map_My_Locations();
    $map_my_locations->run();

}
run_map_my_locations();
