(function($) {
	$.fn.cancelApplicationDialog = function(options) {
	    
	    var thisThing = $(this);
	    
		return this.each(function() {
			$this = $(this);
			
			// Bind the event handler
			$this.bind('click', {link:$this}, handleCancelLink);
		});
	}
	
	function handleCancelLink(e){
	    link = e.data.link;
	    
        // Clear the dialog
        $("#cancelDialog").empty();

        // Turn on the spinner
        $("#cancelDialog").html('<div style="margin-left:auto;margin-right:auto;width:1%;position:relative;top:30px;" id="spinner"></div>');
        var spinner = new Spinner(spinnerOpts).spin(document.getElementById('spinner'));

        $("#cancelDialog").load(link.attr('href'));
        $("#cancelDialog").dialog('open');

        // re-enable the buttons, in case they were disabled by a previous call
        $("#cancelDialogSubmitButton").button("enable");
        $("#cancelDialogCloseButton").button("enable");

        // Set the focus to the close button by default
        $("#cancelDialogCloseButton").focus();
        return false;
	}
	
	$.fn.handleCancelButton = function(){
	    // Check to make sure a reason was selected
	    if($("#cancel_app_form_cancel_reason").val() == 0){
	        alert('You must select a reason!');
	        return;
	    }
	    
	    // Disable the buttons to prevent double-submissions
	    $("#cancelDialogSubmitButton").button("disable");
	    $("#cancelDialogCloseButton").button("disable");
        
        // Submit the form
        var data = $("#cancel_app_form").serialize();
        
        $.ajax({
            type: 'POST',
            url: 'index.php',
            cache: false,
            data: data,
            success: handleCancelResponse,
            error: handleCancelError
        });
        
        // Show the spinner (must do this *after* submitting the form, otherwise we erase our form)
        $("#cancelDialog").html('<div style="margin-left:auto;margin-right:auto;width:1%;position:relative;top:30px;" id="spinner"></div>');
        var spinner = new Spinner(spinnerOpts).spin(document.getElementById('spinner'));
	}
	
	function handleCancelResponse(data, textStatus, jqXHR){
        // Check the result for an error and show it
	    if(data != 'success'){
	        //TODO improve this
	        alert('There was an error!');
	    }
        
	    // Refresh the page
	    location.reload();
	}
	
	function handleCancelError(jqXHR, textStatus, errorThrown){
	    //console.log(data);
        alert('error!');
	}
})(jQuery);