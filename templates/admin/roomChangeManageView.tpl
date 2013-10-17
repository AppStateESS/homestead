<h1>Manage Room Change Request</h1>

<h2>Status: {REQUEST_STATUS}</h2>

<div style="float: right;">
    <button>Hold</button>
    <button>Deny</button>
    <button>Cancel</button>
</div>

<h2>Participants</h2>

<!-- BEGIN PARTICIPANT -->
{ROW}
<!-- END PARTICIPANT -->

<h2>Reason</h2>
<p>{REQUEST_REASON}</p>

<!-- BEGIN denied_reason -->
<h2>Denied Reason</h2>
<h3>Public</h3>
<p>{DENIED_REASON_PUBLIC}</p>

<h3>Private</h3>
<p>{DENIED_REASON_PRIVATE}</p>
<!-- END denied_reason -->

<script type="text/javascript">
	$(document).ready(function() {
		$.get('index.php?module=hms&action=RoomChnageListAvailableBeds', bedListCallback, 'json');
	});

	function bedListCallback(data)
	{
		$("#participant_form_bed_select").html('');
		
		// Check for no available beds
		if(data.length == 0){
			$("#participant_form_bed_select").html("<p>No available beds found. Please contact the Housing Assignments Office.</p>");
			return;
		}

		var html = '<option value="-1">Select destination..</option>';
		
	    // Loop over each bed and add it to the list
		for(i = 0; i < data.length; i++){
			html += '<option value="' + data[i].bedid + '">' + data[i].hall_name + ' ' + data[i].room_number + '</option>';
		}
		
		$("#participant_form_bed_select").append(html);
	    
	}
</script>