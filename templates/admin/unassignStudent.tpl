{START_FORM}

<h1>Unassign Student <small>{TERM}</small></h1>

<div class="row">
    <div class="col-md-4">

        <label for="{USERNAME_ID}">Username</label>
        <div class="input-group">
            {USERNAME}
            <span class="input-group-addon">@appstate.edu</span>
        </div>

        <div class="form-group">
            <label for="{UNASSIGNMENT_TYPE_ID}">Reason</label>
            {UNASSIGNMENT_TYPE}
        </div>

        {REFUND_LABEL}
        <div class="input-group">
            {REFUND}<span class="input-group-addon">%</span>
        </div>

        <div class="form-group">
            <label for="{NOTE_ID}">Note:</label>
            {NOTE}
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-success">Unassign Student</button>
        </div>

    </div>
</div>

{END_FORM}
