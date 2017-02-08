//The top level component class that holds the state and handles the server requests.
var HallEditorBox = React.createClass({
  //Sets the initial state of the class
  getInitialState: function() {
    return {halls: [], floors: [], rooms: [], beds: [], hallId: 0, floorId: 0, roomId: 0, bedId: 0, meal_plan: "1"};
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
  // //Grabs the appropriate data on initial mount
  componentWillMount: function()
  {
    this.getHalls();
    this.setState({meal_plan: mealPlan.meal_plan});
    if(prepopulate.bed_id)
    {
      this.getFloors(prepopulate.hall_id);
      this.getRooms(prepopulate.floor_id);
      this.getBeds(prepopulate.room_id);
      this.setState({hallId: prepopulate.hall_id});
      this.setState({floorId: prepopulate.floor_id});
      this.setState({roomId: prepopulate.room_id});
      this.setState({bedId: prepopulate.bed_id});
    }
  },
  // //Takes care of the ajax request for getting all the active halls
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

      <div>
        <HallBox halls={this.state.halls} hallId={this.state.hallId} changed={this.chooseHall}/>
        <FloorBox floors={this.state.floors} floorId={this.state.floorId} changed={this.chooseFloor}/>
        <RoomBox rooms={this.state.rooms} roomId={this.state.roomId} changed={this.chooseRoom}/>
        <BedBox beds={this.state.beds} bedId={this.state.bedId} changed={this.chooseBed}/>
        <MealPlanBox mealPlan={this.state.meal_plan}/>
        <AssignTypeBox/>
      </div>

    );
  }
});

//The react class responsible for taking care of the creation of the dropdown
//for the Halls
var HallBox = React.createClass({
  change: function() {
    var hallId = parseInt(this.refs.hallChoices.getDOMNode().value);
    this.props.changed(hallId);
  },
  render: function() {
    var options = Array({hall_id:0, hall_name: "Select..."});
    var data = this.props.halls;
    var preSelectId = this.props.hallId;
    for(i = 0; i < data.length; i++)
    {
      options.push(data[i]);
    }
    var selectOptions = options.map(function(node){
      if(node.hall_id == preSelectId)
      {
        return (<option selected="selected" value={node.hall_id}>{node.hall_name}</option>)
      }
      else
      {
        return (<option value={node.hall_id}>{node.hall_name}</option>);
      }
    });
    return (
      <div className="form-group">
        <label id="phpws_form_residence_hall-label" class="select-label" for="phpws_form_residence_hall">
          Residence Hall
        </label>
        <select id="phpws_form_residence_hall" onChange={this.change} className="form-control" name="residence_hall" ref="hallChoices">
          {selectOptions}
        </select>
      </div>
    );
  }
});

//The react class responsible for taking care of the creation of the dropdown
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
      var preSelectId = this.props.floorId;
      for(i = 0; i < data.length; i++)
      {
        options.push(data[i]);
      }
      var selectOptions = options.map(function(node){
          if(node.floor_id == preSelectId)
          {
            return (<option selected="selected" value={node.floor_id}>{node.floor_number}</option>);
          }
          else
          {
            return (<option value={node.floor_id}>{node.floor_number}</option>);
          }
      });
      return (
        <div className="form-group">
          <label id="phpws_form_floor-label" class="select-label" for="form_form_floor">
            Floor
          </label>
          <select id="phpws_form_floor" onChange={this.change} className="form-control" name="floor" ref="floorChoices">
            {selectOptions}
          </select>
        </div>
      );
    }
  }
});

//The react class responsible for taking care of the creation of the dropdown
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
      var preSelectId = this.props.roomId;
      for(i = 0; i < data.length; i++)
      {
        options.push(data[i]);
      }
      var selectOptions = options.map(function(node){
        if(node.room_id == preSelectId)
        {
          return (<option selected="selected"value={node.room_id}>{node.room_number}</option>);
        }
        else
        {
          return (<option value={node.room_id}>{node.room_number}</option>);
        }

      });
      return (
        <div className="form-group">
          <label id="phpws_form_room-label" class="select-label" for="phpws_form_room">
            Room
          </label>
          <select id="phpws_form_room" onChange={this.change} className="form-control" name="room" ref="roomChoices">
            {selectOptions}
          </select>
        </div>
      );
    }
  }
});

