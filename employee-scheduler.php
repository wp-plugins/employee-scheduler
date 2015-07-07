<?php
/*
Plugin Name: Employee Scheduler
Plugin URI: http://wpalchemists.com/plugins
Description: Manage your employees' schedules, let employees view their schedule online, generate timesheets and payroll reports
Version: 1.3
Author: Morgan Kay
Author URI: http://wpalchemists.com
Text Domain: wpaesm
*/

/*  Copyright 2015 Morgan Kay (email : morgan@wpalchemists.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// ------------------------------------------------------------------------
// REQUIRE MINIMUM VERSION OF WORDPRESS:                                               
// ------------------------------------------------------------------------


function wpaesm_requires_wordpress_version() {
	global $wp_version;
	$plugin = plugin_basename( __FILE__ );
	$plugin_data = get_plugin_data( __FILE__, false );

	if ( version_compare($wp_version, "3.8", "<" ) ) {
		if( is_plugin_active($plugin) ) {
			deactivate_plugins( $plugin );
			$msg = __( 'Employee Scheduler cannot be activated, because it requires WordPress version 3.8 or higher!  Please update WordPress and try again.', 'wpaesm' );
			wp_die( $msg );
		}
	}
}
add_action( 'admin_init', 'wpaesm_requires_wordpress_version' );

// ------------------------------------------------------------------------
// REGISTER HOOKS & CALLBACK FUNCTIONS:
// ------------------------------------------------------------------------

// Set-up Action and Filter Hooks
register_activation_hook(__FILE__, 'wpaesm_add_defaults');
register_uninstall_hook(__FILE__, 'wpaesm_delete_plugin_options');
add_action('admin_init', 'wpaesm_options_init' );
add_action('admin_menu', 'wpaesm_add_options_page');


// Require options stuff
require_once( plugin_dir_path( __FILE__ ) . 'options.php' );
// Require views
require_once( plugin_dir_path( __FILE__ ) . 'views.php' );
// Require instructions
require_once( plugin_dir_path( __FILE__ ) . 'instructions.php' );


// Initialize language so it can be translated
function wpaesm_language_init() {
  load_plugin_textdomain( 'wpaesm', false, 'employee-scheduler/languages' );
}
add_action('init', 'wpaesm_language_init');


// ------------------------------------------------------------------------
// REGISTER CUSTOM POST TYPES AND TAXONOMIES:
// ------------------------------------------------------------------------

// Shift Type
function wpaesm_register_tax_shift_type() {

	$labels = array(
		'name'                       => _x( 'Shift Types', 'Taxonomy General Name', 'wpaesm' ),
		'singular_name'              => _x( 'Shift Type', 'Taxonomy Singular Name', 'wpaesm' ),
		'menu_name'                  => __( 'Shift Types', 'wpaesm' ),
		'all_items'                  => __( 'All Shift Types', 'wpaesm' ),
		'parent_item'                => __( 'Parent Shift Type', 'wpaesm' ),
		'parent_item_colon'          => __( 'Parent Shift Type:', 'wpaesm' ),
		'new_item_name'              => __( 'New  Shift Type', 'wpaesm' ),
		'add_new_item'               => __( 'Add New Shift Type', 'wpaesm' ),
		'edit_item'                  => __( 'Edit Shift Type', 'wpaesm' ),
		'update_item'                => __( 'Update Shift Type', 'wpaesm' ),
		'separate_items_with_commas' => __( 'Separate Shift Types with commas', 'wpaesm' ),
		'search_items'               => __( 'Search Shift Types', 'wpaesm' ),
		'add_or_remove_items'        => __( 'Add or remove Shift Types', 'wpaesm' ),
		'choose_from_most_used'      => __( 'Choose from the most used Shift Types', 'wpaesm' ),
		'not_found'                  => __( 'Not Found', 'wpaesm' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'shift_type', array( 'shift' ), $args );

	// Create default statuses
	wp_insert_term(
		'Extra', // the term 
		'shift_type', // the taxonomy
		array(
			'description'=> 'Work done outside of a scheduled shift',
			'slug' => 'extra',
		)
	);

	wp_insert_term(
		'Paid Time Off', // the term 
		'shift_type', // the taxonomy
		array(
			'description'=> 'Paid time that is not a work shift',
			'slug' => 'pto',
		)
	);

}
add_action( 'init', 'wpaesm_register_tax_shift_type', 0 );

// Shift Status
function wpaesm_register_tax_shift_status() {

	$labels = array(
		'name'                       => _x( 'Shift Statuses', 'Taxonomy General Name', 'wpaesm' ),
		'singular_name'              => _x( 'Shift Status', 'Taxonomy Singular Name', 'wpaesm' ),
		'menu_name'                  => __( 'Shift Statuses', 'wpaesm' ),
		'all_items'                  => __( 'All Shift Statuses', 'wpaesm' ),
		'parent_item'                => __( 'Parent Shift Status', 'wpaesm' ),
		'parent_item_colon'          => __( 'Parent Shift Status:', 'wpaesm' ),
		'new_item_name'              => __( 'New Shift Status', 'wpaesm' ),
		'add_new_item'               => __( 'Add New Shift Status', 'wpaesm' ),
		'edit_item'                  => __( 'Edit Shift Status', 'wpaesm' ),
		'update_item'                => __( 'Update Shift Status', 'wpaesm' ),
		'separate_items_with_commas' => __( 'Separate Shift Statuses with commas', 'wpaesm' ),
		'search_items'               => __( 'Search Shift Statuses', 'wpaesm' ),
		'add_or_remove_items'        => __( 'Add or remove Shift Statuses', 'wpaesm' ),
		'choose_from_most_used'      => __( 'Choose from the most used Shift Statuses', 'wpaesm' ),
		'not_found'                  => __( 'Not Found', 'wpaesm' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'shift_status', array( 'shift' ), $args );

	wp_insert_term(
		'Unassigned', // the term 
		'shift_status', // the taxonomy
		array(
			'description'=> 'No one has been assigned to work this shift',
			'slug' => 'unassigned',
		)
	);

	wp_insert_term(
		'Assigned', // the term 
		'shift_status', // the taxonomy
		array(
			'description'=> 'Default status for a shift, indicates that this shift has been assigned to an employee',
			'slug' => 'assigned',
		)
	);

	wp_insert_term(
		'Worked', // the term 
		'shift_status', // the taxonomy
		array(
			'description'=> 'Employee has worked this shift',
			'slug' => 'worked',
		)
	);

}
add_action( 'init', 'wpaesm_register_tax_shift_status', 0 );

// If no other status has been selected, shift will default to 'assigned'
// http://wordpress.mfields.org/2010/set-default-terms-for-your-custom-taxonomies-in-wordpress-3-0/
function wpaesm_default_shift_status( $post_id, $post ) {
    if ( 'publish' === $post->post_status ) {
        $defaults = array(
            'shift_status' => array( 'assigned' ),
            );
        $taxonomies = get_object_taxonomies( $post->post_type );
        foreach ( (array) $taxonomies as $taxonomy ) {
            $terms = wp_get_post_terms( $post_id, $taxonomy );
            if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
                wp_set_object_terms( $post_id, $defaults[$taxonomy], $taxonomy );
            }
        }
    }
}
add_action( 'save_post', 'wpaesm_default_shift_status', 100, 2 );

// Job Category
function wpaesm_register_tax_job_category() {

    $labels = array(
        'name'                       => _x( 'Job Category', 'Taxonomy General Name', 'wpaesm' ),
        'singular_name'              => _x( 'Job Category', 'Taxonomy Singular Name', 'wpaesm' ),
        'menu_name'                  => __( 'Job Categories', 'wpaesm' ),
        'all_items'                  => __( 'All Job Categories', 'wpaesm' ),
        'parent_item'                => __( 'Parent Job Category', 'wpaesm' ),
        'parent_item_colon'          => __( 'Parent Job Category:', 'wpaesm' ),
        'new_item_name'              => __( 'New Job Category', 'wpaesm' ),
        'add_new_item'               => __( 'Add New Job Category', 'wpaesm' ),
        'edit_item'                  => __( 'Edit Job Category', 'wpaesm' ),
        'update_item'                => __( 'Update Job Category', 'wpaesm' ),
        'separate_items_with_commas' => __( 'Separate Job Categories with commas', 'wpaesm' ),
        'search_items'               => __( 'Search Job Categories', 'wpaesm' ),
        'add_or_remove_items'        => __( 'Add or remove Job Categories', 'wpaesm' ),
        'choose_from_most_used'      => __( 'Choose from the most used Job Categories', 'wpaesm' ),
        'not_found'                  => __( 'Not Found', 'wpaesm' ),
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
    );
    register_taxonomy( 'job_category', array( 'job' ), $args );

}
add_action( 'init', 'wpaesm_register_tax_job_category', 0 );

// Shift
function wpaesm_register_cpt_shift() {

	$labels = array(
		'name'                => _x( 'Shifts', 'Post Type General Name', 'wpaesm' ),
		'singular_name'       => _x( 'Shift', 'Post Type Singular Name', 'wpaesm' ),
		'menu_name'           => __( 'Shifts', 'wpaesm' ),
		'parent_item_colon'   => __( 'Parent Shift:', 'wpaesm' ),
		'all_items'           => __( 'All Shifts', 'wpaesm' ),
		'view_item'           => __( 'View Shift', 'wpaesm' ),
		'add_new_item'        => __( 'Add New Shift', 'wpaesm' ),
		'add_new'             => __( 'Add New', 'wpaesm' ),
		'edit_item'           => __( 'Edit Shift', 'wpaesm' ),
		'update_item'         => __( 'Update Shift', 'wpaesm' ),
		'search_items'        => __( 'Search Shifts', 'wpaesm' ),
		'not_found'           => __( 'Not found', 'wpaesm' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'wpaesm' ),
	);
	$args = array(
		'label'               => __( 'shift', 'wpaesm' ),
		'description'         => __( 'Shifts you can assign to employees', 'wpaesm' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', ),
		'taxonomies'          => array( 'shift_type' ),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 70,
		'menu_icon'           => 'dashicons-calendar',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'shift', $args );

}
add_action( 'init', 'wpaesm_register_cpt_shift', 0 );

// Jobs
function wpaesm_register_cpt_job() {

    $labels = array(
        'name'                => _x( 'Jobs', 'Post Type General Name', 'wpaesm' ),
        'singular_name'       => _x( 'Job', 'Post Type Singular Name', 'wpaesm' ),
        'menu_name'           => __( 'Jobs', 'wpaesm' ),
        'parent_item_colon'   => __( 'Parent Job:', 'wpaesm' ),
        'all_items'           => __( 'All Jobs', 'wpaesm' ),
        'view_item'           => __( 'View Job', 'wpaesm' ),
        'add_new_item'        => __( 'Add New Job', 'wpaesm' ),
        'add_new'             => __( 'Add New', 'wpaesm' ),
        'edit_item'           => __( 'Edit Job', 'wpaesm' ),
        'update_item'         => __( 'Update Job', 'wpaesm' ),
        'search_items'        => __( 'Search Jobs', 'wpaesm' ),
        'not_found'           => __( 'Not found', 'wpaesm' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'wpaesm' ),
    );
    $args = array(
        'label'               => __( 'job', 'wpaesm' ),
        'description'         => __( 'jobs', 'wpaesm' ),
        'labels'              => $labels,
        'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions', ),
        'taxonomies'          => array( 'job_category' ),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 70,
        'menu_icon'           => 'dashicons-hammer',
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => true,
        'publicly_queryable'  => true,
        'capability_type'     => 'page',
    );
    register_post_type( 'job', $args );

}
add_action( 'init', 'wpaesm_register_cpt_job', 0 );

// Register Expense Category
function wpaesm_register_tax_expense_category() {

	$labels = array(
		'name'                       => _x( 'Expense Categories', 'Taxonomy General Name', 'wpaesm' ),
		'singular_name'              => _x( 'Expense Category', 'Taxonomy Singular Name', 'wpaesm' ),
		'menu_name'                  => __( 'Expense Categories', 'wpaesm' ),
		'all_items'                  => __( 'All Expense Categories', 'wpaesm' ),
		'parent_item'                => __( 'Parent Expense Category', 'wpaesm' ),
		'parent_item_colon'          => __( 'Parent Expense Category:', 'wpaesm' ),
		'new_item_name'              => __( 'New Expense Category', 'wpaesm' ),
		'add_new_item'               => __( 'Add Expense Category', 'wpaesm' ),
		'edit_item'                  => __( 'Edit Expense Category', 'wpaesm' ),
		'update_item'                => __( 'Update Expense Category', 'wpaesm' ),
		'separate_items_with_commas' => __( 'Separate Expense Categories with commas', 'wpaesm' ),
		'search_items'               => __( 'Search Expense Categories', 'wpaesm' ),
		'add_or_remove_items'        => __( 'Add or remove Expense Categories', 'wpaesm' ),
		'choose_from_most_used'      => __( 'Choose from the most used Expense Categories', 'wpaesm' ),
		'not_found'                  => __( 'Not Found', 'wpaesm' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => false,
		'show_tagcloud'              => false,
	);
	register_taxonomy( 'expense_category', array( 'expense' ), $args );

	wp_insert_term(
		'Mileage', // the term 
		'expense_category', // the taxonomy
		array(
			'description'=> 'Mileage to be reimbursed',
			'slug' => 'mileage',
		)
	);

	wp_insert_term(
		'Receipt', // the term 
		'expense_category', // the taxonomy
		array(
			'description'=> 'Receipts to be reimbursed',
			'slug' => 'receipt',
		)
	);

}
add_action( 'init', 'wpaesm_register_tax_expense_category', 0 );

// Register Expense Status
function wpaesm_register_tax_expense_status() {

	$labels = array(
		'name'                       => _x( 'Expense Statuses', 'Taxonomy General Name', 'wpaesm' ),
		'singular_name'              => _x( 'Expense Status', 'Taxonomy Singular Name', 'wpaesm' ),
		'menu_name'                  => __( 'Expense Statuses', 'wpaesm' ),
		'all_items'                  => __( 'All Expense Statuses', 'wpaesm' ),
		'parent_item'                => __( 'Parent Expense Status', 'wpaesm' ),
		'parent_item_colon'          => __( 'Parent Expense Status:', 'wpaesm' ),
		'new_item_name'              => __( 'New Expense Status', 'wpaesm' ),
		'add_new_item'               => __( 'Add Expense Status', 'wpaesm' ),
		'edit_item'                  => __( 'Edit Expense Status', 'wpaesm' ),
		'update_item'                => __( 'Update Expense Status', 'wpaesm' ),
		'separate_items_with_commas' => __( 'Separate Expense Statuses with commas', 'wpaesm' ),
		'search_items'               => __( 'Search Expense Statuses', 'wpaesm' ),
		'add_or_remove_items'        => __( 'Add or remove Expense Statuses', 'wpaesm' ),
		'choose_from_most_used'      => __( 'Choose from the most used Expense Statuses', 'wpaesm' ),
		'not_found'                  => __( 'Not Found', 'wpaesm' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => false,
		'show_tagcloud'              => false,
	);
	register_taxonomy( 'expense_status', array( 'expense' ), $args );

	wp_insert_term(
		'Reimbursed', // the term 
		'expense_status', // the taxonomy
		array(
			'description'=> 'Expenses for which employee has been reimbursed',
			'slug' => 'reimbursed',
		)
	);

}
add_action( 'init', 'wpaesm_register_tax_expense_status', 0 );


// Expenses
function wpaesm_register_cpt_expense() {

	$labels = array(
		'name'                => _x( 'Expenses', 'Post Type General Name', 'wpaesm' ),
		'singular_name'       => _x( 'Expense', 'Post Type Singular Name', 'wpaesm' ),
		'menu_name'           => __( 'Expenses', 'wpaesm' ),
		'parent_item_colon'   => __( 'Parent Expense:', 'wpaesm' ),
		'all_items'           => __( 'All Expenses', 'wpaesm' ),
		'view_item'           => __( 'View Expense', 'wpaesm' ),
		'add_new_item'        => __( 'Add New Expense', 'wpaesm' ),
		'add_new'             => __( 'Add New', 'wpaesm' ),
		'edit_item'           => __( 'Edit Expense', 'wpaesm' ),
		'update_item'         => __( 'Update Expense', 'wpaesm' ),
		'search_items'        => __( 'Search Expenses', 'wpaesm' ),
		'not_found'           => __( 'Not found', 'wpaesm' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'wpaesm' ),
	);
	$args = array(
		'label'               => __( 'expense', 'wpaesm' ),
		'description'         => __( 'Expenses submitted by employees', 'wpaesm' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', ),
		'taxonomies'          => array( 'expense_category' ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 70,
		'menu_icon'           => 'dashicons-chart-area',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'capability_type'     => 'page',
	);
	register_post_type( 'expense', $args );

}
add_action( 'init', 'wpaesm_register_cpt_expense', 0 );


// ------------------------------------------------------------------------
// CREATE EMPLOYEE USER ROLE
// ------------------------------------------------------------------------

function wpaesm_create_employee_user_role() {
   add_role( 'employee', 'Employee', array( 'read' => true, 'edit_posts' => false, 'publish_posts' => false ) );
}
register_activation_hook( __FILE__, 'wpaesm_create_employee_user_role' );

// create a function to check for the role of the current user
// thanks to http://docs.appthemes.com/tutorials/wordpress-check-user-role-function/
function wpaesm_check_user_role( $role, $user_id = null ) {
 
    if ( is_numeric( $user_id ) )
	$user = get_userdata( $user_id );
    else
        $user = wp_get_current_user();
 
    if ( empty( $user ) )
	return false;
 
    return in_array( $role, (array) $user->roles );
}

// ------------------------------------------------------------------------
// CREATE EMPLOYEE USER FIELDS
// ------------------------------------------------------------------------

add_action( 'show_user_profile', 'wpaesm_employee_profile_fields' );
add_action( 'edit_user_profile', 'wpaesm_employee_profile_fields' );

function wpaesm_employee_profile_fields( $user ) { ?>
	<h3><?php _e("Employee Contact Information", "wpaesm"); ?></h3>

	<table class="form-table">
		<tr>
			<th><label for="address"><?php _e( 'Street Address', 'wpaesm' ); ?></label></th>
			<td>
				<input type="text" name="address" id="address" value="<?php echo esc_attr( get_the_author_meta( 'address', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="city"><?php _e( 'City', 'wpaesm' ); ?></label></th>
			<td>
				<input type="text" name="city" id="city" value="<?php echo esc_attr( get_the_author_meta( 'city', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="state"><?php _e( 'State/Province', 'wpaesm' ); ?></label></th>
			<td>
				<input type="text" name="state" id="state" value="<?php echo esc_attr( get_the_author_meta( 'state', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="zip"><?php _e( 'Zip/Postal Code', 'wpaesm' ); ?></label></th>
			<td>
				<input type="text" name="zip" id="zip" value="<?php echo esc_attr( get_the_author_meta( 'zip', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="phone"><?php _e( 'Phone Number', 'wpaesm' ); ?></label></th>
			<td>
				<input type="text" name="phone" id="phone" value="<?php echo esc_attr( get_the_author_meta( 'phone', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
	</table>
<?php }

add_action( 'personal_options_update', 'wpaesm_save_employee_profile_fields' );
add_action( 'edit_user_profile_update', 'wpaesm_save_employee_profile_fields' );

function wpaesm_save_employee_profile_fields( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }

	update_user_meta( $user_id, 'address', $_POST['address'] );
	update_user_meta( $user_id, 'city', $_POST['city'] );
	update_user_meta( $user_id, 'state', $_POST['state'] );
	update_user_meta( $user_id, 'zip', $_POST['zip'] );
	update_user_meta( $user_id, 'phone', $_POST['phone'] );
}


// ------------------------------------------------------------------------
// CREATE CUSTOM METABOXES
// ------------------------------------------------------------------------

if(!class_exists('WPAlchemy_MetaBox')) {
	include_once 'wpalchemy/MetaBox.php';
	include_once 'wpalchemy/MediaAccess.php';
	
	$wpalchemy_media_access = new WPAlchemy_MediaAccess();
}

define( 'WPAESM_PATH', plugin_dir_path(__FILE__) );

// Add stylesheets and scripts
function wpaesm_add_admin_styles_and_scripts($hook)
{
	wp_enqueue_style( 'wpalchemy-metabox', plugins_url() . '/employee-scheduler/css/meta.css' );
	global $post;  

	if( $hook == 'post-new.php' || $hook == 'post.php' || $hook == 'edit.php' ) {
		if( is_object( $post ) ) {
			if ( 'shift' === $post->post_type || 'expense' === $post->post_type ) { 
			    wp_enqueue_script( 'date-time-picker', plugins_url() . '/employee-scheduler/js/jquery.datetimepicker.js', 'jQuery' );
			    wp_enqueue_script( 'wpaesm_scripts', plugins_url() . '/employee-scheduler/js/wpaesmscripts.js', 'jQuery' );
			    wp_enqueue_script( 'shift-actions', plugin_dir_url(__FILE__) . 'js/shift.js', array( 'jquery' ) );
            	wp_localize_script( 'shift-actions', 'shiftajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) ); 
			}
		}
	}
	if ( 'employee-scheduler_page_payroll-report' == $hook || 'shift_page_add-repeating-shifts' == $hook || 'shift_page_filter-shifts' == $hook || 'expense_page_filter-expenses' == $hook || 'employee-scheduler_page_scheduled-worked' == $hook ) {
		wp_enqueue_script( 'date-time-picker', plugins_url() . '/employee-scheduler/js/jquery.datetimepicker.js', 'jQuery' );
		wp_enqueue_script( 'wpaesm_scripts', plugins_url() . '/employee-scheduler/js/wpaesmscripts.js', 'jQuery' );
	}
	if( 'shift_page_filter-shifts' == $hook || 'employee-scheduler_page_scheduled-worked' == $hook ) {
		wp_enqueue_script( 'stupid-table', plugins_url() . '/employee-scheduler/js/stupidtable.min.js', array( 'jquery' ) );
	}
}
add_action( 'admin_enqueue_scripts', 'wpaesm_add_admin_styles_and_scripts' );

// Create metabox for shifts
$shift_metabox = new WPAlchemy_MetaBox(array
(
    'id' => 'shift_meta',
    'title' => 'Shift Details',
    'types' => array('shift'),
    'template' => WPAESM_PATH . '/wpalchemy/shiftinfo.php',
    'mode' => WPALCHEMY_MODE_EXTRACT,
    'prefix' => '_wpaesm_'
));

// Create metabox for expenses
$expense_metabox = new WPAlchemy_MetaBox(array
(
    'id' => 'expense_meta',
    'title' => 'Expense Details',
    'types' => array('expense'),
    'template' => WPAESM_PATH . '/wpalchemy/expenseinfo.php',
    'mode' => WPALCHEMY_MODE_EXTRACT,
    'prefix' => '_wpaesm_'
));

if( function_exists( 'wpaesp_require_employee_scheduler' ) ) {
	$shift_publish_metabox = new WPAlchemy_MetaBox(array
	(
	    'id' => 'shift_publish_meta',
	    'title' => 'Related Shifts',
	    'types' => array('shift'),
	    'template' => WPAESP_PATH . '/shiftpublish.php',
	    'mode' => WPALCHEMY_MODE_EXTRACT,
	    'prefix' => '_wpaesm_',
	    'context' => 'side',
	    'priority' => 'high'
	));
}


// ------------------------------------------------------------------------
// ADD FIELDS TO TAXONOMIES
// http://en.bainternet.info/wordpress-taxonomies-extra-fields-the-easy-way/
// ------------------------------------------------------------------------

//include the main class file
require_once("Tax-meta-class/Tax-meta-class.php");

/*
* configure taxonomy custom fields
*/
$config = array(
   'id' => 'status_meta_box',
   'title' => 'Shift Status Details',
   'pages' => array('shift_status'),
   'context' => 'normal',
   'fields' => array(),
   'local_images' => false,
   'use_with_theme' => false
);

