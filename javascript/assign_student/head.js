<script type="text/javascript" src="mod/hms/javascript/new_autosuggest/bsn.AutoSuggest_2.1.3.js" charset="utf-8"></script>
<script type="text/javascript" src="mod/hms/javascript/jquery.selectboxes.js"></script>

<script type="text/javascript">

$(document).ready(function(){

    var options = {
        script:"index.php?module=hms&action=AjaxGetUsernameSuggestions&ajax=true&",
        varname:"username",
        json:true,
        shownoresults:false,
        maxresults:6,
        timeout:100000
    };

    var suggest = new bsn.AutoSuggest('phpws_form_username', options);

    if($('#phpws_form_use_bed').val() == "false"){
        $('#phpws_form_floor').attr('disabled', true);
        $('#phpws_form_room').attr('disabled', true);
        $('#phpws_form_bed').attr('disabled', true);
    }

	// Set event listeners
	$("#phpws_form_residence_hall").bind("change", handleHallChange);
	$("#phpws_form_floor").bind("change", handleFloorChange);
	$("#phpws_form_room").bind("change", handleRoomChange);
	$("#phpws_form_bed").bind("change", handleBedChange);

});

</script>

<script type="text/javascript">
//<![CDATA[

var res_hall_drop   = '#phpws_form_residence_hall';
var floor_drop      = '#phpws_form_floor';
var room_drop       = '#phpws_form_room';
var bed_drop        = '#phpws_form_bed';

var bedDropShown = false;

function showBedDrop()
{
    bedDropShown = true;
    
    $('#link_row').hide();
    $('#bed_row').show();

    $('#phpws_form_use_bed').val("true");
}

function handleHallChange()
{
  // Reset and disable all the lower-order drop downs
  setOptions(floor_drop, {});
  setOptions(room_drop, {});
  setOptions(bed_drop, {});
  $(floor_drop).attr('disabled', true);
  $(room_drop).attr('disabled', true);
  $(bed_drop).attr('disabled', true);

  // Get the selected value
  var hallId = $('#phpws_form_residence_hall').val();
  
  // the default value is selected
  if(hallId == 0){
      // alert('default selected');
      return;
  }
  
  // Set the floor drop down to "loading", show the loading animation
  setOptions(floor_drop, {'0': 'Loading...'});
  $("#loading_img").show();

	var request = $.ajax( {
	type : "GET",
	url : "index.php",
	dataType : "json",
	data : {
		module : "hms",
		ajax : true,
		action : "AjaxGetFloorsWithVacancies",
		hallId : hallId
	},
	success : function(data, textStatus) {
		handleFloorResponse(data, textStatus);
	},
	error : function(XMLHttpRequest, textStatus, errorThrown) {
		ajaxError(XMLHttpRequest, textStatus, errorThrown);
	}
});
}

function handleFloorResponse(data, textStatus){
	$("#loading_img").hide();
	$(floor_drop).attr('disabled', false);
	setOptions(floor_drop, data);
}

function handleFloorChange(){
	var floorId = $("#phpws_form_floor").val();
	
	setOptions(room_drop,{});
	setOptions(bed_drop,{});
	$(room_drop).attr('disabled', true);
	$(bed_drop).attr('disabled', true);
	
	if(floorId == 0){
		return;
	}
	
	// Set the floor drop down to "loading", show the loading animation
 	setOptions(room_drop, {'0': 'Loading...'});
 	$("#loading_img").show();
	var request = $.ajax( {
		type : "GET",
		url : "index.php",
		dataType : "json",
		data : {
			module : "hms",
			ajax : true,
			action : "AjaxGetRoomsWithVacancies",
			floorId : floorId
		},
		success : function(data, textStatus) {
			handleRoomResponse(data, textStatus);
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			ajaxError(XMLHttpRequest, textStatus, errorThrown);
		}
	});
}

function handleRoomResponse(data, textStatus)
{
	$("#loading_img").hide();
	$(room_drop).attr('disabled', false);
	setOptions(room_drop, data);
}

function handleRoomChange()
{
	var roomId = $('#phpws_form_room').val();
	
	setOptions(bed_drop,{});
	$(bed_drop).attr('disabled', true);
	
	if(roomId == 0){
		return;
	}
	
	// Set the floor drop down to "loading", show the loading animation
	setOptions(bed_drop, {'0': 'Loading...'});
	$("#loading_img").show();
	
	var request = $.ajax( {
		type : "GET",
		url : "index.php",
		dataType : "json",
		data : {
			module : "hms",
			ajax : true,
			action : "AjaxGetBedsWithVacancies",
			roomId : roomId
		},
		success : function(data, textStatus) {
			handleBedResponse(data, textStatus);
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			ajaxError(XMLHttpRequest, textStatus, errorThrown);
		}
	});
}

function handleBedResponse(data, textStatus)
{
	$("#loading_img").hide();
	$(bed_drop).attr('disabled', false);
	setOptions(bed_drop, data);
}
		
function handleBedChange()
{
	var bedId = $('#phpws_form_bed').val();
	
	if(bedId == 0){
		$('#phpws_form_submit_button').attr('disabled', true);
	}else{
		$('#phpws_form_submit_button').attr('disabled', false);
	}
}

function setOptions(elementId, options)
{
	 $(elementId).empty();
	 $(elementId).addOption(options);
	 $(elementId).selectOptions("", true);
}

function ajaxError(XMLHttpRequest, textStatus, errorThrown)
{
	alert('Ajax error: ' + textStatus);
}
//]]>
</script>
