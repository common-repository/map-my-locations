<?php
/**
 * Register the CPTs
 *
 */

class Map_My_Locations_CPT {

	private $plugin_name;
	private $version;
	private $options;
	private $fields = ['mml_location' => 'json', 'mml_description' => 'wysiwyg', 'pin_color' => 'text', 'orderby' => 'text'];

	public function __construct($plugin_name, $version){

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$Map_My_Locations_Admin = new Map_My_Locations_Admin($plugin_name, $version);
		$this->options = $Map_My_Locations_Admin->get_options();

		add_action('init', array($this, 'register_cpts') );

		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
	}

	public function enqueue_scripts() {
		wp_enqueue_script('wp-color-picker');
    wp_enqueue_style( 'wp-color-picker' );
    wp_add_inline_script( 'wp-color-picker', 'jQuery(".color-picker").wpColorPicker();' );
	}

	public function register_cpts(){

		// used for map setup
		register_post_type( 'mml_map', array(
			'labels' => array(
				'name' => 'Map',
				'singular_name' => 'Map',
				'add_new_item' => 'Add a New Map',
				'edit_item' => 'Edit Map',
			),
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => false,
			'show_in_admin_bar' => false,
			'query_var' => false,
			'supports' => array('title')
		));

		add_action('add_meta_boxes_mml_map', array($this, 'add_meta_boxes_for_mml_map'));
		add_action('save_post_mml_map', array($this, 'save_map_data'));

		foreach( $this->options['location_cpts'] as $cpt ){

			register_post_type( $cpt['name'], array(
				'labels' => array(
					'name' => $cpt['label'],
				),
				'public' => false,
				'show_ui' => true,
				'show_in_admin_bar' => false,
				'query_var' => false,
				'supports' => array('title', 'thumbnail')
			));

			// Add any metaboxs for the cpt
			add_action('add_meta_boxes_'.$cpt['name'], array($this, 'add_meta_boxes'));
			add_action('save_post_'.$cpt['name'], array($this, 'save_meta_data'));
		}

	}

	/*
		Deals with the address metabox for the location CPT's
	*/
	public function add_meta_boxes()
	{
		// Add the location details metabox
		add_meta_box($this->plugin_name.'_description_meta_box', 'Description', array($this, 'description_meta'), '', 'normal', 'low' );
		add_meta_box($this->plugin_name.'_address_meta_box', 'Location Details', array($this, 'address_meta'), '', 'normal', 'low' );
	}

	public function add_meta_boxes_for_mml_map(){
		add_meta_box($this->plugin_name.'_meta_box', 'Map Details', array($this, 'map_meta'), '', 'normal', 'low' );
	}

	/*
		Deals with the description metabox for the location CPT's
	*/
	public function description_meta()
	{
		// required for the loop use
	  global $post;

	  wp_nonce_field( basename( __FILE__ ), 'location_nonce' );

	  $default = array(
	  	'name' => '',
	  	'address' => '',
	  	'lat' => '',
	  	'lng' => '',
	  );

	  // Parse the address meta
	  $descriptionData = get_post_meta( get_the_ID(), 'mml_description', true );
		?>
		<div class="map-my-locations">
			<div class="row">
				<div class="col">
					<?php
						// load 'paste' plugin in minimal/pressthis editor
	          add_filter( 'teeny_mce_plugins', function( $plugins ) {
	            $plugins[] = 'paste';
	            return $plugins;
	          });

	          $settings = array( 'media_buttons' => false,
	                             'teeny' => true,
	                             'tinymce' => array(
	                               /*'block_formats' => 'Paragraph=p; Heading =h4',*/
	                               'toolbar1' => 'bold,italic,underline,undo,redo',
	                               'paste_as_text' => true,
	                               'paste_text_sticky' => true,
	                               'paste_text_sticky_default' => true,
	                             ),
	                             'editor_height' => 200,
	                             'quicktags' => false
	                           );

	          wp_editor( $descriptionData, 'mml_description', $settings );
	        ?>
				</div>
			</div>
		</div>
		<?php
	}

	/*
		Deals with the address metabox for the location CPT's
	*/
	public function address_meta()
	{
		// required for the loop use
	  global $post;

	  $default = array(
	  	'name' => '',
	  	'address' => '',
	  	'lat' => '',
	  	'lng' => '',
	  );

	  // Parse the address meta
	  $locationData = get_post_meta( get_the_ID(), 'mml_location', true );
		?>
		<div class="map-my-locations">	
			<div class="row">
				<div class="col">
					<div id="container-map" class="container-map" data-provider="<?php echo $this->options['map_provider']['provider']; ?>" data-map="true" data-search="true" data-search-position="before" data-name="<?php echo (isset($locationData['name'])?$locationData['name']:''); ?>" data-lat="<?php echo (isset($locationData['lat'])?$locationData['lat']:''); ?>" data-lng="<?php echo (isset($locationData['lng'])?$locationData['lng']:''); ?>" data-return-fields="address,lat,lng" style="width: 100%; height: 450px">
				  	<input class="location-name" data-return="name" type="hidden" name="mml_location[name]" value="<?php echo (isset($locationData['name'])?$locationData['name']:''); ?>" />
				  	<input class="location-address" data-return="address" type="hidden" name="mml_location[address]" value="<?php echo (isset($locationData['name'])?$locationData['address']:''); ?>" />
				  	<input class="location-lat" data-return="lat" type="hidden" name="mml_location[lat]" value="<?php echo (isset($locationData['lat'])?$locationData['lat']:''); ?>" />
				  	<input class="location-lng" data-return="lng" type="hidden" name="mml_location[lng]" value="<?php echo (isset($locationData['lng'])?$locationData['lng']:''); ?>" />
				  </div>
				</div>
			</div>
		</div>
		<?php
	}

