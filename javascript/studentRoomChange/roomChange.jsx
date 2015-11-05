//The top level component class that holds the state and handles the server requests.
var RoomChangeBox = React.createClass({
  // Sets up an intial state for the class, with default values.
  getInitialState: function()
  {
    return {whereState: 0, phoneNumber: "", phoneToggle: false, data: [], reason: "",
      status: false, firstPref: 0, secondPref: 0, halls: [], firstPref: 0, secondPref: 0,
      swapComplete: false, error: undefined};
  },
  // When the class will successfully mount, retrieves data it needs from the server to start.
  componentWillMount: function()
  {
    this.getHalls();
  },
  // Sets the state field to single move(1) and resets the preferences.
  setSingleMove: function()
  {
    this.setState({whereState: 1, firstPref: 0, secondPref: 0});
  },
  // Sets the state to swap(2)
  setSwap: function()
  {
    this.setState({whereState: 2});
  },
  // Sets the phone number to the given value.
  setPhoneNum: function(phone)
  {
    this.setState({phoneNumber: phone});
  },
  // Does a simple check on the phone number
  // TODO needs improvement
  validPhone: function()
  {
    var phone = this.state.phoneNumber;
    return !isNaN(phone);
  },
  // Toggles the phoneToggle value
  togglePhoneCheck: function()
  {
    var toggle = !this.state.phoneToggle;
    this.setState({phoneToggle: toggle});
  },
  // Sets the text for the reason field.
  addText: function(text)
  {
    this.setState({reason: text});
  },
  // Sets the user array to the values passed from the lower component
  setUsers: function(users)
  {
    var dataArr = new Array();
    for(i = 0; i < users.length; i++)
    {
      dataArr.push({user: users[i].username, bedId: 0});
    }
    this.setState({data: dataArr});
  },
  // Sets the beds array with the values according to the location of the user in the users
  // array, this keeps the users and beds linked.
  setUserToBed: function(username, toBed)
  {
    var data = this.state.data;
    for(i = 0; i < data.length; i++)
    {
      var d = data[i];
      if(d.user == username)
      {
        d.bedId = toBed;
      }
    }
  },
  // Toggles the swapComplete field
  toggleSwapComplete: function()
  {
    var swapC = !this.state.swapComplete;
    this.setState({swapComplete: swapC});
  },
  // Sets the firstPref field to the given value
  setFirstPref: function(pref)
  {
    this.setState({firstPref: pref});
  },
  // Sets the secondPref field to the given value
  setSecondPref: function(pref)
  {
    this.setState({secondPref: pref});
  },
  // Useful function for testing, can be removed if you like.
  logInfo: function()
  {
    console.log(this.state.phoneNumber)
    console.log(this.state.reason)
    console.log(this.state.whereState)
    console.log(this.state.data)
    console.log(this.state.firstPref)
    console.log(this.state.secondPref)
    console.log(this.state.swapComplete)
  },
  // Takes care of the logic for various checks to make sure the data is ready to be
  // submitted, popping up alerts if the data is incomplete in some way.
  submit: function()
  {
    // this.logInfo();
    if(!this.state.phoneToggle && !this.validPhone())
    {
      alert("Please enter your cell phone number or check the box saying you dont want to provide your cell phone number.")
    }
    else if(this.state.reason.length == 0)
    {
      alert("Please provide an explanation for why you want this room change.")
    }
    else
    {
      if(this.state.whereState == 0)
      {
        alert("You must choose how you would like to move, to an open bed or swap with someone.")
      }
      else if(this.state.whereState == 1)
      {
        if(!this.state.firstPref || !this.state.secondPref)
        {
          alert("Please indicate your hall preferences by selecting from the First and Second Choice dropdowns.")
        }
        else
        {
          this.postRoomChange("switch");
        }
      }
      else
      {
        if(!this.state.swapComplete)
        {
          alert("You have not completed the swap process, please complete the steps and review your choices.")
        }
        else
        {
          this.postRoomChange("swap");
        }
      }
    }
  },
  //
  handleInvalid: function(data)
  {
    window.location.assign(data.url);
  },
  //
  handleError: function(data)
  {
    this.setState({error: data.error})
  },
  //
  handleSuccess: function(data)
  {
    window.location.assign(data.url);
  },
  // Performs an Ajax request to get a list of the halls that is used to populate the
  // dropdown for the preferences.
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
  // Performs an Ajax request to post all the values to the server to make the room change.
  postRoomChange: function(typeVal)
  {
    var dataArr = {type: typeVal, phoneNumber: this.state.phoneNumber, cellOptOut: this.state.phoneToggle,
      reason: this.state.reason, userToBed: this.state.data, firstChoice: this.state.firstPref,
      secondChoice: this.state.secondPref};

    $.ajax({
      url: 'index.php?module=hms&action=SubmitRoomChangeRequest',
      type: 'POST',
      dataType: 'json',
      data: dataArr,
      success: function(data)
      {
        console.log(data)
        if(data.status == "invalid")
        {
          this.handleInvalid(data);
        }
        else if(data.status == "success")
        {
          this.handleSuccess(data);
        }
        else
        {
          console.log("Bug, some weird corner case? Call the dev")
        }
      }.bind(this),
      error: function(xhr, status, err)
      {
        this.handleError(JSON.parse(xhr.responseText));
      }.bind(this)
    });
  },
  // Render function
  render: function()
  {
    return(
        <div>
          <ErrorBox error={this.state.error}/>
          <PhoneBox phoneChange={this.setPhoneNum} checkChange={this.togglePhoneCheck} />
          <WhereBox singleMove={this.setSingleMove} swap={this.setSwap} singleState={this.state.whereState} setUsers={this.setUsers}
            setUserToBed={this.setUserToBed} halls={this.state.halls} setFirst={this.setFirstPref} setSecond={this.setSecondPref}
            firstPref={this.state.firstPref} secondPref={this.state.secondPref} data={this.state.data} toggleComplete={this.toggleSwapComplete}/>
          <NotesBox change={this.addText} />
          <SubmitBox click={this.submit} />
        </div>
    );
  }
});

