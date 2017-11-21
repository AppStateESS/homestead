<div class="row">
	<div class="col-md-10 col-md-offset-1">
		<h1>Residence Hall Contract <small>{TERM}</small></h1>

		<p>Click the button below to begin the signing process through Docusign. Once you've signed, you'll be automatically returned here to complete your application.</p>

		<!-- BEGIN under18 -->
		<div class="alert alert-info">
			{UNDER_18}
			<h3 style="margin-top:0">We'll ask your parent/guardian to sign via email</h3>
			<p>
				Since you're currently under the age of 18, we'll send a separate email to the person you listed as a parent/guardian on the previous page. The email we send will contain a link for them to sign the Housing Contract.
			</p>
			<p>
				The next page is only for <strong>your</strong> signature. Your parent/guardian will sign via the link in their email.
			</p>
		</div>
		<!-- END under18 -->

        <div class="text-center">
            <button class="btn btn-success btn-lg btn-fill" id="signButton"><i class="fa fa-edit"></i> Sign Contract via Docusign</button>
        </div>
	</div>
</div>

<script>
$().ready(function() {
	$("#signButton").click(function(){
		document.location = '{DOCUSIGN_BEGIN_CMD}';
	});
});
</script>
