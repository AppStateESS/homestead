<script type="text/javascript" src="mod/hms/javascript/profileCancelApplication/cancelApplication.js"></script>
<script type="text/javascript" src="mod/hms/javascript/spin/spin.min.js"></script>
<script type="text/javascript">

var spinnerOpts = {
        lines: 13, // The number of lines to draw
        length: 7, // The length of each line
        width: 4, // The line thickness
        radius: 10, // The radius of the inner circle
        rotate: 0, // The rotation offset
        color: '#000', // #rgb or #rrggbb
        speed: 1, // Rounds per second
        trail: 60, // Afterglow percentage
        shadow: false, // Whether to render a shadow
        hwaccel: false, // Whether to use hardware acceleration
        className: 'spinner', // The CSS class to assign to the spinner
        zIndex: 2e9, // The z-index (defaults to 2000000000)
        top: 'auto', // Top position relative to parent in px
        left: 'auto' // Left position relative to parent in px
      };

$(document).ready(function() {
    $("#cancelDialog").dialog({autoOpen: false,
                               modal: true,
                               width: 425,
                               resizable: false,
                               title: 'Cancel Application',
                               position: 'center',
                               buttons: [
                                          {text: "Cancel Application",
                                           id: "cancelDialogSubmitButton",
                                           click: function() {
                                              $('{LINK_SELECT}').handleCancelButton();
                                               }
                                          },
                                          {text: "Close",
                                           id: "cancelDialogCloseButton",
                                           click: function() {
                                               $(this).dialog("close");
                                               }
                                          }
                                        ]
                              });
	$('{LINK_SELECT}').linkPopup();
});
</script>