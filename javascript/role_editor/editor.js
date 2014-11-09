function removeUser(username, role, managerObj){
    $.post('index.php', {module: 'hms', action: 'RoleRemoveUser', username: username, role: role, className: className, instance: instance},
        function(data){
            managerObj.getMembers();
        }
    );
}


var role = function(){
    this.name = "";
    this.members = new Array();
    this.id;

    this.getName = function(){
        return this.name;
    }

    this.setName = function(name){
        this.name = name;
    }

    this.getMembers = function(){
        return this.members;
    }

    this.addMember = function(member, displayName){
        this.members.push({username: member, fullname: displayName});
    }

    this.getId = function(){
        return this.id;
    }

    this.setId = function(id){
        this.id = id;
    }
}

var roleMan = function(className, instance, div, name){
    this.div       = div;
    this.roles     = new Array();
    this.className = className;
    this.instance  = instance;
    this.name      = name;

    this.getMembers = function(){
        var me = this;

		$.post('index.php', {module: 'hms', action: 'ListRoles'},
			function(data){
				me.roles = new Array(); //clear the roles list
				for(var i in data){
					var newRole = new role();
					newRole.setName(data[i].name);
					me.roles.push(newRole);
				}
				$.post('index.php', {module: 'hms', action: 'ListRoleMembers', type: className, instance: instance},
					function(data){
						for(var i in data){
							for(var j in me.roles){
								if(me.roles[j].getName() == data[i].name){
									me.roles[j].addMember(data[i].username, data[i].display_name);
								}
							}
						}
						me.draw();
					},
					'json'
				);
			},
			'json'
		);
    }

    this.draw = function(){
        $(this.div).empty();
        var contents = "";
        var tabs = new Array();
        var divs = new Array();

        contents += '<div id="roleTabs">';
        tabs.push('<ul>');
        for(var i in this.roles){
            tabs.push('<li><a href="#tabs-'+i+'">'+this.roles[i].getName()+'</a></li>');
            var divContents = '<div id="tabs-'+i+'">';
            var members = this.roles[i].getMembers();
            
            if(members.length == 0){
            	divContents += 'Please add members with the button below.';
            }else{
            	divContents += '<ul>';
            	for(var j in members){
            		divContents += '<li>'+members[j].fullname+'<img width="13" height="13" style="margin-left: 5px; cursor: pointer" src="mod/hms/img/tango/process-stop.png" onclick="removeUser(\''+members[j].username+'\', \''+this.roles[i].getName()+'\', newMan);"></li>';
            	}
            	divContents += '</ul>';
            }
            
            divContents += '</div>';
            divs.push(divContents);
        }
        tabs.push('</ul>');
        contents += tabs.join('')+divs.join('');
        contents += '</div>';

        $(this.div).html(contents);
        $("#roleTabs").tabs();
        $(this.div).append('<button onclick="managerPopup.open(\''+this.className+'\', '+this.instance+', '+this.name+');">Add User</button>');
    }
}
