<div id="reportSetup">
{DIALOG_CONTENTS}
</div>

<a id="showSetup" href="" onClick="return false;">Run in background</a>

<script type="text/javascript">

function backgroundSchedule()
{
	console.log('background!');
	$.ajax({
		url: "index.php?module=hms&action=ScheduleReport",
		success:bgScheduleSuccess,
		error: bgScheduleError,
		data: $("#report-setup-form").serialize() + '&' + $.param({reportClass: "{REPORT_CLASS}",
			   runNow: true})
	});
}

function bgScheduleSuccess(data, textStatus, jqXhr)
{
}

function bgScheduleError(jqXHR, textStatus, errorThrown)
{
}

$("#reportSetup").dialog({autoOpen: false,
                          modal:   true,
                          title: "Background Setup: {REPORT_NAME}",
                          minWidth: 350
                          });
$("#reportSetup").dialog("option", "buttons", {"Schedule":
	                                             function(){backgroundSchedule();
	                                             $(this).dialog("close");}
                                              , "Close":
                                            	 function(){$(this).dialog("close")}
                                              });
//$("#reportSetup").hide();
$("#showSetup").click(function(){
	$("#reportSetup").dialog('open');
});
</script>