var ErrorBox = React.createClass({
  render: function()
  {
    if(this.props.error == undefined)
    {
      return(
        <div></div>
      );
    }
    else
    {

      return(
        <div className="alert alert-danger" role="alert">
          <i className="fa fa-times fa-2x"></i>  {this.props.error.message}
        </div>
      )
    }
  }
});

// The class responsible for the logic and rendering of the phone input box and opt out checkbox.
var PhoneBox = React.createClass({
  // A function that handles changes to the phone input.
  inputChange: function()
  {
    var phoneNum = this.refs.phoneInput.getDOMNode().value;
    this.props.phoneChange(phoneNum);
  },
  // A function that toggles the value of the opt in when the checkbox is clicked.
  checkChange: function()
  {
    this.props.checkChange();
  },
  // Rendering function
  render: function()
  {
    return(
      <div>
        <h3>Contact Info</h3>
        <p>Your RD and the Assignments Office will use this extra contact information (in addition to your ASU email address) to reach you in case there is a question regarding your request.</p>
        <div className="row">
          <div className="col-md-4">
            <label>
              Cell phone Number
            </label>
            <input onChange={this.inputChange} className="form-control" type="text" ref="phoneInput"></input>
            <div className="checkbox">
              <label>
                <input onChange={this.checkChange} type="checkbox" ref="check"></input>
                <em className="text-muted">
                  I don't want to provide a cellphone number
                </em>
              </label>
            </div>
          </div>
        </div>
      </div>
    );
  }
});

var WhereBox = React.createClass({
  // Function for calling to the parent class' singleMove function
  singleMove: function()
  {
    this.props.singleMove();
  },
  // Calls to the parent's class' swap function
  swap: function()
  {
    this.props.swap();
  },
  // Calls to the parent class' setUsers function, passing the users array up
  setUsers: function(users)
  {
    this.props.setUsers(users)
  },
  // Calls to the parent class' setUserToBed function, passing the username and bed id to set
  setUserToBed: function(username, toBed)
  {
    this.props.setUserToBed(username, toBed);
  },
  // Calls to the parent class' setFirst function, passing the hall id to set as the first preference
  setFirst: function(pref)
  {
    this.props.setFirst(pref);
  },
  // Calls to the parent class' setSecond function, passing the hall id to set as the second preference
  setSecond: function(pref)
  {
    this.props.setSecond(pref);
  },
  render: function()
  {
    return(
      <div className="row">
        <div className="col-md-12">
          <h3>Where to?</h3>
          <div className="radio">
            <h4>
              <label>
                <input type="radio" name="swapOptions" onChange={this.singleMove}></input>
                I want to change to an open bed.
              </label>
            </h4>
          </div>

          <div className="row">
            <div className="col-md-8">
              <SingleMoveBox singleState={this.props.singleState} halls={this.props.halls} setFirst={this.setFirst}
                setSecond={this.setSecond} firstPref={this.props.firstPref} secondPref={this.props.secondPref}/>
            </div>
          </div>

          <div className="radio">
            <h4>
              <label>
                <input type="radio" name="swapOptions" onChange={this.swap}></input>
                I want to swap beds with someone I know.
              </label>
            </h4>
          </div>

          <div className="col-md-10">
            <SwapBox singleState={this.props.singleState} setUsers={this.setUsers} setUserToBed={this.setUserToBed} data={this.props.data} toggleComplete={this.props.toggleComplete}/>
          </div>
        </div>
      </div>
    );
  }
});

