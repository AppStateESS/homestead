
// The class responsible for handling the logic involved in a displaying the swap panel
// and making sure that the values of a swap are correctly loaded.
var SwapBox = React.createClass({
  // Sets up an initial state for the class, with default values.
  getInitialState: function()
  {
    return {participants: [], rooms: [], maxParticipants: 4, currParticipants: 0,
      changeState: 0, confirmData: [], error: undefined};
  },
  // Takes care of initialization when the componen mounts
  componentWillMount: function()
  {
    this.getCurrentUser();
  },
  // Takes care of the logic involved in adding a new userId
  addUser: function(newUserId)
  {
    var users = this.state.participants;
    var different = true;
    for(i = 0; i < users.length; i++)
    {
      if(users[i].username == newUserId)
      {
        different = false;
      }
    }
    if(different)
    {
      this.getInfo(newUserId);
      var numParticipants = this.state.currParticipants;
      numParticipants = numParticipants + 1;
      this.setState({currParticipants: numParticipants});
      this.refs.newUserBox.setError(false);
      this.setState({error: undefined});
    }
    else
    {
      var error= {error: "duplicate_value", location: "newUser", message: "That username was already in the list of swap participants."};
      this.handleError(error);
    }
  },
  //Changes the changeState field in order to move between the views
  forwardState: function()
  {
    var users = this.state.participants;
    if(users.length > 1)
    {
      var state = this.state.changeState + 1;
      this.setState({changeState: state});
      if(state == 1)
      {
        this.populateRooms();
        this.props.setUsers(users);
      }
      this.setState({error: undefined});
    }
    else
    {
      var error= {error: "missing_value", location: "newUser", message: "You need to add at least one other student."};
      this.handleError(error);
    }
  },
  // Changes the changeState field in order to move through the views
  stateBack: function()
  {
    var state = this.state.changeState - 1;
    this.setState({changeState: state});
    this.setState({error: undefined});
  },
  // Calls to the parent class to pass the values of a username and bed to be linked
  // in the parent class fields.
  setUserToBed: function(username, toBed)
  {
    this.props.setUserToBed(username, toBed);
  },
  // Retrieves the info of the current user
  getCurrentUser: function()
  {
    var currUser = currentUser;
    this.getInfo(currUser);
  },
  // Retrieves the rooms that each participant by looping through the participants
  // array and calls the getRoom method to retrieve the room for each participant.
  populateRooms: function()
  {
    var users = this.state.participants;
    var rooms = Array();
    this.setState({rooms: rooms});
    for(i = 0; i < users.length; i++)
    {
      this.getRoom(users[i].username);
    }
  },
  // A function that checks to make suer that all the rooms selected are valid and
  // that no room is being picked twice.
  confirmRooms: function()
  {
    var data = this.props.data;
    var check = true;
    // Loops through the bedIds checking to make sure all of them are set
    for(i = 0; i < data.length; i++)
    {
      var bedId = data[i].bedId;
      if(check && (bedId == '0' || bedId == undefined))
      {
        var error= {error: "missing_value", location: "roomSelection", message: "You have not completed the selection of all rooms."};
        this.handleError(error);
        check = false;
      }
    }
    // Loops through the bedIds checking to make sure none are repeated.
    for(i = 0; i < data.length - 1; i++)
    {
      var bedId = data[i].bedId;
      for(j = i + 1; j < data.length; j++)
      {
        if (check && bedId == data[j].bedId)
        {
          var error= {error: "duplicate_value", location: "roomSelection", message: "You have two students going to the same place, please correct this before continuing."};
          this.handleError(error);
          check = false;
        }
      }
    }
    if(check)
    {
      this.setConfirmData(data);
      this.props.toggleComplete();
      this.forwardState();
    }
  },
  // Sets up an array to hold the data for confirming
  setConfirmData: function(data)
  {
    var confirmD = Array();
    var participants = this.state.participants;
    var rooms = this.state.rooms;
    for(i = 0; i < data.length; i++)
    {
      var username = data[i].user;
      var bedId = data[i].bedId;
      var nameData = "unknown";
      var roomData = "unknown";
      var fromBedId = 0
      var fromRoomData = "unknown";
      for(j = 0; j < participants.length; j++)
      {
        if(participants[j].username == username)
        {
          nameData = participants[j].name;
          fromBedId = participants[j].currentBedId;
        }
      }
      for(j = 0; j < participants.length; j++)
      {
        if(rooms[j].bedId == fromBedId)
        {
          fromRoomData = rooms[j].location;
        }
        if(rooms[j].bedId == bedId)
        {
          roomData = rooms[j].location;
        }
      }
      confNode = {name: nameData, room: roomData, fromRoom: fromRoomData};
      confirmD.push(confNode);
    }
    this.setState({confirmData: confirmD});
  },
  // Handles submissions that cause error cases by creating an alert box to display the error message.
  handleError: function(data)
  {
    if(data.location == "newUser")
    {
      this.refs.newUserBox.setError(true);
    }
    if(data.location == "roomSelection")
    {
      this.refs.roomSelectionBox.setError(true);
    }
    this.setState({error: data});
  },
  // A function for making the ajax call to the AjaxGetBedByUsernameCommand, upon
  // successfully making the call the data would then be pushed onto the beds array
  getRoom: function(userId)
  {
    $.ajax({
      url: 'index.php?module=hms&action=AjaxGetBedByUsername&username='+userId,
      type: 'GET',
      dataType: 'json',
      success: function(data)
      {
        var beds = this.state.rooms;
        beds.push(data);
        this.setState({rooms: beds});
      }.bind(this),
      error: function(xhr, status, err)
      {
        this.handleError(JSON.parse(xhr.responseText));
      }.bind(this)
    });
  },
  // A function for making the ajax call to the AjaxGetInfoByUsernameCommand, upon
  // successfully making the call the data would then be push onto the beds array.
  getInfo: function(userId)
  {
    $.ajax({
      url: 'index.php?module=hms&action=AjaxGetInfoByUsername&username='+userId,
      type: 'GET',
      dataType: 'json',
      success: function(data)
      {
        var users = this.state.participants;
        users.push(data);
        this.setState({participants: users});
      }.bind(this),
      error: function(xhr, status, err)
      {
        this.handleError(JSON.parse(xhr.responseText));
      }.bind(this)
    });
  },
  // The render function for the class
  render: function()
  {
    if(this.props.singleState != 2)
    {
      return(<div></div>);
    }
    else {
      return(
          <div className="panel panel-default">
            <div className="panel-body">
              <ErrorBox error={this.state.error}/>
              <ListBox users={this.state.participants} changeState={this.state.changeState} forwardState={this.forwardState}/>
              <RoomSelectionBox changeState={this.state.changeState} users={this.state.participants} rooms={this.state.rooms} change={this.setUserToBed} ref="roomSelectionBox"/>
              <ConfirmBox changeState={this.state.changeState} data={this.state.confirmData}/>
              <NewUserBox addUser={this.addUser} currPart={this.state.currParticipants} maxPart={this.state.maxParticipants} changeState={this.state.changeState} ref="newUserBox"/>
              <ButtonBox forward={this.forwardState} back={this.stateBack} confirmRooms={this.confirmRooms} changeState={this.state.changeState}
                toggleComplete={this.props.toggleComplete}/>
            </div>
          </div>
          );
    }
  }
});

