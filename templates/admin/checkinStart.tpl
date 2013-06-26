<h1 class="tango32 tango-edit-paste">Check-In</h1>

{START_FORM}
<div id="hallSelector" style="margin: auto; text-align: center;">
  <!-- BEGIN hall_list_label -->
  {RESIDENCE_HALL_LABEL}: {RESIDENCE_HALL}
  <!-- END hall_list_label -->
</div>

<div id="hallDiv" style="margin: auto; text-align: center; margin-bottom: 30px;">
  <span id="hallName" style="font-size:2em;"></span>&nbsp;<a id="changeLink" href="#">Change Hall</a>
</div>

<div id="searchBoxDiv" style="margin: auto; text-align: center">
	{BANNER_ID_LABEL}: {BANNER_ID}
	
	<div id="cardswipe-error" style="display:none; color:#c09853; margin-top:10px; margin-bottom:10px;padding:8px 35px 8px 14px; text-shadow:0 1px 0 rgba(255, 255, 255, 0, 0.5); background-color:#fcf8e3; border: 1px solid #fbeed5; border-radius:4px;">
       The card reader didn't read the student's ID. Please try swiping the card again.
    </div>
    
	<div style="text-align:right;">
	  {SUBMIT}
	</div>
</div>

{END_FORM}