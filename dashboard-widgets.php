<?php

// ------------------------------------------------------------------------
// DASHBOARD WIDGET FOR WHO IS CLOCKED IN RIGHT NOW      
// ------------------------------------------------------------------------

function wpaesm_create_clocked_in_now_widget() {

	if( current_user_can( 'delete_users' ) ) { // only administrators can see this widget
		wp_add_dashboard_widget(
	                 'clocked_in',
	                 'Clocked In Now',
	                 'wpaesm_clocked_in_now_widget'
	        );	
	}
}
add_action( 'wp_dashboard_setup', 'wpaesm_create_clocked_in_now_widget' );

function wpaesm_clocked_in_now_widget() { 

	// find all of the shifts that have a clock in time, but not a clock out time
	$args = array( 
	    'post_type' => 'shift',
	    'posts_per_page' => -1,
	    'meta_query' => array(   
	       array(
	         'key' => '_wpaesm_clockin',
	         'value' => array( '' ),
	         'compare' => 'NOT IN'
	       ),
	       array(
	         'key' => '_wpaesm_clockout',
	         'compare' => 'NOT EXISTS'
	       )
	    )
	);
	
	$at_work = new WP_Query( $args );
	
	// The Loop
	if ( $at_work->have_posts() ) { ?>
		<table class="wp-list-table widefat fixed posts">
			<thead>
				<tr>
					<th><?php _e('Employee', 'wpaesm'); ?></th>
					<th><?php _e('Clock In Time', 'wpaesm'); ?></th>
					<th><?php _e('Shift', 'wpaesm'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php while ( $at_work->have_posts() ) : $at_work->the_post();
					$id = get_the_id();
					// get employee associated with this shift
					$users = get_users( array(
						'connected_type' => 'shifts_to_employees',
						'connected_items' => $id
					) );
					foreach($users as $user) {
						$employee = $user->display_name;
					}
					// get meta data
					global $shift_metabox; 
					$meta = $shift_metabox->the_meta(); 

					?>
					<tr>
						<td><?php echo $employee; ?></td>
						<td><?php echo $meta['clockin'] ?></td>
						<td><a href="<?php echo get_edit_post_link(); ?>"><?php _e( 'Shift details' ); ?></a></td>
					</tr>
				<?php endwhile; ?>
			</tbody>
	</table>
	<?php } else {
		_e( 'No one is clocked in right now.', 'wpaesm' ); 
	}
	
	// Reset Post Data
	wp_reset_postdata();
	
}


// ------------------------------------------------------------------------
// DASHBOARD WIDGET FOR RECENT SHIFT NOTES   
// ------------------------------------------------------------------------

function wpaesm_create_recent_shift_notes_widget() {

	if( current_user_can( 'delete_users' ) ) { // only administrators can see this widget
		wp_add_dashboard_widget(
	                 'recent_notes',
	                 'Recent Shift Notes',
	                 'wpaesm_recent_shift_notes_widget'
	        );
    }	
}
add_action( 'wp_dashboard_setup', 'wpaesm_create_recent_shift_notes_widget' );

function wpaesm_recent_shift_notes_widget() {

	// find all of the shifts that have notes
	$args = array( 
	    'post_type' => 'shift',
	    'posts_per_page' => -1,
	    'meta_query' => array(   
	       array(
	         'key' => '_wpaesm_lastnote',
	         'value' => array( '' ),
	         'compare' => 'NOT IN'
	       ),
	    ),
	    'meta_key' => '_wpaesm_lastnote',
	    'orderby' => 'meta_value',
	    'order' => 'DESC',
	);
	
	$recent_notes = new WP_Query( $args );
	
	// The Loop
	if ( $recent_notes->have_posts() ) { ?>
		<?php while ( $recent_notes->have_posts() ) : $recent_notes->the_post();
			$id = get_the_id();
			// get employee associated with this shift
			$users = get_users( array(
				'connected_type' => 'shifts_to_employees',
				'connected_items' => $id
			) );
			foreach($users as $user) {
				$employee = $user->display_name;
			}
			// get meta data
			global $shift_metabox; 
			$meta = $shift_metabox->the_meta(); 

			?>
			<div class="note" style="border:1px solid #e1e1e1; padding: 8px; margin-bottom: 8px;">
				<p><strong><?php _e( 'Employee: ', 'wpaesm' ); ?></strong><?php echo $employee; ?><br />
				<?php 
					$notes = $meta['employeenote'];
					foreach( $notes as $note ) { ?>
						<strong><?php _e( 'Date: ', 'wpaesm' ); ?></strong><?php echo $note['notedate']; ?><br />
						<strong><?php _e( 'Note: ', 'wpaesm' ); ?></strong><?php echo $note['notetext']; ?><br />
					<?php }
				?>	
				
				<a href="<?php echo get_edit_post_link(); ?>"><?php _e( 'View Shift details' ); ?></a></p>
			</div>
		<?php endwhile; ?>
	<?php } else {
		_e( 'No one is clocked in right now.', 'wpaesm' ); 
	}
	
	// Reset Post Data
	wp_reset_postdata();

}

?>