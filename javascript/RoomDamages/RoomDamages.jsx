import React from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';

class RoomDamagesBox extends React.Component{
    constructor(props){
        super(props);

        this.state = {damages: [], formVisibility: false};
        this.addDamage = this.addDamage.bind(this);
        this.removeDamage = this.removeDamage.bind(this);
        this.componentWillMount = this.componentWillMount.bind(this);
        this.toggleFormVisibility = this.toggleFormVisibility.bind(this);
        this.getData = this.getData.bind(this);
        this.getOptions = this.getOptions.bind(this);
        this.postData = this.postData.bind(this);
        this.deleteData = this.deleteData.bind(this);
    }
    addDamage(type, side, desc) {
        this.postData(type, side, desc);
    }
    removeDamage(id) {
        this.deleteData(id);
    }
    componentWillMount() {
        this.getData();
        this.getOptions();
    }
    toggleFormVisibility() {
        this.setState({formVisibility: !this.state.formVisibility});
    }
    getData() {
        $.ajax({
            url: 'index.php?module=hms&action=RetrieveRoomDamage&roomPersistentId='+ this.props.roomPersistentId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                this.setState({damages: data});
            }.bind(this),
            error: function(xhr, status, err) {
                alert("Failed to grab the damages data.")
                console.error(this.props.url, status, err.toString());
            }.bind(this)
        });
    }
    getOptions() {
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
    postData(type, side, desc) {
        $.ajax({
            url: 'index.php?module=hms&action=AddRoomDamage&roomPersistentId='+ this.props.roomPersistentId+'&damageType='+type+'&side='+side+'&description=' + desc + '&term=' + this.props.term,
            type: 'POST',
            success: function(data) {
                this.getData();
                this.toggleFormVisibility();
            }.bind(this),
            error: function(xhr, status, err) {
                alert("Failed to add room damages to database properly. "+ err.toString())
                console.error(this.props.url, status, err.toString());
            }.bind(this)
        });
    }
    deleteData(id) {
        $.ajax({
            url: 'index.php?module=hms&action=RemoveRoomDamage&roomDamageId='+id,
            type: 'DELETE',
            success: function(data) {
                this.getData();
            }.bind(this),
            error: function(xhr, status, err) {
                alert("Failed to remove room damages from database properly. " + err.toString())
                console.error(this.props.url, status, err.toString());
            }
        });
    }
    render() {
        return (
            <div className="RoomDamagesBox">
                <RoomDamagesList data={this.state.damages} onDelete={this.removeDamage}/>
                <RoomDamagesDataBox visibility={this.state.formVisibility} options={this.state.options} toggle={this.toggleFormVisibility} onAdd={this.addDamage}/>
            </div>
        );
    }
}

class RoomDamagesList extends React.Component{
  render() {
    var removeClick = this.props.onDelete;
    var tableNodes = this.props.data.map(function(rowNode){
      return (
        <DamagesRow onDelete={removeClick} data={rowNode}/>
      );
    });
    return (
      <table className="table table-striped table-hover">
        <thead>
            <th>Category</th>
            <th>Description</th>
            <th>Side</th>
            <th>Term</th>
            <th>Reported On</th>
            <th>Actions</th>
        </thead>
          {tableNodes}
      </table>
    );
  }
}

class DamagesRow extends React.Component{
    constructor(props){
        super(props);

        this.delete = this.delete.bind(this);
    }
    delete() {
        var idToDelete = this.props.data.id;
        this.props.onDelete(idToDelete);
    }
    render() {
        return (
            <tbody>
                <tr>
                    <td>{this.props.data.category}</td>
                    <td>{this.props.data.description}</td>
                    <td>{this.props.data.side}</td>
                    <td>{this.props.data.term}</td>
                    <td>{this.props.data.reported_on}</td>
                    <td><a href="javascript:;" title={this.props.data.note}>
                        <i className="fa fa-comment"></i>
                    </a>
                    <button onClick={this.delete} className="close">
                        <i className="far fa-trash-alt"></i>
                    </button></td>
                </tr>
            </tbody>
        );
    }
}



class RoomDamagesDataBox extends React.Component{
    constructor(props){
        super(props);

        this.toggleFormVisibility = this.toggleFormVisibility.bind(this);
        this.add = this.add.bind(this);
    }
    toggleFormVisibility() {
        this.props.toggle();
    }
    add(dmgType, side, desc) {
        this.props.onAdd(dmgType, side, desc);
    }
    render() {
        var dataBox
        if(this.props.visibility){
            dataBox = <RoomDamagesForm data={this.props.data} options={this.props.options} onAdd={this.add} hideForm={this.toggleFormVisibility} />;
        }
        else{
            dataBox = <button onClick={this.toggleFormVisibility} className="btn btn-success btn-md">Add Damages</button>;
        }
        return (
            <div>
                {dataBox}
            </div>
        );
    }
}

class RoomDamagesForm extends React.Component{
    constructor(props){
        super(props);

        this.add = this.add.bind(this);
        this.hideForm = this.hideForm.bind(this);
    }
    add() {
        var dmgType = this.refs.damageTypeChoices.value;
        var side = this.refs.damageSideChoices.value;
        var desc = this.refs.damageDescription.value;
        this.props.onAdd(dmgType, side, desc);
    }
    hideForm() {
        this.props.hideForm();
    }
    render() {
        var options = Array({category:"Welcome", id: 0, description: "Select the type of damage"});//{id: 0, description: "Select the type of Damage"}
        var data = this.props.options;

        for(var i = 0; i < data.length; i++){
            options.push(data[i]);
        }
        var selectOptions = options.map(function(node){
            if(node.category === "Welcome"){
                return (<option value={node.id}>{node.description}</option>);
            }
            else{
                var dmgTypes = node.DamageTypes;
                var options = [];
                for(var i=0; i < dmgTypes.length;i++){
                    var object = dmgTypes[i];
                    options[i+1] = <option value={object.id}>{object.description}</option>;
                }

                return(<optgroup label={node.category}>
                    {options}
                </optgroup>);
            }
        });

        return(
            <div className="form-group">
                <h4>
                    <strong>Add Room Damage</strong>
                </h4>
                <div className="row">
                    <div className="col-md-7">
                        <label>
                            Damage Type:
                        </label>
                        <select className="form-control" ref="damageTypeChoices">
                            {selectOptions}
                        </select>
                    </div>
                </div>

                <div className="row">
                    <div className="col-md-7">
                        <label>
                            Side of Room:
                        </label>
                        <select className="form-control" ref="damageSideChoices">
                            <option value="Both">Both</option>
                            <option value="Left">Left</option>
                            <option value="Right">Right</option>
                        </select>
                    </div>
                </div>

                <label>Description</label>
                <textarea className="form-control" ref="damageDescription"></textarea>

                <p></p>

                <div className="row">
                    <div className="col-md-2">
                        <button onClick={this.add} className="btn btn-success btn-md">Add Damage</button>
                    </div>

                    <div className="col-md-3 col-md-offset-7">
                        <button onClick={this.hideForm} className="btn btn-outline-dark btn-md float-right">Close Form</button>
                    </div>
                </div>
            </div>
        );
    }
}


ReactDOM.render(<RoomDamagesBox term={window.term} roomPersistentId={window.roomPersistentId}/>, document.getElementById('RoomDamages'));
