<div class="hms">
    <div class="box">
        <div class="box-title"><h2>Confirm Roommate - {NAME}</h2></div>
        <div class="box-content">
<!-- BEGIN ERROR -->
            <p style="color: #F00">{ERROR}</p>
<!-- END ERROR -->
            {START_FORM}
            {CAPTCHA_IMAGE}<br />
            <p>By copying the word in the image above into the box below, you are indicating that you understand that you are <b>confirming roommate status with {NAME}</b>.  This action cannot be undone without contacting Housing and Residence Life.</p>
<!-- BEGIN RLC_WITHDRAWAL -->
            <p style="display: none">{RLC}</p>
            <p><b style="color: #F00">Unique Housing Options Warning:</b> You have applied for a Unique Housing Option.  <b>Accepting this roommate request will withdraw your Unique Housing Options Application.</b></p>
<!-- END RLC_WITHDRAWAL -->
            {CAPTCHA} {SUBMIT}
            <p><i>'Captcha' technology provides added protection against malicious software hackers, but can sometimes be hard to read.  If the word is too obscured, click on it and another will be provided.</i></p>
            {END_FORM}
        </div>
    </div>
</div>
