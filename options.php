<?php
// Delete options table entries ONLY when plugin deactivated AND deleted
function wpaesm_delete_plugin_options() {
	delete_option('wpaesm_options');
}

// Define default option settings
function wpaesm_add_defaults() {
	$tmp = get_option('wpaesm_options');
    if(!is_array($tmp)) {
		delete_option('wpaesm_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
		$defaultfromname = get_bloginfo('name');
		$defaultfromemail = get_bloginfo('admin_email');
		$arr = array(	"notification_from_name" => $defaultfromname,
						"notification_from_email" => $defaultfromemail,
						"notification_subject" => "You have been scheduled for a work shift",
						"admin_notification_email" => $defaultfromemail,
						"week_starts_on" => "Monday",
						"hours" => "40",
						"otrate" => "1.5",
						"mileage" => ".56",
						"calculate" => "actual"
		);
		update_option('wpaesm_options', $arr);
	}
}

// Add menu page
function wpaesm_add_options_page() {
	add_menu_page( 
		'Employee Scheduler', 
		'Employee Scheduler', 
		'manage_options', 
		'/employee-schedule-manager/options.php', 
		'wpaesm_render_options', 
		'dashicons-admin-generic', 
		87.2317 
	);
	add_submenu_page( '/employee-schedule-manager/options.php', 'Instructions', 'Instructions', 'manage_options', 'instructions', 'wpaesm_instructions' );
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION SPECIFIED IN: add_options_page()
// ------------------------------------------------------------------------------


// Create Options
function wpaesm_options_init(){

	register_setting( 'wpaesm_plugin_options', 'wpaesm_options', 'wpaesm_validate_options' );

	add_settings_section(
		'wpaesm_main_section', 
		__( 'Settings', 'wpaesm' ), 
		'wpaesm_options_section_callback', 
		'wpaesm_plugin_options'
	);

	// TO DO - for public plugin, prefix all settings
	add_settings_field(
		'notification_from_name',
		__( 'Message Sender (Name)', 'wpaesm' ),
		'wpaesm_notification_from_name_render',
		'wpaesm_plugin_options',
		'wpaesm_main_section',
		array( 
			__( 'Email notifications sent to employees will come from this name', 'wpaesm' )
		)
	);

	add_settings_field(
		'notification_from_email',
		__( 'Message Sender (Email Address)', 'wpaesm' ),
		'wpaesm_notification_from_email_render',
		'wpaesm_plugin_options',
		'wpaesm_main_section',
		array( 
			__( 'Email notifications sent to employees will come from this email address', 'wpaesm' )
		)
	);

	add_settings_field(
		'notification_subject',
		__( 'Message Sender (Email Address)', 'wpaesm' ),
		'wpaesm_notification_subject_render',
		'wpaesm_plugin_options',
		'wpaesm_main_section',
		array( 
			__( 'Email notifications sent to employees about scheduled shifts will have this subject', 'wpaesm' )
		)
	);

	add_settings_field(
		'admin_notify_status',
		__( 'Shift Status Notification', 'wpaesm' ),
		'wpaesm_admin_notify_status_render',
		'wpaesm_plugin_options',
		'wpaesm_main_section',
		array( 
			__( 'Notify admin when an employee changes shift status', 'wpaesm' )
		)
	);

	add_settings_field(
		'admin_notify_note',
		__( 'Shift Note Notification', 'wpaesm' ),
		'wpaesm_admin_notify_note_render',
		'wpaesm_plugin_options',
		'wpaesm_main_section',
		array( 
			__( 'Notify admin when an employee adds a note to a shift', 'wpaesm' )
		)
	);

	add_settings_field(
		'admin_notification_email',
		__( 'Admin Notification Email', 'wpaesm' ),
		'wpaesm_admin_notification_email_render',
		'wpaesm_plugin_options',
		'wpaesm_main_section',
		array( 
			__( 'Enter the email address that will receive email notifications about employee activities', 'wpaesm' )
		)
	);

	add_settings_field(
		'geolocation',
		__( 'Geolocation', 'wpaesm' ),
		'wpaesm_geolocation_render',
		'wpaesm_plugin_options',
		'wpaesm_main_section',
		array( 
			__( 'Check to record the location where employees clock in and out', 'wpaesm' )
		)
	);

	add_settings_field(
		'week_starts_on',
		__( 'Week Starts On:', 'wpaesm' ),
		'wpaesm_week_starts_on_render',
		'wpaesm_plugin_options',
		'wpaesm_main_section',
		array( 
			__( 'For scheduling purposes, what day does the work-week start on?', 'wpaesm' )
		)
	);

}

function wpaesm_notification_from_name_render( $args ) {
	$options = get_option( 'wpaesm_options' ); ?>

	<input type="text" size="57" name="wpaesm_options[notification_from_name]" value="<?php echo $options['notification_from_name']; ?>" />
	<br /><span class="description"><?php echo $args[0]; ?></span>

<?php }

function wpaesm_notification_from_email_render( $args ) {
	$options = get_option( 'wpaesm_options' ); ?>

	<input type="text" size="57" name="wpaesm_options[notification_from_email]" value="<?php echo $options['notification_from_email']; ?>" />
	<br /><span class="description"><?php echo $args[0]; ?></span>
<?php }

function wpaesm_notification_subject_render( $args ) {
	$options = get_option( 'wpaesm_options' ); ?>

	<input type="text" size="57" name="wpaesm_options[notification_subject]" value="<?php echo $options['notification_subject']; ?>" />
	<br /><span class="description"><?php echo $args[0]; ?></span>
<?php }

function wpaesm_admin_notify_status_render( $args ) {
	$options = get_option( 'wpaesm_options' ); ?>

	<label><input name="wpaesm_options[admin_notify_status]" type="checkbox" value="1" <?php if (isset($options['admin_notify_status'])) { checked('1', $options['admin_notify_status']); } ?> /> <?php _e('Turn on shift status notifications', 'wpaesm'); ?></label>
	<br /><span class="description"><?php echo $args[0]; ?></span>
<?php }

function wpaesm_admin_notify_note_render( $args ) {
	$options = get_option( 'wpaesm_options' ); ?>

	<label><input name="wpaesm_options[admin_notify_note]" type="checkbox" value="1" <?php if (isset($options['admin_notify_note'])) { checked('1', $options['admin_notify_note']); } ?> /> <?php _e('Turn on shift note notifications', 'wpaesm'); ?></label>
	<br /><span class="description"><?php echo $args[0]; ?></span>
<?php }

function wpaesm_admin_notification_email_render( $args ) {
	$options = get_option( 'wpaesm_options' ); ?>

	<input type="text" size="57" name="wpaesm_options[admin_notification_email]" value="<?php echo $options['admin_notification_email']; ?>" />
	<br /><span class="description"><?php echo $args[0]; ?></span>
<?php }

function wpaesm_geolocation_render( $args ) {
	$options = get_option( 'wpaesm_options' ); ?>

	<label><input name="wpaesm_options[geolocation]" type="checkbox" value="1" <?php if (isset($options['geolocation'])) { checked('1', $options['geolocation']); } ?> /> <?php _e('Record location when employees clock in and clock out', 'wpaesm'); ?></label>
	<br /><span class="description"><?php echo $args[0]; ?></span>
<?php }

function wpaesm_week_starts_on_render( $args ) {
	$options = get_option( 'wpaesm_options' ); ?>

	<select name='wpaesm_options[week_starts_on]'>
		<option value='Sunday' <?php selected('Sunday', $options['week_starts_on']); ?>><?php _e('Sunday', 'wpaesm'); ?></option>
		<option value='Monday' <?php selected('Monday', $options['week_starts_on']); ?>><?php _e('Monday', 'wpaesm'); ?></option>
		<option value='Tuesday' <?php selected('Tuesday', $options['week_starts_on']); ?>><?php _e('Tuesday', 'wpaesm'); ?></option>
		<option value='Wednesday' <?php selected('Wednesday', $options['week_starts_on']); ?>><?php _e('Wednesday', 'wpaesm'); ?></option>
		<option value='Thursday' <?php selected('Thursday', $options['week_starts_on']); ?>><?php _e('Thursday', 'wpaesm'); ?></option>
		<option value='Friday' <?php selected('Friday', $options['week_starts_on']); ?>><?php _e('Friday', 'wpaesm'); ?></option>
		<option value='Saturday' <?php selected('Saturday', $options['week_starts_on']); ?>><?php _e('Saturday', 'wpaesm'); ?></option>
	</select>
	<br /><span class="description"><?php echo $args[0]; ?></span>
<?php }



// Render the Plugin options form
function wpaesm_render_options() {
	?>

	<div class="wrap">
		<h2><?php _e( 'Employee Scheduler', 'wpaesm' ); ?></h2>
		<?php settings_errors(); ?>

		<div id="main" style="width: 75%; float: left">
			<form method="post" action="options.php">
				<?php
				settings_fields( 'wpaesm_plugin_options' );
				do_settings_sections( 'wpaesm_plugin_options' );
				submit_button();
				?>
			</form>
		</div>

		<aside style="width: 24%; float: right;">
			<?php do_action( 'wpaesm_options_sidebar' ); ?>
		</aside>

	</div>
	<?php	
}

function wpaesm_options_section_callback() {

}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wpaesm_validate_options($input) {
	 // strip html from textboxes
	$input['notification_from_name'] =  wp_filter_nohtml_kses($input['notification_from_name']); 
	$input['notification_from_email'] =  wp_filter_nohtml_kses($input['notification_from_email']);
	$input['notification_subject'] =  wp_filter_nohtml_kses($input['notification_subject']);
	$input['admin_notification_email'] =  wp_filter_nohtml_kses($input['admin_notification_email']);
	return $input;
}

function wpaesm_display_options_sidebar() { ?>
	<h3><?php _e( 'Upgrade to Pro!', 'wpaesm' ); ?></h3>
	<p><?php _e( 'Employee Scheduler Pro has more features to make it easier to manage your employees!', 'wpaesm' ); ?></p>
	<ul>
		<li>* <?php _e( 'Bulk create shifts', 'wpaesm' ); ?></li>
		<li>* <?php _e( 'Bulk edit shifts', 'wpaesm' ); ?></li>
		<li>* <?php _e( 'Create payroll reports', 'wpaesm' ); ?></li>
		<li>* <?php _e( 'Easily filter shifts and expenses on several criteria', 'wpaesm' ); ?></li>
		<li>* <?php _e( 'View report comparing employees\' scheduled hours to hours actually worked', 'wpaesm' ); ?></li>
	</ul>
	<p><a href="https://wpalchemists.com/downloads/employee-scheduler-pro/" target="_blank" class="button button-primary">
		<?php _e( 'Uprade to Pro', 'wpaesm' ); ?>
	</a></p>
	<h3><?php _e( 'Donate now!', 'wpaesm' ); ?></h3>
	<p><?php _e( 'Show your appreciation and support continued development of this plugin.', 'wpaesm' ); ?></p>
	<p><a href="https://wpalchemists.com/donate/" target="_blank" class="button button-primary">
		<?php _e( 'Donate', 'wpaesm' ); ?>
	</a></p>
<?php }
add_action( 'wpaesm_options_sidebar', 'wpaesm_display_options_sidebar', 10 );

?>