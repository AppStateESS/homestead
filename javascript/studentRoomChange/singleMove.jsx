
// The class responsible for the logic involved in single person move to an open bed.
var SingleMoveBox = React.createClass({
  // Sets up an initial state for the class, with default values.
  getInitialState: function()
  {
    return ({hasError: false});
  },
  // Calls the parent class to set the first preference for the single person move room change
  addFirstPref: function(pref)
  {
    this.props.setFirst(pref);
  },
  // Calls the parent class to set the second preference for the single person move room change
  addSecondPref: function(pref)
  {
    this.props.setSecond(pref);
  },
  setError: function(status)
  {
    this.setState({hasError: status});
  },
  // The render function for the class
  render: function()
  {
    if(this.props.singleState == 1)
    {
      return(
        <div>
          <h4>Hall Preferences</h4>
          <div className="col-md-8">
            <FirstPrefBox secondPref={this.props.secondPref} change={this.addFirstPref} halls={this.props.halls}
              hasError={this.state.hasError}/>
            <SecondPrefBox firstPref={this.props.firstPref} change={this.addSecondPref} halls={this.props.halls}
              hasError={this.state.hasError}/>
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

// The class responsible for the dropdown where the students can select from the list of halls what their
// first preference is to room change to.
var FirstPrefBox = React.createClass({
  // Function responsible for ensuring the value is stored after a change to the dropdown, it passes
  // the value up to the parent class.
  change: function()
  {
    var hallPref = this.refs.hallChoices.getDOMNode().value;
    this.props.change(hallPref);
  },
  // The render function for the class
  render: function()
  {
    // Adds a dummy value to the front of the array that will allow us to know
    // whether the user has made a selection
    var options = Array({hall_id:0, hall_name: "Choose from below..."});
    var halls = this.props.halls;
    var secondPref = this.props.secondPref;

    for(i = 0; i < halls.length; i++)
    {
      options.push(halls[i]);
    }

    // All values in the array are mapped to their own option tag, with any that
    // have been selected in the other dropdown being disabled.
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

    var prefClasses = classNames({
                      "form-group" : true,
                      "has-error" : this.props.hasError
    });

    return(
      <div className="row">
        <div className="col-md-6">
          <div className={prefClasses}>
            <label className="control-label">First Preference</label>
            <select onChange={this.change} className="form-control" ref="hallChoices">
              {selectOptions}
            </select>
          </div>
        </div>
      </div>
    );
  }
});

// The class responsible for the dropdown where the students can select from the list of halls what their
// second preference is to room change to.
var SecondPrefBox = React.createClass({
  // Function responsible for ensuring the value is stored after a change to the dropdown, it passes
  // the value up to the parent class.
  change: function()
  {
    var hallPref = this.refs.hallChoices.getDOMNode().value;
    this.props.change(hallPref);
  },
  // The render function for the class
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

    var prefClasses = classNames({
                      "form-group" : true,
                      "has-error" : this.props.hasError
    });

    return(
      <div className="row">
        <div className="col-md-6">
          <div className={prefClasses}>
            <label className="control-label">Second Preference</label>
            <select onChange={this.change} className="form-control" ref="hallChoices">
              {selectOptions}
            </select>
          </div>
        </div>
      </div>
    );
  }
});