$status_meta = new Tax_Meta_Class($config);

$status_meta->addColor('status_color',array('name'=> 'Shift Status Color '));

$status_meta->Finish();


// ------------------------------------------------------------------------
// ADD COLUMNS TO SHIFTS OVERVIEW PAGE
// ------------------------------------------------------------------------

function wpaesm_shift_overview_columns_headers($defaults) {
    $defaults['shiftdate'] = 'Shift Date';
    $defaults['shifttime'] = 'Scheduled Time';
    return $defaults;
}
function wpaesm_shift_overview_columns($column_name, $post_ID) {
	global $shift_metabox;
	$meta = $shift_metabox->the_meta();
    if ($column_name == 'shiftdate' && isset($meta['date'])) {
        echo $meta['date'];
    }    
    if ($column_name == 'shifttime' && isset($meta['starttime']) && isset($meta['endtime'])) {
        echo $meta['starttime'] . "-" . $meta['endtime'];
    }    
}

add_filter('manage_shift_posts_columns', 'wpaesm_shift_overview_columns_headers', 10);
add_action('manage_shift_posts_custom_column', 'wpaesm_shift_overview_columns', 10, 2);


// ------------------------------------------------------------------------
// CONNECT SHIFTS TO JOBS AND EMPLOYEES
// https://github.com/scribu/wp-posts-to-posts/blob/master/posts-to-posts.php
// ------------------------------------------------------------------------

