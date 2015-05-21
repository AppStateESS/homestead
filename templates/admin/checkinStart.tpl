<h1>
    <i class="fa fa-check-square-o"></i>
    Check-In
</h1>

{START_FORM}
<div class="col-md-3 col-md-offset-3">

</div>

<div class="col-md-6 col-md-offset-3">
    <div class="form-group">
        <span id="hallName" style="font-size:2em;"></span>
        &nbsp;
        <a id="changeLink" href="#">Change Hall</a>
        <div class="row">
          <div class="col-md-6">
            <div id="hallSelector" class="form-group">
            <!-- BEGIN hall_list_label -->
            {RESIDENCE_HALL_LABEL}: {RESIDENCE_HALL}
            <!-- END hall_list_label -->
            </div>
          </div>
        </div>
    </div>

    <div id="searchBoxDiv" class="form-group">
	     {BANNER_ID_LABEL}: {BANNER_ID}

	     <div id="cardswipe-error" class="alert alert-warning" role="alert" hidden>
            The card reader didn't read the student's ID. Please try swiping the card again.
       </div>
    </div>
</div>

<div id="checkInButtonDiv" class="col-md-1 col-md-offset-8">
	  {SUBMIT}
</div>

{END_FORM}
