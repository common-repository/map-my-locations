<?php

if ( ! defined( 'WPINC' ) ) die;

?>

<div class="wrap map-my-locations">

	<h2>Map My Locations - <?php esc_attr_e('Location Setup', 'map-my-locations' ); ?></h2>

	<?php $this->display_notice() ;?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

		<input type="hidden" name="action" value="map_my_location_add_cpt">
		<?php wp_nonce_field( 'map_my_location_add_cpt', 'add_cpt' ); ?>

		<label>Location Grouping Name</label>
		<input type="text" name="cpt[label]">

		<input type="submit" name="submit" id="submit" class="button button-primary" value="Add">
	</form>

	<br>
	<hr>
	<br>
	
	<table class="wp-list-tabl widefat striped pages" cellspacing="0" id="email_status">
		<thead>
			<tr>
				<th><strong>Location Groups</strong></th>
				<th><strong>Short Code</strong></th>
				<th><strong>Count</strong></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if(count($this->options['location_cpts'])==0){?>
				<tr><td colspan="3">No data yet...</td></tr>
			<?php } else { ?>
				<?php foreach( $this->options['location_cpts'] as $index => $cpt ){ ?>
				<tr>
					<td><?php echo $cpt['label']?></td>
					<td>[<?php echo $cpt['name'];?>]</td>
					<td><?php echo wp_count_posts($cpt['name'])->publish;?></td>
					<td><a class="delete" href="<?php echo admin_url('admin-post.php?action=map_my_location_remove_cpt&index='.$index);?>">Delete</a></td>
				</tr>			
				<?php } ?>
			<?php } ?>	
		</tbody>
	</table>	

</div>