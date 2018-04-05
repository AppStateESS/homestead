import React from 'react';
import ReactDOM from 'react-dom';
import classNames from 'classnames';
import $ from 'jquery';

class RlcMembersList extends React.Component{
    constructor(props){
        super(props);

        this.state = {rlcMembers: [], alert: {message: "", alert: ""}, sortedBy: "", sortOrder: "", loadingMembers: true}
        this.componentWillMount = this.componentWillMount.bind(this);
        this.startSort = this.startSort.bind(this);
        this.sortMembers = this.sortMembers.bind(this);
        this.setStatus = this.setStatus.bind(this);
        this.remove = this.remove.bind(this);
        this.removeDeny = this.removeDeny.bind(this);
        this.getRlcMembers = this.getRlcMembers.bind(this);
    }
    componentWillMount(){
        this.getRlcMembers();
        this.sortMembers();
    }
    startSort(column){
        if(this.state.sortedBy === column){
            if(this.state.sortOrder === "ASC"){
                this.setState({sortOrder: "DESC"}); // TODO: These seem backwards. IF sortOrder is ASC, why are we setting state to DESC?
            }else{
                this.setState({sortOrder: "ASC"});
            }

        }else{
            this.setState({sortedBy: column, sortOrder: "ASC"});
        }

        this.sortMembers();
    }
    sortMembers(){
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
    }
    setStatus(newStatus, assignId){
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
    }
    remove(assignId){
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
    }
    removeDeny(assignId){
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
    }
    getRlcMembers(){
        var inputData = {id: this.props.rlcId};
        $.ajax({
            url: 'index.php?module=hms&action=AjaxGetRLCMembers',
            type: 'GET',
            datatype: 'json',
            data: inputData,
            success: function(data) {
                var members = JSON.parse(data);
                this.setState({rlcMembers: members, loadingMembers: false});
            }.bind(this),
            error: function(xhr, status, err) {
                alert("Failed to load the community members.")
                console.error(this.props.url, status, err.toString());
            }.bind(this)
        });
    }
    render(){
        var backUrl = "index.php?module=hms&action=ShowSearchByRlc"
        var addMembersUrl = "index.php?module=hms&action=ShowAdminAddRlcMember&communityId=" + this.props.rlcId;
        var exportUrl = "index.php?module=hms&action=CreateCsvByRlc&id=" + this.props.rlcId

        return(
            <div>
                <div className="row">
                    <div className="col-md-12">
                        <AlertBox alert={this.state.alert}/>
                    </div>
                </div>

                <div className="row">
                    <div className="col-md-12">
                        <a className="btn btn-default" href={backUrl}>
                            <i className="fa fa-chevron-left"></i> RLC List
                        </a>
                    </div>
                </div>

                <div className="row">
                    <div className="col-md-12">
                        <h2>{this.props.rlcName} Members <small>{this.props.term}</small></h2>
                        <a className="btn btn-default pull-right" href={exportUrl}>
                            <i className="fa fa-file-excel-o"></i> Export to Spreadsheet
                        </a>
                        <a className="btn btn-success pull-right" style={{marginRight: '2em'}} href={addMembersUrl}>
                            <i className="fa fa-plus"></i> Add Member(s)
                        </a>
                    </div>
                </div>

                <ListBox rlcMembers={this.state.rlcMembers} loadingMembers={this.state.loadingMembers} remove={this.remove} removeDeny={this.removeDeny}
                    startSort={this.startSort} setStatus={this.setStatus}/>
            </div>
        );
    }
}

