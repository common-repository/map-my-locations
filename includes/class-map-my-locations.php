<?php
/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    Map_My_Locations
 * @subpackage Map_My_Locations/includes
 * @author     Your Name <email@example.com>
 */
class Map_My_Locations {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Map_My_Locations_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'MAP_MY_LOCATIONS_VERSION' ) ) {
			$this->version = MAP_MY_LOCATIONS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'map-my-locations';

		$this->load_dependencies();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {


		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-map-my-locations-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-map-my-locations-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-map-my-locations-cpt.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-map-my-locations-geo.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-map-my-locations-helpers.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-map-my-locations-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-map-my-locations-map-table.php';

		/**
		 * The class responsible for defining all actions that occur in the public
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-map-my-locations-public.php';

		$this->loader = new Map_My_Locations_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Map_My_Locations_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Map_My_Locations_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

    	// Add Plugin menu
    	$this->loader->add_action( 'admin_menu', $plugin_admin, 'plugin_admin_menu' );
    	
    	// admin form actions
    	$this->loader->add_action( 'admin_post_map_my_location_add_cpt', $plugin_admin, 'add_cpt' );
    	$this->loader->add_action( 'admin_post_map_my_location_remove_cpt', $plugin_admin, 'remove_cpt' );

    	$this->loader->add_action( 'admin_post_map_my_location_save_options', $plugin_admin, 'save_options' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Map_My_Locations_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_shortcode( 'map_my_locations', $plugin_public, 'display_map' );

	}

	private function run_cpts(){
		new Map_My_Locations_CPT( $this->get_plugin_name(), $this->get_version()  );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		
		$this->loader->run();

		$this->run_cpts();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}

}
