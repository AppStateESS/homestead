var RlcPagerBox = React.createClass({
    getInitialState: function()
    {
        return ({rlcMembers: [], alert: {message: "", alert: ""}, sortedBy: "", sortOrder: ""});
    },
    componentWillMount: function()
    {
        this.getRlcMembers();
        this.sortMembers();
    },
    startSort: function(column)
    {
        if(this.state.sortedBy == column){
            if(this.state.sortOrder == "ASC"){
                this.setState({sortOrder: "DESC"});
            }else{
                this.setState({sortOrder: "ASC"});
            }

        }else{
            this.setState({sortedBy: column, sortOrder: "ASC"});
        }

        this.sortMembers();
    },

    sortMembers: function()
    {
        var members = this.state.rlcMembers;
        switch(this.state.sortedBy){
            case "gender":
                members.sort(function(a,b){
                    return a.gender.localeCompare(b.gender);
                });
                break;
            case "studentType":
                members.sort(function(a,b){
                    return a.studentType.localeCompare(b.studentType);
                });
                break;
            case "username":
                members.sort(function(a,b){
                    return a.username.localeCompare(b.username);
                });
                break;
            case "status":
                members.sort(function(a,b){
                    return a.status.localeCompare(b.status);
                });
                break;
            case "assignment":
                members.sort(function(a,b){
                    return a.assignment.localeCompare(b.assignment);
                });
                break;
            case "roommate":
                members.sort(function(a,b){
                    return a.roommate.localeCompare(b.roommate);
                });
                break;
            default:
                members.sort(function(a,b){
                    return a.name.localeCompare(b.name);
                });
        }

        this.setState({rlcMembers: members});

    },
    setStatus:function(newStatus, assignId)
    {
        var inputData = {status: newStatus, assignmentId: assignId};
        $.ajax({
            url: 'index.php?module=hms&action=AjaxSetRlcAssignmentStatus',
            type: 'POST',
            datatype: 'json',
            data: inputData,
            success: function(data)
            {
                var alertData = JSON.parse(data);
                this.setState({alert: alertData});
                this.getRlcMembers();
            }.bind(this),
            error: function(xhr, status, err)
            {
                alert("Failed to set status properly.")
                console.error(this.props.url, status, err.toString());
            }.bind(this)
        });
    },
    remove: function(assignId)
    {
        $.ajax({
            url: 'index.php?module=hms&action=RemoveRlcAssignment&assignmentId='+assignId,
            type: 'POST',
            success: function(data)
            {
                var alertData = JSON.parse(data);
                this.setState({alert: alertData});
                this.getRlcMembers();
            }.bind(this),
            error: function(xhr, status, err)
            {
                alert("Failed to remove assigned student.")
                console.error(this.props.url, status, err.toString());
            }.bind(this)
        });
    },
    removeDeny: function(assignId)
    {
        $.ajax({
            url: 'index.php?module=hms&action=RemoveDenyRlcAssignment&assignId='+assignId,
            type: 'POST',
            success: function(data)
            {
                var alertData = JSON.parse(data);
                this.setState({alert: alertData});
                this.getRlcMembers();
            }.bind(this),
            error: function(xhr, status, err)
            {
                alert("Failed to remove and deny assigned student.")
                console.error(this.props.url, status, err.toString());
            }.bind(this)
        });
    },
    getRlcMembers: function()
    {
        var inputData = {id: rlcId};
        $.ajax({
            url: 'index.php?module=hms&action=AjaxGetRLCMembers',
            type: 'GET',
            datatype: 'json',
            data: inputData,
            success: function(data) {
                var members = JSON.parse(data);
                this.setState({rlcMembers: members});
            }.bind(this),
            error: function(xhr, status, err) {
                alert("Failed to grab the members.")
                console.error(this.props.url, status, err.toString());
            }.bind(this)
        });
    },
    render: function()
    {
        var backUrl = "index.php?module=hms&action=ShowSearchByRlc"
        var addMembersUrl = "index.php?module=hms&action=ShowAdminAddRlcMember&communityId=" + rlcId;
        var exportUrl = "index.php?module=hms&action=CreateCsvByRlc&id="+rlcId
        return(
            <div>
                <AlertBox alert={this.state.alert}/>
                <a className="btn btn-default" href={backUrl}>
                    <i className="fa fa-chevron-left"></i> RLC List
                </a>
                <a className="btn btn-default pull-right" href={exportUrl}>
                    <i className="fa fa-file-excel-o"></i> Export to Spreadsheet
                </a>
                <h2>{rlcName} Assignments <small>{term}</small></h2>
                <a className="btn btn-success" href={addMembersUrl}>
                    <i className="fa fa-plus"></i> Add Member(s)
                </a>
                <ListBox rlcMembers={this.state.rlcMembers} remove={this.remove} removeDeny={this.removeDeny}
                    startSort={this.startSort} setStatus={this.setStatus}/>
            </div>
        );
    }
});

var AlertBox = React.createClass({
    render: function()
    {
        if(this.props.alert == undefined || this.props.alert.message == "") {
            return (<div></div>)
        } else {
            var alert;
            var success = false;
            var error = false;

            if(this.props.alert.type == "success") {
                var success = true;
            } else {
                var error = true;
            }

            var alertClass = classNames({
                'alert': true,
                'alert-success': success,
                'alert-danger': error
            });

            var faClass = classNames({
                'fa': true,
                'fa-2x': true,
                'fa-check': success,
                'fa-times': error
            });

            var alertSymbol = (<i className={faClass}></i>);

            return (
                <div className={alertClass} role="alert">
                    {alertSymbol} {this.props.alert.message}
                </div>
            );
        }
    }
});

