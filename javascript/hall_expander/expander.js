//abstract element class
var element = function(){
    this.index = 0;
    this.enabled = false;
    this.type = 'element';
    this.name = 'Element';

    this.isEnabled = function(){
        return this.enabled;
    }

    this.draw = function(){
        return "";
    }

    this.load = function(obj){
        this.name = obj.name;
        this.index = obj.id;
        this.enabled = obj.enabled;
    }
}

/* Hall Class */
var hall = function(){
    this.children = new Array();
}
hall.prototype = new element();
hall.prototype.constructor = hall;
hall.prototype.type = 'hall';

hall.prototype.addChild = function(child){
    this.children.push(child);
}

hall.prototype.draw = function(){
    var output = '<span id="hall_'+this.index+'" class="hall"><input type="checkbox" ref="'+this.index+'" objtype="'+this.type+'" '+(this.isEnabled() ? '' : 'disabled="true"')+' />'+this.name;
    output += '<span class="subtree"><ul>';

    for(var i in this.children){
        output += '<li class="noexpand">'+this.children[i].draw()+'</li>';
    }
    output +="</ul></span></span>";

    return output;
}

hall.prototype.load = function(obj){
    (new element).load.call(this, obj);
    for(var i in obj.floors){
        var newFloor = new floor();
        newFloor.load(obj.floors[i]);
        this.addChild(newFloor);
    }
}

/* Floor Class */
var floor = function(){}
floor.prototype = new element();
floor.prototype.constructor = floor;
floor.prototype.type = 'floor';

floor.prototype.draw = function(){
    return '<input type="checkbox" ref="'+this.index+'" objtype="'+this.type+'" '+(this.isEnabled() ? '' : 'disabled="true"' )+' />'+this.name;
}