class AlertBox extends React.Component{
    render(){
        if(this.props.alert === undefined || this.props.alert.message === "") {
            return (<div></div>)
        } else {
            var success = false;
            var error = false;

            if(this.props.alert.type === "success") {
                success = true;
            } else {
                error = true;
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
}

class ListBox extends React.Component{
    constructor(props){
        super(props);
        this.sortGender = this.sortGender.bind(this);
        this.sortStudentType = this.sortStudentType.bind(this);
        this.sortUsername = this.sortUsername.bind(this);
        this.sortStatus = this.sortStatus.bind(this);
        this.sortAssignment = this.sortAssignment.bind(this);
        this.sortRoommate = this.sortRoommate.bind(this);
    }
    sortGender(){
        this.props.startSort("gender");
    }
    sortStudentType(){
        this.props.startSort("studentType");
    }
    sortUsername(){
        this.props.startSort("username");
    }
    sortStatus(){
        this.props.startSort("status")
    }
    sortAssignment(){
        this.props.startSort("assignment")
    }
    sortRoommate(){
        this.props.startSort("roommate")
    }
    render(){
        if(this.props.loadingMembers){
            return (<div><p className="text-muted"><i className="fa fa-spinner fa-spin"></i> Loading Community Members...</p></div>)
        }else if (this.props.rlcMembers.length === 0) {
            return (<div><p className="text-muted">There are no members currently in this community.</p></div>)
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
}

class ListRowBox extends React.Component{
    constructor(props){
        super(props);
        this.remove = this.remove.bind(this);
        this.removeDeny = this.removeDeny.bind(this);
        this.setConfirm = this.setConfirm.bind(this);
        this.setDecline = this.setDecline.bind(this);
        this.setNotInvited = this.setNotInvited.bind(this);
        this.setPending = this.setPending.bind(this);
        this.setSelfSelectAvail = this.setSelfSelectAvail.bind(this);
    }
    remove(){
        this.props.remove(this.props.node.assignmentId);
    }
    removeDeny(){
        this.props.removeDeny(this.props.node.assignmentId);
    }
    setConfirm(){
        this.props.setStatus("confirmed", this.props.node.assignmentId);
    }
    setDecline(){
        this.props.setStatus("declined", this.props.node.assignmentId);
    }
    setNotInvited(){
        this.props.setStatus("new", this.props.node.assignmentId);
    }
    setPending(){
        this.props.setStatus("invited", this.props.node.assignmentId);
    }
    setSelfSelectAvail(){
        this.props.setStatus("selfselect-invite", this.props.node.assignmentId);
    }
    setSelfSelected(){
        this.props.setStatus("selfselect-assigned", this.props.node.assignmentId);
    }
    render(){
        var profileLink = "index.php?module=hms&action=ShowStudentProfile&username=" + this.props.node.username;
        var applicationLink = "index.php?module=hms&action=ShowRlcApplicationReView&appId=" + this.props.node.applicationId;
        var success = false;
        var danger = false;
        var muted = false;

        if(this.props.node.status === 'confirmed' || this.props.node.status === "self-selected") {
            success = true;
        } else if(this.props.node.status === 'declined') {
            danger = true;
        } else if(this.props.node.status === 'not invited' || this.props.node.status === 'pending' || this.props.node.status === 'self-select available') {
            muted = true;
        }

        var studentTypeFont = classNames({
            'text-success': success,
            'text-danger': danger,
            'text-muted': muted
        });

        // Just use the first roommate
        var roommate = ''
        if(Array.isArray(this.props.node.roommates) && this.props.node.roommates.length > 0){
            roommate = <a href={this.props.node.roommates[0].profileUri}>{this.props.node.roommates[0].name}</a>
        }

        return (
            <tr>
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
                            <li><a className="dropdown-item" onClick={this.setConfirm} href="javascript:;">Confirmed</a></li>
                            <li><a className="dropdown-item" onClick={this.setDecline} href="javascript:;">Declined</a></li>
                            <li><a className="dropdown-item" onClick={this.setNotInvited} href="javascript:;">Not invited</a></li>
                            <li><a className="dropdown-item" onClick={this.setPending} href="javascript:;">Pending</a></li>
                            <li><a className="dropdown-item" onClick={this.setSelfSelectAvail} href="javascript:;">Self-Select Available</a></li>
                            <li><a className="dropdown-item" onClick={this.setSelfSelected} href="javascript:;">Self-Selected</a></li>
                        </ul>
                    </div>
                </td>
                <td>{this.props.node.assignment}</td>
                <td>{roommate}</td>
                <td>
                    <div className="btn-group">
                        <button type="button" className="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i className="fa fa-gear"></i> <span className="caret"></span>
                        </button>
                        <ul className="dropdown-menu">
                            <li><a className="dropdown-item" href={applicationLink}>View RLC Application</a></li>
                            <li><a className="dropdown-item" onClick={this.remove} href="javascript:;">Remove Membership</a></li>
                            <li><a className="dropdown-item" onClick={this.removeDeny} href="javascript:;">Remove &amp; Deny Application</a></li>
                        </ul>
                    </div>
                </td>
            </tr>);
    }
}


//Inserts all the react components within the giving element.
ReactDOM.render(<RlcMembersList rlcId={window.rlcId} rlcName={window.rlcName} term={window.term}/>, document.getElementById('rlcMembers'));
