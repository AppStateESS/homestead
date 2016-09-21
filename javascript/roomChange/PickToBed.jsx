var RoomChangeDestination = React.createClass({
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
            url: 'index.php?module=hms&action=RoomChangeGetDetails&participantId='+partId,
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
                <InfoBox fromBed={this.state.rcData.fromBed} toBed={this.state.rcData.toBed} availableBeds={this.state.beds}/>
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
        var options = Array({bedid: 0, hall_name: "Select a New Room"});
        var data = this.props.data;
        for(i = 0; i < data.length; i++) {
            options.push(data[i]);
        }
        var selectOptions = options.map(function(node){
            if(node.id == 0){
                return (<option key={node.bedid} value={node.bedid}>{node.hall_name}</option>)
            } else {
                return (<option key={node.bedid} value={node.bedid}>{node.hall_name} {node.room_number}</option>)
            }
        });
        return (
            <div className="row">
                <div className="col-md-6">
                    <div className="form-group">
                        <select className="form-control" ref="bedChoices">
                            {selectOptions}
                        </select>
                    </div>
                </div>
            </div>
        );
    }
});

var InfoBox = React.createClass({
    getInitialState: function(){
        return {showDropdown: false};
    },
    handleClickChangeButton: function() {
        this.setState({showDropdown: true});
    },
    render: function() {
        if(this.props.toBed == null) {
            toLocation = "To Be Selected";
        } else {
            toLocation = this.props.toBed;
        }

        if(this.state.showDropdown){
            var dropdown = <RoomChangeDropdown onAdd={this.props.chooseBed} data={this.props.availableBeds}/>;
            var changeButton = '';
            var destination = '';
        } else {
            var destination = toLocation;
            var changeButton = <button className="btn btn-default btn-xs" onClick={this.handleClickChangeButton}><i className="fa fa-bed"></i> Change Destination</button>;
        }

        return (
            <div className="row">
                <div className="col-md-12">
                    <p>
                        <strong>From</strong> {this.props.toBed} <strong>To</strong> {destination} {changeButton}
                    </p>
                </div>
                {dropdown}
            </div>
        );
    }
});

React.render(
    <RoomChangeDestination/>,
    document.getElementById('RoomPicker')
);
