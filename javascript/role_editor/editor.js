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

        contents += '<button type="button" class="btn btn-primary pull-right" onclick="managerPopup.open(\''+this.className+'\', '+this.instance+', '+this.name+');"><i class="fa fa-user-plus"></i> Add User</button>';

        for(var i in this.roles){
            contents += '<h3>'+this.roles[i].getName()+'</h3>';
            var members = this.roles[i].getMembers();

            if(members.length == 0){
            	contents += '<p class="text-muted"><em>No users with this role.</em></p>';
            }else{
            	contents += '<ul>';
            	for(var j in members){
            		contents += '<li>'+members[j].fullname+' <i class="far fa-trash-alt" style=" cursor: pointer" onclick="removeUser(\''+members[j].username+'\', \''+this.roles[i].getName()+'\', newMan);"></i>';
            	}
            	contents += '</ul>';
            }
        }

        $(this.div).html(contents);
    }
}