// The class responsible for listing the usernames and names of the participants
// that have been entered so far.
var ListBox = React.createClass({
  // Advances the state forward by making a call to the parent class.
  forwardState: function()
  {
    this.props.forwardState();
  },
  // The class' render function.
  render: function()
  {
    if(this.props.changeState != 0)
    {
      return (
        <div></div>
      );
    }
    else
    {
      var data = this.props.users;
      var currentUser = data[0];
      var otherUsers = Array();
      for(i = 1; i < data.length; i++)
      {
        otherUsers.push(data[i]);
      }
      var userBoxes = otherUsers.map(function(node){
        return (<OtherUserBox  user={node}/>)
      });
      return (
        <div>
          <h4><strong>Step 1:</strong> Add people who want to move</h4>
          <CurrentUserBox user={currentUser}/>
          {userBoxes}
        </div>
      );
    }
  }
});

// Class responsible for the creation and logic of all the Swap panel's buttons.
var ButtonBox = React.createClass({
  // A function that takes care of making function calls to verify the rooms and
  // advancing the state.
  stepForward: function()
  {
    if(this.props.changeState == 1)
    {
      this.props.confirmRooms();
    }
    else
    {
      this.props.forward();
    }
  },
  // A function that takes care of making function calls to move the state backward,
  // and if the complete flag is set reseting it.
  stepBack: function()
  {
    this.props.back();
    if(this.props.changeState == 2)
    {
        this.props.toggleComplete();
    }
  },
  // This classes render function
  render: function()
  {
    var backButton;
    var forwardButton;
    var topMargin = {marginTop: '15px'};
    var style;
    var click;
    var text;

    if(this.props.changeState == 1)
    {
      style = "col-md-2 col-md-offset-7";
      click = this.confirm;
      text = "Review Choices ";
    }

    if(this.props.changeState == 0)
    {
      style = "col-md-2 col-md-offset-10";
      click = this.stepForward;
      text = "Choose Swap Locations ";
    }

    if(this.props.changeState == 0 || this.props.changeState == 1)
    {
      forwardButton = (
            <div className={style}>
              <a onClick={this.stepForward} style={topMargin} className="btn btn-primary pull-right">{text}<i className="fa fa-chevron-right"></i></a>
            </div>
        );
    }

    if(this.props.changeState)
    {
      backButton = (
            <a onClick={this.stepBack} style={topMargin} className="btn btn-default"><i className="fa fa-chevron-left"></i> Go Back and Make Changes</a>
      );
    }

    return(
      <div className="row">
        <div className="col-md-3">
          {backButton}
        </div>
        {forwardButton}
      </div>
    );
  }
});

