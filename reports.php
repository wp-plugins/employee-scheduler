<?php

// ------------------------------------------------------------------------
// PAYROLL REPORT                                              
// ------------------------------------------------------------------------


function wpaesp_add_reports_pages() {
	add_submenu_page( '/employee-schedule-manager/options.php', 'Payroll Report', 'Payroll Report', 'manage_options', 'payroll-report', 'wpaesm_payroll_report' );
	add_submenu_page( '/employee-schedule-manager/options.php', 'Scheduled/Worked', 'Scheduled/Worked', 'manage_options', 'scheduled-worked', 'wpaesm_scheduled_worked_report' );
}
add_action('admin_menu', 'wpaesp_add_reports_pages');

function wpaesm_payroll_report() { ?>
	<div class="wrap">
		
		<!-- Display Plugin Icon, Header, and Description -->
		<div class="icon32" id="icon-options-general"><br></div>
		<h2><?php _e('Payroll Report', 'wpaesm'); ?></h2>

		
		<form method='post' action='<?php echo admin_url( 'admin.php?page=payroll-report'); ?>' id='payroll-report'>
			<table class="form-table">
				<tr>
					<th scope="row"><?php _e( 'Select Date Range:', 'wpaesm' ); ?></th>
					<td>
						<?php _e( 'From', 'wpaesm' ); ?> <input type="text" size="10" name="thisdate" id="thisdate" value="" /> <?php _e( 'to', 'wpaesm' ); ?> <input type="text" size="10" name="repeatuntil" id="repeatuntil" value="" />
					</td>					
				</tr>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e( 'Generate Report', 'wpaesm' ); ?>" />
			</p>
		</form>

		<?php if($_POST) { 
			if( ($_POST['thisdate'] == '____-__-__') || ($_POST['repeatuntil'] == '____-__-__')) {
				_e('You must enter both a start date and an end date to create a report.', 'wpaesm');
			} elseif($_POST['thisdate'] > $_POST['repeatuntil']) {
				_e('The report end date must be after the report begin date.', 'wpaesm');
			} else {
				$options = get_option('wpaesm_options');
				$calculate = $options['calculate'];
				$start = $_POST['thisdate'];
				$end = $_POST['repeatuntil'];
				$reportstart = date( 'F j, Y', strtotime( $_POST['thisdate'] ) );
				$reportend = date( 'F j, Y', strtotime( $_POST['repeatuntil'] ) );
				?>
				<h3><?php _e('Payroll report for ' . $reportstart . ' to ' . $reportend, 'wpaesm'); ?></h3>

				<table id="payroll-report">
					<tr>
						<th><?php _e('Staff', 'wpaesm'); ?></th>
						<th><?php _e('Total Reg.', 'wpaesm'); ?></th>
						<th><?php _e('Total OT', 'wpaesm'); ?></th>
						<th><?php _e('PTO Hrs.', 'wpaesm'); ?></th>
						<th><?php _e('Total Hrs.', 'wpaesm'); ?></th>
						<th><?php _e('Total Mileage', 'wpaesm'); ?></th>
						<th><?php _e('Receipts', 'wpaesm'); ?></th>
						<th><?php _e('Deductions', 'wpaesm'); ?></th>
						<th><?php _e('Gross Pay', 'wpaesm'); ?></th>
					</tr>
					<?php 
						$employees = array_merge( get_users( 'role=employee&orderby=nicename' ), get_users( 'role=administrator&orderby=nicename' ) );
						usort( $employees, 'wpaesm_alphabetize' );
						foreach($employees as $employee) { ?>
							<tr>
								<td class="name">
									<?php echo $employee->display_name; ?>
								</td>
								<td class="reghours sum">
									<?php echo wpaesm_reghours( $employee->ID, $start, $end, $calculate ); ?>
								</td>
								<td class="overtime sum">
									<?php echo wpaesm_overtime( $employee->ID, $start, $end, $calculate ); ?>
								</td>
								<!-- <td class="workedhours">
									<?php echo wpaesm_workedhours( $employee->ID, $start, $end, $calculate ); ?>
								</td> -->
								<td class="pto sum">
									<?php echo wpaesm_ptohours( $employee->ID, $start, $end ); ?>
								</td>
								<td class="totalhrs sum">
									<?php echo wpaesm_totalhours( $employee->ID, $start, $end, $calculate ); ?>
								</td>
								<td class="mileage sum">
									<?php echo wpaesm_mileage( $employee->ID, $start, $end ); ?>
								</td>
								<td class="receipts sum">
									<?php echo wpaesm_receipts( $employee->ID, $start, $end ); ?>
								</td>
								<td class="deduction sum">
									<?php echo wpaesm_deduction( $employee->ID, $start, $end ); ?>
								</td>
								<td class="payment sum">
									<?php echo wpaesm_payment( $employee->ID, $start, $end, $calculate ); ?>
								</td>
							</tr>
						<?php } ?>
					<tr id="totals" class="summary">
						<td class="label">
							<?php _e( 'Total:', 'wpaesm' ); ?>
						</td>
						<td class="reghours total">
						</td>
						<td class="overtime total">
						</td>
						<td class="pto total">
						</td>
						<td class="total total">
						</td>
						<td class="mileage total">
						</td>
						<td class="receipts total">
						</td>
						<td class="deduction total">
						</td>
						<td class="payment total">
						</td>
					</tr>
				</table>

				<script type="text/javascript">
				// https://gist.github.com/nikolajbaer/778800
				jQuery(function(){
					function tally (selector) {
						jQuery(selector).each(function () {
							var total = 0,
							column = jQuery(this).siblings(selector).andSelf().index(this);
							jQuery(this).parents().prevUntil(':has(' + selector + ')').each(function () {
								total += parseFloat(jQuery('td.sum:eq(' + column + ')', this).html()) || 0;
							})
						jQuery(this).html(total);
						});
					}
					tally('td.total');
				});
				</script> 
		<?php } 
		} ?>


	</div>
<?php }