var ListBox = React.createClass({
    sortGender: function()
    {
        this.props.startSort("gender");
    },
    sortStudentType: function()
    {
        this.props.startSort("studentType");
    },
    sortUsername: function()
    {
        this.props.startSort("username");
    },
    sortStatus: function()
    {
        this.props.startSort("status")
    },
    sortAssignment: function()
    {
        this.props.startSort("assignment")
    },
    sortRoommate: function()
    {
        this.props.startSort("roommate")
    },
    render: function()
    {
        if(this.props.rlcMembers.length == 0) {
            return (<div></div>)
        } else {
            var data = this.props.rlcMembers;
            var removeFunc = this.props.remove;
            var removeDenyFunc = this.props.removeDeny;
            var setStatusFunc = this.props.setStatus;
            var tableRows = data.map(function(node){
                return <ListRowBox node={node} remove={removeFunc} removeDeny={removeDenyFunc}
                    setStatus={setStatusFunc}/>
            });

            var genderSort = (<a onClick={this.sortGender} href="javascript:;"><i className="fa fa-sort"></i></a>);
            var studentTypeSort = (<a onClick={this.sortStudentType} href="javascript:;"><i className="fa fa-sort"></i></a>);
            var usernameSort = (<a onClick={this.sortUsername} href="javascript:;"><i className="fa fa-sort"></i></a>);
            var statusSort = (<a onClick={this.sortStatus} href="javascript:;"><i className="fa fa-sort"></i></a>);
            var assignmentSort = (<a onClick={this.sortAssignment} href="javascript:;"><i className="fa fa-sort"></i></a>);
            var roommateSort = (<a onClick={this.sortRoommate} href="javascript:;"><i className="fa fa-sort"></i></a>);

            return(
                <table className="table table-striped table-hover">
                    <thead>
                        <th>Name</th>
                        <th>Banner Id</th>
                        <th>Gender {genderSort}</th>
                        <th>Student Type {studentTypeSort}</th>
                        <th>Username {usernameSort}</th>
                        <th>Status {statusSort}</th>
                        <th>Assignment {assignmentSort}</th>
                        <th>Roommate {roommateSort}</th>
                        <th>Action</th>
                    </thead>
                    <tbody>
                        {tableRows}
                    </tbody>
                </table>);
        }
    }
});

var ListRowBox = React.createClass({
    remove: function()
    {
        this.props.remove(this.props.node.assignmentId);
    },
    removeDeny: function()
    {
        this.props.removeDeny(this.props.node.assignmentId);
    },
    setConfirm: function()
    {
        this.props.setStatus("confirmed", this.props.node.assignmentId);
    },
    setDecline: function()
    {
        this.props.setStatus("declined", this.props.node.assignmentId);
    },
    setNotInvited: function()
    {
        this.props.setStatus("new", this.props.node.assignmentId);
    },
    setPending: function()
    {
        this.props.setStatus("invited", this.props.node.assignmentId);
    },
    setSelfSelectAvail: function()
    {
        this.props.setStatus("selfselect-invite", this.props.node.assignmentId);
    },
    setSelfSelected: function()
    {
        this.props.setStatus("selfselect-assigned", this.props.node.assignmentId);
    },
    render: function()
    {
        var profileLink = "index.php?module=hms&action=ShowStudentProfile&username=" + this.props.node.username;
        var applicationLink = "index.php?module=hms&action=ShowRlcApplicationReView&appId=" + this.props.node.applicationId;
        var success = false;
        var danger = false;
        var muted = false;

        if(this.props.node.status == 'confirmed' || this.props.node.status == "self-selected") {
            success = true;
        } else if(this.props.node.status == 'declined') {
            danger = true;
        } else if(this.props.node.status == 'not invited' || this.props.node.status == 'pending' || this.props.node.status == 'self-select available') {
            muted = true;
        }

        var studentTypeFont = classNames({
            'text-success': success,
            'text-danger': danger,
            'text-muted': muted
        });

        return (<tr>
            <td><a href={profileLink}>{this.props.node.name}</a></td>
            <td>{this.props.node.bannerId}</td>
            <td>{this.props.node.gender}</td>
            <td>{this.props.node.studentType}</td>
            <td>{this.props.node.username}</td>
            <td>
                <div className="btn-group">
                    <button type="button" className="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <em className={studentTypeFont}>{this.props.node.status}</em>  <span className="caret"></span>
                    </button>
                    <ul className="dropdown-menu">
                        <li><a onClick={this.setConfirm} href="javascript:;">Confirmed</a></li>
                        <li><a onClick={this.setDecline} href="javascript:;">Declined</a></li>
                        <li><a onClick={this.setNotInvited} href="javascript:;">Not invited</a></li>
                        <li><a onClick={this.setPending} href="javascript:;">Pending</a></li>
                        <li><a onClick={this.setSelfSelectAvail} href="javascript:;">Self-Select Available</a></li>
                        <li><a onClick={this.setSelfSelected} href="javascript:;">Self-Selected</a></li>
                    </ul>
                </div>
            </td>
            <td>{this.props.node.assignment}</td>
            <td>{this.props.node.roommates}</td>
            <td>
                <div className="btn-group">
                    <button type="button" className="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i className="fa fa-gear"></i> <span className="caret"></span>
                    </button>
                    <ul className="dropdown-menu">
                        <li><a href={applicationLink}>View RLC Application</a></li>
                        <li><a onClick={this.remove} href="javascript:;">Remove RLC Assignment</a></li>
                        <li><a onClick={this.removeDeny} href="javascript:;">Remove and Deny </a></li>
                    </ul>
                </div>
            </td>
        </tr>);
    }
})


//Inserts all the react components within the giving element.
React.render(
  <RlcPagerBox/>,
  document.getElementById('rlcPager')
);
