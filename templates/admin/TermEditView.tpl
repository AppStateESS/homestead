<div class="row">
	<div class="col-md-7">
        <h1>{TITLE}</h1>
		<a class="btn btn-success btn-small pull-right" href="{NEW_TERM_URI}"><i class="fa fa-plus"></i> Add New Term</a>
    </div>
</div>

<div class="row">
	<div class="col-md-3">
		<fieldset>
			<legend>Current Term</legend>
			<p>{CURRENT_TERM_TEXT}</p>
			<!-- BEGIN CURTERM_LINK -->
			<p><a href="{SET_TERM_URI}" class="btn btn-danger">{SET_TERM_TEXT}</a></p>
			<!--  END CURTERM_LINK -->
		</fieldset>
	</div>
</div>
<div class="row">
	<div class="col-md-5">
		<fieldset>
			<legend>Banner Queue</legend>
			<p>The Banner Queue for this term is
				<!-- BEGIN queue_enabled -->{QUEUE_ENABLED}
				<strong class="text-success">enabled</strong>
				<!-- END queue_enabled -->

				<!-- BEGIN queue_disabled -->{QUEUE_DISABLED}
				<strong class="text-danger">disabled</strong>
				<!-- END queue_disabled -->
			</p>

			<!-- BEGIN BQ_LINK -->
			<p>{BANNER_QUEUE_LINK}</p>
			<!-- END BQ_LINK -->

			<!-- BEGIN BANNER_QUEUE_PROCESS -->
			<p>
				There are {BANNER_QUEUE_COUNT} items currently queued for reporting to Banner.
				<!-- BEGIN BQP_LINK -->
				<a href="{BANNER_QUEUE_PROCESS_URI}" class="btn btn-danger">Process and Disable</a>
				<!-- END BQP_LINK -->
			</p>
			<!-- END BANNER_QUEUE_PROCESS -->
		</fieldset>
    </div>
</div>
<div class="row">
	<div class="col-md-5">
		<fieldset>
			<legend>Terms &amp; Conditions</legend>
			{TERMS_CONDITIONS_CONTENT}
		</fieldset>
	</div>
</div>
<div class="row">
	<div class="col-md-9">
		<fieldset>
			<legend>Student Application Dates &amp; Deadlines</legend>
			{FEATURES_DEADLINES_CONTENT}
		</fieldset>
    </div>
</div>
