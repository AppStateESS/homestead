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
 * Semaphore
 *
 *   Control access to a resource.  Only allow a limited number of
 * users to acquire a resource at a given time.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package hms
 * @subpackage javascript
 */
var Semaphore = function(count){
    this.count = count;

    this.acquire = function(){
        if(this.count > 0){
            this.count--;
            return true;
        }
        return false;
    }

    this.release = function(){
        this.count++;
        return true;
    }
}

/*
 * AssignWidget
 *
 *   Creates an assignment widget for a bed with the id of bed field of the div
 * passed to the constructor.  Create a new one by calling new AssignWidget(div),
 * everything else should happen automagically.
 *
 * @param div - the div to create the widget in
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package hms
 * @subpackage javascript
 */
var AssignWidget = function(div, semaphore){
    this.div = div;
    this.bed = $(this.div).attr('bed');
    this.username = '';
    this.fullname = '';
    this.profile_link = '';
    this.overlayShown = false;
    this.semaphore = semaphore // determine whether or not a dialog is already show on the page
    this.haveSemaphore = false;

    this.getStaticAssigner = function(){
        if(this.fullname.length == 0){
            return '<a href="#" onclick="return false;"><b><i>Unassigned</i></b></a>';
        }

        return this.profile_link;
    }

    this.getOverlay = function(){
        var offset = $(this.div).offset();
        var height = $(this.div).height();
        var output = '<div id="floor_overlay_'+this.bed+'" class="overlay" style="position: absolute; top: '+(offset.top+height)+'px; left: '+offset.left+'px;">';
               
        output += '<table>';
        output += '<tr><th>Username</th><td><input id="username_'+this.bed+'" class="username-input" type="text" value="'+this.username+'" /></td></tr>';
        output += '<tr><th>Full Name</th><td><b id="fullname_'+this.bed+'">'+this.fullname+'</b></td></tr>';
        output += '<tr><th>Meal Plan</th><td>'+dropbox+'</td></tr>';
        output += '<tr><th>Assignment Type</th><td>'+assignmentBox+'</td></tr>';
        output += '<tr><td></td><td style="text-align: right"><span id="status_'+this.bed+'" /><button id="accept_'+this.bed+'">Assign</button><button id="cancel_'+this.bed+'">Cancel</button></td></tr>';
        output += '</table>';
        output += '<div id="message_'+this.bed+'" />'; 
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
            if(!me.haveSemaphore && !me.semaphore.acquire())
                return;

            me.haveSemaphore = true;
            me.overlayShown = !me.overlayShown;

            if(me.overlayShown){
                $(document.body).append(me.getOverlay());
                //options is defined at the top of this document
                var suggest = new bsn.AutoSuggest('username_'+me.bed, options);
                $("#username_"+me.bed).keydown(function(e){
                        if(e.keyCode == 13){
                            me.submitAssignment();
                        }
                    });
                $("#username_"+me.bed).keyup(function(){                        
                        me.updateUsername();
                    });
                $("#accept_"+me.bed).click(function(){
                        $("#status_"+me.bed).html('<img src="images/core/ajax-loader.gif" />');
                        me.syncOverlay();
                        me.submitAssignment()
                            });
                $("#cancel_"+me.bed).click(me.toggleOverlayFunc());
            } else {
                $("#overlay_"+me.bed).remove();
                me.semaphore.release();
                me.haveSemaphore = false;
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

    this.syncOverlay = function(){
        if($("#username_"+this.bed).get().length > 0){
            this.username = $("#username_"+this.bed).val();
        }
        this.mealplan = $('#overlay_'+this.bed).children().find("#select_meal_plan").val();
        this.assignmenttype = $('#overlay_'+this.bed).children().find("#select_assignment_type").val();
    }

    this.submitAssignment = function(){
        var me = this;
        $.post('index.php', {module: 'hms', action: 'FloorAssignStudent', bed: this.bed, mealplan: this.mealplan, username: this.username, assignmenttype: this.assignmenttype},
               function(data){
                   if(!data.success){
                       $("#message_"+me.bed).html('<div class="error"><img src="images/mod/hms/tango/dialog-error.png" />'+data.message+'</div>');
                       $("#status_"+me.bed).html('');
                   } else {
                       $("#message_"+me.bed).html('<div class="success"><img src="images/mod/hms/icons/check.png" />Student Assigned!</div>');
                       $("#status_"+me.bed).html('');
                       setTimeout(function(){
                               var func = me.toggleOverlayFunc();
                               func();
                               var newAssigner = new AssignWidget(me.div);
                               newAssigner.setup();
                           }, 2500);
                   }
               },'json');
    }
}