function wpaesm_p2p_check() {
	if ( !is_plugin_active( 'posts-to-posts/posts-to-posts.php' ) ) {
		require_once dirname( __FILE__ ) . '/wpp2p/autoload.php';
		define( 'P2P_PLUGIN_VERSION', '1.6.3' );
		define( 'P2P_TEXTDOMAIN', 'cdcrm' );
	}
}
add_action( 'admin_init', 'wpaesm_p2p_check' );

function wpaesm_p2p_load() {
	//load_plugin_textdomain( P2P_TEXTDOMAIN, '', basename( dirname( __FILE__ ) ) . '/languages' );
	if ( !function_exists( 'p2p_register_connection_type' ) ) {
		require_once dirname( __FILE__ ) . '/wpp2p/autoload.php';
	}
	P2P_Storage::init();
	P2P_Query_Post::init();
	P2P_Query_User::init();
	P2P_URL_Query::init();
	P2P_Widget::init();
	P2P_Shortcodes::init();
	register_uninstall_hook( __FILE__, array( 'P2P_Storage', 'uninstall' ) );
	if ( is_admin() )
		wpaesm_load_admin();
}

function wpaesm_load_admin() {
	P2P_Autoload::register( 'P2P_', dirname( __FILE__ ) . '/wpp2p/admin' );

	new P2P_Box_Factory;
	new P2P_Column_Factory;
	new P2P_Dropdown_Factory;

	new P2P_Tools_Page;
}