var NotesBox = React.createClass({
  change: function()
  {
    var reason = this.refs.reasonInput.getDOMNode().value;
    this.props.change(reason);
  },
  render: function()
  {
    return(
      <div>
        <h3>Reason</h3>
        <p>Please provide a short explanation of why you would like to move to a different room. A few sentences are sufficient. You should also indicate any special circumstances (i.e. you want to switch rooms with a friend on your floor).</p>
        <div className="row">
          <div className="col-md-4">
            <textarea onBlur={this.change} className="form-control" rows="5" cols="40" ref="reasonInput"></textarea>
          </div>
        </div>
      </div>
    );
  }
});

var SubmitBox = React.createClass({
  click: function()
  {
    this.props.click();
  },
  render: function()
  {
    return(
      <div>
        <p></p>
        <a onClick={this.click} className="btn btn-lg btn-success">Submit Request</a>
      </div>
    );
  }
})

var SingleMoveBox = React.createClass({
  addFirstPref: function(pref)
  {
    this.props.setFirst(pref);
  },
  addSecondPref: function(pref)
  {
    this.props.setSecond(pref);
  },
  render: function()
  {
    if(this.props.singleState == 1)
    {
      return(
        <div>
          <h4>Hall Preferences</h4>
          <div className="col-md-8">
            <FirstPrefBox secondPref={this.props.secondPref} change={this.addFirstPref} halls={this.props.halls}/>
            <SecondPrefBox firstPref={this.props.firstPref} change={this.addSecondPref} halls={this.props.halls}/>
          </div>
        </div>
      );
    }
    else
    {
      return(<div></div>);
    }
  }
});

var FirstPrefBox = React.createClass({
  change: function()
  {
    var hallPref = this.refs.hallChoices.getDOMNode().value;
    this.props.change(hallPref);
  },
  render: function()
  {
    var options = Array({hall_id:0, hall_name: "Choose from below..."});
    var halls = this.props.halls;
    var secondPref = this.props.secondPref;
    for(i = 0; i < halls.length; i++)
    {
      options.push(halls[i]);
    }
    var selectOptions = options.map(function(node){
      if(node.hall_id == 0)
      {
        return (<option value={node.hall_id}>{node.hall_name}</option>);
      }
      else if(node.hall_id == secondPref)
      {
        return(<option disabled value={node.hall_id}>{node.hall_name}</option>);
      }
      else
      {
        return (<option value={node.hall_id}>{node.hall_name}</option>);
      }
    });
    return(
      <div className="row">
        <div className="col-md-6">
          <label>First Preference</label>
          <select onChange={this.change} className="form-control" ref="hallChoices">
            {selectOptions}
          </select>
        </div>
      </div>
    );
  }
});

var SecondPrefBox = React.createClass({
  change: function()
  {
    var hallPref = this.refs.hallChoices.getDOMNode().value;
    this.props.change(hallPref);
  },
  render: function()
  {
    var options = Array({hall_id:0, hall_name: "Choose from below..."});
    var halls = this.props.halls;
    var firstPref = this.props.firstPref;
    for(i = 0; i < halls.length; i++)
    {
      options.push(halls[i]);
    }
    var selectOptions = options.map(function(node){
      if(node.hall_id == 0)
      {
        return (<option value={node.hall_id}>{node.hall_name}</option>);
      }
      else if(node.hall_id == firstPref)
      {
        return(<option disabled value={node.hall_id}>{node.hall_name}</option>);
      }
      else
      {
        return (<option value={node.hall_id}>{node.hall_name}</option>);
      }
    });
    return(
      <div className="row">
        <div className="col-md-6">
          <label>Second Preference</label>
          <select onChange={this.change} className="form-control" ref="hallChoices">
            {selectOptions}
          </select>
        </div>
      </div>
    );
  }
});

