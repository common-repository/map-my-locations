<?php

if ( ! defined( 'WPINC' ) ) die;

?>

<div class="wrap map-my-locations">

	<h1 class="wp-heading-inline">Map My Locations - <?php esc_attr_e('Maps', 'map-my-locations' ); ?></h1>

	<a href="<?php echo admin_url( 'post-new.php' );?>?post_type=mml_map" class="page-title-action">Add New</a>

	<?php
	
    $exampleListTable = new Map_My_Locations_Map_Table();
    $exampleListTable->prepare_items();

 	// the table
    $exampleListTable->display();

    ?>

</div>