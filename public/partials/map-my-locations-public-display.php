<?php
// EXIT IF ACCESSED DIRECTLY.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

?>
<div class="map-my-locations">

    <div id="map_<?php echo $attributes['map'] ?>" class="container-map row flex-row-reverse no-gutters" 
      data-provider="<?php echo $this->options['map_provider']['provider']; ?>"
      data-list="<?php echo $attributes['list']; ?>"
      data-style="<?php echo $attributes['style'] ?>"
      data-pin-color="<?php echo $attributes['pin_color'] ?>"
      style="width: 100%; height: 600px">
    </div>

    <?php
    // Add temp script
    wp_register_script( $this->plugin_name.'_data', '' );
    wp_enqueue_script( $this->plugin_name.'_data', '' );

    // Add in the map data
    wp_localize_script( $this->plugin_name.'_data', $this->plugin_slug.'_data', ['markers' => $locations] );
    ?>
    
</div>