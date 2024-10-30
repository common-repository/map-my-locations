<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Map_My_Locations
 * @subpackage Map_My_Locations/admin
 * @author     Your Name <email@example.com>
 */
class Map_My_Locations_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version='1.0.1' ) {

		$this->plugin_name = $plugin_name;
		$this->plugin_slug = str_replace('-', '_', $plugin_name);
		$this->version = $version;

		$this->options = $this->get_options();
	}

	public function get_options(){
		return get_option( $this->plugin_slug.'_options', array('location_cpts'=>array(),'map_provider'=>array('provider'=>'mapbox')) );
	}

	public function update_options($options=array()){
		if(empty($options)){
			$options = $this->options;
		}
		return update_option( $this->plugin_slug.'_options', $options );
	}

	public function get_map_provider(){
		if( isset($this->options['map_provider']['provider']) ){
			return $this->options['map_provider']['provider'];
		}
		return false;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '/css/admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . '/js/admin.js', array( 'jquery' ), $this->version, false );

		wp_localize_script( $this->plugin_name, $this->plugin_slug.'_options', $this->options );

	}

	// redirect back
	private function redirect( $success = true ) {

		// To make the Coding Standards happy, we have to initialize this.
		if ( ! isset( $_SERVER['HTTP_REFERER']  ) ) { // Input var okay.
			$_SERVER['HTTP_REFERER']  = wp_login_url();
		}

		// Sanitize the value of the $_POST collection for the Coding Standards.
		$url = sanitize_text_field(
			wp_unslash( $_SERVER['HTTP_REFERER'] ) // Input var okay.
		);

		$url = ( !$success ) ?
			add_query_arg( 'error', 'true', remove_query_arg( 'settings-saved', $url ) ) :
			add_query_arg( 'success', 'true', $url );

		wp_safe_redirect( urldecode( $url ) );
		exit;
	}

	private function display_notice(){

		if(isset($_GET['success']) ) { ?>
	    <div class="notice notice-success is-dismissible">
	        <p><?php _e( 'Saved', 'map-my-locations' ); ?></p>
	    </div>
		<?php }

		if( $error = get_transient( $this->plugin_name.'_error' ) ) { ?>
	    <div class="notice notice-error is-dismissible">
	        <p><?php echo $error; ?></p>
	    </div>
		<?php } 

		delete_transient( $this->plugin_name.'_error' );	
	}

	// Register the administration menu for this plugin into the WordPress Dashboard menu
	public function plugin_admin_menu() {

		add_menu_page( 'Map My Locations', 'Map My Locations', 'manage_options', $this->plugin_name, function(){
			include_once( 'partials/' . $this->plugin_name . '-admin-options.php' );
		});
	    add_submenu_page( $this->plugin_name, 'Settings', 'Settings', 'manage_options', $this->plugin_name );

	    // add_submenu_page( $this->plugin_name, 'Locations', 'Locations Setup', 'manage_options', $this->plugin_name.'-locations', function(){
	    // 	include_once( 'partials/' . $this->plugin_name . '-admin-locations.php' );
	    // });
	    add_submenu_page( $this->plugin_name, 'Maps', 'Maps', 'manage_options', $this->plugin_name.'-maps', function(){
	    	include_once( 'partials/' . $this->plugin_name . '-admin-maps.php' );
	    });
	}

	public function add_cpt(){

		// check nonce
		if( !isset( $_POST['add_cpt'] ) || !wp_verify_nonce( $_POST['add_cpt'], 'map_my_location_add_cpt') ) {
			echo "Error";
			exit;
		}

		$cpt = $_POST['cpt'];

      	// Sanitize fields
      	foreach ($cpt as $key => $field) {
      		$cpt[$key] = sanitize_text_field($field);
      	}		

      	// setup default
      	$default_cpt = array(
      		'public' 	=> true,
      		'default'	=> false,
      	);

      	// setup name (slug)
      	$cpt['name'] = 'mml_'.sanitize_title($cpt['label']);

      	$cpt = array_merge($default_cpt,$cpt);

		// check if label already exsits
		if( in_array( $cpt['label'], wp_list_pluck( $this->options['location_cpts'], 'label' ) ) ){

			// already exists error
			set_transient($this->plugin_name.'_error', 'Already Exists', 45 );
			$this->redirect(false);
		}


		// add to option
		$this->options['location_cpts'][] = $cpt;
		
		// save options
		$this->update_options();

		$this->redirect();
		

	}

	public function remove_cpt(){

		$index = sanitize_text_field($_GET['index']);

		unset($this->options['location_cpts'][$index]);

		$this->update_options();

		$this->redirect();

	}

	public function save_options(){
		// check nonce
		if( !isset( $_POST['options'] ) || !wp_verify_nonce( $_POST['options'], 'map_my_location_save_options') ) {
			echo "Error";
			exit;
		}	

		if( isset($_POST['map_provider']) ){
			$this->options['map_provider'] = $_POST['map_provider'];
			$this->update_options();
		}

		$this->redirect();

	}

}
