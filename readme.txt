=== Employee Scheduler ===
Contributors: gwendydd, jpkay
Tags: employee, schedule, clock in, clock out, payroll, work schedule, timesheet, volunteer schedule
Donate link: https://wpalchemists.com/donate/
Requires at least: 3.8
Tested up to: 4.3.1
Stable tag: trunk
License: GPL 2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Complete employee schedule management system: create and display schedule, let employees clock in and out, report expenses.

== Description ==
The Employee Scheduler does everything you need to keep track of your employees' schedules!  It works just as well for managing volunteer schedules.

* Create a work schedule for employees
* Send email notifications to employees when their shifts are created or updated
* Display the schedule on your website - only logged-in users will see it
* Employees can clock in and clock out
* Employees can report expenses and mileage

Upgrade to [Employee Scheduler Pro](https://wpalchemists.com/downloads/employee-scheduler-pro/) for even more features!

* Bulk create shifts
* Bulk edit shifts
* Employees can claim unassigned shifts
* Employees can drop shifts
* Create payroll reports
* Easily filter shifts and expenses on several criteria
* View report comparing employees' scheduled hours to hours actually worked
* personal, priority support

== Installation ==
The Plugin can be installed directly from the main WordPress Plugin page.

1. Go to the Plugins => Add New page.
2. Enter 'Employee Scheduler' (without quotes) in the textbox and click the 'Search Plugins' button.
3. In the list of relevant Plugins click the 'Install' link for Plugin Options Starter Kit on the right hand side of the page.
4. Click the 'Install Now' button on the popup page.
5. Click 'Activate Plugin' to finish installation.
6. That's it!

After installation, you can go to Employee Scheduler --> Instructions in your WordPress dashboard to learn how to configure the plugin.

== Frequently Asked Questions ==
= Will anyone who visits my website be able to see the schedule? =
No - only logged in users with administrator or employee user roles can see the schedule, the single shift view, or any other Employee Scheduler pages.

== Screenshots ==
1. Plugin settings page
2. Creating a job
3. Creating a shift
4. Master schedule
5. Your schedule - the employee who is logged in sees only their own shifts
6. Single shift view - if the shift is assigned to the logged-in user who is viewing the shift, and the shift is today, a "clock in" button appears.  If they have already clocked in, they will see a "clock out" button.
7. Expense report form
8. Extra work form, for reporting work that is not a part of a scheduled shift

== Changelog ==
= 1.7.0 =
* added option to send an email to site admin whenever an employee clocks out
* added option to require admin approval before counting extra shifts as worked

= 1.6.0 =
* error messages will display if clock in and clock out times are not recorded
* added new admin page to view schedules
* added location taxonomy to shifts
* updated code documentation

= 1.5.0 =
* when employee fills out "extra work" form, the scheduled time fields are filled in
* make shifts show up on schedule when no job is assigned
* make shifts show up on schedule when no employee is assigned
* add HTML to emails
* change h2 to h1 on admin screens to conform to new accessibility standards
* improved options validation
* added filters to accommodate new features in Employee Scheduler Pro (ability to drop and claim shifts)
* fixed compatibility problem with wpp2p and WP 4.3

= 1.4.2 =
* fix compatibility issues with other plugins using WPP2P

= 1.4.1 =
* improved geolocation error reporting
* minor changes to work with schedule conflict checking in Employee Scheduler Pro

= 1.4 =
* updated datetimepicker js library
* fixed bugs in [employee_profile] shortcode

= 1.3 =
* fixed header on "your schedule" shortcode to show the correct dates
* when clock out button is visible, clock in time is displayed
* fixed formatting issues on [employee_profile] shortcode

= 1.2 =
* made shift email notifications more reliable
* improved shift status change notification email

= 1.1 =
* removed conflict with other plugins that use WP Alchemy for metaboxes
* removed extraneous files
* minor fixes to be compatible with Employee Scheduler Pro

= 1.0 = 
* Initial release