function wpaesm_overtime( $userid, $start, $end, $calculate ) {
	$overtime = '';
	$overtimehours = array();
	$options = get_option('wpaesm_options');
	$weeks = array();
	// find the first date of the report
	$reportstart = strtotime($start);
	$reportend = strtotime($end);
	$reportstartday = date("l", $reportstart);
	// find the date the week started
	if($reportstartday == $options['week_starts_on']) { // today is first day of the week
		$weekstart = $reportstart;
		$weeks[0]['weekstart'] = $weekstart;
		$weeks[0]['weekstartymd'] = date('Y-m-d', $reportstart);
	} else { // find the most recent first day of the week
		$sunday = 'last ' . $options['week_starts_on'];
		$weekstart = strtotime($sunday, $reportstart);
		$weeks[0]['weekstart'] = $weekstart;
		$weeks[0]['weekstartymd'] = date('Y-m-d', strtotime($sunday, $reportstart));
	}
	// find the date the week ended
	$weeks[0]['weekendymd'] = date('Y-m-d', strtotime('+ 6 days', $weekstart));
	$weeks[0]['weekend'] = strtotime($weeks[0]['weekendymd']);
	// if that date is earlier than the report end date, then start another week
	$i=0;

	while($weeks[$i]['weekend'] < $reportend) {
		$i++;
		$weeks[$i]['weekstart'] = $weeks[$i-1]['weekend'] + 86400; // 1 day
		$weeks[$i]['weekend'] = $weeks[$i]['weekstart'] + 518400; // 6 days
		$weeks[$i]['weekstartymd'] = date('Y-m-d', $weeks[$i]['weekstart']);
		$weeks[$i]['weekendymd'] = date('Y-m-d', $weeks[$i]['weekend']);
	}
	// figure out what shift statuses we need based on $calculate
	if( "actual" == $calculate ) {
		$statuses = array(
		        'taxonomy' => 'shift_status',
		        'field' => 'slug',
		        'terms' => 'worked',
		        'operator' => 'IN'
		      );
	} elseif ( "scheduled" == $calculate ) {
		$statuses = array(
				'relation' => 'OR',
				array(
			        'taxonomy' => 'shift_status',
			        'field' => 'slug',
			        'terms' => 'worked',
			        'operator' => 'IN'
			    ),
			    array(
			        'taxonomy' => 'shift_status',
			        'field' => 'slug',
			        'terms' => 'assigned',
			        'operator' => 'IN'
			    ),
			    array(
			        'taxonomy' => 'shift_status',
			        'field' => 'slug',
			        'terms' => 'confirmed',
			        'operator' => 'IN'
			    ),
			    array(
			        'taxonomy' => 'shift_status',
			        'field' => 'slug',
			        'terms' => 'tentative',
			        'operator' => 'IN'
			    ),
			);
	}
	// for each week, calculate the number of hours the employee worked (exclude PTO)
	foreach($weeks as $week) {

		$args = array( 
			'post_type' => 'shift',
	  	    'tax_query' => array(
		    'relation' => 'AND',
		      $statuses,
		      array(
		        'taxonomy' => 'shift_type',
		        'field' => 'slug',
		        'terms' => 'pto',
		        'operator' => 'NOT IN'
		      )
		    ),
		    'posts_per_page' => -1,
		    'meta_query' => array(
		       array(
		         'key' => '_wpaesm_date',
		         'value' => $week['weekstartymd'],
		         'type' => 'CHAR',
		         'compare' => '>='
		       ),
		       array(
		         'key' => '_wpaesm_date',
		         'value' => $week['weekendymd'],
		         'compare' => '<='
		       )
		    ),
		    'connected_type' => 'shifts_to_employees',
			'connected_items' => $userid,
		);

		
		$worked = new WP_Query( $args );
		$workedmins = array();
		$days = array();
		$i = 0;
		// The Loop
		if ( $worked->have_posts() ) :
			while ( $worked->have_posts() ) : $worked->the_post();
				// subtract start time from end time for each of those shifts to get number of hours
				global $shift_metabox;
				$meta = $shift_metabox->the_meta();
				if( "actual" == $calculate && isset( $meta['clockin'] ) && isset( $meta['clockout'] ) ) {
					$to_time = strtotime($meta['clockin']);
					$from_time = strtotime($meta['clockout']);
				} elseif( "scheduled" == $calculate && isset( $meta['starttime'] ) && isset( $meta['endtime'] ) ) {
					$to_time = strtotime($meta['starttime']);
					$from_time = strtotime($meta['endtime']);
				}
				// round time to the nearest 15 minutes
				$minutes = round(abs($to_time - $from_time) / 60,2);
				$quarters = round($minutes/15) * 15;
				$workedmins[] = $quarters;
				$days[ strtotime( $meta['date'] ) ] = $quarters/60;
			endwhile;
		endif;
		wp_reset_postdata();



		// compare the number of hours worked to employee's overtime limit
		$allworkedmins = array_sum($workedmins);
		$workedhours = $allworkedmins/60;
		$user_ot = get_user_meta( $userid, 'hours', true );
		if( !isset( $user_ot ) || '' == $user_ot ) {
			$user_ot = $options['hours'];
		}

		// if the number of hours worked in the week is greater than overtime limit, find out how many hours within the report period are overtime
		if( $workedhours > $user_ot ) {

			// if week falls totally within report, then all hours over overtime limit are overtime hours
			if($week['weekstart'] >= $reportstart && $week['weekend'] <= $reportend) {
				$overtimehours[] = ($workedhours - $user_ot);
				
			} else { 
				$hours_so_far = 0;
				$this_week_ot = 0;

				foreach( $days as $day => $time ) {
					// go through the days and sequentially add up the hours
					$hours_so_far += $time;
					if( $hours_so_far > $user_ot ) {
						if( $day <= $reportend && $day >= $reportstart ) { // add these to overtime if the day is within the report
							$this_week_ot += $hours_so_far - $user_ot;
							$hours_so_far = $user_ot;
						}
					}
				}
				if($this_week_ot != 0) {
					$overtimehours[] = $this_week_ot;
				}
			}
		}
	}

	if( !empty( $overtimehours ) ) {
		$overtime = array_sum($overtimehours);
	} else {
		$overtime = 0;
	}
	return $overtime;

}

