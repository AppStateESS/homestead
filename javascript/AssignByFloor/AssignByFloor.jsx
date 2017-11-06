import React from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import PropTypes from 'prop-types';

class AssignByFloor extends React.Component{

    constructor(props, context) {
        super(props, context);

        this.state = {
            hallList: [],
            assignmentOptions: [],
            currentAssignmentType: 0
        };

        this.updateAssignmentType = this.updateAssignmentType.bind(this);
        this.componentWillMount = this.componentWillMount.bind(this);
    }

    updateAssignmentType(value) {
        this.setState({
            currentAssignmentType: value
        });
    }

    componentWillMount() {
        var hallList = [];

        $.getJSON('index.php', {
            module: 'hms',
            action: 'JSONGetHalls'
        }, function (data) {
                hallList = data;

            $.getJSON('index.php', {
                module: 'hms',
                action: 'JSONGetOptions'
            }, function(data){
                this.setState({
                    hallList: hallList,
                    assignmentOptions: data.assignment_type,
                    currentAssignmentType: data.default_assignment
                });

            }.bind(this));
        }.bind(this));
    }

    render() {
        return (
            <div>
                <Options {...this.state} updateAssignmentType={this.updateAssignmentType} />
                <Halls hallList={this.state.hallList}  assignmentType={this.state.currentAssignmentType} />
            </div>
        );
    }
}

class Options extends React.Component{
    constructor(props){
        super(props);

        this.state = {currentAssignmentType: 0};

        this.componentWillReceiveProps = this.componentWillReceiveProps.bind(this);
        this.updateAssignmentType = this.updateAssignmentType.bind(this);
    }

    componentWillReceiveProps(nextProps) {
        this.setState({
            currentAssignmentType: nextProps.currentAssignmentType
        });
    }

    updateAssignmentType(event) {
        this.props.updateAssignmentType(event.target.value);
        this.setState({
            currentAssignmentType: event.target.value
        });
    }

    render() {
        return (
            <div className="panel panel-primary">
                <div className="panel-heading">
                    <h3 className="panel-title">Assignment settings</h3>
                </div>
                <div className="row panel-body">
                    <div className="col-sm-6 form-group">
                        <label htmlFor="assignmentType">Assignment&nbsp;type:</label>
                        <DropSelect options={this.props.assignmentOptions}  selectId='assignmentType' default={this.state.currentAssignmentType} ref="assignmentType" onChange={this.updateAssignmentType} />
                    </div>
                </div>
            </div>
        );
    }
}

class DropSelect extends React.Component{
    render() {
        return (
            <select className="form-control" id={this.props.selectId} value={this.props.default} onChange={this.props.onChange}>
                {this.props.options.map(function(value, i){
                    return <option key={i} value={value.id}>{value.value}</option>;
                })}
            </select>
        );
    }
}

DropSelect.defaultProps = {
        options: [],
        selectId: null,
        default: null
};

class Halls extends React.Component{
    constructor(props){
        super(props);

        this.state =  {
            hallName: 'Choose a hall',
            selected: false,
            icon: 'fa-building-o',
            floors: [],
            floorDisabled: true,
            timestamp: Date.now()
        };
        this.loadFloors = this.loadFloors.bind(this);
        this.updateHall = this.updateHall.bind(this);
    }
    loadFloors(hallId) {
        //this.getInitialState();
        $.getJSON('index.php', {
            module: 'hms',
            action: 'JSONGetFloors',
            hallId: hallId
        }, function (data) {
            if (data) {
                this.setState({
                    floors: data,
                    floorDisabled: false
                });
            }
        }.bind(this));
    }
    updateHall(index) {
        this.setState({
            hallName: this.props.hallList[index].title,
            selected: true,
            timestamp: Date.now()
        });
        this.loadFloors(this.props.hallList[index].id);
    }
    render() {
        return (
            <div>
                <DropDown floorList={this.state.floorList} icon={this.state.icon} listing={this.props.hallList} onClick={this.updateHall} selected={this.state.selected} title={this.state.hallName}/>
                <Floors key={this.state.timestamp} floorDisabled={this.state.floorDisabled} floorList={this.state.floors} assignmentType={this.props.assignmentType} />
            </div>
        );
    }
}

