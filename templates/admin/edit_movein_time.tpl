<h1>{TITLE}</h1>

<h2>Create Move-in Time</h2>

{START_FORM}

<div class="row">
    <div class="col-md-3">
        <h3>Begin Date &amp; Time</h3>
        <div class="form-group">
            <label for="{BEGIN_MONTH_ID}">Month</label>
            {BEGIN_MONTH}
        </div>
        <div class="form-group">
            <label for="{BEGIN_DAY_ID}">Day</label>
            {BEGIN_DAY}
        </div>

        <div class="form-group">
            <label for="{BEGIN_YEAR_ID}">Year</label>
            {BEGIN_YEAR}
        </div>

        <div class="form-group">
            <label for="{BEGIN_HOUR_ID}">Hour</label>
            {BEGIN_HOUR}
        </div>
    </div>

    <div class="col-md-3 col-md-offset-1">
        <h3>End Date &amp; Time</h3>
        <div class="form-group">
            <label for="{END_MONTH_ID}">Month</label>
            {END_MONTH}
        </div>

        <div class="form-group">
            <label for="{END_DAY_ID}">Day</label>
            {END_DAY}
        </div>


        <div class="form-group">
            <label for="{END_YEAR_ID}">Year</label>
            {END_YEAR}
        </div>

        <div class="form-group">
            <label for="{END_HOUR_ID}">Hour</label>
            {END_HOUR}
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"></i> Create Move-in Time</button>
        </div>
    </div>
</div>

{END_FORM}

<hr>

<h2>Existing Move-in Times</h2>
{MOVEIN_TIME_PAGER}
