<?php wp_enqueue_script('jquery-ui-datepicker'); ?>
<?php wp_enqueue_style('jquery-ui'); ?>
<?php wp_enqueue_style('bootstrap'); ?>
<?php $nonce = wp_create_nonce("tec_nonce_1"); ?>
<script>  
jQuery(document).ready(function(){
	jQuery('#date').datepicker();  
});
jQuery(document).ready(function(){
    jQuery('#submit_event_form').click(function(){ 
		var data = {
			'action':'tec_save_event', 
			'title': jQuery('#title').val(), 
			'date': jQuery('#date').val(), 
			'time': jQuery('#time').val(), 
			'location': jQuery('#location').val(), 
			'description': jQuery('#description').val(),
			'security': '<?php echo $nonce; ?>'
		};
		jQuery.post(ajaxurl, data, function(response) {
			jQuery("#add_event_form").fadeOut("fast");
			jQuery("#event_submitted").html(response);
			jQuery("#event_submitted").fadeIn("fast");
			alert('Got this from the server: ' + response);
		});
		return false;
	});
});
</script> 
<h1>Add a new event</h1>
<form id="add_event_form">
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