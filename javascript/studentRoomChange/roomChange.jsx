//The top level component class that holds the state and handles the server requests.
var RoomChangeBox = React.createClass({
  // Sets up an initial state for the class, with default values.
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
  // Sets the state field to single move(1) and resets the preferences, resets any errors
  // related to the radio being unselected when submit is clicked.
  setSingleMove: function()
  {
    this.setState({whereState: 1, firstPref: 0, secondPref: 0});
    if(this.state.error != undefined && this.state.error.location == "where")
    {
        this.setState({error: undefined});
        this.refs.whereBox.setWhereError(false);
    }
  },
  // Sets the state to swap(2), resets any errors
  // related to the radio being unselected when submit is clicked.
  setSwap: function()
  {
    this.setState({whereState: 2});
    if(this.state.error != undefined && this.state.error.location == "where")
    {
        this.setState({error: undefined});
        this.refs.whereBox.setWhereError(false);
    }
  },
  // Sets the phone number to the given value, resets any errors
  // related to the phone input being empty when submit is clicked.
  setPhoneNum: function(phone)
  {
    this.setState({phoneNumber: phone});
    if(this.state.error != undefined && this.state.error.location == "phone")
    {
      this.refs.phoneBox.setError(false);
      this.setState({error: undefined});
    }
  },
  // Does a simple check on the phone number
  // TODO needs improvement
  invalidPhone: function()
  {
    var phone = this.state.phoneNumber;
    if(phone.length == 0)
    {
      return true;
    }
    return isNaN(phone);
  },
  // Toggles the phoneToggle value, resets any errors
  // related to the phone input being empty when submit is clicked.
  togglePhoneCheck: function()
  {
    var toggle = !this.state.phoneToggle;
    this.setState({phoneToggle: toggle});
    if(this.state.error != undefined && this.state.error.location == "phone")
    {
      this.refs.phoneBox.setError(false);
      this.setState({error: undefined});
    }
  },
  // Sets the text for the reason field.
  addText: function(text)
  {
    this.setState({reason: text});
    if(this.state.error != undefined && this.state.error.location == "reason")
    {
      this.refs.reasonBox.setError(false);
      this.setState({error: undefined});
    }
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
  // Takes care of the logic for various checks to make sure the data is ready to be
  // submitted, popping up alerts if the data is incomplete in some way.
  submit: function()
  {
    console.log(!this.state.phoneToggle)
    console.log(!this.invalidPhone())
    // Check to see if the phone is given or if they have checked the box saying
    // they dont want to provide their phone number.
    if(!this.state.phoneToggle && this.invalidPhone())
    {
      var error= {error: "missing_value", location: "phone", message: "Please enter your cell phone number or check the box saying you dont want to provide your cell phone number."};
      this.handleError(error);
    }
    // Check to make sure the text area for the reason is not empty.
    else if(this.state.reason.length == 0)
    {
      var error= {error: "missing_value", location: "reason", message: "Please provide an explanation for why you want this room change."};
      this.handleError(error);
    }
    // The static components are all in order check the dynamic parts.
    else
    {
      // State still at 0, means they have not yet selected where they want to go.
      if(this.state.whereState == 0)
      {
        var error= {error: "missing_value", location: "where", message: "You must choose how you would like to move, to an open bed or swap with someone."};
        this.handleError(error);
      }
      // State of 1, a single move has been selected.
      else if(this.state.whereState == 1)
      {
        // Check that both preferences have been set.
        if(!this.state.firstPref || !this.state.secondPref)
        {
          var error= {error: "missing_value", location: "preferences", message: "Please indicate your hall preferences by selecting from the First and Second Choice dropdowns."};
          this.handleError(error);
        }
        else
        {
          this.postRoomChange("switch");
        }
      }
      // State of 2, indicates a swap has been selected.
      else
      {
        // Check to make sure the swapComplete flag, indicating the state had
        // progressed to the review state, has been set before making a post to
        // the server
        if(!this.state.swapComplete)
        {
          var error= {error: "missing_value", message: "You have not completed the swap process, please complete the steps and review your choices."};
          this.handleError(error);
        }
        else
        {
          this.postRoomChange("swap");
        }
      }
    }
  },
  // Handles invalid submissions by redirecting to the menu where an error will occur
  handleInvalid: function(data)
  {
    window.location.assign(data.url);
  },
  // Handles submissions that cause error cases by creating an alert box to display the error message.
  handleError: function(data)
  {
    if(data.location == "phone")
    {
      this.refs.phoneBox.setError(true);
    }
    if(data.location == "reason")
    {
      this.refs.reasonBox.setError(true);
    }
    if(data.location == "preferences")
    {
      this.refs.whereBox.setPrefError(true);
    }
    if(data.location == "where")
    {
      this.refs.whereBox.setWhereError(true);
    }
    this.setState({error: data})
  },
  // Handles the successful submissions after post by sending the user to the menu
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
        if(data.status == "invalid")
        {
          this.handleInvalid(data);
        }
        else if(data.status == "success")
        {
          this.handleSuccess(data);
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
          <PhoneBox phoneChange={this.setPhoneNum} checkChange={this.togglePhoneCheck} ref="phoneBox"/>
          <WhereBox singleMove={this.setSingleMove} swap={this.setSwap} singleState={this.state.whereState} setUsers={this.setUsers}
            setUserToBed={this.setUserToBed} halls={this.state.halls} setFirst={this.setFirstPref} setSecond={this.setSecondPref}
            firstPref={this.state.firstPref} secondPref={this.state.secondPref} data={this.state.data} toggleComplete={this.toggleSwapComplete}
            ref="whereBox"/>
          <ReasonBox change={this.addText} ref="reasonBox"/>
          <SubmitBox click={this.submit} />
        </div>
    );
  }
});