// Class responsible for displaying the information about the current user and listing
// it at the top of the list of participants to be involved in the room change.
var CurrentUserBox = React.createClass({
  // This class render function
  render: function()
  {
    if(this.props.user  != undefined)
    {
      var padding = {paddingTop: '5px'};
      return(
        <div className="row">
          <div className="col-md-4">
            <div className="input-group">
            <input className="form-control" placeholder={this.props.user.username} disabled></input>
            <span className="input-group-addon">@appstate.edu</span>
            </div>
          </div>
          <label style={padding}>&nbsp;{this.props.user.name}</label>
        </div>
      );
    }
    else
    {
      return(
      <div></div>
      );
    }
  }
});

// Class responsible for displaying the information about each user and adding it
// to the list of participants to be involved in the room change.
var OtherUserBox = React.createClass({
  // A function for removing the participant from the list, makes a call to the
  // parent class
  remove: function()
  {
    var userId = this.refs.userName.getDOMNode().value;
    if (userId != '')
    {
      this.props.change(this.props.value, userId);
    }
  },
  // The class' render function
  render: function()
  {
    var padding = {paddingTop: '5px'};
    return (
      <div className="row">
          <div className="col-md-4">
            <div className="input-group">
              <input className="form-control" onBlur={this.userChange} placeholder={this.props.user.username} disabled ref="userName"></input>
              <span className="input-group-addon">@appstate.edu</span>
            </div>
          </div>
          <label style={padding}>&nbsp;{this.props.user.name}</label>
      </div>
    );
  }
});

