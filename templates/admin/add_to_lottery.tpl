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

<button class="btn btn-success"><i class="fa fa-plus"></i> Add to lottery</button>
{END_FORM}
