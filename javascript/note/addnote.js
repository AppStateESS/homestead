/*
 * NoteLink
 *
 *   Turns a hyperlink into a clickable way of accessing a note dialog.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package hms
 */

var NoteLink = function(link){
    this.link = link;
    var me    = this;

    this.showDialog = function(){
        $("#note-link-dialog").dialog('open');
    }

    this.hideDialog = function(){
    }

    $(document.body).append('<div id="note-link-dialog">'
                            +'<form action="index.php">'
                            +'<input type="hidden" name="module" value="hms" />'
                            +'<input type="hidden" name="action" value="AddNote" />'
                            +'<div>Username: <input type="text" name="username" /></div>'
                            +'<div>Note: <br /><textarea style="width: 309px; height: 128px;" row="5" name="note" /></div>'
                            +'<input type="submit" value="Add Note"'
                            +'</div>');
    $("#note-link-dialog").dialog({modal: true, autoOpen: false, width: 350, height: 250 });

    $(this.link).click(function(){
            console.log(me);
            me.showDialog();
        });
}