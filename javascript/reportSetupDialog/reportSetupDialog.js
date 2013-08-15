$.fn.reportSetupDialog = function(settings) {
    settings = jQuery.extend({
        dialogId: null,
        linkId: null,
        reportName: null,
        reportClass: null,
        runNow: null,
        formId: null
    }, settings);
    
    jQuery(this).each(function() {
       
        var matched = this;
        var opts = jQuery.extend({}, settings);
        
        function _initialize() {
            // Create the dialog box
            $("#"+opts.dialogId).dialog({autoOpen: false,
                modal:   true,
                title: "Background Setup: " + opts.reportName,
                minWidth: 350
                });
            // Setup the options and buttons
            $("#"+opts.dialogId).dialog("option", "buttons", {"Schedule":
                                       function(){backgroundSchedule();
                                       $(this).dialog("close");
                                       }
                                    , "Close":
                                       function(){$(this).dialog("close")}
                                    });
            // Bind the click event for opening the dialog
            $("#"+opts.dialogId+"-link").click(function(){
                $("#"+opts.dialogId).dialog('open');
            });
            
            // Setup the datepicker and time picker, if any
            $("#"+opts.formId + "_datePicker").datepicker();
            $("#"+opts.formId + "_timePicker").timePicker({
               startTime:"00:00",
               endTime:"23:45",
               show24Hours: false,
               separator: ':',
               step: 15
            });
        }
        
        function backgroundSchedule()
        {
            //console.log($("#"+opts.formId));
            $.ajax({
                url: "index.php?module=hms&action=ScheduleReport",
                success:bgScheduleSuccess,
                error: bgScheduleError,
                data: $("#"+opts.formId).serialize() + '&' + $.param({reportClass: opts.reportClass,
                       runNow: opts.runNow})
            });
        }

        function bgScheduleSuccess(data, textStatus, jqXhr)
        {
        }

        function bgScheduleError(jqXHR, textStatus, errorThrown)
        {
        }

        $(document).ready(function(){
            _initialize();
        });
    });
}