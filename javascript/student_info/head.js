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

    var demographicsToggle = new hideOther("demographics_toggle", "student_demographics", false);
    var statusToggle       = new hideOther("status_toggle",       "housing_status",       false);
    var applicationToggle  = new hideOther("application_toggle",  "applications",         false);
    var historyToggly	   = new hideOther("history_toggle", 	  "history",			  false);
    
    $("#note_dialog").dialog(
            { 
                modal: true,
                autoOpen: false,
                width: 350,
                height: 250
            });
    
    $("#add_note").click(function(){
        $("#note_dialog").dialog('open');
    });
});
</script>