function wpaesm_reghours( $userid, $start, $end, $calculate ) {
	// subtract overtime from worked hours
	$reghours = wpaesm_workedhours( $userid, $start, $end, $calculate ) - wpaesm_overtime( $userid, $start, $end, $calculate );
	
	return $reghours;
}

function wpaesm_workedhours( $userid, $start, $end, $calculate ) {

	// figure out what shift statuses we need based on $calculate
	if( "actual" == $calculate ) {
		$statuses = array(
		        'taxonomy' => 'shift_status',
		        'field' => 'slug',
		        'terms' => 'worked',
		        'operator' => 'IN'
		      );
	} elseif ( "scheduled" == $calculate ) {
		$statuses = array(
				'relation' => 'OR',
				array(
			        'taxonomy' => 'shift_status',
			        'field' => 'slug',
			        'terms' => 'worked',
			        'operator' => 'IN'
			    ),
			    array(
			        'taxonomy' => 'shift_status',
			        'field' => 'slug',
			        'terms' => 'assigned',
			        'operator' => 'IN'
			    ),
			    array(
			        'taxonomy' => 'shift_status',
			        'field' => 'slug',
			        'terms' => 'confirmed',
			        'operator' => 'IN'
			    ),
			    array(
			        'taxonomy' => 'shift_status',
			        'field' => 'slug',
			        'terms' => 'tentative',
			        'operator' => 'IN'
			    ),
			);
	}
	// find shifts worked by employee on given dates, exclude PTO
	$args = array( 
		'post_type' => 'shift',
  	    'tax_query' => array(
	    'relation' => 'AND',
	      $statuses,
	      array(
	        'taxonomy' => 'shift_type',
	        'field' => 'slug',
	        'terms' => 'pto',
	        'operator' => 'NOT IN'
	      )
	    ),
	    'posts_per_page' => -1,
	    'meta_query' => array(
	       array(
	         'key' => '_wpaesm_date',
	         'value' => $start,
	         'type' => 'CHAR',
	         'compare' => '>='
	       ),
	       array(
	         'key' => '_wpaesm_date',
	         'value' => $end,
	         'compare' => '<='
	       )
	    ),
	    'connected_type' => 'shifts_to_employees',
		'connected_items' => $userid,
	);
	
	$worked = new WP_Query( $args );
	$workedmins = array();

	// The Loop
	if ( $worked->have_posts() ) :
		while ( $worked->have_posts() ) : $worked->the_post();
			// subtract start time from end time for each of those shifts to get number of hours
			global $shift_metabox;
			$meta = $shift_metabox->the_meta();
			if( "actual" == $calculate && isset( $meta['clockin'] ) && isset( $meta['clockout'] ) ) {
				$to_time = strtotime($meta['clockin']);
				$from_time = strtotime($meta['clockout']);
			} elseif( "scheduled" == $calculate && isset( $meta['starttime'] ) && isset( $meta['endtime'] ) ) {
				$to_time = strtotime($meta['starttime']);
				$from_time = strtotime($meta['endtime']);
			}
			$minutes = round(abs($to_time - $from_time) / 60,2);
			$quarters = round($minutes/15) * 15;
			$workedmins[] = $quarters;
		endwhile;
	endif;
	wp_reset_postdata();

	// add up the number of hours
	$allworkedmins = array_sum($workedmins);
	$workedhours = $allworkedmins/60;
	return $workedhours;
}

