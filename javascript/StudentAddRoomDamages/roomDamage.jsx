import React from 'react';
import ReactDOM from 'react-dom';
import classNames from 'classnames';
import $ from 'jquery';

// Top level component responsible for handling most of the high level logic,
// storing relevant data, and making ajax calls.
class RoomDamageBox extends React.Component{
    // Sets up an initial state for the class, with default values.
    constructor(props){
        super(props);

        this.state = {room: {location: this.props.roomLocation},
        damages: undefined,
        newDamages: [],
        options: [],
        alert: undefined}

        this.componentWillMount = this.componentWillMount.bind(this);
        this.addUnsavedDamages = this.addUnsavedDamages.bind(this);
        this.saveDamages = this.saveDamages.bind(this);
        this.getRoomDamages = this.getRoomDamages.bind(this);
        this.getOptions = this.getOptions.bind(this);
        this.addRoomDamages = this.addRoomDamages.bind(this);
        this.removeRoomDamages = this.removeRoomDamages.bind(this);
    }
    componentWillMount(){
        this.getRoomDamages();
        this.getOptions();
    }
    addUnsavedDamages(type, sideInput, noteInput){
        var options = this.state.options

        var categ = '';
        var dmgTypeDesc = '';
        var found = false;

        for(var i = 0; i < options.length; i++){
            if(!found){
                var dmgTypeArr = options[i].DamageTypes;
                for(var j = 0; j < dmgTypeArr.length; j++){
                    if(dmgTypeArr[j].id == type){ // NB: Must be double equals, not triple
                        categ = options[i].category;
                        dmgTypeDesc = dmgTypeArr[j].description;
                        found = true;
                    }
                }
            }
        }

        var newDamageNode = {dmgTypeId: type, category: categ, description: dmgTypeDesc, side: sideInput, note: noteInput};
        var newDmgsArr = this.state.newDamages;
        newDmgsArr.push(newDamageNode);
        this.setState({newDamages: newDmgsArr});
    }
    saveDamages(){
        var newDamages = this.state.newDamages;
        //console.log(newDamages)
        for(var i = 0; i < newDamages.length; i++)
        {
            var damage = newDamages[i];
            this.addRoomDamages(damage.dmgTypeId, damage.side, damage.note);
        }
        this.setState({newDamages: []});
    }
    // Function responsible for setting up an ajax call to retrieve the current room damages.
    getRoomDamages(){
        var inputData = {roomPersistentId: this.props.roomPID};
        $.ajax({
            url: 'index.php?module=hms&action=RetrieveRoomDamage',
            type: 'GET',
            datatype: 'json',
            data: inputData,
            success: function(data)
            {
                var outputData = [];
                outputData = JSON.parse(data);
                this.setState({damages: outputData});
            }.bind(this),
            error: function(xhr, status, err)
            {
                alert("Failed to set status properly.")
                console.error(this.props.url, status, err.toString());
            }.bind(this)
        });
    }
    getOptions(){
        $.ajax({
            url: 'index.php?module=hms&action=AjaxGetRoomDamageTypes',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                this.setState({options: data});
            }.bind(this),
            error: function(xhr, status, err) {
                alert("Failed to grab the damages options for drop down")
                console.error(this.props.url, status, err.toString());
            }.bind(this)
        });
    }
    // Function responsible for setting up and executing the ajax call to add room damages.
    addRoomDamages(type, side, desc){
        //console.log(desc)
        $.ajax({
          //url: 'index.php?module=hms&action=AddRoomDamage&roomPersistentId='+roomPID+'&damageType='+type+'&side='+side+'&description='+desc+'&term='+term,
          url: 'index.php?module=hms&action=AddRoomDamage&roomPersistentId=' + this.props.roomPID + '&term=' + this.props.term,
          type: 'POST',
          data: {damageType: type,
                side: side,
                description: desc
            },
          success: function(data) {
              var newAlert = JSON.parse(data);
              this.setState({alert: newAlert});
              this.getRoomDamages();
          }.bind(this),
          error: function(xhr, status, err) {
            alert("Failed to add room damages to database properly. "+ err.toString())
            console.error(this.props.url, status, err.toString());
          }.bind(this)
        });
    }
    // Function responsible for removing room damages from the newDamages array
    removeRoomDamages(id){
        var newDmgsArr = this.state.newDamages;
        var len = newDmgsArr.length;
        var indexToSplice = -1;
        for(var i = 0; i < len; i++){
            if(newDmgsArr[i].id === id){
                indexToSplice = i;
            }
        }
        if(indexToSplice !== -1){
            newDmgsArr.splice(indexToSplice, 1);
        }
        this.setState({newDamages: newDmgsArr});
    }
    render(){
        return (
            <div>
                <h2>Room Damages <small>{this.state.room.location}</small></h2>
                <CurrentDamagesTable roomDamages={this.state.damages}/>
                <AddDamageBox options={this.state.options} addDamage={this.addUnsavedDamages} newDamages={this.state.newDamages}
                    removeRoomDamages={this.removeRoomDamages} alert={this.state.alert} saveDamages={this.saveDamages}/>
            </div>
        );
    }
}

