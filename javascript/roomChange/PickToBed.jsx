var RoomChangeBox = React.createClass({
  getInitialState: function() {
    return {beds: [], rcData: []};
  },
  chooseBed: function(nameToAdd)
  {
    this.postData(nameToAdd);
  },
  removeClick: function(bed)
  {
    this.deleteData(bed);
  },
  componentWillMount: function(){
    // Grabs the RoomChange data
    this.getBeds();
    this.getData();
  },
  getBeds: function(){
    $.ajax({
      url: 'index.php?module=hms&action=RoomChangeListAvailableBeds&gender='+gender,
      type: 'GET',
      dataType: 'json',
      success: function(data) {
        this.setState({beds: data});
      }.bind(this),
      error: function(xhr, status, err) {
        alert(err.toString())
        console.error(this.props.url, status, err.toString());
      }.bind(this)
    });
  },
  getData: function(){
    $.ajax({
      url: 'index.php?module=hms&action=RoomChangeRetrieveDetails&participantId='+partId,
      type: 'GET',
      dataType: 'json',
      success: function(data) {
        console.log(data);
        this.setState({rcData: data});
      }.bind(this),
      error: function(xhr, status, err) {
        alert(err.toString())
        console.error(this.props.url, status, err.toString());
      }.bind(this)
    });
  },
  postData: function(bed){
    $.ajax({
      url: 'index.php?module=hms&action=RoomChangeSetToBed&participantId='+partId+'&bedId='+bed+'&oldBed='+oldBed,
      type: 'POST',
      dataType: 'text',
      success: function(){
        this.getData();
      }.bind(this),
      error: function(xhr, status, err){
        alert("An error occured while setting the bed "+ err.toString())
        console.error(this.props.url, status, err.toString());
      }.bind(this)
    });
  },
  render: function() {
    return (
      <div className="form-group">
        <InfoBox data={this.state.rcData}/>
        <RoomChangeDropdown onAdd={this.chooseBed} data={this.state.beds}/>
      </div>
    );
  }
  });

  var RoomChangeDropdown = React.createClass({
    add: function() {
      var bedToAdd = this.refs.bedChoices.getDOMNode().value;
      this.props.onAdd(bedToAdd);
    },
    render: function() {
      var options = Array({bedid:0, hall_name: "Select a New Room"});
      var data = this.props.data;
      for(i = 0; i < data.length; i++)
      {
        options.push(data[i]);
      }
      var selectOptions = options.map(function(node){
        if(node.id == 0)
        {
          return (<option value={node.bedid}>{node.hall_name}</option>)
        }
        else
        {
          return (<option value={node.bedid}>{node.hall_name} {node.room_number}</option>)
        }
      });
      return (
        <div className="form-group">
          <select className="form-control" ref="bedChoices">
            {selectOptions}
          </select>
          <p></p>
          <div className="button-group">
            <a onClick={this.add} className="btn btn-md btn-success">Set</a>
          </div>
        </div>
      );
    }
  });

  var InfoBox = React.createClass({
    render: function() {
      var data = this.props.data;
      if(data.to == "TBD")
      {
        toFrom = <div>
              <p><strong>From</strong> {data.from}</p>
              <p>A destination has not yet been chosen.</p>
            </div>;
      }
      else
      {
        toFrom = <p><strong>From</strong> {data.from} <strong>To</strong> {data.to}</p>;
      }
      console.log({toFrom})
      return (
        <div className="row">
          <div className="col-md-12">
              {toFrom}
          </div>
        </div>
      );
    }
  });

  React.render(
    <RoomChangeBox/>,
    document.getElementById('RoomPicker')
  );
