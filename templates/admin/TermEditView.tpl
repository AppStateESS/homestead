<div class="row">
	<div class="col-md-7">
        <h1>{TITLE}</h1>
    </div>
</div>

<a class="btn btn-primary" href="{NEW_TERM_URI}"><i class="fa fa-plus"></i> Add New Term</a>

<div class="row">
	<div class="col-md-3">
		<fieldset>
			<legend>{CURRENT_TERM_LEGEND}</legend>
			<p>{CURRENT_TERM_TEXT}</p>
			<!-- BEGIN CURTERM_LINK --><p>{CURRENT_TERM_LINK}</p><!--  END CURTERM_LINK -->
		</fieldset>
	</div>
</div>
<div class="row">
	<div class="col-md-5">
		<fieldset>
			<legend>{BANNER_QUEUE_LEGEND}</legend>
			<p>{BANNER_QUEUE_TEXT}<!-- BEGIN BQ_LINK -->&nbsp;&nbsp;[{BANNER_QUEUE_LINK}]<!-- END BQ_LINK --></p>
			<!-- BEGIN BANNER_QUEUE_PROCESS --><p>{BANNER_QUEUE_COUNT}<!-- BEGIN BQP_LINK --> [{BANNER_QUEUE_PROCESS}]<!-- END BQP_LINK --></p><!-- END  
			BANNER_QUEUE_PROCESS -->
		</fieldset>
    </div>
</div>
<div class="row">
	<div class="col-md-5">
		<fieldset>
			<legend>{TERMS_CONDITIONS_LEGEND}</legend>
			{TERMS_CONDITIONS_CONTENT}
		</fieldset>
	</div>
</div>
<div class="row">
	<div class="col-md-9">
		<fieldset>
			<legend>{FEATURES_DEADLINES_LEGEND}</legend>
			{FEATURES_DEADLINES_CONTENT}
		</fieldset>
    </div>
</div>