// Component responsible for displaying the table of the current saved damages
class CurrentDamagesTable extends React.Component{
    render(){
        if(this.props.roomDamages !== undefined) {
            var rows = this.props.roomDamages.map(function(node){
                return (
                    <tr key={node.id}>
                        <td>{node.category}</td>
                        <td>{node.description}</td>
                        <td>{node.side}</td>
                        <td>{node.term}</td>
                        <td>{node.reported_on}</td>
                        <td>
                            <a href="javascript:;" title={node.note}>
                                <i className="fa fa-comment"></i>
                            </a>
                        </td>
                    </tr>
                );
            });
        } else {
            var emptyMsg = (<p>No reported damages for your room.</p>)
        }

        return (
            <div className="row">
                <div className="col-md-8">
                    <div className="panel panel-default">
                        <div className="panel-heading">
                            <h4>Existing Damages</h4>
                        </div>
                        <div className="panel-body">
                            <p>Damages that we already know about are listed below. You won't be charged for these when you check-out.</p>
                            <table className="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Damage Type</th>
                                        <th>Side</th>
                                        <th>Term</th>
                                        <th>Reported On</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rows}
                                </tbody>
                            </table>
                            {emptyMsg}
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}


// Component responsible for collecting the relevant information for adding room damage, as
// well as the button to add it to the UnsavedDamagesTable table.
class AddDamageBox extends React.Component{
    constructor(props){
        super(props);

        this.state = {dmgTypeValid: undefined, descValid: undefined}
        this.add = this.add.bind(this);
    }
    add(){

        var dmgType = this.refs.damageType.value;
        var side = this.refs.side.value;
        var desc = this.refs.desc.value;

        var dmgTypeUnset = (dmgType === 0);
        var descUnset = (desc === "");

        // If the form data is valid, then update state
        if(!dmgTypeUnset && !descUnset){
            // Reset the form for the next damage
            this.refs.damageType.value = '0';
            this.refs.side.value = 'Both';
            this.refs.desc.value = '';

            this.props.addDamage(dmgType, side, desc);
        }

        // Otherwise, update state to indicate an error
        this.setState({dmgTypeInvalid: dmgTypeUnset, descInvalid: descUnset});

    }
    render(){
        var options = Array({category:"Welcome", id: 0, description: "Select the type of damage"});//{id: 0, description: "Select the type of Damage"}

        var data = this.props.options;
        for(var i = 0; i < data.length; i++) {
          options.push(data[i]);
        }

        var selectOptions = options.map(function(node){
            if(node.category === "Welcome") {
                return (<option key={node.id} value={node.id}>{node.description}</option>);
            } else {
              var dmgTypes = node.DamageTypes;
              var options = [];
              for(var i = 0; i < dmgTypes.length;i++) {
                var object = dmgTypes[i];
                options[i+1] = <option key={object.id} value={object.id}>{object.description}</option>;
              }

              return(<optgroup key={node.category} label={node.category}>
                {options}
              </optgroup>);
            }
        });

        var dmgTypeError = false;
        var descError = false;

        if(this.state.dmgTypeInvalid !== undefined) {
            dmgTypeError = this.state.dmgTypeInvalid;
        }

        if(this.state.descInvalid !== undefined) {
            descError = this.state.descInvalid;
        }

        var dmgTypeClasses = classNames({
            'form-group': true,
            'has-error': dmgTypeError
        });

        var descClasses = classNames({
            'form-group': true,
            'has-error': descError
        });

        return (
            <div className='row'>
                <div className="col-md-8">
                    <div className="panel panel-default">
                        <div className="panel-heading">
                            <h4>New Room Damages</h4>
                        </div>

                        <div className="panel-body">
                            <div className="row">
                                <div className="col-md-10">
                                    <p>Add any additional damages you've found in your room. The left/right side of the room are from the perspective of facing into the room from the hallway.</p>
                                </div>
                            </div>

                            <AlertBox alert={this.props.alert}/>

                            <UnsavedDamagesTable newRoomDamages={this.props.newDamages} removeRow={this.props.removeRoomDamages} saveDamages={this.props.saveDamages}/>
                            <hr/>
                            <div className="row">
                                <div className="col-md-5">
                                    <div className={dmgTypeClasses}>
                                        <label>Damage Type:</label>
                                        <select className="form-control" ref="damageType">
                                            {selectOptions}
                                        </select>
                                    </div>
                                </div>
                                <div className="col-md-3">
                                    <div className="form-group">
                                        <label>Side of Room:</label>
                                        <select className="form-control" ref="side">
                                            <option>Both</option>
                                            <option>Left</option>
                                            <option>Right</option>
                                        </select>
                                    </div>
                                </div>
                                <div className="col-md-4">
                                    <div className={descClasses}>
                                        <label>Description</label>
                                        <input type="text" className="form-control" ref="desc"></input>
                                    </div>
                                </div>
                            </div>
                            <div className="form-group">
                                <button onClick={this.add} className="btn btn-md btn-success pull-right"><i className="fa fa-plus"></i> Add Damage</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

class AlertBox extends React.Component{
    render(){
        if(this.props.alert === undefined){
            return (
                <div></div>
            );
        }
        else{
            //console.log(this.props.alert)
            if(this.props.alert.status === "success"){
                return (
                    <div className="alert alert-success">
                        <i className="fa fa-check fa-2x"></i> <span>Room Damages successfully added.</span>
                    </div>
                );
            }
            else{
                return (
                    <div className="alert alert-danger">
                        <i className="fa fa-times fa-2x"></i> <span>{this.props.alert.status}</span>
                    </div>
                );
            }
        }
    }
}

// Component responsible for displaying the table of the unsaved damages that have been
// added but not saved by the user.
class UnsavedDamagesTable extends React.Component{
    constructor(props){
        super(props);

        this.removeRow = this.removeRow.bind(this);
    }
    removeRow(id){
        this.props.removeRow(id);
    }
    render(){
        //console.log(this.props.newRoomDamages)
        if(this.props.newRoomDamages.length !== 0){
            var data = this.props.newRoomDamages;
            var removeRow = this.removeRow
            var rows = data.map(function(node){
                return (
                    <UnsavedDamageRow key={node.dmgTypeId + node.category + node.side} node={node} removeRow={removeRow}/>
                );
            });
            return (
                <div>
                    <h5><strong>New Unsaved Damages</strong></h5>
                    <table className="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Damage Type</th>
                                <th>Side</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            {rows}
                        </tbody>
                    </table>
                    <div className="row">
                        <div className="col-md-12">
                            <button onClick={this.props.saveDamages} className="btn btn-lg btn-primary pull-right">Save New Damages</button>
                        </div>
                    </div>
                </div>
            );
        }
        else {
            return (<div></div>);
        }
    }
}

class UnsavedDamageRow extends React.Component{
    constructor(props){
        super(props);

        this.removeRow = this.removeRow.bind(this);
    }
    removeRow(){
        this.props.removeRow(this.props.node.id);
    }
    render(){
        var node = this.props.node;
        var commentStyle = {'marginRight': '5px'};
        return (
            <tr>
                <td>{node.category}</td>
                <td>{node.description}</td>
                <td>{node.side}</td>
                <td>
                    <a style={commentStyle} className="pull-left" href="javascript:;" title={node.note}>
                        <i className="fa fa-comment"></i>
                    </a>
                    <button onClick={this.removeRow} className="close pull-right">
                      <i className="far fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        )
    }
}



//Inserts all the react components within the given element.
ReactDOM.render(<RoomDamageBox roomLocation={window.roomLocation} roomPID={window.roomPID} term={window.term}/>, document.getElementById('roomDamage'));
