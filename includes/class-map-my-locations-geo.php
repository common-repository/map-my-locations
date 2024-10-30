<?php

/**
 * Geo class
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Map_My_Locations
 * @subpackage Map_My_Locations/includes
 */

class Map_My_Locations_GEO {

	private $plugin_name;
	private $version;
	private $table;

	public function __construct($plugin_name, $version){

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		global $wpdb;
		$this->table = $wpdb->prefix . "mml_search_geodata"; 
	}

	public function store($post_id, $lat, $lng){

		if( $this->check_exists( $post_id ) ) {
			$return = $this->update( $post_id, $lat, $lng );
		} else {
			$return = $this->insert( $post_id, $lat, $lng );
		}

		return $return;		
	}

	public function insert($post_id, $lat, $lng) {
		global $wpdb;
	 
		$wpdb->insert(
			$this->table,
			array(
				'post_id' => $post_id,
				'lat'     => $lat,
				'lng'     => $lng,
			),
			array(
				'%d',
				'%f',
				'%f'
			)
		);
		
		return true;
		
	}	
	
	public function check_exists($post_id) {
	 
		global $wpdb;
	 
		//Check data validity
		if( !is_int($post_id) ){
			return false;
		}
	 
		$sql = "SELECT * FROM $this->table WHERE post_id = {$post_id}";
		$geodata = $wpdb->get_row($sql);
	 
		 if($geodata) {
			return true;
		 }
		 
		 return false;
		 
	}	

	public function update($post_id, $lat, $lng) {
	 
		global $wpdb;	 
	 
		$wpdb->update(
			$this->table,
			array(
				'lat'     => $lat,
				'lng'     => $lng,
			),
			array(
				'post_id' => $post_id,
			),
			array(
				'%f',
				'%f'
			)
		);
		
		return true;
		
	}	

}