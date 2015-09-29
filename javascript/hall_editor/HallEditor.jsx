//The top level component class that holds the state and handles the server requests.
var HallEditorBox = React.createClass({
  //Sets the initial state of the class
  getInitialState: function() {
    return {halls: [], floors: [], rooms: [], beds: [], hallId: 0, floorId: 0, roomId: 0, bedId: 0};
  },
  //Resets the states of all the lower level dropdowns and then gets the floors for the hall chosen
  chooseHall: function(hallIdToGet)
  {
    this.setState({hallId: hallIdToGet, floors: [], floorId: 0, rooms: [], roomId: 0, beds: [], bedId: 0});
    this.getFloors(hallIdToGet);
  },
  //Resets the states for the room and bed, so that the relevant components are updated, and then gets the rooms for the floor chosen
  chooseFloor: function(floorIdToGet)
  {
    this.setState({floorId: floorIdToGet, rooms: [], roomId: 0, beds: [], bedId: 0});
    this.getRooms(floorIdToGet);
  },
  //Resets the bed's state, so it can be passed ot the relevant components, and then gets the beds for the room chosen
  chooseRoom: function(roomIdToGet)
  {
    this.setState({roomId: roomIdToGet, beds: [], bedId: 0});
    this.getBeds(roomIdToGet);
  },
  //Sets the state of the bedId which is passed to the relevant components
  chooseBed: function(bedIdToSet)
  {
    this.setState({bedId: bedIdToSet});
  },
  //Grabs the appropriate data on initial mount
  componentWillMount: function()
  {
    this.getHalls();
  },
  //Takes care of the ajax request for getting all the active halls
  getHalls: function()
  {
    $.ajax({
      url: 'index.php?module=hms&action=AjaxGetHalls',
      type: 'GET',
      dataType: 'json',
      success: function(data)
      {
        this.setState({halls: data});
      }.bind(this),
      error: function(xhr, status, err)
      {
        alert(err.toString())
        console.error(this.props.url, status, err.toString());
      }.bind(this)
    });
  },
  //Takes care of the ajax request for getting all the halls with rooms on them
  getFloors: function(hallId)
  {
    $.ajax({
      url: 'index.php?module=hms&action=AjaxGetFloors&hallId='+hallId,
      type: 'GET',
      dataType: 'json',
      success: function(data)
      {
        this.setState({floors: data});
      }.bind(this),
      error: function(xhr, status, err)
      {
        alert(err.toString())
        console.error(this.props.url, status, err.toString());
      }.bind(this)
    });
  },
  //Takes care of the ajax request for getting all the rooms on a floor
  getRooms: function(floorId)
  {
    $.ajax({
      url: 'index.php?module=hms&action=AjaxGetRooms&floorId='+floorId,
      type: 'GET',
      dataType: 'json',
      success: function(data)
      {
        this.setState({rooms: data});
      }.bind(this),
      error: function(xhr, status, err)
      {
        alert(err.toString())
        console.error(this.props.url, status, err.toString());
      }.bind(this)
    });
  },
  //Takes care of the ajax request for retrieving the beds in a room+
  getBeds: function(roomId)
  {
    $.ajax({
      url: 'index.php?module=hms&action=AjaxGetBeds&roomId='+roomId,
      type: 'GET',
      dataType: 'json',
      success: function(data)
      {
        this.setState({beds: data});
      }.bind(this),
      error: function(xhr, status, err)
      {
        alert(err.toString())
        console.error(this.props.url, status, err.toString());
      }.bind(this)
    });
  },
  //Renders all the components and passes the appropriate state values to them
  render: function()
  {
    return (
      <div className="form-group col-md-6">
        <HallBox halls={this.state.halls} hallId={this.state.hallId} floorId={this.state.floorId} changed={this.chooseHall}/>
        <FloorBox floors={this.state.floors} floorId={this.state.floorId} roomId={this.state.roomId} changed={this.chooseFloor}/>
        <RoomBox rooms={this.state.rooms} roomId={this.state.roomId} bedId={this.state.bedId} changed={this.chooseRoom}/>
        <BedBox beds={this.state.beds} bedId={this.state.bedId} changed={this.chooseBed}/>
      </div>
    );
  }
});


//The react class responsible for taking care of the creation of the dropdown and button
//for the Halls
var HallBox = React.createClass({
  change: function() {
    var hallId = parseInt(this.refs.hallChoices.getDOMNode().value);
    this.props.changed(hallId);
  },
  render: function() {
    var options = Array({hall_id:0, hall_name: "Select..."});
    var data = this.props.halls;
    for(i = 0; i < data.length; i++)
    {
      options.push(data[i]);
    }
    var selectOptions = options.map(function(node){
        return (<option value={node.hall_id}>{node.hall_name}</option>);
    });
    return (
      <div className="form-group">
        <label>Residence Hall</label>
        <div className="row">
          <div className="col-md-8">
            <select onChange={this.change} className="form-control" ref="hallChoices">
              {selectOptions}
            </select>
          </div>
          <HallButton id={this.props.hallId} floorId={this.props.floorId} go={this.go}/>
        </div>
      </div>
    );
  }
});


