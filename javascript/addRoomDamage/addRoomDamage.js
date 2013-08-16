(function($) {
	$.fn.addDamageDialog = function(options) {
	    
	    var thisThing = $(this);
	    
		return this.each(function() {
			$this = $(this);
			
			// Bind the event handler
			$this.bind('click', {link:$this}, handleOpenLink);
		});
	}
	
	function handleOpenLink(e){
	    link = e.data.link;
	    
        // Clear the dialog
        $("#addDamageDialog").empty();

        // Turn on the spinner
        $("#addDamageDialog").html('<div style="margin-left:auto;margin-right:auto;width:1%;position:relative;top:30px;" id="spinner"></div>');
        var spinner = new Spinner(spinnerOpts).spin(document.getElementById('spinner'));

        $("#addDamageDialog").load(link.attr('href'));
        $("#addDamageDialog").dialog('open');

        // re-enable the buttons, in case they were disabled by a previous call
        $("#addDamageSubmitButton").button("enable");
        $("#addDamageCloseButton").button("enable");

        // Set the focus to the close button by default
        $("#addDamageSubmitButton").focus();
        return false;
	}
	
	$.fn.handleAddButton = function(){
	    
	    // Disable the buttons to prevent double-submissions
	    $("#cancelDialogSubmitButton").button("disable");
	    $("#cancelDialogCloseButton").button("disable");
	    
	    if($("#phpws-form-damage-type").val() == '') {
	    	alert('Please choose a damage type from the drop down box.');
	    	$("#cancelDialogSubmitButton").button("enable");
		    $("#cancelDialogCloseButton").button("enable");
		    return;
	    }
	    
        // Submit the form
        var data = $("#addDamageForm").serialize();
        
        $.ajax({
            type: 'POST',
            url: 'index.php',
            cache: false,
            data: data,
            success: handleAddResponse,
            error: handleAddError
        });
        
        // Show the spinner (must do this *after* submitting the form, otherwise we erase our form)
        $("#addDamageDialog").html('<div style="margin-left:auto;margin-right:auto;width:1%;position:relative;top:30px;" id="spinner"></div>');
        var spinner = new Spinner(spinnerOpts).spin(document.getElementById('spinner'));
	}
	
	function handleAddResponse(data, textStatus, jqXHR){
        // Check the result for an error and show it
	    if(data != 'success'){
	        //TODO improve this
	        alert('There was an error!');
	        //console.log(data);
	    }
        
	    // Refresh the page
	    location.reload();
	}
	
	function handleAddError(jqXHR, textStatus, errorThrown){
	    //console.log(data);
        alert('error!');
	}
})(jQuery);