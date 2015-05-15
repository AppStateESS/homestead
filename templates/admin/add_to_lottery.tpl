<h2>Add a student to the lottery</h2>
<!-- BEGIN error_msg -->
<span class="error">{ERROR_MSG}<br /></span>
<!-- END error_msg -->

<!-- BEGIN success_msg -->
<span class="success">{SUCCESS_MSG}<br /></span>
<!-- END success_msg -->
{START_FORM}
<div class="form-group">
    {ASU_USERNAME_LABEL}
    {ASU_USERNAME}
</div>
<div class="form-group row">
    <div class="col-xs-2">
        {PHYSICAL_DISABILITY_LABEL}
    </div>
    <div class="col-xs-10">
        {PHYSICAL_DISABILITY}
    </div>
</div>
<div class="form-group row">
    <div class="col-xs-2">
        {PSYCH_DISABILITY_LABEL}
    </div>
    <div class="col-xs-10">
        {PSYCH_DISABILITY}
    </div>
</div>
<div class="form-group row">
    <div class="col-xs-2">
        {MEDICAL_NEED_LABEL}
    </div>
    <div class="col-xs-10">
        {MEDICAL_NEED}
    </div>
</div>
<div class="form-group row">
    <div class="col-xs-2">
        {GENDER_NEED_LABEL}
    </div>
    <div class="col-xs-10">
        {GENDER_NEED}
    </div>
</div>
<button class="btn btn-success"><i class="fa fa-plus"></i> Add to lottery</button>
{END_FORM}
