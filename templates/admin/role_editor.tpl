<div id="manager"></div>
<div id="managerPopup"></div>

<script type="text/javascript">
var className = {CLASS_NAME};
var instance  = {ID};

var managerPopup = new function(){
    this.opened = false;
    this.manager = undefined;
    $("#managerPopup").dialog({autoOpen: false});

    this.open = function(className, instance, manager){
        if(this.opened){
            return false;
        }

        this.manager = manager;

        $.post('index.php', {module: 'hms', action: 'ListRoles'},
            function(data){
                var options = new Array();
                for(var i in data){
                    options.push('<option value="'+data[i].id+'">'+data[i].name+'</option>');
                }
                $("#managerPopup").html(
                    '<div id="popupError"></div>'+
					'<form id="popupForm" onsubmit="return managerPopup.submit();">'+
					'<input type="hidden" id="popupType" value="'+className+'" />'+
					'<input type="hidden" id="popupInstance" value="'+instance+'" />'+
					'<table>'+
						'<tr>'+
							'<th>'+
								'<label for="username">Username: </label>'+
							'</th>'+
							'<td>'+
								'<input id="popupUsername" type="text" name="username" />'+
							'</td>'+
						'</tr>'+
						'<tr>'+
							'<th>'+
								'<label for="role">Role: </label>'+
							'</th>'+
							'<td>'+
								'<select id="popupRole">'+options.join('')+'</select>'+
							'</td>'+
						'</tr>'+
					'</table>'+
					'<input type="submit" value="Submit" />'+
					'</form>'
                );
                $("#managerPopup").dialog('open');
            },
            'json'
        );
    }

    this.submit = function(){
        var me = this;
        var username = $("#popupUsername").val();
        var role     = $("#popupRole").val();
        var type     = $("#popupType").val();
        var instance = $("#popupInstance").val();

        $.post('index.php', {module: 'hms', action: 'RoleAddUser', username: username, role: role, class: type, instance: instance},
            function(data){
                if(data == "true"){
                    me.close();
                } else {
                    $("#popupError").html('<span class="error">' + data + '</span>');
                }
            },
            'json'
        );

		return false;
    }

    this.close = function(){
        this.manager.getMembers();
        this.manager = undefined;

        $("#managerPopup").dialog('close');
        $("#managerPopup").empty();
        this.opened = false;
    }
}

var newMan = new roleMan(className, instance, "#manager", "newMan");
newMan.getMembers();
</script>
