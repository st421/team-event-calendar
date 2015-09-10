<?php  
/*
    Plugin Name: Team Event Calendar
    Plugin URI: https://github.com/st421/team-event-calendar/
    Description: An event calendar for easy display of upcoming events geared towards sports teams.
    Author: S. Tyler 
    Version: 1.0 
    Author URI: susanltyler.com
*/     

// Hooks, shortcodes, global variables, etc.
$events_table = $wpdb->prefix . "tec_events";
register_activation_hook(__FILE__,'tec_install');
register_deactivation_hook(__FILE__, 'tec_uninstall'); 
add_shortcode('calendar','tec_display_calendar');
add_shortcode('upcoming_events','tec_display_upcoming_events');
add_action('admin_menu','tec_admin_actions');  
add_action('wp_ajax_tec_add_event','tec_save_event');
add_action('wp_ajax_admin_remove_event','tec_admin_delete_event');
add_filter('query_vars', 'tec_add_event_vars');

/*
 * Registers style sheets, menu pages, and scripts for the plugin.
 */
function tec_admin_actions() {  
	wp_register_style('teamEventCalendarStyle', plugins_url('/css/team_event_calendar_style.css', __FILE__));
	wp_register_style('datePickerCSS', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/base/jquery-ui.css');
	wp_register_script('datePickerJS', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js');
	add_menu_page('Team Events', 'Team Events', 'administrator', 'team_events_calendar', 'tec_admin');  
	add_submenu_page('team_events_calendar', 'Add New Event', 'Add New Event', 'administrator', 'tec_add_new_event', 'tec_add_new_event');
	add_submenu_page(NULL, 'Edit Event', 'Edit Event', 'administrator', 'tec_edit_old_event', 'tec_edit_old_event');
} 

function tec_admin() {  
        include('tec_admin_page.php');  
}

function tec_add_new_event() {
	include('tec_add_new_event_page.php');
}

function tec_edit_old_event() {
	include('tec_edit_event_page.php');
}

function tec_add_event_vars($vars) {
	$vars[0] = 'title';
	$vars[1] = 'date';
	return $vars;
}

function tec_event_template() {
    if(is_page('event')) {
        $page_template = dirname( __FILE__ ) . '/event.php';
    }
    return $page_template;
}

/*
 * Upon install, create a new database for the event calendar.
 */
function tec_install() {
	global $wpdb;
	$event = new Event();
	$events_table = $wpdb->prefix . "tec_events";
	if($wpdb->get_var("SHOW TABLES LIKE '$events_table'") != $events_table) {
		$sql_query = "CREATE TABLE " . $events_table . " (id int NOT NULL AUTO_INCREMENT,";
		$sql_query .= $event->table_row_init();
		$sql_query .= "PRIMARY KEY (id));";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql_query);
	}
}

/*
 * Gets user submitted data and enters it into the event database.
 */
function tec_save_event() {
	$success_message = "Event successfully updated!";
	$failure_message = "Oops! Something went wrong!";
	$event = new Event();
	check_ajax_referer('tec_nonce_1');
	$event_id = $_POST['id'];
	$event->set_fields($_POST['title'], $_POST['date'], $_POST['time'], $_POST['location'], $_POST['brief'], $_POST['description']);

	if(tec_save_event_data($event, $event_id)) {
		echo $success_message;
	} else {
		echo $failure_message;
	}
	die();
} 

function tec_admin_delete_event() {
	$event_id = $_POST['ID_to_delete'];
	tec_delete_event_data($event_id);
}

/*
 * Saves a new event to the database or updates
 * an existing event if a valid ID is provided.
 */
function tec_save_event_data($event, $id = NULL) {
	global $wpdb, $events_table;
	if(is_null($id)) {
		$query_vars = " (" . $event->table_row_query() . ") ";
		$query_vals = " (" . $event->table_row_values() . ")";
		$query = "INSERT INTO " . $events_table . $query_vars . "VALUES" . $query_vals . ";";
	} else {
		$query_values = " SET " . $event->table_row_update();
		$query = "UPDATE " . $events_table . $query_values . " WHERE id=" . $id . ";";
	}
	if($wpdb->query($query)) return 1; else return 0;
}


/*
 * Removes an event from the database.
 */
function tec_delete_event_data($id) {
	global $wpdb, $events_table;
	$query = "DELETE FROM " . $events_table . " WHERE id='" . $id . "';";
	$wpdb->query($query);
}

function tec_display_calendar($atts) {
	global $wpdb, $events_table;
	$calendar_events = $wpdb->get_results("SELECT * FROM " . $events_table . " ORDER BY date ASC;");
	$main_event = new Event();
	$table_str = "<table>" . $main_event->header() . "<tbody>";
	foreach($calendar_events as $event) {
		$main_event->set_values($event->title,$event->date,$event->time,$event->location,$event->brief,$event->description);
		$table_str .= $main_event->table_row_string();
	}
	$table_str .= "</tbody></table>";
}

function tec_display_upcoming_events($atts) {
	global $wpdb, $events_table;
	$query = "SELECT * FROM " . $events_table . " WHERE date >= DATE_FORMAT(NOW(),'%Y-%m-%d') ORDER BY date ASC LIMIT 3;";
	$upcoming_events = $wpdb->get_results($query);
	if($upcoming_events != '') {
		$event_page = get_page_by_title('Event');
		$main_event = new Event();
		$list_str = '<ul>';
		foreach($upcoming_events as $event) {
			$main_event->set_values($event->title,$event->date,$event->time,$event->location,$event->brief,$event->description);
			$list_str .= $main_event->list_element_string($event_page->id);
		}
		$list_str .= '</ul>';
		echo $list_str;
	}
}

/* 
 * Upon uninstalling the plugin, remove the tables created. 
*/
function tec_uninstall() {
	global $wpdb, $events_table;
	$wpdb->query("DROP TABLE IF EXISTS " . $events_table . ";");
}
?>