//The react class responsible for taking care of the logic for creating the button
var HallButton = React.createClass({
  render: function(){
    if(this.props.id != 0 && !this.props.floorId)
    {
      var link = 'index.php?module=hms&action=EditResidenceHallView&hallId='+this.props.id;
      return(<div>
        <a href={link} className="btn btn-md btn-success">Edit Hall</a>
      </div>);
    }
    else {
      return(<div></div>);
    }
  }
});


//The react class responsible for taking care of the creation of the dropdown and button
//for the Floors
var FloorBox = React.createClass({
  change: function() {
    var floorId = this.refs.floorChoices.getDOMNode().value;
    this.props.changed(floorId);
  },
  render: function() {
    if(this.props.floors[0] == undefined)
    {
      return (<div></div>);
    }
    else
    {
      var options = Array({floor_id:0, floor_number: "Select..."});
      var data = this.props.floors;
      for(i = 0; i < data.length; i++)
      {
        options.push(data[i]);
      }
      var selectOptions = options.map(function(node){
          return (<option value={node.floor_id}>{node.floor_number}</option>);
      });
      return (
        <div className="form-group">
          <label>Floor</label>
          <div className="row">
            <div className="col-md-8">
              <select onChange={this.change} className="form-control" ref="floorChoices">
                {selectOptions}
              </select>
            </div>
            <FloorButton id={this.props.floorId} roomId={this.props.roomId}/>
          </div>
        </div>
      );
    }
  }
});


//The react class responsible for taking care of the logic for creating the button
var FloorButton = React.createClass({
  render: function(){
    if(this.props.id != 0 && !this.props.roomId)
    {
      var link = 'index.php?module=hms&action=EditFloorView&floor='+this.props.id;
      return(<div>
        <a href={link} className="btn btn-md btn-success">Edit Floor</a>
      </div>);
    }
    else {
      return(<div></div>);
    }
  }
});


//The react class responsible for taking care of the creation of the dropdown and button
//for the Rooms
var RoomBox = React.createClass({
  change: function() {
    var roomId = this.refs.roomChoices.getDOMNode().value;
    this.props.changed(roomId);
  },
  render: function() {
    if(this.props.rooms[0] == undefined)
    {
      return (<div></div>);
    }
    else
    {
      var options = Array({room_id:0, room_number: "Select..."});
      var data = this.props.rooms;
      for(i = 0; i < data.length; i++)
      {
        options.push(data[i]);
      }
      var selectOptions = options.map(function(node){
          return (<option value={node.room_id}>{node.room_number}</option>);
      });
      return (
        <div className="form-group">
          <label>Room</label>
          <div className="row">
            <div className="col-md-8">
              <select onChange={this.change} className="form-control" ref="roomChoices">
                {selectOptions}
              </select>
            </div>
            <RoomButton id={this.props.roomId} bedId={this.props.bedId}/>
          </div>
        </div>
      );
    }
  }
});


//The react class responsible for taking care of the logic for creating the button
var RoomButton = React.createClass({
  render: function(){
    if(this.props.id != 0 && !this.props.bedId)
    {
      var link = 'index.php?module=hms&action=EditRoomView&room='+this.props.id;
      return(<div>
        <a href={link} className="btn btn-md btn-success">Edit Room</a>
      </div>);
    }
    else {
      return(<div></div>);
    }
  }
});



//The react class responsible for taking care of the creation of the dropdown and button
//for the Beds
var BedBox = React.createClass({
  change: function() {
    var bedId = this.refs.bedChoices.getDOMNode().value;
    this.props.changed(bedId);
  },
  render: function() {
    if(this.props.beds[0] == undefined)
    {
      return (<div></div>);
    }
    else
    {
      var options = Array({bed_id:0, bed_letter: "Select..."});
      var data = this.props.beds;
      for(i = 0; i < data.length; i++)
      {
        options.push(data[i]);
      }
      var selectOptions = options.map(function(node){
          return (<option value={node.bed_id}>{node.bed_letter}</option>);
      });
      return (
        <div className="form-group">
          <label>Bed</label>
          <div className="row">
            <div className="col-md-8">
              <select onChange={this.change} className="form-control" ref="bedChoices">
                {selectOptions}
              </select>
            </div>
            <BedButton id={this.props.bedId}/>
          </div>
        </div>
      );
    }
  }
});


//The react class responsible for taking care of the logic for creating the button
var BedButton = React.createClass({
  render: function(){

    if(this.props.id != 0)
    {
      var link = 'index.php?module=hms&action=EditBedView&bed='+this.props.id;
      return(<div>
        <a href={link} className="btn btn-md btn-success">Edit Bed</a>
      </div>);
    }
    else {
      return(<div></div>);
    }
  }
});


//Inserts all the react components within the giving element.
React.render(
  <HallEditorBox/>,
  document.getElementById('HallPicker')
);
