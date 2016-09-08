<?php wp_enqueue_script('jquery-ui-datepicker'); ?>
<?php wp_enqueue_style('jquery-ui'); ?>
<?php wp_enqueue_style('bootstrap'); ?>
<script>  
jQuery(document).ready(function(){
	jQuery('#date').datepicker();  
});
</script> 
<h1>Add a new event</h1>
<?php 
tec_add_event();

function tec_add_event() {
	$nonce = wp_create_nonce('tec_nonce_1');
?>
<script type='text/javascript'>
jQuery(document).ready(function(){
	jQuery('#submit_event_form').click(function() { 
		var data = {
			action:'tec_save_event', 
			title: jQuery('#title').val(), 
			date: jQuery('#date').val(), 
			time: jQuery('#time').val(), 
			location: jQuery('#location').val(), 
			description: jQuery('#description').val(), 
			_ajax_nonce: '<?php echo $nonce; ?>'
		};
		jQuery.post(ajaxurl, data, function(response) {
			jQuery("#add_event_form").fadeOut("fast");
			jQuery("#event_submitted").html(response);
			jQuery("#event_submitted").fadeIn("fast");
			alert('Got this from the server: ' + response);
		});
	});
});
</script>
<form method='POST' id='add_event_form'>
  <div class="form-group">
    <label for="title">Title</label>
    <input type="text" class="form-control" id="title">
  </div>
  <div class="form-group">
    <label for="date">Date</label>
    <input type="text" class="form-control" id="date">
  </div>
  <div class="form-group">
    <label for="time">Time</label>
    <input type="text" class="form-control" id="time">
  </div>
  <div class="form-group">
    <label for="location">Location</label>
    <input type="text" class="form-control" id="location">
  </div>
  <div class="form-group">
    <label for="description">Description</label>
    <textarea type="text" class="form-control" id="description" COLS=100 ROWS=5></textarea>
  </div>
	<button type='submit' name='action' id='submit_event_form' class='btn'>Submit</button>
</form>
<div id='event_submitted'></div>
<?php } ?>