function wpaesm_ptohours( $userid, $start, $end ) {
	$ptohours = '';
	// find shifts in the PTO category in the given date range, add up the hours
	$args = array( 
		'post_type' => 'shift',
  	    'tax_query' => array(
	    'relation' => 'AND',
	      array(
	        'taxonomy' => 'shift_type',
	        'field' => 'slug',
	        'terms' => 'pto',
	        'operator' => 'IN'
	      )
	    ),
	    'posts_per_page' => -1,
	    'meta_query' => array(
	       array(
	         'key' => '_wpaesm_date',
	         'value' => $start,
	         'type' => 'CHAR',
	         'compare' => '>='
	       ),
	       array(
	         'key' => '_wpaesm_date',
	         'value' => $end,
	         'compare' => '<='
	       )
	    ),
	    'connected_type' => 'shifts_to_employees',
		'connected_items' => $userid,
	);
	
	$ptoquery = new WP_Query( $args );
	$ptomins = array();

	// The Loop
	if ( $ptoquery->have_posts() ) :
		while ( $ptoquery->have_posts() ) : $ptoquery->the_post();
			// subtract start time from end time for each of those shifts to get number of hours
			global $shift_metabox;
			$meta = $shift_metabox->the_meta();
			$to_time = strtotime($meta['starttime']);
			$from_time = strtotime($meta['endtime']);
			$minutes = round(abs($to_time - $from_time) / 60,2);
			$quarters = round($minutes/15) * 15;
			$ptomins[] = $quarters;
		endwhile;
	endif;
	wp_reset_postdata();

	// add up the number of hours
	$allptomins = array_sum($ptomins);
	$ptohours = $allptomins/60;
	return $ptohours;
}

