<div class="row">
    <div class="col-md-8">
        <h1>Room Change Request</h1>

        <p>
            You have a room change request from: <strong>{REQUESTOR}</strong>
        </p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <p>Here's the plan:</p>
        <table class="table table-striped">
            <tr>
                <th>Person</th>
                <th>Moving From</th>
                <th>Moving To</th>
            </tr>
            <!-- BEGIN PARTICIPANTS -->
            <tr class="{STRONG_STYLE}">
                <td>{NAME}</td>
                <td>{FROM_BED}</td>
                <td>{TO_BED}</td>
            </tr>
            <!-- END PARTICIPANTS -->
        </table>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        {START_FORM}
        <p>If this plan is ok with you, then click the Approve button below. When everyone has approved the plan, the request will be sent to your current and future Residence Director(s), and to the Housing Assignments Office for final approval. You will be notified via email when your room change request is approved or denied.</p>

        {CAPTCHA}

        <button type="submit" class="btn btn-primary" formaction="{APPROVE_URI}">I Approve</button>
        <button type="submit" class="btn btn-danger" formaction="{DECLINE_URI}">Decline</button>
        {END_FORM}
    </div>
</div>
