<?php  
/*
  Plugin Name: Team Event Calendar
  Plugin URI: http://susanltyler.com/team-event-calendar-plugin
  Description: An event calendar that provides event entry, storage, editing, and 
  display. Geared towards teams or other groups that need to display season
  calendars and upcoming events on their Wordpress site.
  Author: S. Tyler 
  Version: 2.0 
  Author URI: http://susanltyler.com
*/

require_once(ABSPATH . '/wp-content/plugins/wp-plugin-helper/wp_display_helper.php');

// must be declared globally to work during install/uninstall
global $events_table, $event_params, $wpdb;

$events_table = $wpdb->prefix . "tec_events";
$event_params = array(
  new TableField("title","VARCHAR(255)"),
  new TableField("date","DATE"),
  new TableField("time","VARCHAR(20)"),
  new TableField("location","VARCHAR(255)"),
  new TableField("description","VARCHAR(500)")
);

register_activation_hook(__FILE__,'tec_install');
register_deactivation_hook(__FILE__,'tec_uninstall'); 

add_shortcode('calendar','tec_display_user_calendar');
add_shortcode('upcoming_events','tec_display_upcoming_events');

add_action('admin_menu','tec_admin_setup');  
add_action('wp_ajax_tec_save_event','tec_save_event');
add_action('wp_ajax_tec_delete_event','tec_delete_event');

/*
  Calls functions necessary for plugin install.
  -Creates table in database for events.
 */
function tec_install() {
	global $events_table, $event_params, $wpdb;
	create_table($events_table,$event_params);
}

/*
  Calls functions necessary for plugin uninstall. 
  -Drops events table in database.
 */
function tec_uninstall() {
	global $events_table;
	drop_table($events_table);
}

/*
  Registers style sheets and menu pages.
 */
function tec_admin_setup() {  
	wp_register_style('bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css');
	wp_register_style('jquery-ui', '//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css');
	wp_register_style('tec-style', plugins_url('css/team_event_calendar_style.css', __FILE__));
	add_menu_page('Team Events', 'Team Events', 'administrator', 'team_events_calendar', 'tec_admin');  
	add_submenu_page('team_events_calendar', 'Add New Event', 'Add New Event', 'administrator', 'tec-event', 'tec_event_page');
	add_submenu_page(NULL, 'Edit Event', 'Edit Event', 'administrator', 'tec-event', 'tec_event_page');
} 

/*
  Called by add_menu_page to provide main plugin menu page.
 */
function tec_admin() {  
	include('tec_admin_page.php');  
}

/*
  Called by add_submenu_page to provide plugin page for adding/updating
  events.
 */
function tec_event_page() {
	include('tec_event_page.php');
}

/*
  Shortcode function for displaying upcoming events.
 */
function tec_display_upcoming_events($atts) {
	echo tec_get_upcoming_events();
}

/*
  Shortcode function for displaying all events in a table.
 */
function tec_display_user_calendar($atts) {
	global $events_table, $event_params;
	echo get_basic_table($events_table, $event_params, "tec_calendar");
}

/*
  Displays a calendar with all events, plus a link to an edit page for each
  event and a column allowing the admin user to delete entries with one click.
 */
function tec_display_admin_calendar() {
	global $events_table, $event_params;
	echo get_admin_table($events_table, $event_params, "tec_calendar", "title", "tec-event");
}

/*
  Returns a form for editing an event.
 */
function tec_edit_event_form($id) {
	global $events_table, $event_params;
	echo get_basic_form($event_params, "event_form", true, get_item_by_id($events_table, $id));
}

/*
  Returns a form for adding an event.
 */
function tec_add_event_form() {
	global $event_params;
	echo get_basic_form($event_params, "event_form");
}

/*
  Saves an event passed by POST data (see tec_event_page.php).
 */
function tec_save_event() {
	check_ajax_referer('tec_nonce_save','security');
	global $events_table, $event_params;
	if(!empty($_POST['id'])) {
		$success = update_table_item($events_table,$event_params,$_POST);
	} else {
		$success = save_table_item($events_table,$event_params,$_POST);
	}
	if($success) {
		echo "Event successfully saved";
	} else {
		echo "ERROR; event not saved";
	}
	die();
}

/*
  Deletes an event passed by POST data (see tec_admin_page.php).
 */
function tec_delete_event() {
	check_ajax_referer('tec_nonce_del','security');
	global $events_table;
	delete_table_item($events_table, $_POST);
	die();
}

/*
  Returns an HTML list of upcoming events.
 */
function tec_get_upcoming_events() {
	global $events_table;
	$upcoming_events = get_recent_items($events_table, 3);
	$ul = '';
	if($upcoming_events != '') {	
		$event_page = get_page_by_title('Event');
		$ul = '<ul id="tec_upcoming">';
		foreach($upcoming_events as $event) {
			$ul .= '<li><span class="event_title">';
			$ul .= $event->title;
			$ul .= '</span><span class="event_date">';
			$ul .= format_date($event->date,'d/m/Y');
			$ul .= '</span></li>';
		}
		$ul .= '</ul>';
	}	
	return $ul;
}

/*function tec_event_template() {
  if(is_page('event')) {
    $page_template = dirname( __FILE__ ) . '/event.php';
  }
  return $page_template;
}*/
?>