// The class responsible for providing an input box and button to add students to
// the room change.
var NewUserBox = React.createClass({
  // Sets up an initial state for the class, with default values.
  getInitialState: function() {
    return ({hasError: false});
  },
  // Retrieves the value via reference from the input and makes a call to the parent
  // function passing the value up.
  userAdd: function()
  {
    var userId = this.refs.userName.getDOMNode().value;
    if(userId != '')
    {
      this.props.addUser(userId);
      this.refs.userName.getDOMNode().value = '';
    }
  },
  // Sets the error field to the value passed to the function.
  setError: function(status)
  {
    this.setState({hasError: status});
  },
  // The class' render function
  render: function()
  {
    if(this.props.changeState == 0 && this.props.currPart < this.props.maxPart)
    {
      var topMargin = {marginTop: '10px'};

      var newUserClasses = classNames({
                            'input-group': true,
                            'has-error': this.state.hasError
                          });

      return (
        <div>
          <label style={topMargin}>Add Another Student</label>
          <div className="row">
          <div className="col-md-4">
            <div className={newUserClasses}>
              <input className="form-control" placeholder="Username" ref="userName"></input>
              <span className="input-group-addon">@appstate.edu</span>
            </div>
          </div>
          <div className="col-md-3">
            <a onClick={this.userAdd} className="btn btn-success"><i className="fa fa-plus"></i> Add Student</a>
          </div>
        </div>
        </div>
      );
    }
    else
    {
      return (
        <div></div>
      );
    }
  }
});

// The class responsible for generating dropdowns for each participant to select
// the room they desire to move to.
var RoomSelectionBox = React.createClass({
  // Sets up an initial state for the class, with default values.
  getInitialState: function()
  {
    return ({hasError: false});
  },
  // A function that passes the call from a changed room box up to its parent class
  change: function(username, bedId)
  {
    this.props.change(username, bedId);
  },
  // Sets the error field to the value passed to the function.
  setError: function(status)
  {
    this.setState({hasError: status});
  },
  // The class' render function
  render: function()
  {
    if(this.props.changeState == 1)
    {
      var users = this.props.users;
      var rooms = this.props.rooms;
      var change = this.change;
      var error = this.state.hasError;
      var roomBoxes = users.map(function(node){
        return (<RoomBox hasError={error} change={change} user={node} rooms={rooms}/>)
      });
      return (
          <div>
            <h4><strong>Step 2:</strong> Room Selection</h4>
            {roomBoxes}
          </div>
      );
    }
    else
    {
      return (
        <div></div>
      );
    }
  }
});

// The class responsible for creating dropdowns with the choices that a user can
// make for a participant to move to, these choices do not include the participant's
// current room.
var RoomBox = React.createClass({
  // Retrieves the value via reference from the input and makes a call to the parent
  // function passing the value up.
  change: function()
  {
    var bedId = this.refs.roomChoices.getDOMNode().value;
    this.props.change(this.props.user.username, bedId);
  },
  // This class' render function
  render: function()
  {
    var options = Array({bedId: -1, location: "Select a room for this participant"});
    var data = this.props.rooms;
    for(i = 0; i < data.length; i++)
    {
      options.push(data[i]);
    }
    var user = this.props.user;

    var selectOptions = options.map(function(node)
    {
      if(user.currentBedId != node.bedId)
      {
        return(<option value={node.bedId}>{node.location}</option>);
      }
    });

    var roomClasses = classNames({
      'input-group': true,
      'has-error': this.props.hasError
    });

    return (
      <div className="row">
        <div className="col-md-4">
          <div className={roomClasses}>
            <label className="control-label">{this.props.user.name}</label>
            <select onChange={this.change} className="form-control" ref="roomChoices">
              {selectOptions}
            </select>
          </div>
        </div>
      </div>
    );
  }
});

// The class responsible for the final state of the swap panel, displays a list
// of the participants, where they are currently located and where they intend
// to move.
var ConfirmBox = React.createClass({
  render: function()
  {
    if(this.props.changeState >= 2)
    {
      var data = this.props.data;

      var listItems = data.map(function(node)
      {
          return(<ConfirmItemBox nodeData={node}/>);
      });
      return(
        <div>
          <h4><strong>Step 3:</strong> Review Room Swaps</h4>
          {listItems}
        </div>
      );
    }
    else
    {
      return(
        <div></div>
      );
    }
  }
});

// The class responsible for each list item of the ConfirmBox puts each of the
// important items inside a strong tag to draw the user's eye to the important details.
var ConfirmItemBox = React.createClass({
  render: function()
  {
    return (
      <div>
        <p><strong>{this.props.nodeData.name}</strong> intends to move from <strong>{this.props.nodeData.fromRoom}</strong> to <strong>{this.props.nodeData.room}</strong></p>
      </div>
    );
  }
});
