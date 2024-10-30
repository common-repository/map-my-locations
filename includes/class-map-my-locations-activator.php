<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Map_My_Locations
 * @subpackage Map_My_Locations/includes
 * @author     Your Name <email@example.com>
 */
class Map_My_Locations_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		self::setup_default_cpt();

		self::setup_geo_table();
	
	}

	public static function setup_default_cpt(){

		$map_my_locations = new Map_My_Locations();

		// save inital custom post type
		$Map_My_Locations_Admin = new Map_My_Locations_Admin($map_my_locations->get_plugin_name());

		$options = $Map_My_Locations_Admin->get_options();

		if( !in_array('mml_location', wp_list_pluck( $options['location_cpts'], 'name' ) ) ){

			$options['location_cpts'][] = array(
				'default'	=> true,
				'label' 	=> 'Locations',
				'name' 		=> 'mml_location',
				'public' 	=> true
			);

			$Map_My_Locations_Admin->update_options($options);
		}

	}

	public static function setup_geo_table(){

		// setup custom table
	    global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	 
	    $charset_collate = $wpdb->get_charset_collate();
	 
		$table_name = $wpdb->prefix . 'mml_search_geodata';
	 
	    $sql = "CREATE TABLE $table_name (
	        id mediumint(9) NOT NULL AUTO_INCREMENT,
	        post_id BIGINT NULL UNIQUE,
	        lat DECIMAL(9,6) NULL,
	        lng DECIMAL(9,6) NULL,
	        UNIQUE KEY id (id)
	    ) {$charset_collate};";
	 
	    dbDelta( $sql );	

	}

}