	/*
		Deals with saving the meta for the location CPT's
	*/
	public function save_meta_data($post_id)
	{
		// Make sure this isn't a WP autosave
	  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
	    return;
	  
	  // Check if it's not a revision.
	  if ( wp_is_post_revision( $post_id ) )
	    return;

	  // Verify the nonce before proceeding.
	  if ( !isset( $_POST['location_nonce'] ) || !wp_verify_nonce( $_POST['location_nonce'], basename( __FILE__ ) ) )
	    return $post_id;

	  // Loop through fields and save as necessary
	  foreach ( $this->fields AS $fieldName => $type ) 
	  {
      if ( array_key_exists( $fieldName, $_POST ) ) 
      {
        switch($type)
        {
          case 'text':
          	// Add in standard text field
          	update_post_meta( $post_id, $fieldName, sanitize_text_field( $_POST[$fieldName] ) );
          break;

          case 'wysiwyg':
          	// Add in standard text field
          	update_post_meta( $post_id, $fieldName, $this->wpKsesCustom( $_POST[$fieldName] ) );
          break;

          case 'json':
          	$fieldArr = [];
          	
          	// Sanitize fields
          	foreach ($_POST[$fieldName] as $key => $field) {
          		$fieldArr[$key] = sanitize_text_field($field);
          	}

          	// Json encode fields
          	update_post_meta( $post_id, $fieldName, $fieldArr );
          break;
        }
      }
	  }

	  // store long lat
	  if( isset($_POST['mml_location']) ){

	  	$GEO = new Map_My_Locations_GEO($this->plugin_name, $this->version);

	  	$GEO->store($post_id, $_POST['mml_location']['lat'], $_POST['mml_location']['lng'] );

	  }

	}

	public function map_meta($post){

		$mml_map_data =  get_post_meta( $post->ID, 'mml_map_data', true );

		$mapbox_styles = array(
			'light-v10'			=> 'Light',
			'dark-v10' 			=> 'Dark',
		);

		wp_nonce_field( basename( __FILE__ ), 'map_nonce' );
		?>
		<div class="map-my-locations">
			<table class="form-table">
				<tbody>		
					<tr>
						<th scope="row"><?php esc_attr_e( 'Map Theme', 'map-my-location' ); ?></th>
						<td>
							<select name="mml_map_data[style]">
								<?php foreach ($mapbox_styles as $slug => $name) { ?>
									<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( isset($mml_map_data['style']) ? $mml_map_data['style'] : '', $slug, true ); ?> ><?php echo $name;?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">Show Side List</th>
						<td>
							<label><input type="radio" name="mml_map_data[list]" value='true' <?php checked( isset($mml_map_data['list']) ? $mml_map_data['list'] : '', 'true', true ); ?> > Yes</label>
							&nbsp;&nbsp;&nbsp;
							<label><input type="radio" name="mml_map_data[list]" value='false' <?php checked( isset($mml_map_data['list']) ? $mml_map_data['list'] : 'false', 'false', true ); ?>> No</label>
						</td>
					</tr>
					<tr>
						<th scope="row">Pin Color</th>
						<td>
							<input type="text" name="mml_map_data[pin_color]" value='<?php echo (isset($mml_map_data['pin_color']) ? $mml_map_data['pin_color'] : '') ?>' class="color-picker" />
						</td>
					</tr>
					<tr>
						<th scope="row">Order By</th>
						<td>
							<select name="mml_map_data[orderby]">
								<?php foreach (['ID' => 'Default', 'title' => 'Title'] as $key => $name) { ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset($mml_map_data['orderby']) ? $mml_map_data['orderby'] : '', $key, true ); ?> >
										<?php echo ucwords($name);?>
									</option>
								<?php } ?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>

			<?php $Map_My_Locations_Admin = new Map_My_Locations_Admin('map-my-location'); ?>
			<input type="hidden" name="mml_map_data[map_provider]" value="<?php echo isset($mml_map_data['map_provider']) ? $mml_map_data['map_provider'] : $Map_My_Locations_Admin->get_map_provider();?>">
		</div>
		<?php
	}

	public function save_map_data($post_id){
		// Verify the nonce before proceeding.
		if ( !isset( $_POST['map_nonce'] ) || !wp_verify_nonce( $_POST['map_nonce'], basename( __FILE__ ) ) ){
			return $post_id;
		}

		$mml_map_data = isset( $_POST['mml_map_data'] ) ? (array) $_POST['mml_map_data'] : array();
		$mml_map_data = array_map( 'esc_attr', $mml_map_data );

		update_post_meta( $post_id, 'mml_map_data', $mml_map_data );

	}

	/*
	Limits the wp KSES function to a small amount of pre-defined tags
	*/
	private function wpKsesCustom($data)
	{
		// Custom WP_KSES filters
		$allowed_html = array(
			'p' => array(
				'class' => array(),
				'title' => array(),
				'style' => array(),
			),
			'h4' => array(),
			/*'a' => array(
				'href' => array(),
			  'title' => array(),
			  'title' => array()
			),*/
			'b' => array(),
			'strong' => array(),
			'i' => array(),
			'em' => array(),
			'span' => array(
				'class' => array(),
				'title' => array(),
				'style' => array(),
			),
		);

		return wp_kses($data, $allowed_html);
	}
}