<h1>Residence Hall Contract - {TERM}</h1>

<p>You must sign the Residence Hall Contract. Click the button below to begin the signing process through Docusign. Once you've signed, you'll be automatically sent back here to complete your application.<p>

<!-- BEGIN under18 -->
{UNDER_18}
<h3>We'll ask your parent/guardian to sign via email</h3>
<p>Since you're currently under the age of 18, we'll send a separate email to the person you listed as a parent/guardian on the previous page. The email we send will contain a link for them to sign the Housing Contract.</p>
<p>
The next page is only for <strong>your</strong> signature. Your parent/guardian will sign via the link in their email.
</p>
<!-- END under18 -->

<button id="signButton">Sign Contract via Docusign</button>

<script>
$().ready(function() {
	$("#signButton").click(function(){
		document.location = '{DOCUSIGN_BEGIN_CMD}';
	});
});
</script>