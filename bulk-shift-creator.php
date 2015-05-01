<?php
function wpaesp_add_bulk_creator_page() {
	$bulk_page = add_submenu_page( 'edit.php?post_type=shift', 'Bulk Add Shifts', 'Bulk Add Shifts', 'manage_options', 'add-repeating-shifts', 'wpaesp_bulk_shift_form' );
}
add_action('admin_menu', 'wpaesp_add_bulk_creator_page');

function wpaesp_bulk_shift_form() { 
	// get a list of all of the repeating days
	$daysofweek = array();
		if( isset( $_POST['repeatmon'] ) && "Monday" == $_POST['repeatmon'] ) {
			array_push($daysofweek, $_POST['repeatmon']);
		}
		if( isset( $_POST['repeattues'] ) && "Tuesday" == $_POST['repeattues'] ) {
			array_push($daysofweek, $_POST['repeattues']);
		}
		if( isset( $_POST['repeatweds'] ) && "Wednesday" == $_POST['repeatweds'] ) {
			array_push($daysofweek, $_POST['repeatweds']);
		}
		if( isset( $_POST['repeatthurs'] ) && "Thursday" == $_POST['repeatthurs'] ) {
			array_push($daysofweek, $_POST['repeatthurs']);
		}
		if( isset( $_POST['repeatfri'] ) && "Friday" == $_POST['repeatfri'] ) {
			array_push($daysofweek, $_POST['repeatfri']);
		}
		if( isset( $_POST['repeatsat'] ) && "Saturday" == $_POST['repeatsat'] ) {
			array_push($daysofweek, $_POST['repeatsat']);
		}
		if( isset( $_POST['repeatsun'] ) && "Sunday" == $_POST['repeatsun'] ) {
			array_push($daysofweek, $_POST['repeatsun']);
		}
	// get a list of dates on which the shift repeats
	if( isset( $_POST['thisdate'] ) && isset( $_POST['repeatuntil'] ) ) {
		$begindate = $_POST['thisdate'];
		$enddate = $_POST['repeatuntil'];
	}
	if( isset( $begindate ) && isset( $enddate) ) {
		$shiftdates = wpaesp_get_repeating_dates( $begindate, $enddate, $daysofweek );
	}

	if( isset( $_POST['job'] ) ) {
		$job = get_post($_POST['job']);
		$job_name = $job->post_title;
	}

	$total = 0;
	if( isset( $_POST['employees'] ) && is_array( $_POST['employees'] ) ) {
		foreach( $_POST['employees'] as $employee ) {
			// insert posts with all of the same meta data
			$i = 0;
			if( isset( $shiftdates ) ) {
				foreach($shiftdates as $shiftdate) {

					if( 0 == $i ) {
						$newshift = array(
							'post_type'     => 'shift',
							'post_title'    => $_POST['shift-name'],
							'post_status'   => 'publish',
						);
					} else {
						$newshift = array(
							'post_type'     => 'shift',
							'post_title'    => $_POST['shift-name'],
							'post_status'   => 'publish',
							'post_parent'	=> $first,
						);
					}
					$createdshift = wp_insert_post($newshift);
					if( 0 == $i ) {
						$first = $createdshift;
					} 		
					wp_set_object_terms( $createdshift, $_POST['type'], 'shift_type' );
					wp_set_object_terms( $createdshift, $_POST['status'], 'shift_status' );
					// add a serialised array for wpalchemy to work - see http://www.2scopedesign.co.uk/wpalchemy-and-front-end-posts/
					$data = array('_wpaesp_date','_wpaesp_starttime','_wpaesp_endtime','_wpaesp_job','_wpaesp_employee','_wpaesp_notify');
					$str = $data;
					update_post_meta( $createdshift, 'shift_meta_fields', $str );

					add_post_meta( $createdshift, '_wpaesp_date', $shiftdate );
					add_post_meta( $createdshift, '_wpaesp_starttime', $_POST['starttime'] );
					add_post_meta( $createdshift, '_wpaesp_endtime', $_POST['endtime'] );

					// Create connections
					if( isset( $_POST['job'] ) ) {
						p2p_type( 'shifts_to_jobs' )->connect( $createdshift, $_POST['job'], array(
						    'date' => current_time('mysql')
						) );
					}
					p2p_type( 'shifts_to_employees' )->connect( $createdshift, $employee, array(
					    'date' => current_time('mysql')
					) );
					$i++;
				}

				if(isset($_POST['notify']) && $_POST['notify'] == "1") {
					$date = $begindate;
					$repeatdays = $daysofweek;
					$repeatuntil = $enddate;
					wpaesp_send_notification_email($_POST['employee'], $job_name, $date, $_POST['starttime'], $_POST['endtime'], $repeatdays, $repeatuntil);
				}
			} 
			$total += $i;
		}
	} else {
		// do all the same stuff, but without the employee
		$i = 0;
		if( isset( $shiftdates ) ) {
			foreach($shiftdates as $shiftdate) {

				if( 0 == $i ) {
					$newshift = array(
						'post_type'     => 'shift',
						'post_title'    => $_POST['shift-name'],
						'post_status'   => 'publish',
					);
				} else {
					$newshift = array(
						'post_type'     => 'shift',
						'post_title'    => $_POST['shift-name'],
						'post_status'   => 'publish',
						'post_parent'	=> $first,
					);
				}
				$createdshift = wp_insert_post($newshift);
				if( 0 == $i ) {
					$first = $createdshift;
				} 		
				wp_set_object_terms( $createdshift, $_POST['type'], 'shift_type' );
				wp_set_object_terms( $createdshift, $_POST['status'], 'shift_status' );
				// add a serialised array for wpalchemy to work - see http://www.2scopedesign.co.uk/wpalchemy-and-front-end-posts/
				$data = array('_wpaesp_date','_wpaesp_starttime','_wpaesp_endtime','_wpaesp_job','_wpaesp_employee','_wpaesp_notify');
				$str = $data;
				update_post_meta( $createdshift, 'shift_meta_fields', $str );

				add_post_meta( $createdshift, '_wpaesp_date', $shiftdate );
				add_post_meta( $createdshift, '_wpaesp_starttime', $_POST['starttime'] );
				add_post_meta( $createdshift, '_wpaesp_endtime', $_POST['endtime'] );

				// Create connections
				if( isset( $_POST['job'] ) ) {
					p2p_type( 'shifts_to_jobs' )->connect( $createdshift, $_POST['job'], array(
					    'date' => current_time('mysql')
					) );
				}

				$i++;
			}
		} 
		$total += $i;
	}

// RENDER FORM	?>
	<div class="wrap">
		<!-- Display Plugin Icon, Header, and Description -->
		<div class="icon32" id="icon-options-general"><br></div>
		<?php if($total > 0) { ?>
			<div id="message" class="updated" style="padding: 15px;">
				<?php $url = get_bloginfo('wpurl') . '/wp-admin/edit.php?post_type=shift';
				sprintf( __( 'Created %i new shifts.  <a href="%s">View all shifts', 'wpaesp' ), $total, $url ); ?></a>.
			</div>
		<?php } ?>

		<h1><?php _e('Bulk Add Shifts', 'wpaesp'); ?></h1>
		<p><?php _e('If you have a shift that repeats on a regular basis with the same employee, job, and time, you can bulk add it here instead of having to create each shift individually.  If you choose several employees, a separate series of shifts will be created for each employee.', 'wpaesp'); ?></p>

		<form id="bulk-shifts" action="<?php bloginfo('wpurl') ?>/wp-admin/edit.php?post_type=shift&page=add-repeating-shifts" method="post">
			<table class="form-table">
				<tr>
					<th scope="row"><?php _e('Shift Name', 'wpaesp'); ?></th>
					<td>
						<input type="text" size="60" name="shift-name" value="" />
					</td>
				</tr>	
				<tr>
					<th scope="row"><?php _e('Job', 'wpaesp'); ?></th>
					<td>
						<?php $args = array( 
						    'post_type' => 'job',  
						    'posts_per_page' => -1,  
						    'orderby' => 'name',
						    'order' => 'asc',
						);

						$job_query = new WP_Query( $args );

						if ( $job_query->have_posts() ) : ?>
							<select name="job" class="required">
								<option value=""></option>
								<?php while ( $job_query->have_posts() ) : $job_query->the_post(); ?>
									<option value="<?php echo get_the_ID(); ?>"><?php the_title(); ?></option>
								<?php endwhile; ?>
							</select>
						<?php endif;
						wp_reset_postdata(); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Employee(s)', 'wpaesp' ) ?></th>
					<td>
						<!-- <select name="employee">
							<option value=""></option> -->
						<ul id="employees">
							<?php $employees = array_merge( get_users( 'role=employee&orderby=nicename' ), get_users( 'role=administrator&orderby=nicename' ) );
							usort( $employees, 'wpaesp_alphabetize' );
							foreach ( $employees as $employee ) { ?>
								<li><input type="checkbox" name="employees[]" value="<?php echo $employee->ID; ?>" ><?php echo $employee->display_name; ?></li>
							<?php } ?>
						</ul>
						<!-- </select> -->


					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('This shift repeats every:', 'wpaesp') ?></th>
					<td>
						<ul>
							<li>
								<input type="checkbox" name="repeatmon" value="Monday"/> <?php _e('Monday', 'wpaesp'); ?>
							</li>
							<li>
								<input type="checkbox" name="repeattues" value="Tuesday"/> <?php _e('Tuesday', 'wpaesp'); ?>
							</li>
							<li>
								<input type="checkbox" name="repeatweds" value="Wednesday"/> <?php _e('Wednesday', 'wpaesp'); ?>
							</li>
							<li>
								<input type="checkbox" name="repeatthurs" value="Thursday"/> <?php _e('Thursday', 'wpaesp'); ?>
							</li>
							<li>
								<input type="checkbox" name="repeatfri" value="Friday"/> <?php _e('Friday', 'wpaesp'); ?>
							</li>
							<li>
								<input type="checkbox" name="repeatsat" value="Saturday"/> <?php _e('Saturday', 'wpaesp'); ?>
							</li>
							<li>
								<input type="checkbox" name="repeatsun" value="Sunday"/> <?php _e('Sunday', 'wpaesp'); ?>
							</li>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Dates', 'wpaesp'); ?></th>
					<td>
						From <input type="text" size="10" name="thisdate" id="thisdate" value="" /> to <input type="text" size="10" name="repeatuntil" id="repeatuntil" value="" />
					</td>					
				</tr>
				<tr>
					<th scope="row"><?php _e('Time', 'wpaesp'); ?></th>
					<td>
						From <input type="text" size="10" name="starttime" id="starttime" value="" /> to <input type="text" size="10" name="endtime" id="endtime" value="" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Shift Type', 'wpaesp'); ?></th>
					<td>
						<select name="type">
							<option value=""></option>
							<?php $shifttypes = get_terms( 'shift_type', 'hide_empty=0' );
							foreach ( $shifttypes as $type ) { ?>
								<option value="<?php echo $type->slug; ?>" ><?php echo $type->name; ?></option>
							<?php } ?>
						</select>

					</td>					
				</tr>
				<tr>
					<th scope="row"><?php _e('Shift Status', 'wpaesp'); ?></th>
					<td>
						<select name="status">
							<option value=""></option>
							<?php $shift_statuses = get_terms( 'shift_status', 'hide_empty=0' );
							foreach ( $shift_statuses as $status ) { ?>
								<option value="<?php echo $status->slug; ?>" ><?php echo $status->name; ?></option>
							<?php } ?>
						</select>

					</td>					
				</tr>
				<tr>
					<th scope="row"><?php _e('Notify Employee', 'wpaesp'); ?></th>
					<td>
						<span><?php _e('Employee will only recieve one email', 'wpaesp'); ?><br /></span>
						<input type="checkbox" name="notify" value="1"/> <?php _e('Notify Employee', 'wpaesp'); ?>
					</td>					
				</tr>

			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Create Shifts') ?>" />
			</p>
		</form>

	</div>
<?php }

// ------------------------------------------------------------------------
// GET REPEATING DATES
// ------------------------------------------------------------------------

function wpaesp_get_repeating_dates( $begindate, $enddate, $daysofweek ) {
    $repeaton = array();

    $min_date = strtotime($begindate);
    $max_date = min(strtotime('+6 months'), strtotime($enddate));
    $dow = array_values($daysofweek);

    while ($min_date <= $max_date) {
    	$date = date('Y-m-d', $min_date);
        if (in_array(date('l', $min_date), $daysofweek)) {
            array_push($repeaton, $date);
        }
        $min_date = strtotime('+1 day', $min_date);
    }

    return $repeaton;
}

 function wpaesp_alphabetize( $a, $b ) {
   return strcmp( $a->user_nicename, $b->user_nicename );
 }

?>