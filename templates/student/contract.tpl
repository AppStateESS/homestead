<h1>Residence Hall Contract - {TERM}</h1>

<p>You must sign the Residence Hall Contract. Click the button below to begin the signing process through Docusign. Once you've signed, you'll be automatically sent back here to complete your application.<p>

<button id="signButton">Sign Contract via Docusign</button>

<script>
$().ready(function() {
	$("#signButton").click(function(){
		document.location = '{DOCUSIGN_BEGIN_CMD}';
	});
});
</script>