var SwapBox = React.createClass({
  //Sets the initial state of the class
  getInitialState: function()
  {
    return {participants: [], rooms: [], maxParticipants: 4, currParticipants: 0, changeState: 0, confirmData: []};
  },
  //Takes care of initialization when the componen mounts
  componentWillMount: function()
  {
    this.getCurrentUser();
  },
  //
  changeUser: function(oldValue, newValue)
  {
    this.state.participants[this.state.participants.indexOf(oldValue)] = newValue;
  },
  //Takes care of the logic involved in adding a new userId
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
    }
    else
    {
      alert("That username was already in the list of swap participants.")
    }
  },
  //Changes the changeState field in order to toggle between the two views
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
    }
    else
    {
      alert("You need to add at least one other student.");
    }
  },
  //
  stateBack: function()
  {
    var state = this.state.changeState - 1;
    this.setState({changeState: state});
  },
  //
  setUserToBed: function(username, toBed)
  {
    this.props.setUserToBed(username, toBed);
  },
  //
  getCurrentUser: function()
  {
    var currUser = currentUser;
    this.getInfo(currUser);
  },

  //
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
  //
  confirmRooms: function()
  {
    var data = this.props.data;
    var check = true;
    for(i = 0; i < data.length; i++)
    {
      var bedId = data[i].bedId;
      if(check && (bedId == '0' || bedId == undefined))
      {
        alert("You have not completed the selection of all rooms.");
        check = false;
      }
    }
    for(i = 0; i < data.length - 1; i++)
    {
      var bedId = data[i].bedId;
      for(j = i + 1; j < data.length; j++)
      {
        if (check && bedId == data[j].bedId)
        {
          alert("You have two students going to the same place, please correct this before continuing.");
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
  //
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
  //
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
        alert("An error occurred while getting the room for " + userId)
        console.error(this.props.url, status, err.toString());
      }
    });
  },
  //
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
        alert("Invalid User")
        console.error(this.props.url, status, err.toString());
      }
    });
  },
  //
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
              <ListBox change={this.changeUser}  users={this.state.participants} changeState={this.state.changeState} forwardState={this.forwardState}/>
              <RoomSelectionBox changeState={this.state.changeState} users={this.state.participants} rooms={this.state.rooms} change={this.setUserToBed} />
              <ConfirmBox changeState={this.state.changeState} data={this.state.confirmData}/>
              <AddBox addUser={this.addUser} currPart={this.state.currParticipants} maxPart={this.state.maxParticipants} changeState={this.state.changeState}/>
              <ButtonBox forward={this.forwardState} back={this.stateBack} confirmRooms={this.confirmRooms} changeState={this.state.changeState}
                toggleComplete={this.props.toggleComplete}/>
            </div>
          </div>
          );
    }
  }
});




var ListBox = React.createClass({
  changeUser: function(oldValue, newValue)
  {
    this.props.change(oldValue, newValue);
  },
  forwardState: function()
  {
    this.props.forwardState();
  },
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
        return (<OtherUserBox change={this.changeUser} user={node}/>)
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


var AddBox = React.createClass({
  addUser: function(userName)
  {
    this.props.addUser(userName);
  },
  render: function(){
      return(
          <NewUserBox addUser={this.addUser} currPart={this.props.currPart} maxPart={this.props.maxPart} changeState={this.props.changeState}/>
      );
  }
});



var ButtonBox = React.createClass({
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
  stepBack: function()
  {
    this.props.back();
    if(this.props.changeState == 2)
    {
        this.props.toggleComplete();
    }
  },
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

var CurrentUserBox = React.createClass({
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

var OtherUserBox = React.createClass({
  remove: function()
  {
    var userId = this.refs.userName.getDOMNode().value;
    if (userId != '')
    {
      this.props.change(this.props.value, userId);
    }
  },
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

var NewUserBox = React.createClass({
  userAdd: function()
  {
    var userId = this.refs.userName.getDOMNode().value;
    if(userId != '')
    {
      this.props.addUser(userId);
      this.refs.userName.getDOMNode().value = '';
    }
  },
  render: function()
  {
    if(this.props.changeState == 0 && this.props.currPart < this.props.maxPart)
    {
      var topMargin = {marginTop: '10px'};
      return (
        <div>
          <label style={topMargin}>Add Another Student</label>
          <div className="row">
          <div className="col-md-4">
            <div className="input-group">
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

var RoomSelectionBox = React.createClass({
  change: function(username, bedId)
  {
    this.props.change(username, bedId);
  },
  render: function()
  {
    if(this.props.changeState == 1)
    {
      var users = this.props.users;
      var rooms = this.props.rooms;
      var change = this.change;
      var roomBoxes = users.map(function(node){
        return (<RoomBox change={change} user={node} rooms={rooms}/>)
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


var RoomBox = React.createClass({
  change: function()
  {
    var bedId = this.refs.roomChoices.getDOMNode().value;
    this.props.change(this.props.user.username, bedId);
  },
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
    return (
      <div className="row">
        <div className="col-md-4">
          <label>{this.props.user.name}</label>
          <select onChange={this.change} className="form-control" ref="roomChoices">
            {selectOptions}
          </select>
        </div>
      </div>
    );
  }
});

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



React.render(
  <RoomChangeBox/>,
  document.getElementById('RoomChange')
);
