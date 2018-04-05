<h2>Send RLC Invites for {TERM}</h2>

{START_FORM}

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="respond_by_date">Respond by date:</label><br />
            {RESPOND_BY_DATE}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="phpws_form_time">Time:</label>{TIME}
        </div>
    </div>
    <div class="col-md-4">
        <strong>Student Type:</strong>
        <div class="radio">
            <label>
                {TYPE_1} {TYPE_1_LABEL}
            </label>
        </div>
        <div class="radio">
            <label>
                {TYPE_2} {TYPE_2_LABEL}
            </label>
        </div>
    </div>
</div>

<button class="btn btn-primary"><i class="far fa-envelope"></i> Send Invites</button>
{END_FORM}
