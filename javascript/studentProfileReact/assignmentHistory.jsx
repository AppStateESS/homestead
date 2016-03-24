
var HistoryBox = React.createClass({
    getInitialState: function()
    {
        return ({history: [], current: []});
    },
    componentWillMount: function()
    {
        this.getHistory();
    },
    getHistory: function()
    {
        var inputData = {banner_id: bannerId};
        $.ajax({
            url         : 'index.php?module=hms&action=AjaxGetAssignmentHistory',
            type        : 'GET',
            datatype    : 'json',
            data        : inputData,
            success: function (data) {
                var historyData = JSON.parse(data);

                this.setState({
                                    history     : historyData.history,
                                    current     : historyData.current
                });
            }.bind(this),
            error: function() {

            }.bind(this)
        });
    },
    render: function()
    {
        var rows;
        var emptyMessage;
        var data = this.state.history;
        if(data.length != 0)
        {
            var refreshHistory = this.getHistory;
            var currentAssignment = this.state.current;
            rows = data.map(function(node){
                return (
                    <TableRow data={node} bannerId={bannerId} refresh={refreshHistory} current={currentAssignment}/>
                );
            });
        }
        else
        {
            emptyMessage = (
                <p>No Assignments found</p>
            );
        }
        return(
            <div>
                <table className="table table-striped">
                        <tbody>
                            <tr>
                                <th>Room</th>
                                <th>Term</th>
                                <th>Assignment</th>
                                <th>Unassignment</th>
                            </tr>
                            {rows}
                        </tbody>
                </table>
                {emptyMessage}
            </div>
        )
    }
});

var TableRow = React.createClass({
    getInitialState: function()
    {
        return ({editing: false});
    },
    editAssignment: function()
    {
        this.setState({
                            editing : true
        });
    },
    cancel: function()
    {
        this.setState({
                            editing : false
        });
    },
    save: function(newReason)
    {
        var inputData = {reason: newReason, bannerId: this.props.bannerId, term: this.props.current.term};
        $.ajax({
            url: 'index.php?module=hms&action=AjaxEditAssignmentHistory',
            type: 'POST',
            dataType: 'json',
            data: inputData,
            success: function(data){
                this.props.refresh();
                this.setState({
                                    editing: false
                });
            }.bind(this),
            error: function(xhr, status, err){

            }.bind(this)
        });
    },
    render: function()
    {
        var dateStyle = {
                            fontSize    : '11px',
                            color       : '#7C7C7C'
        };
        var noneStyle = {color: '#7C7C7C'}

        var assignment;
        var unassignment;

        if(this.props.data.assignedReason == null)
        {
            assignment = (
                <td>
                    <em style={noneStyle}>None</em>
                </td>
            );
        }
        else if(this.state.editing)
        {
            assignment = (
                <AssignmentSelect id={this.props.data.id}
                                  cancel={this.cancel}
                                  save={this.save}/>
            );
        }
        else
        {
            var edit = '';
            if(this.props.current.id == this.props.data.id)
            {
                edit = (
                    <button className="btn btn-xs btn-default" onClick={this.editAssignment} href="javascript:;"><i className="fa fa-pencil"></i> Edit Reason</button>
                );
            }
            assignment = (
                <td>
                    <em>{this.props.data.assignedReason}</em> by {this.props.data.assignedBy} {edit}
                    <br></br>
                    <span style={dateStyle}>on {this.props.data.assignedOn}</span>
                </td>
            );
        }

        if(this.props.data.removedReason == null)
        {
            unassignment = (
                <td>
                    <em style={noneStyle}>None</em>
                </td>
            );
        }
        else
        {
            unassignment = (
                <td key={this.props.data.id}>
                    <em>{this.props.data.removedReason}</em> by {this.props.data.removedBy}
                    <br></br>
                    <span style={dateStyle}>on {this.props.data.removedOn}</span>
                </td>
            );
        }
        return(
            <tr>
                <td>{this.props.data.room}</td>
                <td>{this.props.data.term}</td>
                {assignment}
                {unassignment}
            </tr>
        );
    }
});

var AssignmentSelect = React.createClass({
    cancel: function()
    {
        this.props.cancel();
    },
    save: function()
    {
        var reason = this.refs.assignmentDropdown.getDOMNode().value;
        if(reason != '-1')
        {
            this.props.save(reason);
        }
    },
    render: function()
    {
        return (
            <td>
                <select ref="assignmentDropdown">
                    <option value='-1'>Choose a new reason...</option>
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
                <button onClick={this.save} class="btn btn-sm">Save</button>
                <button onClick={this.cancel} class="btn btn-sm">Cancel</button>
            </td>
        )
    }
});



React.render(
    <HistoryBox/>,
    document.getElementById('assignmentHistory')
);