function wpaesm_p2p_init() {
	// Safe hook for calling p2p_register_connection_type()
	do_action( 'p2p_init' );
}

require dirname( __FILE__ ) . '/wpp2p/scb/load.php';
scb_init( 'wpaesm_p2p_load' );
add_action( 'wp_loaded', 'wpaesm_p2p_init' );

function wpaesm_create_connections() {
    // create the connection between shifts and employees (users)
    p2p_register_connection_type( array(
        'name' => 'shifts_to_employees',
        'from' => 'shift',
        'to' => 'user',
        'cardinality' => 'many-to-one',
        'admin_column' => 'from',
        'to_labels' => array(
			'singular_name' => __( 'Employee', 'wpaesm' ),
			'search_items' => __( 'Search employees', 'wpaesm' ),
			'not_found' => __( 'No employees found.', 'wpaesm' ),
			'create' => __( 'Add Employee', 'wpaesm' ),
		),
    ) );
    // create the connection between expenses and employees (users)
    p2p_register_connection_type( array(
        'name' => 'expenses_to_employees',
        'from' => 'expense',
        'to' => 'user',
        'cardinality' => 'many-to-one',
        'admin_column' => 'from',
        'to_labels' => array(
			'singular_name' => __( 'Employee', 'wpaesm' ),
			'search_items' => __( 'Search employees', 'wpaesm' ),
			'not_found' => __( 'No employees found.', 'wpaesm' ),
			'create' => __( 'Add Employee', 'wpaesm' ),
		),
    ) );
    // create the connection between shifts and jobs
    p2p_register_connection_type( array(
        'name' => 'shifts_to_jobs',
        'from' => 'shift',
        'to' => 'job',
        'cardinality' => 'many-to-one',
        'admin_column' => 'from',
        'to_labels' => array(
            'singular_name' => __( 'Job', 'wpaesm' ),
            'search_items' => __( 'Search jobs', 'wpaesm' ),
            'not_found' => __( 'No jobs found.', 'wpaesm' ),
            'create' => __( 'Add Job', 'wpaesm' ),
        ),
    ) );

    // create the connection between expenses and jobs
    p2p_register_connection_type( array(
        'name' => 'expenses_to_jobs',
        'from' => 'expense',
        'to' => 'job',
        'cardinality' => 'many-to-one',
        'admin_column' => 'from',
        'to_labels' => array(
            'singular_name' => __( 'Job', 'wpaesm' ),
            'search_items' => __( 'Search jobs', 'wpaesm' ),
            'not_found' => __( 'No jobs found.', 'wpaesm' ),
            'create' => __( 'Add Job', 'wpaesm' ),
        ),
    ) );
}
add_action( 'p2p_init', 'wpaesm_create_connections' );


