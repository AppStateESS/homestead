<h1>Add a New Term</h1>

<!-- BEGIN term_form -->
{START_FORM}

<div class="row">
    <div class="col-md-2">
        <div class="form-group">
            {TERM_DROP_LABEL}
            {TERM_DROP}
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            {YEAR_DROP_LABEL}
            {YEAR_DROP}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>{COPY_PICK_LABEL}</label>
            <div class="checkbox">
                <label>
                    {COPY_PICK_1} {COPY_PICK_1_LABEL_TEXT}
                </label>
            </div>

            <div class="checkbox" style="margin-left:15px;">
                <label>
                    {COPY_PICK_2} {COPY_PICK_2_LABEL_TEXT}
                </label>
            </div>

            <div class="checkbox" style="margin-left:15px;">
                <label>
                    {COPY_PICK_3} {COPY_PICK_3_LABEL_TEXT}
                </label>
            </div>
        </div>

        <div class="form-group">
            {FROM_TERM_LABEL}
            {FROM_TERM}
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-success">Create Term</button>
        </div>
    </div>
</div>

{END_FORM}
<!-- END term_form -->
