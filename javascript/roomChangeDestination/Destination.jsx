var StaticRoomChangeBox = React.createClass({
    getInitialState: function() {
        return {beds: [], rcData: []};
    },
    componentWillMount: function(){
        // Grabs the RoomChange data
        this.getData();
    },
    getData: function(){
        $.ajax({
            url: 'index.php?module=hms&action=RoomChangeGetDetails&participantId='+partId,
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
                <StaticInfoBox data={this.state.rcData}/>
            </div>
        );
    }
});

var StaticInfoBox = React.createClass({
    render: function() {
        var data = this.props.data;
        
        if(data.to == "TBD"){
            toFrom = <div>
                <p><strong>From</strong> {data.fromBed}</p>
                <p>A destination has not yet been chosen.</p>
            </div>;
        } else {
            toFrom = <p><strong>From</strong> {data.fromBed} <strong>To</strong> {data.toBed}</p>;
        }

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
    <StaticRoomChangeBox/>,
    document.getElementById('destination')
);