//The react class responsible for taking care of the creation of the dropdown
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
      var preSelectId = this.props.bedId;
      for(i = 0; i < data.length; i++)
      {
        options.push(data[i]);
      }
      var selectOptions = options.map(function(node){
        if(node.bed_id == preSelectId)
        {
          return (<option selected="selected" value={node.bed_id}>{node.bed_letter}</option>);
        }
        else
        {
          return (<option value={node.bed_id}>{node.bed_letter}</option>);
        }

      });
      return (
        <div className="form-group">
          <label id="phpws_form_bed-label" class="select-label" for="phpws_form_bed">
            Bed
          </label>
          <select id="phpws_form_bed" onChange={this.change} className="form-control" name="bed" ref="bedChoices">
            {selectOptions}
          </select>
        </div>
      );
    }
  }
});


//The react class responsible for taking care of the creation of the dropdown
//for the meal plan
var MealPlanBox = React.createClass({
  render: function() {
    var options = Array({plan_id:"2", plan_option: "Low"}, {plan_id: "1", plan_option: "Standard"},
                        {plan_id: "0", plan_option: "High"}, {plan_id: "8", plan_option: "Super"},
                        {plan_id: "-1", plan_option: "None"}, {plan_id: "S5", plan_option: "Summer (5 weeks)"});
    var preSelectId = this.props.mealPlan;
    console.log(this.props.mealPlan)
    var selectOptions = options.map(function(node){
      if(node.plan_id == preSelectId)
      {
        return (<option selected="selected" value={node.plan_id}>{node.plan_option}</option>);
      }
      else
      {
        return (<option value={node.plan_id}>{node.plan_option}</option>);
      }
    });
    return (
      <div className="form-group">
          <label id="phpws_form_meal_plan-label" class="select-label" for="phpws_form_meal_plan">
            Meal Plan:
          </label>
          <select defaultValue={this.props.mealPlan} id="phpws_form_meal_plan" className="form-control" name="meal_plan" ref="mealChoices">
            {selectOptions}
          </select>
      </div>
    );
  }
});


//The react class responsible for taking care of the creation of the dropdown
//for the Assignment type
var AssignTypeBox = React.createClass({
  change: function() {
    var choice = this.refs.typeChoices.getDOMNode().value;
    this.props.changed(choice);
  },
  render: function() {
    return (
      <div className="form-group">
        <label id="phpws_form_assignment_type-label" class="select-label" for="phpws_form_assignment_type">Assignment Type:</label>
        <select id="phpws_form_assignment_type" onChange={this.change} className="form-control" name="assignment_type" ref="typeChoices">
              <option selected="selected" value="-1">Choose assignment type...</option>
              <option value="admin">Administrative</option>
              <option value="appeals">Appeals</option>
              <option value="lottery">Lottery</option>
              <option value="freshmen">Freshmen</option>
              <option value="transfer">Transfer</option>
              <option value="aph">APH</option>
              <option value="rlc_freshmen">RLC Freshmen</option>
              <option value="rlc_transfer">RLC Transfer</option>
              <option value="rlc_continuing">RLC Continuing</option>
              <option value="honors_freshmen">Honors Freshmen</option>
              <option value="honors_continuing">Honors Continuing</option>
              <option value="llc_freshmen">LLC Freshmen</option>
              <option value="llc_continuing">LLC Continuing</option>
              <option value="international">International</option>
              <option value="ra">RA</option>
              <option value="ra_roommate">RA Roommate</option>
              <option value="medical_freshmen">Medical Freshmen</option>
              <option value="medical_continuing">Medical Continuing</option>
              <option value="special_freshmen">Special Needs Freshmen</option>
              <option value="special_continuing">Special Needs Continuing</option>
              <option value="rha">RHA/NRHH</option>
              <option value="scholars">Diversity & Plemmons Scholars</option>
        </select>
      </div>
    );
  }
});


//Inserts all the react components within the giving element.
React.render(
  <HallEditorBox/>,
  document.getElementById('StudentAssigner')
);
