<?php

if ( ! defined( 'WPINC' ) ) die;

?>

<div class="wrap map-my-locations">

	<h2>Map My Locations - <?php esc_attr_e('Options', 'map-my-locations' ); ?></h2>

	<?php $this->display_notice() ;?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">	

		<input type="hidden" name="action" value="map_my_location_save_options">
		<?php wp_nonce_field( 'map_my_location_save_options', 'options' ); ?>


		<p>Setup your map provider</p>
		<table class="form-table">
			<tbody>		
				<tr>
					<th scope="row">Map Provider</th>
					<td>
						<select name="map_provider[provider]">
							<!--<option value="google">Google Maps</option>-->
							<option value="mapbox" <?= isset($this->options['map_provider']['provider']) && $this->options['map_provider']['provider'] == 'mapbox' ? 'selected' : ''; ?> >MapBox</option>
							<option value="google" <?= isset($this->options['map_provider']['provider']) && $this->options['map_provider']['provider'] == 'google' ? 'selected' : ''; ?> >Google Maps</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">Map API Key</th>
					<td>
						<input type="password" name="map_provider[api_key]" value="<?= isset($this->options['map_provider']['api_key']) ? $this->options['map_provider']['api_key'] : '';?>">
					</td>
				</tr>		
				<tr>
					<th colspan="2"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save"></th>
				</tr>
			</tbody>
		</table>

	</form>


</div>