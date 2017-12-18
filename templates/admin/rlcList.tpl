<h2>Residential Learning Communities</h2>

<div class="row" style="margin-bottom:1em;">
    <div class="col-md-2 col-md-push-9">
        <div class="btn-group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                RLC Maintenance <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <!-- BEGIN rosters -->
                <li><a href="{ALL_MEMBERS_URI}">View All Members</a></li>
                <!-- END rosters -->

                <li role="separator" class="divider"></li>

                <!-- BEGIN applications -->
                <li><a href="{APPLICATIONS_URI}">View Applications</a></li>
                <li><a href="{DENIED_APPS_URI}">View Declined Applications</a></li>
                <!-- END applications -->

                <li role="separator" class="divider"></li>

                <!-- BEGIN invites -->
                <li><a href="{SEND_INVITES_URI}">Send Invites</a></li>
                <!-- END invites -->

                <!-- BEGIN rejects -->
                <li><a href="{SEND_REJECTS_URI}">Send Declined App Notices</a></li>
                <!-- END rejects -->

                <li role="separator" class="divider"></li>
                <li><a href="{ADD_URI}"><i class="fa fa-plus"></i> Add a Community</a></li>
            </ul>
        </div>
    </div>
</div>

<hr/>

<div id="RlcCardList"></div>

<script type="text/javascript" src="{vendor_bundle}"></script>
<script type="text/javascript" src="{entry_bundle}"></script>
