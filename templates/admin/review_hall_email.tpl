<div class="row">
    <div class="col-md-8">
        <h1>Review Message</h1>
        Review your message below. Emails will be sent to residents of the following Halls/Floors: <br /><br />
        <!-- BEGIN halls -->
        <ul>
          <li>{HALL}
            <ul>
            <!-- BEGIN floors -->
            <li>{FLOOR}</li>
            <!-- END floors -->
            </ul>
          </li>
        </ul>
        <!-- END halls -->
        {START_FORM}
        <strong>From:</strong> {FROM}<br />
        <strong>Subject:</strong> {SUBJECT}<br />
        <strong>Message:</strong><br />
        {BODY}
        <br /><br />
        <a class="btn btn-default" href="{EDIT_URI}"><i class="fa fa-chevron-left"></i> Edit Message</a>

        <button type="submit" class="btn btn-success pull-right">Send Messages</button>
        {END_FORM}
    </div>
</div>
