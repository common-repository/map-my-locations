<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Map_My_Locations
 * @subpackage Map_My_Locations/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * @package    Map_My_Locations
 * @subpackage Map_My_Locations/public
 * @author     Your Name <email@example.com>
 */
class Map_My_Locations_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;
	private $plugin_slug;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_slug = str_replace('-', '_', $plugin_name);

		$this->options = $this->get_options();
	}

	public function get_options(){
		return get_option( $this->plugin_slug.'_options', array('location_cpts'=>array(),'map_provider'=>array('provider'=>'mapbox')) );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/public.js', array( 'jquery' ), $this->version, true );

		// get options
		wp_localize_script( $this->plugin_name, $this->plugin_slug.'_options', $this->options );
	}

	/*
	 Function to display a map based on a shortcode and the relevant params
	*/
	public function display_map($atts) {

		$attributes = shortcode_atts( array(
			'title' 	=> 'My new map',
			'map' 		=> 'mml_location',
			'style' 	=> 'light-v10',
			'list' 		=> 'false',
			'orderby' 	=> 'ID',
			'pin_color' => null,
			'id'		=> false,
		), $atts );

		// get shortcode settings
		if( $attributes['id'] ){
			$settings = get_post_meta($attributes['id'], 'mml_map_data', true );
			if( is_array($settings) ){
				$attributes = array_merge($attributes, $settings);
			}
		}

		//  get the locations and put into array
		$queryArgs = array(
			'post_type' => $attributes['map'],
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' =>  $attributes['orderby'],
			'order' => 'ASC'
		);

		$locations = [];
		$query = new WP_Query( $queryArgs );

		if( $query->have_posts() ){
			while( $query->have_posts() ){
				$query->the_post();

				$dataSet = get_post_meta(get_the_ID(), 'mml_location', true);
				$dataSet['title'] = get_the_title();
				$dataSet['description'] = nl2br(get_post_meta(get_the_ID(), 'mml_description', true));
				$dataSet['image_src'] = get_the_post_thumbnail_url(get_the_ID());

				$locations[] = $dataSet;

			}
		}
		wp_reset_postdata();

		// create the html
		$content = '';

		// Output buffer the return
		ob_start();
		include('partials/map-my-locations-public-display.php');
		$content.= ob_get_clean();

    	return $content;
	}
}