function wpaesm_totalhours( $userid, $start, $end, $calculate ) {
	$totalhours = wpaesm_workedhours( $userid, $start, $end, $calculate ) + wpaesm_ptohours( $userid, $start, $end );
	
	return $totalhours;
}

function wpaesm_mileage( $userid, $start, $end ) {
	$mileage = '';
	// find expenses in the mileage category reported by employee
	$args = array( 
		'post_type' => 'expense',
  	    'tax_query' => array(
	    'relation' => 'AND',
	      array(
	        'taxonomy' => 'expense_category',
	        'field' => 'slug',
	        'terms' => 'mileage',
	        'operator' => 'IN'
	      ),
	    ),
	    'posts_per_page' => -1,
	    'meta_query' => array(
	       array(
	         'key' => '_wpaesm_date',
	         'value' => $start,
	         'type' => 'CHAR',
	         'compare' => '>='
	       ),
	       array(
	         'key' => '_wpaesm_date',
	         'value' => $end,
	         'compare' => '<='
	       )
	    ),
	    'connected_type' => 'expenses_to_employees',
		'connected_items' => $userid,
	);
	
	$mileagequery = new WP_Query( $args );
	$miles = array();

	// The Loop
	if ( $mileagequery->have_posts() ) :
		while ( $mileagequery->have_posts() ) : $mileagequery->the_post();
			// subtract start time from end time for each of those shifts to get number of hours
			global $expense_metabox;
			$meta = $expense_metabox->the_meta();
			$miles[] = $meta['amount'];
		endwhile;
	endif;
	wp_reset_postdata();

	$mileage = array_sum($miles);
	return $mileage;
}

function wpaesm_receipts( $userid, $start, $end ) {
	$receipts = '';
	// find expenses in the receipts category reported by employee
	$args = array( 
		'post_type' => 'expense',
  	    'tax_query' => array(
	    'relation' => 'AND',
	      array(
	        'taxonomy' => 'expense_category',
	        'field' => 'slug',
	        'terms' => 'receipt',
	        'operator' => 'IN'
	      ),
	    ),
	    'posts_per_page' => -1,
	    'meta_query' => array(
	       array(
	         'key' => '_wpaesm_date',
	         'value' => $start,
	         'type' => 'CHAR',
	         'compare' => '>='
	       ),
	       array(
	         'key' => '_wpaesm_date',
	         'value' => $end,
	         'compare' => '<='
	       )
	    ),
	    'connected_type' => 'expenses_to_employees',
		'connected_items' => $userid,
	);
	
	$receiptquery = new WP_Query( $args );
	$reimburse = array();

	// The Loop
	if ( $receiptquery->have_posts() ) :
		while ( $receiptquery->have_posts() ) : $receiptquery->the_post();
			// subtract start time from end time for each of those shifts to get number of hours
			global $expense_metabox;
			$meta = $expense_metabox->the_meta();
			$reimburse[] = $meta['amount'];
		endwhile;
	endif;
	wp_reset_postdata();

	$receipts = array_sum($reimburse);	
	return $receipts;
}