class Floors extends React.Component{
    constructor(props){
        super(props);

        this.state = {
            selected: false,
            floorName: 'Choose a floor',
            icon: 'fa-list',
            rooms: [],
            displayStatus: 'empty',
            mounted: false
        };
        this.componentDidMount = this.componentDidMount.bind(this);
        this.componentWillUnMount = this.componentWillUnMount.bind(this);
        this.updateFloor = this.updateFloor.bind(this);
        this.loadRooms = this.loadRooms.bind(this);
    }
    componentDidMount(){
        this.setState({mounted: true});
    }
    componentWillUnMount(){
        this.setState({mounted: false});
    }
    updateFloor(index) {
        this.setState({
            floorName: this.props.floorList[index].title,
            selected: true
        });
        this.setState({
            displayStatus: 'loading'
        });
        this.loadRooms(this.props.floorList[index].id);
    }
    loadRooms(floorId) {
        $.getJSON('index.php', {
            module: 'hms',
            action: 'JSONGetRooms',
            floorId: floorId
        }, function (data) {
            if (this.state.mounted) {
                this.setState({
                    displayStatus: 'show',
                    rooms: data
                });
            }
        }.bind(this));
    }
    render() {
        return (
            <div>
                <DropDown disabled={this.props.floorDisabled} icon={this.state.icon} listing={this.props.floorList} onClick={this.updateFloor} selected={this.state.selected} title={this.state.floorName}/>
                <div className="room-list">
                    <Rooms roomList={this.state.rooms} display={this.state.displayStatus} assignmentType={this.props.assignmentType}/>
                </div>
            </div>
        );
    }
}

Floors.propTypes = {
    floorList: PropTypes.array,
    floorDisabled: PropTypes.bool
};
Floors.defaultProps ={
    floorList: []
}

class Rooms extends React.Component{
    render() {
        if (this.props.display === 'show') {
            if (this.props.roomList.length === 0) {
                return (<p className="well text-center"><big>No rooms found for this floor.</big></p>);
            }
            return (
                <div>
                    {this.props.roomList.map(function (room, i) {
                        return (
                            <Room key={i} room={room} assignmentType={this.props.assignmentType} tab={i} />
                        );
                    }, this)}
                </div>
            );
        } else if (this.props.display === 'loading') {
            return (
                <div className="text-center well"><i className="fa fa-spinner fa-spin fa-2x"></i></div>
            );
        } else {
            return (
                <div></div>
            );
        }
    }
}

class Room extends React.Component{
    render() {
        var bedCount = this.props.room.beds ? this.props.room.beds.length : 0;
        return (
            <div>
                <h3>Room# {this.props.room.room_number} - {this.props.room.gender}</h3>
                {this.props.room.beds.map(function (bed, i) {
                    bed.tab = i + (this.props.tab * bedCount) + 1;
                    return (
                        <Bed bed={bed} key={i} assignmentType={this.props.assignmentType}/>
                    );
                }, this)}
            </div>
        );
    }
}

class Bed extends React.Component{
    constructor(props){
        super(props);

        this.state = {
            assignment : '',
            bed : this.props.bed
        };

        this.componentDidMount = this.componentDidMount.bind(this);
        this.readyAssignment = this.readyAssignment.bind(this);
        this.plugStudent = this.plugStudent.bind(this);
        this.assignByBannerId = this.assignByBannerId.bind(this);
        this.assignByUsername = this.assignByUsername.bind(this);
        this.failureMessage = this.failureMessage.bind(this);
        this.loadingMessage = this.loadingMessage.bind(this);
        this.successMessage = this.successMessage.bind(this);
        this.setAssignment = this.setAssignment.bind(this);
        this.resetForm = this.resetForm.bind(this);
        this.alertTag = this.alertTag.bind(this);
        this.processInput = this.processInput.bind(this);
    }
    componentDidMount() {
        this.readyAssignment();
    }
    readyAssignment() {
        if (this.state.bed.banner_id) {
            this.successMessage(this.state.bed.student);
        } else {
            this.setAssignment(<AssignmentForm update={this.processInput} bed={this.state.bed} />);
        }
    }
    plugStudent(student){
        var tempBed = this.state.bed;
        tempBed.asu_username = student.username;
        tempBed.banner_id = student.banner_id;
        tempBed.student = student.first_name + ' ' + student.last_name;
        this.setState({
            bed : tempBed
        });
    }
    assignByBannerId(banner_id) {
        this.loadingMessage();
        $.getJSON('index.php', {
            module: 'hms',
            action: 'JSONAssignStudent',
            banner_id: banner_id,
            reason: this.props.assignmentType,
            bed_id: this.props.bed.bed_id
        }, function (data) {
            if (data.status === 'success') {
                this.plugStudent(data.student);
                this.successMessage();
            } else if (data.status === 'failure') {
                this.failureMessage(data.message);
            } else {
                this.failureMessage('Failed to assign student');
            }
        }.bind(this));
    }
    assignByUsername(username) {
        this.loadingMessage();
        $.getJSON('index.php', {
            module: 'hms',
            action: 'JSONAssignStudent',
            username: username,
            reason: this.props.assignmentType,
            bed_id: this.props.bed.bed_id
        }, function (data) {
            if (data.status === 'success') {
                this.plugStudent(data.student);
                this.successMessage();
            } else if (data.status === 'failure') {
                this.failureMessage(data.message);
            } else {
                this.failureMessage('Failed to assign student');
            }
        }.bind(this));
    }
    failureMessage(message) {
        var fail = this.alertTag(message, 'danger', true);
        this.setAssignment(fail);
    }
    loadingMessage() {
        this.setAssignment(<div className="alert alert-info"><i className="fa fa-cog fa-spin fa-lg"></i> Searching for student...</div>);
    }
    successMessage() {
        var success = this.alertTag(this.state.bed.student, 'success', false);
        this.setAssignment(success);
    }
    setAssignment(assignment) {
        this.setState({
            assignment: assignment
        });
    }
    resetForm(){
        this.setAssignment(<AssignmentForm tab={this.state.tab} update={this.processInput} bed={this.props.bed} />);
    }
    alertTag(message, type, dismiss) {
        var dismissString = '';
        var button = null;
        if (dismiss) {
            dismissString = ' alert-dismissible';
            button = <button type="button" className="close" onClick={this.resetForm} aria-label="Close"><span aria-hidden="true">&times;</span></button>;
        } else {
            button = <i className="fa-lg pull-right fa fa-check-circle"></i>;
        }
        var message2 = <div>{button}{message}</div>;
        return React.createElement('div', {
            className : 'alert alert-' + type + dismissString,
            role: 'alert'
        }, message2);
    }
    processInput(event) {
        var value = event.target.value;
        if (value.length < 2) {
            return;
        }

        var reg = new RegExp(/[^\d]/);
        // if 9 characters and all the characters are digits
        if (value.length === 9 && !reg.test(value)) {
            this.assignByBannerId(value);
        } else {
            this.assignByUsername(value);
        }
    }
    render() {
        return (
            <div className="row bed-list-item">
                <div className="col-sm-2">
                    <big><i className="fa fa-bed fa-lg"></i> {this.props.bed.bedroom_label}{this.props.bed.bed_letter}</big>
                </div>
                <div className="col-sm-10">
                    {this.state.assignment}
                </div>
            </div>
        );
    }
}

