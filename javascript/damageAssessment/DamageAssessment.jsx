// Polyfill for Array.prototype.findIndex, because it's not in IE (except Edge)
if (!Array.prototype.findIndex) {
  Array.prototype.findIndex = function(predicate) {
    if (this === null) {
      throw new TypeError('Array.prototype.findIndex called on null or undefined');
    }
    if (typeof predicate !== 'function') {
      throw new TypeError('predicate must be a function');
    }
    var list = Object(this);
    var length = list.length >>> 0;
    var thisArg = arguments[1];
    var value;

    for (var i = 0; i < length; i++) {
      value = list[i];
      if (predicate.call(thisArg, value, i, list)) {
        return i;
      }
    }
    return -1;
  };
}

var ReactCSSTransitionGroup = React.addons.CSSTransitionGroup;

// Component representing
var DamageResponsibility = React.createClass({
    handleCostChange: function(e)
    {
        var updatedResp = this.props.responsibility;
        updatedResp.assessedCost = e.target.value;

        this.props.handleCostChange(updatedResp);
    },
    render: function()
    {
        return (
            <div className="row" style={{marginTop:'.5em'}}>
                <div className="col-md-2 col-md-offset-1">{this.props.responsibility.studentName}</div>
                <div className="col-md-3">
                    <div className="input-group">
                        <span className="input-group-addon">$</span>
                        <input type="number" className="form-control" placeholder="0" min="0" ref="dmgCharge" aria-label="Amount" value={this.props.assessedCost} onChange={this.handleCostChange} />
                        <span className="input-group-addon">.00</span>
                    </div>
                </div>
            </div>
        );
    }
});


// Component representing an individual damage
var DamageItem = React.createClass({
    handleSplitCost: function() {
        var splitAmount = this.props.damageTypes[this.props.damage.damage_type].cost / this.props.damage.responsibilities.length;

        var newResp = this.props.damage.responsibilities;

        for(resp of newResp){
            resp.assessedCost = splitAmount;
            this.props.updateResponsibilityCallback(resp);
        }

    },
    render: function() {
        // Get the damageType (name, desc, cost) from the list of damageTypes based on this damage's type id
        var dmgType = this.props.damageTypes[this.props.damage.damage_type];

        // Setup the list of students responsible for this damage
        var responsibilities = this.props.damage.responsibilities.map(function(resp){
            return (<DamageResponsibility responsibility={resp} key={resp.id} assessedCost={resp.assessedCost} handleCostChange={this.props.updateResponsibilityCallback}/>);
        }.bind(this));

        var sumOfCharges = 0;
        for (i = 0; i < this.props.damage.responsibilities.length; i++){
            if(this.props.damage.responsibilities[i].assessedCost != ''){
                sumOfCharges += parseFloat(this.props.damage.responsibilities[i].assessedCost, 10);
            }
        }

        // Display to two decimal places
        sumOfCharges = sumOfCharges.toFixed(2);

        return (
            <div>
                <div className="row">
                    <div className="col-md-3 col-md-offset-1">
                        <h4>{dmgType.category} - {dmgType.description} - ${dmgType.cost}</h4>
                    </div>
                    <div className="col-md-2">
                        <button type="button" style={{marginTop: "1em"}} className="btn btn-default btn-xs" onClick={this.handleSplitCost}>Split Evenly</button>
                    </div>
                </div>
                <div className="row">
                    <div className="col-md-12">
                        {responsibilities}
                    </div>
                </div>
                <div className="row">
                    <div className="col-md-2 col-md-offset-3">
                        Total: ${sumOfCharges}
                    </div>
                </div>
            </div>
        );
    }
});