// ------------------------------------------------------------------------
// SEND NOTIFICATION WHEN SHIFT IS CREATED - http://codex.wordpress.org/Plugin_API/Action_Reference/save_post
// ------------------------------------------------------------------------

function wpaesm_get_latest_priority( $filter ) // figure out what priority the notify employee function needs, thanks to http://wordpress.stackexchange.com/questions/116221/how-to-force-function-to-run-as-the-last-one-when-saving-the-post
{
    if ( empty ( $GLOBALS['wp_filter'][ $filter ] ) )
        return PHP_INT_MAX;

    $priorities = array_keys( $GLOBALS['wp_filter'][ $filter ] );
    $last       = end( $priorities );

    if ( is_numeric( $last ) )
        return PHP_INT_MAX;

    return "$last-z";
}
add_action( 'save_post', 'wpaesm_run_that_action_last', 0 ); 

function wpaesm_notify_employee( $post_id ) {
	if(is_admin()) { // we only need to run this function if we're in the dashboard
		$options = get_option('wpaesm_options'); // get options
		global $shift_metabox; // get metabox data
		$meta = $shift_metabox->the_meta( $post_id ); 
		if( isset( $meta['notify'] ) && "1" == $meta['notify'] ) {  // only send the email if the "notify employee" option is checked 
			// get the employee id
			$users = get_users( array(
				'connected_type' => 'shifts_to_employees',
				'connected_items' => $post_id,
			) );
			foreach($users as $user) {
				$employeeid = $user->ID;
			}
			// get the job 
			$jobs = new WP_Query( array(
				'connected_type' => 'shifts_to_jobs',
				'connected_items' => $post_id,
			) );
			if ( $jobs->have_posts() ) {
				while ( $jobs->have_posts() ) : $jobs->the_post();
					$jobname = get_the_title();
				endwhile;
			} else {
				$jobname = '';
			}
			wp_reset_postdata();
			// get the other meta data
			if( isset( $meta['date'] ) ) {
				$date = $meta['date'];
			} else {
				$date = '';
			}
			if( isset( $meta['starttime'] ) ) {
				$starttime = $meta['starttime'];
			} else {
				$starttime = '';
			}
			if( isset( $meta['endtime'] ) ) {
				$endtime = $meta['endtime'];
			} else {
				$endtime = '';
			}

			// send the email
			if( !isset( $employeeid ) ) {
				$error = __( 'We could not send a notification, because you did not select an employee.  Click your back button and select an employee for this shift, or uncheck the employee notification option.', 'wpaesm' );
				wp_die( $error );
			}
			if( isset( $employeeid ) && isset( $jobname ) ) {
				wpaesm_send_notification_email( $employeeid, $jobname, $date, $starttime, $endtime, '', '', $post_id );
			}
		}
	}
}

