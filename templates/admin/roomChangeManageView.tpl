<h1>Manage Room Change Request</h1>

<div style="float: right;">
    <!-- BEGIN approve_btn -->
    <form action="index.php?module=hms&action=RoomChangeApprove" method="post">
        <input type="hidden" name="requestId" value="{REQUEST_ID_APPROVE}">
        <button type="submit" class="btn btn-primary">Approve</button>
    </form>
    <!-- END approve_btn -->
    
    <!-- BEGIN hold_btn -->
        <button type="button" class="btn btn-default">Hold</button>
    <!-- END hold_btn -->
    
    <!-- BEGIN deny_btn -->
        <button type="button" id="deny-btn" class="btn btn-default" data-request-id="{REQUEST_ID_DENY_BTN}">Deny</button>
    <!-- END deny_btn -->
    
    <!-- BEGIN cancel2 -->
        <button type="button" id="cancel-btn" class="btn btn-default" data-request-id="{REQUEST_ID_CANCEL_BTN}">Cancel</button>
    <!-- END cancel2 -->
</div>

<h2>Status: {REQUEST_STATUS}</h2>

<div id="cancel-form">
  <form action="index.php?module=hms&action=RoomChangeCancel&requestId={REQUEST_ID_CANCEL}" method="post">
    <p>Please give a reason for cancelling this request. The reason will be available to all participants.</p>
    <textarea name="cancel-reason" style="width:300px;" placeholder="Enter a cancellation reason..."></textarea><br />
    <button class="btn" type="submit">Cancel Request</button>
  </form>
</div>

<div id="deny-form">
  <form action="index.php?module=hms&action=RoomChangeDeny&requestId={REQUEST_ID_DENY}" method="post">
    <p>Please give a reason for denying this request. The reason will be available to all participants.</p>
    <textarea name="deny-reason-public" style="width:300px;" placeholder="Enter a denial reason..."></textarea><br />
    <p>Private reason for denying this request. This reason will only be available to staff members.</p>
    <textarea name="deny-reason-private" style="width:300px;" placeholder="Enter a private denial reason..."></textarea><br />
    <button class="btn" type="submit">Deny Request</button>
  </form>
</div>

<!-- BEGIN cancellation_private -->
<h3>Private Cancellation/Denial Reason:</h3>
<p>
{CANCELLED_REASON_PRIVATE}
</p>
<!-- END cancellation_private -->

<hr style="clear: right;"/>
<h2>Participants</h2>
<!-- BEGIN PARTICIPANT -->
{ROW}
<!-- END PARTICIPANT -->

<h2>Reason</h2>
<p>{REQUEST_REASON}</p>

<!-- BEGIN cancellation -->
<h3>Cancellation/Denial Reason:</h3>
<p>
{CANCELLED_REASON_PUBLIC}
</p>
<!-- END cancellation -->

<!-- BEGIN denied_reason -->
<h2>Denied Reason</h2>
<h3>Public</h3>
<p>{DENIED_REASON_PUBLIC}</p>

<h3>Private</h3>
<p>{DENIED_REASON_PRIVATE}</p>
<!-- END denied_reason -->

<script type="text/javascript">
	$(document).ready(function() {
		$.get('index.php?module=hms&action=RoomChnageListAvailableBeds',{gender: $("#participant_form_gender").val()}, bedListCallback, 'json');
		
		
		// Cancel Form
		$("#cancel-form").hide();
		$("#cancel-btn").click(function(event){
			console.log('click');
			$("#cancel-form").show();
		});
		
		// Deny Form
		$("#deny-form").hide();
        $("#deny-btn").click(function(event){
            console.log('click');
            $("#deny-form").show();
        });
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