function wpaesm_deduction( $userid, $start, $end ) {
	$all_deductions = array();
	$deductions_health_self = get_user_meta( $userid, 'deductions_health_self', true );
	if(isset($deductions_health_self) && $deductions_health_self != '') {
		$all_deductions[] = $deductions_health_self;
	}
	$deductions_health_family = get_user_meta( $userid, 'deductions_health_family', true );
	if(isset($deductions_health_family) && $deductions_health_family != '') {
		$all_deductions[] = $deductions_health_family;
	}
	$deductions_dental_self = get_user_meta( $userid, 'deductions_dental_self', true );
	if(isset($deductions_dental_self) && $deductions_dental_self != '') {
		$all_deductions[] = $deductions_dental_self;
	}
	$deductions_dental_family = get_user_meta( $userid, 'deductions_dental_family', true );
	if(isset($deductions_dental_family) && $deductions_dental_family != '') {
		$all_deductions[] = $deductions_dental_family;
	}
	$deductions_garnish = get_user_meta( $userid, 'deductions_garnish', true );
	if(isset($deductions_garnish) && $deductions_garnish != '') {
		$all_deductions[] = $deductions_garnish;
	}
	$deductions_withhold = get_user_meta( $userid, 'deductions_withhold', true );
	if(isset($deductions_withhold) && $deductions_withhold != '') {
		$all_deductions[] = $deductions_withhold;
	}
	$deductions_other = get_user_meta( $userid, 'deductions_other', true );
	if(isset($deductions_other) && $deductions_other != '') {
		$all_deductions[] = $deductions_other;
	}

	$deduction = array_sum( $all_deductions );
	return $deduction;
}

function wpaesm_payment( $userid, $start, $end, $calculate ) {
	$payment = '';
	$options = get_option('wpaesm_options');
	// multiply totalhours by employee wage
	$wage = get_user_meta( $userid, 'wage', true );
	if(isset($wage) && $wage != '') {
		$reghours = wpaesm_reghours( $userid, $start, $end, $calculate ) + wpaesm_ptohours( $userid, $start, $end, $calculate );
		$regpay = $wage * $reghours;
		$otpay = $wage * wpaesm_overtime( $userid, $start, $end, $calculate ) * $options['otrate'];

		// add it to mileage and receipts
		$milespay = ($options['mileage'] * wpaesm_mileage( $userid, $start, $end, $calculate ));
		$totalpay = $milespay + wpaesm_receipts( $userid, $start, $end, $calculate ) + $regpay + $otpay;
		// subtract deduction
		$grosspay = $totalpay - wpaesm_deduction( $userid, $start, $end, $calculate );
		$payment = money_format('%i', $grosspay);
	}
	return $payment;
}


// ------------------------------------------------------------------------
// SCHEDULED/WORKED REPORT                               
// ------------------------------------------------------------------------



