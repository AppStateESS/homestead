//This widget requires the bsn.Autosuggest script
var options = {
script:"index.php?module=hms&action=AjaxGetUsernameSuggestions&ajax=true&",
varname:"username",
json:true,
shownoresults:false,
maxresults:6,
timeout:100000
};

/*
 * AssignWidget
 *
 *   Creates an assignment widget for a bed with the id of the contents of the
 * div passed as the parameter to the constructor.  Create a new one by calling
 * new AssignWidget(div).  Everything else should happen automagically.  The div
 * ***MUST*** contain the id and only the id of the bed this widget is for.
 *
 * @param div - the div to create the widget in
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package hms
 * @subpackage javascript
 */
var AssignWidget = function(div){
    this.div = div;
    this.bed = $(this.div).text();
    this.username = '';
    this.fullname = '';
    this.profile_link = '';
    this.overlayShown = false;

    this.getStaticAssigner = function(){
        if(this.fullname.length == 0){
            return '<a href="#" onclick="return false;"><b><i>Unassigned</i></b></a>';
        }

        return this.profile_link;
    }

    this.getOverlay = function(){
        var offset = $(this.div).offset();
        var height = $(this.div).height();
        var output = '<div id="overlay_'+this.bed+'" class="overlay" style="position: absolute; top: '+(offset.top+height)+'px; left: '+offset.left+'px; z-index: 50;">';

        output += '<table>';
        output += '<tr><th>Username</th><td><input id="username_'+this.bed+'" class="username-input" type="text" value="'+this.username+'" /></td></tr>';
        output += '<tr><th>Full Name</th><td><b id="fullname_'+this.bed+'">'+this.fullname+'</b></td></tr>';
        output += '<tr><th>Meal Plan</th><td>'+dropbox+'</td></tr>';
        output += '<tr><td></td><td style="text-align: right"><button id="accept_'+this.bed+'">Assign</button><button id="cancel_'+this.bed+'">Cancel</button></td></tr>';
        output += '</table>';
        output += '</div>';



        return output;
    }

    this.setup = function(){
        var me = this;
        $.post('index.php', {module: 'hms', action: 'GetBedAssignmentInfo', bed_id: this.bed}, function(data){
            me.username = data.username;
            me.fullname = data.fullname;
            me.profile_link = data.profile_link;

            $(me.div).html(me.getStaticAssigner());
            if(me.username.length == 0)
                $(me.div).click(me.toggleOverlayFunc());
        }, 'json');
    }

    this.toggleOverlayFunc = function(){
        var me = this;
        return function(){
            me.overlayShown = !me.overlayShown;

            if(me.overlayShown){
                $(document.body).append(me.getOverlay());
                //options is defined at the top of this document
                var suggest = new bsn.AutoSuggest('username_'+me.bed, options);
                $("#username_"+me.bed).keyup(function(){
                        me.updateUsername();
                    });
                $("#cancel_"+me.bed).click(me.toggleOverlayFunc());
            } else {
                $("#overlay_"+me.bed).remove();
            }
        }
    }

    this.updateUsername = function(){
        var me = this;
        if($("#username_"+this.bed).get().length > 0){
            this.username = $("#username_"+this.bed).val();
        }
        
        $.post('index.php', {module: 'hms', action: 'AjaxGetFullnameByUsername', username: this.username},
               function(data){
                   me.fullname = data;
                   me.updateFullname();
               });
    }

    this.updateFullname = function(){
        $("#fullname_"+this.bed).html(this.fullname);
    }

    this.submitAssignment = function(){
    }
}