function wpaesm_run_that_action_last() {  // add the notification action now, with lowest priority so it runs after meta data has been saved
    add_action( 
        'save_post', 
        'wpaesm_notify_employee',
        wpaesm_get_latest_priority( current_filter() ),
        2 
    ); 

}

function wpaesm_send_notification_email( $employeeid, $jobname, $date, $starttime, $endtime, $repeatdays, $repeatuntil, $postid ) {
	$employeeinfo = get_user_by( 'id', $employeeid );
	$employeeemail = $employeeinfo->user_email;	
	$options = get_option('wpaesm_options');

	$to = $employeeemail;

	$subject = $options['notification_subject'];

	$message = __( 'You have been scheduled to work the following shift: ', 'wpaesm' ) . "\n\n";
	if( isset( $date ) && '' !== $date ) {
		$message .= __( 'Date: ', 'wpaesm' ) . $date . "\n";
	}
	if( isset( $starttime ) && '' !== $starttime && isset( $endtime ) && '' !== $endtime ) {
		$message .= __( 'Time: ', 'wpaesm' ) . $starttime . " - " . $endtime . "\n";
	}
	if( isset( $jobname ) && '' !== $jobname ) {
		$message .= __( 'Doing the job: ', 'wpaesm' ) . $jobname . "\n";
	}
	if( isset( $repeatdays ) && '' !== $repeatdays ) {
		$message .= __( 'This shift repeats every ', 'wpaesm' );
		$message .= implode(', ', $repeatdays);
		$message .= __( ' until ', 'wpaesm' );
		$message .= $repeatuntil;
	}
	$content = wp_strip_all_tags( get_post_field( 'post_content', $postid ) );
	if( isset( $content ) && !empty( $content ) ) {
		$message .= __( 'Shift Details: ', 'wpaesm' ) . "\n\n" . $content;
	}

	$headers = "From: " . $options['notification_from_name'] . "<" . $options['notification_from_email'] . ">";

	wp_mail( $to, $subject, $message, $headers );
}

?>