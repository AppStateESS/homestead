<h2>Add a student to the lottery</h2>
<!-- BEGIN error_msg -->
<span class="error">{ERROR_MSG}<br /></span>
<!-- END error_msg -->

<!-- BEGIN success_msg -->
<span class="success">{SUCCESS_MSG}<br /></span>
<!-- END success_msg -->
{START_FORM}
<div class="form-group row">
    <div class="col-md-4">
    <div>
        {ASU_USERNAME_LABEL}
    </div>
    <div class="input-group">	
        {ASU_USERNAME}
	<span class="input-group-addon">
            @appstate.edu
        </span>
    </div>
    </div>
</div>
<div class="form-group row">
    <div class="col-md-4">
        {PHYSICAL_DISABILITY_LABEL}
    </div>
    <div class="col-md-4">
        {PHYSICAL_DISABILITY}
    </div>
</div>
<div class="form-group row">
    <div class="col-md-4">
        {PSYCH_DISABILITY_LABEL}
    </div>
    <div class="col-md-4">
        {PSYCH_DISABILITY}
    </div>
</div>
<div class="form-group row">
    <div class="col-md-4">
        {MEDICAL_NEED_LABEL}
    </div>
    <div class="col-md-4">
        {MEDICAL_NEED}
    </div>
</div>
<div class="form-group row">
    <div class="col-md-4">
        {GENDER_NEED_LABEL}
    </div>
    <div class="col-md-4">
        {GENDER_NEED}
    </div>
</div>
<button class="btn btn-success"><i class="fa fa-plus"></i> Add to lottery</button>
{END_FORM}
