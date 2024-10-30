<?php

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class Map_My_Locations_Map_Table extends WP_List_Table {

	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Map', 'map-my-location' ),
			'plural'   => __( 'Maps', 'map-my-location' ), 
			'ajax'     => false 

		]);
	}

	public static function get_maps( $per_page = 5, $page_number = 1 ) {
		$maps = get_posts(array(
			'post_type' => 'mml_map',
			'posts_per_page' => $per_page,
			'paged' => $page_number
		));
		return array_map(
			function( $post ) {
				return (array) $post;
			},
			$maps
		);
	}

	public static function delete_map( $id ) {
		// TODO
	}

	public static function record_count() {
		return isset(wp_count_posts( 'mml_map' )->publish) ? wp_count_posts( 'mml_map' )->publish : 0;
	}

	public function no_items() {
		_e( 'No maps created.', 'map-my-location' );
	}

	public function column_post_title( $item ) {

		// create a nonce
		$delete_nonce = wp_create_nonce( 'delete-post_'.$item['ID'] );

		$title = '<strong>' . $item['post_title'] . '</strong>';

		$actions = [
			'edit' => sprintf( '<a href="%s?post=%s&action=%s">Edit</a>', esc_url( admin_url( 'post.php' ) ), absint( $item['ID'] ),'edit' ),
			'delete' => sprintf( '<a href="%s?post=%s&action=%s&_wpnonce=%s">Delete</a>', esc_url( admin_url( 'post.php' ) ), absint( $item['ID'] ),'delete', $delete_nonce )
		];

		return $title . $this->row_actions( $actions );
	}

	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
		);
	}
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items() {

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();

        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = self::record_count();

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ) );

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = self::get_maps( $per_page, $current_page );
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
        	//'cb'      	  		=> '<input type="checkbox" />',
           // 'ID'          		=> 'ID',
            'post_title'       	=> 'Title',
            'theme'             => 'Theme',
            'side_list'         => 'Side List',
            'pin_color'         => 'Pin Color',
            'orderby'           => 'Order By',
            'shortcode'   		=> 'Shortcode',
        );

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('title' => array('title', false));
    }



    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'ID':
            case 'post_title':
                return $item[ $column_name ];
            break;

            case 'shortcode':
            	return mml_make_shortcode($item['ID']);
            break;

            case 'theme';
                return (get_post_meta( $item['ID'], 'mml_map_data', true )['style'] == 'light-v10'? 'Light' : 'Dark');
            break;

            case 'orderby';
                return (isset(get_post_meta( $item['ID'], 'mml_map_data', true )['orderby']) ? ucwords(get_post_meta( $item['ID'], 'mml_map_data', true )['orderby']) : '');
            break;

            case 'side_list';
                return (isset(get_post_meta( $item['ID'], 'mml_map_data', true )['list']) ? ucwords(get_post_meta( $item['ID'], 'mml_map_data', true )['list']) : '');
            break;

            case 'pin_color';
                return (isset(get_post_meta( $item['ID'], 'mml_map_data', true )['pin_color']) && get_post_meta( $item['ID'], 'mml_map_data', true )['pin_color'] != '' ? '<span style="width: 20px; height: 20px; display: block; background: '.(get_post_meta( $item['ID'], 'mml_map_data', true )['pin_color']).';" title="'.(get_post_meta( $item['ID'], 'mml_map_data', true )['pin_color']).'"></span>' : 'Map default');
            break;

            default:
                return print_r( $item, true ) ;
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'title';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }


        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
}