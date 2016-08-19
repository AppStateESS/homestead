var RoomChangeBox = React.createClass({
  getInitialState: function() {
    return {beds: [], rcData: []};
  },
  removeClick: function(bed)
  {
    this.deleteData(bed);
  },
  componentWillMount: function(){
    // Grabs the RoomChange data
    this.getData();
  },
  getData: function(){
    $.ajax({
      url: 'index.php?module=hms&action=RoomChangeRetrieveDetails&participantId='+partId,
      type: 'GET',
      dataType: 'json',
      success: function(data) {

        this.setState({rcData: data});
      }.bind(this),
      error: function(xhr, status, err) {
        alert(err.toString())
        console.error(this.props.url, status, err.toString());
      }.bind(this)
    });
  },
  render: function() {
    return (
      <div className="form-group">
        <InfoBox data={this.state.rcData}/>
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
    document.getElementById('destination')
  );