// The class responsible for displaying error messages for failed submissions.
var ErrorBox = React.createClass({
  // Sets up an initial state for the class, with default values.
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
      console.log(this.props.error)
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
  // Sets up an initial state for the class, with default values.
  getInitialState: function()
  {
    return ({hasError: false});
  },
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
  //
  setError: function(status)
  {
    this.setState({hasError: status});
  },
  // Rendering function
  render: function()
  {
    var phoneInputClasses = classNames({
                            'form-group': true,
                            'has-error': this.state.hasError
                          });

    var phoneCheckClasses = classNames({
                            'has-error': this.state.hasError
    });

    return(
      <div>
        <h3>Contact Info</h3>
        <p>Your RD and the Assignments Office will use this extra contact information (in addition to your ASU email address) to reach you in case there is a question regarding your request.</p>
        <div className="row">
          <div className="col-md-4">
            <div className={phoneInputClasses}>
              <label className="control-label">
                Cell Phone Number
              </label>
              <input onChange={this.inputChange} className="form-control" type="text" ref="phoneInput"></input>
            </div>
            <div className={phoneCheckClasses}>
              <div className="checkbox">
                <label>
                  <input onChange={this.checkChange} type="checkbox" ref="check"></input>
                  <em className="text-muted">
                    I don't want to provide a cell phone number
                  </em>
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
});

// The class responsible for the logic involved in selecting whether the user
// desires a swap or a move to an open bed.  It also contains the components and logic
// responsible for the details of these moves
var WhereBox = React.createClass({
  // Sets up an initial state for the class, with default values.
  getInitialState: function()
  {
    return ({hasError: false});
  },
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
    this.props.setUsers(users);
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
  //
  setPrefError: function(status)
  {
    this.refs.singleMove.setError(status);
  },
  setWhereError: function(status)
  {
    this.setState({hasError: status});
  },
  // The render function for the class
  render: function()
  {
    var whereClasses = classNames({
                      'form-group': true,
                      'has-error': this.state.hasError
    });

    return(
      <div className="row">
        <div className="col-md-12">
          <h3>Where to?</h3>
          <div className={whereClasses}>
            <div className="radio">
              <h4>
                <label className="control-label">
                  <input type="radio" name="swapOptions" onChange={this.singleMove}></input>
                  I want to change to an open bed.
                </label>
              </h4>
            </div>
          </div>

          <div className="row">
            <div className="col-md-8">
              <SingleMoveBox singleState={this.props.singleState} halls={this.props.halls} setFirst={this.setFirst}
                setSecond={this.setSecond} firstPref={this.props.firstPref} secondPref={this.props.secondPref}
                ref="singleMove"/>
            </div>
          </div>

          <div className={whereClasses}>
            <div className="radio">
              <h4>
                <label className="control-label">
                  <input type="radio" name="swapOptions" onChange={this.swap}></input>
                  I want to swap beds with someone I know.
                </label>
              </h4>
            </div>
          </div>

          <div className="col-md-10">
            <SwapBox singleState={this.props.singleState} setUsers={this.setUsers} setUserToBed={this.setUserToBed} data={this.props.data} toggleComplete={this.props.toggleComplete}/>
          </div>
        </div>
      </div>
    );
  }
});

// The class responsible for the text box where the student is supposed to give a reason for the room change.
var ReasonBox = React.createClass({
  // Sets up an initial state for the class, with default values.
  getInitialState: function()
  {
    return ({hasError: false});
  },
  // Function responsible for ensuring the value is stored after a change to the textarea, it passes
  // the value up to the parent class.
  change: function()
  {
    var reason = this.refs.reasonInput.getDOMNode().value;
    this.props.change(reason);
  },
  //
  setError: function(status)
  {
    this.setState({hasError: status});
  },
  // The render function for the class
  render: function()
  {
    var reasonClasses = classNames({
                        "form-group": true,
                        "has-error": this.state.hasError
    });

    return(
      <div>
        <h3>Reason</h3>
        <p>Please provide a short explanation of why you would like to move to a different room. A few sentences are sufficient. You should also indicate any special circumstances (i.e. you want to switch rooms with a friend on your floor).</p>
        <div className="row">
          <div className="col-md-4">
            <div className={reasonClasses}>
              <textarea onChange={this.change} className="form-control" rows="5" cols="40" ref="reasonInput"></textarea>
            </div>
          </div>
        </div>
      </div>
    );
  }
});

// The class responsible for the submit button
var SubmitBox = React.createClass({
  // Function responsible for informing the parent class that the button has been clicked.
  click: function()
  {
    this.props.click();
  },
  // The render function for the class
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




// The main render that creates the react components of the page.
React.render(
  <RoomChangeBox/>,
  document.getElementById('RoomChange')
);