// Room level component - encapsulates one or more damages to a room
var DamageRoom = React.createClass({
    getInitialState: function()
    {
        // For each daamage, and for each responsibility -- initialize the assessedCost field of each responsibility
        damages = this.props.room.damages;
        for(i = 0; i < damages.length; i++){
            for(j = 0; j < damages[i].responsibilities.length; j++){
                damages[i].responsibilities[j].assessedCost = 0;
            }
        }

        return ({damages: damages,
                submitted: false,
                saved: false,
                saveError: false,
                visible: true
            });
    },
    updateResponsibility: function(responsiblity)
    {
        // Find the corresponding responsiblity in our current state and update it
        var currentDamages = this.state.damages;

        var dmgIndex = currentDamages.findIndex(function(element, index, arr){
            if(responsiblity.damage_id == element.id){
                return true;
            } else {
                return false;
            }
        }.bind(this));


        var currentResp = this.state.damages[dmgIndex].responsibilities;

        var respIndex = currentResp.findIndex(function(element, index, arr){
            if(responsiblity.id == element.id){
                return true;
            } else {
                return false;
            }
        }.bind(this));

        currentResp[respIndex] = responsiblity;
        currentDamages[dmgIndex].responsibilities = currentResp;

        this.setState({damages: currentDamages});
    },
    handleSave: function()
    {
        // Update state to disable submit button and show spinner
        this.setState({submitted: true}, function(){
            // Post to server
            this.saveResponsibilities();
        }.bind(this));
    },
    saveResponsibilities: function()
    {
        var responsibilities = [];

        for(i = 0; i < this.state.damages.length; i++){
            for (j = 0; j < this.state.damages[i].responsibilities.length; j++){
                responsibilities.push(this.state.damages[i].responsibilities[j]);
            }
        }

        $.ajax({
            // Load the list of damage types
            url: 'index.php?module=hms&action=AssessRoomDamage&ajax=true',
            method: 'POST',
            data: {responsibilities: responsibilities},
            dataType: 'text',
            success: function(data) {
                this.setState({saved: true});
                this.setTimer();
            }.bind(this),
            error: function(xhr, status, err) {
                this.setState({saveError: true});
                console.error(status, err.toString());
            }.bind(this)
        });
    },
    setTimer: function(){
        this.timer != null ? clearTimeout(this.timer) : null;

        this.timer = setTimeout(function(){
            this.props.removeRoomCallback(this.props.room.id);
            this.timer = null;
        }.bind(this), this.props.hideDelay);
    },
    render: function()
    {
        var damageItems = this.state.damages.map(function(damage){
            return (
                <DamageItem key={damage.id} damage={damage} damageTypes={this.props.damageTypes} updateResponsibilityCallback={this.updateResponsibility}/>
            );
        }.bind(this));

        if(this.state.saved){
            var submitButton = <button className="btn btn-success disabled" disabled="disabled">Saved Successfully!</button>;
        } else if(this.state.saveError){
            var submitButton = <button className="btn btn-default disabled btn-danger" disabled="disabled">Something went wrong while sending to Student Accounts!</button>
        } else if(this.state.submitted){
            var submitButton = <button className="btn btn-default disabled" disabled="disabled"><i className="fa fa-spinner fa-pulse"></i> Sending to Student Accounts</button>
        } else {
            var submitButton = <button className="btn btn-default" onClick={this.handleSave}>Report to Student Accounts</button>
        }

        return(
            <div className="panel panel-default">
                <div className="panel-body">
                    <div className="row">
                        <div className="col-md-12">
                            <h3 style={{marginTop:"0px"}}>{this.props.room.hallName} {this.props.room.room_number}</h3>
                            {damageItems}
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-md-6 col-md-offset-5">
                            <div style={{marginTop: "1em"}}>
                                {submitButton}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
});

// Top-level parent component
var DamageAssessment = React.createClass({
    getInitialState: function()
    {
        return ({roomList: null, damageTypes: null, error: null});
    },
    componentWillMount: function()
    {
        // Load the list of damage types
        $.ajax({
            url: 'index.php?module=hms&action=GetDamageTypes',
            data: {term: this.props.term},
            dataType: 'json',
            success: function(data) {
                this.setState({damageTypes: data});

                // Load the list of rooms with damages that need to be assessed
                $.ajax({
                    url: 'index.php?module=hms&action=GetRoomDamagesToAssess',
                    data: {term: this.props.term},
                    dataType: 'json',
                    success: function(data) {
                        this.setState({roomList: data});
                    }.bind(this),
                    error: function(xhr, status, err) {
                        if(xhr.status == 401){
                            this.setState({error: "You do not have permission to assess damages in any halls. You need to have the RD or Coordinator role for at least one Residence Hall."})
                            return;
                        }
                        console.error(status, err.toString());
                    }.bind(this)
                });

            }.bind(this),
            error: function(xhr, status, err) {
                console.error(status, err.toString());
            }.bind(this)
        });

    },
    removeRoomCallback: function(roomId){

        var roomList = this.state.roomList;

        var roomIndex = roomList.findIndex(function(element, index, arr){
            if(roomId == element.id){
                return true;
            } else {
                return false;
            }
        }.bind(this));

        roomList.splice(roomIndex, 1);
        this.setState({roomList: roomList});
    },
    render: function()
    {
        if (this.state.error !== null){
            var rooms = <div className="alert alert-danger" role="alert">{this.state.error}</div>
        } else if(this.state.roomList == null) {
            var rooms = <h3><i className="fa fa-spinner fa-pulse"></i> Loading Damages</h3>
        } else if (this.state.roomList.length == 0) {
            var rooms = <h3>There are no damages to assess!</h3>
        } else {
            var rooms = this.state.roomList.map(function(room) {
                key = room.room_number + room.hallName;
                return (<DamageRoom room={room} damageTypes={this.state.damageTypes} hideDelay={1500} removeRoomCallback={this.removeRoomCallback} key={key}/>);
            }.bind(this));
        }

        return (
            <div className="row">
                <div className="col-md-11 col-md-offset-1">
                    <ReactCSSTransitionGroup transitionName="dmgAssessment" transitionEnter={false} transitionLeaveTimeout={3000}>
                        {rooms}
                    </ReactCSSTransitionGroup>
                </div>
            </div>
        );
    }
});

ReactDOM.render(<DamageAssessment term={term}/>, document.getElementById('DamageAssessment'));
