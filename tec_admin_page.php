<?php wp_enqueue_style('teamEventCalendarStyle'); ?>
<?php tec_admin_remove_event(); ?>
<h1>Team Events Calendar Plugin</h1>
<h3>Events</h3>
<?php tec_display_events_table(); 

function tec_admin_remove_event() {
?>
<script type="text/javascript">
<!--
jQuery(document).ready(function() {
	jQuery("td.delete").click(function() {
		var $this = jQuery(this);
		jQuery.ajax({
			type: "post",
			url: "admin-ajax.php",
			data: {action: 'admin_remove_event', ID_to_delete: $this.parent().children('td.id_span').html()},
			success: function(data){ 
				$this.parent().fadeOut('slow');
			}
		});
		return false;
	});
});
-->
</script>
<?php }

function tec_display_events_table() {
	global $wpdb, $events_table;
	$events = $wpdb->get_results("SELECT * FROM " . $events_table . " ORDER BY date ASC;");
	echo '<table class="widefat"><thead><th>ID</th><th>Title</th><th>Date</th><th>Time</th><th>Location</th><th>Brief</th><th>Description</th><th>Delete?</th></thead><tbody>';
	foreach($events as $event) {
		$event_date = tec_format_date($event->date,'-','/');
		$path = 'admin.php?page=tec_edit_old_event&id=' . $event->id;
		$url = admin_url($path);
		echo '<tr><td class="id_span">' . $event->id . '</td><td><a href="' . $url . '">' . $event->title . '</a></td><td>' . $event_date . '</td><td>' . $event->time . '</td><td>' . $event->location . '</td><td>' . $event->brief . '</td><td>' . $event->description . '</td><td class="delete"></td></tr>';
	}
	echo '</tbody></table>';
}
?>