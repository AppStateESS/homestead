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
