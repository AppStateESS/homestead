<h1>Manage Room Change Request</h1>

<h2>Status: {REQUEST_STATUS}</h2>

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

<div id="selectBedDialog">
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$("#selectBedDialog").dialog({
			modal : true,
			autoOpen : false,
			title : "Choose a Bed",
			buttons : {
				"Done" : saveSelectedBed,
				"Cancel" : function() {
					$(this).dialog('close');
				}
			}
		});

		$(".showSelectBed").click(function(event) {
			event.preventDefault();
			$("#selectBedDialog").load('index.php?module=hms&action=RoomChnageListAvailableBeds').dialog('open');
		});
	});

	function saveSelectedBed() {
		//TODO
	}
</script>