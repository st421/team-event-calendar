<?php wp_enqueue_style('datePickerCSS'); ?>
<?php wp_enqueue_script('datePickerJS'); ?>
<script>  
jQuery(document).ready(function(){
	jQuery('#date').datepicker();  
});
</script> 
<h1>Edit event</h1>
<?php 
tec_edit_event(); 

function tec_edit_event() {
	$nonce = wp_create_nonce('tec_nonce_1');
?>
<script type='text/javascript'>
<!--
jQuery(document).ready(function(){
	jQuery('#submit_event_form').click(function() { 
		jQuery.ajax({
			type: "post",
			url: "admin-ajax.php",
			data: {action:'tec_add_event', id: jQuery("#event_id").val(), title: jQuery('#title').val(), date: jQuery('#date').val(), time: jQuery('#time').val(), location: jQuery('#location').val(), brief: jQuery('#brief').val(), description: jQuery('#description').val(), _ajax_nonce: '<?php echo $nonce; ?>'},
			success: function(data){ 
				jQuery("#add_event_form").fadeOut("fast");
				jQuery("#event_submitted").html(data);
				jQuery("#event_submitted").fadeIn("fast");
			}
		});
		return false;
	});
});
-->
</script>
<?php global $wpdb;
$events_table = $wpdb->prefix . "tec_events";
$id = $_GET['id'];
$event = $wpdb->get_results("SELECT * FROM " . $events_table . " WHERE id='" . $id . "';");
$event = $event[0];
?>
<form method='POST' id='add_event_form'><table class="form-table">
<tr valign="top"><th scope="row">Title</th><td><input type="text" name="title" id="title" value="<?php echo $event->title; ?>"/></td></tr>
<tr valign="top"><th scope="row">Date</th><td><input type="text" name="date" id="date" value="<?php echo tec_format_date($event->date,'-','/'); ?>" /></td></tr>
<tr valign="top"><th scope="row">Time</th><td><input type="text" name="time" id="time" value="<?php echo $event->time; ?>" /></td></tr>
<tr valign="top"><th scope="row">Location</th><td><input type="text" name="location" id="location" value="<?php echo $event->location; ?>" /></td></tr>
<tr valign="top"><th scope="row">Brief description</th><td><input type="text" name="brief" id="brief" value="<?php echo $event->brief; ?>" /></td></tr>
<tr valign="top"><th scope="row">Description</th><td><textarea name="description" id="description" COLS=100 ROWS=5><?php echo $event->description; ?></textarea></td></tr>
<tr valign="top"><th scope="row"><input type="submit" name="action" id="submit_event_form" class="button-secondary" value="update" /></th></tr></table>
<input type='hidden' id="event_id" value="<?php echo $id; ?>" />
</form>
<div id='event_submitted'></div>
<?php
} ?>