function wpaesm_scheduled_worked_report() { ?>
	<div class="wrap">
		
		<!-- Display Plugin Icon, Header, and Description -->
		<div class="icon32" id="icon-options-general"><br></div>
		<h2><?php _e('Scheduled vs. Worked Report', 'wpaesm'); ?></h2>

		
		<form method='post' action='<?php echo admin_url( 'admin.php?page=scheduled-worked'); ?>' id='payroll-report'>
			<table class="form-table">
<!-- 				<tr>
					<th scope="row"><?php _e( 'Employee: ', 'wpaesm' ); ?></th>
					<td>
						<select name="employee">
							<option value=""></option>
							<?php $employees = array_merge( get_users( 'role=employee&orderby=nicename' ), get_users( 'role=administrator&orderby=nicename' ) );
							usort( $employees, 'wpaesm_alphabetize' );
							foreach ( $employees as $employee ) { ?>
								<option value="<?php echo $employee->ID; ?>" ><?php echo $employee->display_name; ?></option>
							<?php } ?>
						</select>
					</td>
				</tr> -->
				<tr>
					<th scope="row"><?php _e( 'Date Range: ', 'wpaesm' ); ?></th>
					<td>
						<?php _e( 'From ', 'wpaesm' ); ?>
						<input type="text" size="10" name="thisdate" id="thisdate" value="" /> 
						<?php _e( ' to ', 'wpaesm'); ?> 
						<input type="text" size="10" name="repeatuntil" id="repeatuntil" value="" />
					</td>					
				</tr>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e( 'Generate Report', 'wpaesm' ); ?>" />
			</p>
		</form>

		<?php if( $_POST ) { 
			if( ( $_POST['thisdate'] == '____-__-__') || ( $_POST['repeatuntil'] == '____-__-__' ) ) {
				_e('You must enter both a start date and an end date to create a report.', 'wpaesm');
			} elseif( $_POST['thisdate'] > $_POST['repeatuntil'] ) {
				_e( 'The report end date must be after the report begin date.', 'wpaesm' );
			} else {
				// find all of the shifts
				// can they be ordered by employee?  probably need js for that
				// for each shift, make a row showing shift name, scheduled start/end, actual start/end, difference
				$args = array(
					'post_type' => 'shift',
					'posts_per_page' => -1,
					'meta_query' => array(
						array(
							'key' => '_wpaesm_date',
					         'value' => $_POST['thisdate'],
					         'type' => 'CHAR',
					         'compare' => '>='
							),
						array(
							'key' => '_wpaesm_date',
					         'value' => $_POST['repeatuntil'],
					         'type' => 'CHAR',
					         'compare' => '<='
							)
						),
					'tax_query' => array(
							'relation' => 'AND',
							array(
								'taxonomy' => 'shift_status',
						        'field' => 'slug',
						        'terms' => 'worked',
						        'operator' => 'IN'
							),
							array(
								'taxonomy' => 'shift_type',
						        'field' => 'slug',
						        'terms' => 'extra',
						        'operator' => 'NOT IN'
							)
						)
					);

			$worked = new WP_Query( $args );
			
			// The Loop
			if ( $worked->have_posts() ) { ?>
				<table id="filtered-shifts" class="wp-list-table widefat fixed posts">
					<thead>
						<tr>
							<th data-sort='string'><span><?php _e( 'Shift', 'wpaesm' ); ?></span></th>
							<th data-sort='string'><span><?php _e( 'Date', 'wpaesm' ); ?></span></th>
							<th data-sort='string'><span><?php _e( 'Employee', 'wpaesm' ); ?></span></th>
							<th data-sort='string'><span><?php _e( 'Duration Scheduled', 'wpaesm' ); ?></span></th>
							<th data-sort='string'><span><?php _e( 'Duration Worked', 'wpaesm' ); ?></span></th>
							<th data-sort='string'><span><?php _e( 'Difference', 'wpaesm' ); ?></span></th>
							<th data-sort='string'><span><?php _e( 'Notes', 'wpaesm' ); ?></span></th>
						</tr>
					</thead>
					<tbody>
						<?php while ( $worked->have_posts() ) : $worked->the_post(); 
							$postid = get_the_id();
							global $shift_metabox;
							$meta = $shift_metabox->the_meta(); 
							// get employee associated with this shift
							$users = get_users( array(
								'connected_type' => 'shifts_to_employees',
								'connected_items' => $postid,
							) );
							if( isset( $users ) ) {
								foreach( $users as $user ) {
									$employee = $user->display_name;
									$employeeid = $user->ID;
								}
							}?>
							<tr>	
								<td class="title">	
									<?php the_title(); ?><br />
									<a href="<?php echo get_edit_post_link(); ?>"><?php _e( 'Edit', 'wpaesm' ); ?></a> |
									<a href="<?php the_permalink(); ?>"><?php _e( 'View', 'wpaesm' ); ?></a>
								</td>
								<td class="date">
									<?php if( isset( $meta['date'] ) ) {
										echo $meta['date'];
									} ?>
								</td>
								<td class="employee">
									<?php if( isset( $employeeid ) ) { ?>
										<a href="<?php echo get_edit_user_link( $employeeid ); ?>"><?php echo $employee; ?></a>
										<?php unset( $employeeid);
									} ?>
								</td>
								<td class="scheduled">
									<?php if( isset( $meta['starttime'] ) && $meta['starttime'] !== '____-__-__' && isset( $meta['endtime'] ) && $meta['endtime'] !== '____-__-__') {
										$a = new DateTime($meta['starttime']);
										$b = new DateTime($meta['endtime']);
										$sched_duration = $a->diff($b);
										echo $sched_duration->format("%H:%I");
									} else {
										_e( 'Shift times not set.', 'wpaesm' );
									} ?>
								</td>
								<td class="worked">
									<?php if( !isset( $meta['clockin'] ) || '' == $meta['clockin'] ) {
										_e( 'Employee has not clocked in', 'wpaesm' );
										unset( $work_duration );
									} elseif( ( isset( $meta['clockin'] ) && '' !== $meta['clockin'] ) && ( !isset( $meta['clockout'] ) || '' == $meta['clockout'] ) ) {
										_e( 'Employee has not clocked out.', 'wpaesm' );
										unset( $work_duration );
									} elseif( isset( $meta['clockin'] ) && isset( $meta['clockout'] ) ) {
										$a = new DateTime($meta['clockin']);
										$b = new DateTime($meta['clockout']);
										$work_duration = $a->diff($b);
										echo $work_duration->format("%H:%I");
									} ?>
								</td>
								<td class="difference">
									<?php if( isset( $sched_duration ) && isset( $work_duration ) ) {
										// http://stackoverflow.com/questions/11556731/how-we-can-add-two-date-intervals-in-php
										$e = new DateTime('00:00');
										$f = clone $e;
										$e->add($sched_duration);
										$e->sub($work_duration);
										if( "1" == wpa_date_interval_compare($sched_duration, $work_duration) ) {
											echo "- ";
										} elseif( "-1" == wpa_date_interval_compare($sched_duration, $work_duration) ) {
											echo "+ ";
										}
										echo $f->diff($e)->format("%H:%I");
									} else {
										_e( 'NA', 'wpaesm' ); 
									} ?>	
								</td>
								<td class="notes">
									<?php if( isset( $meta['employeenote'] ) && is_array( $meta['employeenote'] ) ) { ?>
										<ul>
											<?php foreach( $meta['employeenote'] as $note ) { ?>
												<li><strong><?php echo $note['notedate']; ?>: </strong> <?php echo $note['notetext']; ?></li>
											<?php } ?>
										</ul>
									<?php } ?>
								</td>
							</tr>

						<?php endwhile; ?>
					</tbody>
				</table>
			<?php } else {
				echo '<p>' . __( 'Sorry, no shifts were found', 'wpaesm' ) . '</p>';
				}
			}
		} ?>


	</div>
<?php }



function wpa_date_interval_compare($a, $b) { // http://stackoverflow.com/questions/8724710/php-datetimediff-results-comparison


    foreach ($a as $key => $value) {            
        // after seconds 's' comes 'invert' and other crap we do not care about
        // and it means that the date intervals are the same
        if ($key == 'invert') {                
            return 0;
        }

        // when the values are the same we can move on
        if ($a->$key == $b->$key) {
            continue;
        }

        // finally a level where we see a difference, return accordingly
        if ($a->$key < $b->$key) {
            return -1;
        } else {
            return 1;
        }
    }
}

?>