<script type="text/javascript">
$(document).ready(function(){
    function hideMe(actor, actee){
        actee.hide('normal');
    }

    function showMe(actor, actee){
        actee.show('normal');
    }

    function hideOther(){
        this.actor;
        this.actee;
        
        this.hideMe;
        this.showMe;
    }

    function hideOther(actor, actee, hidden){
        var parent  = this;
        this.actor  = $("#"+actor);
        this.actee  = $("#"+actee);
        this.hideMe = hideMe;
        this.showMe = showMe;
        
        if(hidden == true){
            this.actee.hide();
            this.actor.toggle(
                function(){
                    parent.showMe(parent.actor, parent.actee);
                    parent.actor.html("[-]");
                },
                function(){
                    parent.hideMe(parent.actor, parent.actee);
                    parent.actor.html("[+]");
                }
            );
        } else {
            this.actor.toggle(
                function(){
                    parent.hideMe(parent.actor, parent.actee);
                    parent.actor.html("[+]");
                },
                function(){
                    parent.showMe(parent.actor, parent.actee);
                    parent.actor.html("[-]");
                }
            );
        }
    }

    var demographicsToggle = new hideOther("demographics_toggle", "student_demographics", true);
    var statusToggle       = new hideOther("status_toggle",       "housing_status",       false);
    
    $("#note_dialog").hide();
    $("#add_note").click(function(){
        $("#note_dialog").show();
        $("#note_dialog").dialog(
        { 
            modal: true, 
            width: 350,
            height: 250,
            overlay: { 
                opacity: 0.5, 
                background: "black" 
            } 
        });
    });
});
</script>

<div class="hms">
  <div class="box">
    <div class="box-content">
        <h1>{FIRST_NAME} {MIDDLE_NAME} {LAST_NAME}, ({BANNER_ID})</h1><h2>Login as this student ( {LOGIN_AS_STUDENT} )</h2>
        <br>
        <table>
            <tr>
                <th><a id="demographics_toggle">[+]</a>Student Demographics</th>
            </tr>
            <tr>
            <td rowspan="6">
            <div id="student_demographics">
                <table cellspacing="3" cellpadding="2">
                    <tr>
                        <th>ASU Email Address:</th>
                        <td><a href="mailto:{USERNAME}@appstate.edu">{USERNAME}@appstate.edu</a></td>
                    </tr> 

                    <tr>
                        <th>Gender</th>
                        <td>{GENDER}</td>
                    </tr>
                    
                    <tr>
                        <th>Birthday</th>
                        <td>{DOB}</td>                    
                    </tr>
                    
                    <tr>
                        <th>Type</th>
                        <td>{TYPE}</td>
                    </tr>
                    
                    <tr>
                        <th>Class</th>
                        <td>{CLASS}</td>
                    </tr>
                    
                    <tr>
                        <th>Phone Number</th>
                        <td>({PHONE_AC}) {PHONE_NUMBER}</td>
                    </tr>
                    
                    <tr>
                       <th>Addresses</th>
                    
                    
                        <td>
                            <!-- BEGIN pr_address -->
                                Permanent address:<br />
                                {PR_ADDRESS_L1}<br />
                                
                                <!-- BEGIN pr_address_2 -->
                                {PR_ADDRESS_L2}<br />
                                <!-- END pr_address_2 -->
                                <!-- BEGIN pr_address_3 -->
                                {PR_ADDRESS_L3}<br />
                                <!-- END pr_address_3 -->

                                {PR_ADDRESS_CITY}, {PR_ADDRESS_STATE} {PR_ADDRESS_ZIP}<br />
                            <!-- END pr_address -->
                            <!-- BEGIN address_space -->
                            {ADDRESS_SPACE}<br />
                            <!-- END  address_space -->
                            <!-- BEGIN ps_address -->
                                Student address:<br />
                                {PS_ADDRESS_L1}<br />

                                <!-- BEGIN ps_address_2 -->
                                {PS_ADDRESS_L2}<br />
                                <!-- END ps_address_2 -->

                                <!-- BEGIN ps_address_3 -->
                                {PS_ADDRESS_L3}<br />
                                <!-- END ps_address_3 -->
                                {PS_ADDRESS_CITY}, {PS_ADDRESS_STATE} {PS_ADDRESS_ZIP}<br />
                            <!-- END ps_address -->
                            </ul>
                        </td>
                </div>
                </table>
                </td>
            </tr>
        </table>
        <br>
        <table>
            <tr>
                <th><a id="status_toggle">[-]</a>Housing Status</th>
            </tr>
            <tr>
                <td>
                <div id="housing_status">
                <table>
                    <tr>
                        <th>Have Application: </th>
                        <td>{APPLICATION_RECEIVED}  [{APPLICATION}]
                    </tr>
                    <tr>
                        <th>Assigned:</th>
                        <td>{ASSIGNED}  [{ROOM_ASSIGNMENT}]</td>
                    </tr>
                    <tr>
                        <th>Roommate</th>
                        <td>{ROOMMATE}</td>
                    </tr>
                    <tr>
                        <th>RLC</td>
                        <td>{RLC_STATUS}</td>
                    </tr>
                </table>
                </div>
                </td>
            </tr>
        </table>
        </div>
    </div>
</div>
<div class="flora" id="note_dialog" title="Enter a note for: {FIRST_NAME} {MIDDLE_NAME} {LAST_NAME}">
{START_FORM}
{NOTE}
<br>
{SUBMIT}
{END_FORM}
</div>

<h1>Recent Notes</h1>
<a id=add_note>Add a note</a>
{NOTE_PAGER}
<h1>Student Log</h1>
{LOG_PAGER}
