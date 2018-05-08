<h2>
    <i class="fa fa-check-square-o"></i>  <span id="hallName" class="text-primary"></span> Check-In <a class="btn btn-outline-dark" id="changeLink" href="#">Change Hall</a>
</h2>

{START_FORM}

<div class="row">
    <div class="col-md-6">
        <div id="hallSelector" class="form-group">
            <!-- BEGIN hall_list_label -->
            {RESIDENCE_HALL}
            <!-- END hall_list_label -->
        </div>

        <div id="searchBoxDiv" class="form-group">
            {BANNER_ID}
            <div id="cardswipe-error" class="alert alert-warning" role="alert" hidden>
                The card reader didn't read the student's ID. Please try swiping the card again.
            </div>
        </div>
    </div>
</div>
<div id="checkInButtonDiv">
    {SUBMIT}
</div>
{END_FORM}