class AssignmentForm extends React.Component{
    render() {
        var tab = this.props.tab;

        var input = React.createElement('input', {
            placeholder: 'Banner Id or username',
            className: 'form-control form-inline',
            type: 'text',
            tabIndex: this.props.bed.tab,
            autoFocus: tab === 1,
            'data-bed-id': this.props.bed.bed_id,
            onBlur: this.props.update,
            ref: 'assignment'
        });

        // onBlur covers the button click
        var button = React.createElement('button', {
            className: "btn btn-primary"
        }, 'Assign student');

        return (
            <div className="input-group">{input}
                <span className="input-group-btn">{button}</span>
            </div>
        );
    }
}


class Assigned extends React.Component{
    render() {
        return (
            <div className="alert alert-success">
                <i className="fa-lg pull-right fa fa-check-circle"></i>{this.props.student}
            </div>
        );
    }
}

Assigned.propTypes = {
    student: PropTypes.string
}
Assigned.defaultProps = {
    student: ''
}

class DropDown extends React.Component{
    render() {
        var buttonClass = this.props.selected ? 'btn-success' : 'btn-default';
        var buttonDisabled = this.props.disabled ? 'disabled' : '';
        //var listing = this.props.listing;
        return (
            <div className="btn-group btn-group-justified">
                <div className="btn-group" role="group">
                    <button aria-expanded="false" className={buttonClass + ' btn btn-lg dropdown-toggle'} data-toggle="dropdown" disabled={buttonDisabled} type="button">
                        <i className={'fa ' + this.props.icon}></i>{' '}
                        {this.props.title}
                        {' '}
                        <span className="caret"></span>
                    </button>
                    <ul className="dropdown-menu" role="menu">
                        {this.props.listing.map(function (listItem, i) {
                    return (
                            <DropDownChoice key={i} onClick={this.props.onClick.bind(null, i)} title={listItem.title}/>
                    );
                }, this)}
                    </ul>
                </div>
            </div>
        );
    }
}

DropDown.propTypes = {
    listing: PropTypes.array,
    selected: PropTypes.bool,
    title: PropTypes.string,
    icon: PropTypes.string,
    disabled: PropTypes.bool
}
DropDown.defaultProps = {
    listing: [],
    selected: false,
    title: 'Click here to choose',
    icon: 'fa-check',
    disabled: false
}

class DropDownChoice extends React.Component{
    render() {
        return (
            <li onClick={this.props.onClick}>
                <a style={{cursor: 'pointer', fontSize: '1.3em'}}>{this.props.title}</a>
            </li>
        );
    }
}

// This script will not run after compiled UNLESS the below is wrapped in $(window).load(function(){...});
ReactDOM.render(<AssignByFloor sourceHttp={window.sourceHttp}/>, document.getElementById('assign-by-floor'));
