var AssignByFloor = React.createClass({

    getInitialState: function () {
        return {
            hallList: [],
            mealPlanOptions: [],
            assignmentOptions: [],
            currentMealPlan: 0,
            currentAssignmentType: 0
        };
    },

    updateMealPlan: function(value) {
        this.setState({
            currentMealPlan: value
        });
    },

    updateAssignmentType: function(value) {
        this.setState({
            currentAssignmentType: value
        });
    },

    componentWillMount: function () {
        var hallList = [];
        var options = [];

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
                    mealPlanOptions: data.meal_plan,
                    assignmentOptions: data.assignment_type,
                    currentMealPlan: data.default_plan,
                    currentAssignmentType: data.default_assignment
                });

            }.bind(this));
        }.bind(this));
    },

    render: function () {
        return (
            <div>
                <Options {...this.state} updateMealPlan={this.updateMealPlan} updateAssignmentType={this.updateAssignmentType} />
                <Halls hallList={this.state.hallList} mealPlan={this.state.currentMealPlan} assignmentType={this.state.currentAssignmentType} />
            </div>
        );
    }
});

var Options = React.createClass({
    getInitialState: function () {
        return {
            currentMealPlan: 0,
            currentAssignmentType: 0
        };
    },

    componentWillReceiveProps: function(nextProps) {
        this.setState({
            currentMealPlan: nextProps.currentMealPlan,
            currentAssignmentType: nextProps.currentAssignmentType
        });
    },

    updateMealPlan: function(event) {
        this.props.updateMealPlan(event.target.value);
        this.setState({
            currentMealPlan: event.target.value
        });
    },

    updateAssignmentType: function(event) {
        this.props.updateAssignmentType(event.target.value);
        this.setState({
            currentAssignmentType: event.target.value
        });
    },


    render: function() {
        return (
            <div className="panel panel-primary">
                <div className="panel-heading">
                    <h3 className="panel-title">Assignment settings</h3>
                </div>
                <div className="row panel-body">
                    <div className="col-sm-6 form-group">
                        <label htmlFor="mealPlan">Meal&nbsp;Plan:</label>
                        <DropSelect options={this.props.mealPlanOptions} selectId='mealPlan' default={this.state.currentMealPlan} ref="mealPlan" onChange={this.updateMealPlan} />
                    </div>
                    <div className="col-sm-6 form-group">
                        <label htmlFor="assignmentType">Assignment&nbsp;type:</label>
                        <DropSelect options={this.props.assignmentOptions}  selectId='assignmentType' default={this.state.currentAssignmentType} ref="assignmentType" onChange={this.updateAssignmentType} />
                    </div>
                </div>
            </div>
        );
    }
});

var DropSelect = React.createClass({

    getDefaultProps: function () {
        return {
            options: [],
            selectId: null,
            default: null
        };
    },

    render: function() {
        return (
            <select className="form-control" id={this.props.selectId} value={this.props.default} onChange={this.props.onChange}>
                {this.props.options.map(function(value, i){
                    return <option key={i} value={value.id}>{value.value}</option>;
                })}
            </select>
        );
    }
});

var Halls = React.createClass({
    getInitialState: function () {
        return {
            hallName: 'Choose a hall',
            selected: false,
            icon: 'fa-building-o',
            floors: [],
            floorDisabled: true,
            timestamp: Date.now()
        };
    },

    loadFloors: function (hallId) {
        this.getInitialState();
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

    },

    updateHall: function (index) {
        this.setState({
            hallName: this.props.hallList[index].title,
            selected: true,
            timestamp: Date.now()
        });
        this.loadFloors(this.props.hallList[index].id);
    },

    render: function () {
        return (
            <div>
                <DropDown floorList={this.state.floorList} icon={this.state.icon} listing={this.props.hallList} onClick={this.updateHall} selected={this.state.selected} title={this.state.hallName}/>
                <Floors key={this.state.timestamp} floorDisabled={this.state.floorDisabled} floorList={this.state.floors} mealPlan={this.props.mealPlan} assignmentType={this.props.assignmentType} />
            </div>
        );
    }
});

var Floors = React.createClass({
    getInitialState: function () {
        return {
            selected: false,
            floorName: 'Choose a floor',
            icon: 'fa-dashboard',
            rooms: [],
            displayStatus: 'empty'
        };
    },

    propTypes: {
        floorList: React.PropTypes.array,
        floorDisabled: React.PropTypes.bool
    },

    getDefaultProps: function () {
        return {
            floorList: []
        };
    },

    updateFloor: function (index) {
        this.setState({
            floorName: this.props.floorList[index].title,
            selected: true
        });
        this.setState({
            displayStatus: 'loading'
        });
        this.loadRooms(this.props.floorList[index].id);
    },

    loadRooms: function (floorId) {
        $.getJSON('index.php', {
            module: 'hms',
            action: 'JSONGetRooms',
            floorId: floorId
        }, function (data) {
            if (this.isMounted()) {
                this.setState({
                    displayStatus: 'show',
                    rooms: data
                });
            }
        }.bind(this));
    },

    render: function () {
        return (
            <div>
                <DropDown disabled={this.props.floorDisabled} icon={this.state.icon} listing={this.props.floorList} onClick={this.updateFloor} selected={this.state.selected} title={this.state.floorName}/>
                <div className="room-list">
                    <Rooms roomList={this.state.rooms} display={this.state.displayStatus} mealPlan={this.props.mealPlan} assignmentType={this.props.assignmentType}/>
                </div>
            </div>
        );
    }

});

var Rooms = React.createClass({
    render: function () {
        var icon = React.createElement('img', {
            src: sourceHttp + 'mod/hms/img/loading.gif',
            width: '200px'
        });

        if (this.props.display == 'show') {
            if (this.props.roomList.length === 0) {
                return (<p className="well text-center"><big>No rooms found for this floor.</big></p>);
            }
            return (
                <div>
                    {this.props.roomList.map(function (room, i) {
                        return (
                            <Room key={i} room={room} mealPlan={this.props.mealPlan} assignmentType={this.props.assignmentType} tab={i} />
                        );
                    }, this)}
                </div>
            );
        } else if (this.props.display == 'loading') {
            return (
                <div className="text-center well">{icon}</div>
            );
        } else {
            return (
                <div></div>
            );
        }
    }
});

var Room = React.createClass({
    render: function () {
        var bedCount = this.props.room.beds ? this.props.room.beds.length : 0;
        return (
            <div>
                <h3>Room# {this.props.room.room_number} - {this.props.room.gender}</h3>
                {this.props.room.beds.map(function (bed, i) {
                    bed.tab = i + (this.props.tab * bedCount) + 1;
                    return (
                        <Bed bed={bed} key={i} mealPlan={this.props.mealPlan} assignmentType={this.props.assignmentType}/>
                    );
                }, this)}
            </div>
        );
    }
});

var Bed = React.createClass({
    getInitialState: function () {
        return {
            assignment : '',
            bed : this.props.bed
        };
    },

    componentDidMount: function() {
        this.readyAssignment();
    },

    readyAssignment: function() {
        if (this.state.bed.banner_id) {
            this.successMessage(this.state.bed.student);
        } else {
            this.setAssignment(<AssignmentForm update={this.processInput} bed={this.state.bed} />);
        }
    },

    plugStudent: function(student)
    {
        var tempBed = this.state.bed;
        tempBed.asu_username = student.username;
        tempBed.banner_id = student.banner_id;
        tempBed.meal_option = this.props.mealPlan;
        tempBed.student = student.first_name + ' ' + student.last_name;
        this.setState({
            bed : tempBed
        });
    },

    assignByBannerId: function (banner_id) {
        this.loadingMessage();
        $.getJSON('index.php', {
            module: 'hms',
            action: 'JSONAssignStudent',
            banner_id: banner_id,
            reason: this.props.assignmentType,
            meal_plan: this.props.mealPlan,
            bed_id: this.props.bed.bed_id
        }, function (data) {
            if (data.status == 'success') {
                this.plugStudent(data.student);
                this.successMessage();
            } else if (data.status == 'failure') {
                this.failureMessage(data.message);
            } else {
                this.failureMessage('Failed to assign student');
            }
        }.bind(this));

    },

    assignByUsername: function (username) {
        this.loadingMessage();
        $.getJSON('index.php', {
            module: 'hms',
            action: 'JSONAssignStudent',
            username: username,
            reason: this.props.assignmentType,
            meal_plan: this.props.mealPlan,
            bed_id: this.props.bed.bed_id
        }, function (data) {
            if (data.status == 'success') {
                this.plugStudent(data.student);
                this.successMessage();
            } else if (data.status == 'failure') {
                this.failureMessage(data.message);
            } else {
                this.failureMessage('Failed to assign student');
            }
        }.bind(this));
    },

    failureMessage: function(message) {
        var fail = this.alertTag(message, 'danger', true);
        this.setAssignment(fail);
    },

    loadingMessage: function() {
        this.setAssignment(<div className="alert alert-info"><i className="fa fa-cog fa-spin fa-lg"></i> Searching for student...</div>);
    },

    successMessage: function() {
        var success = this.alertTag(this.state.bed.student, 'success', false);
        this.setAssignment(success);
    },

    setAssignment: function(assignment) {
        this.setState({
            assignment: assignment
        });
    },

    resetForm: function()
    {
        this.setAssignment(<AssignmentForm tab={this.state.tab} update={this.processInput} bed={this.props.bed} />);
    },

    alertTag: function(message, type, dismiss) {
        var dismissString = '';
        var button = null;
        if (dismiss) {
            dismissString = ' alert-dismissible';
            button = <button type="button" className="close" onClick={this.resetForm} aria-label="Close"><span aria-hidden="true">&times;</span></button>;
        } else {
            button = <i className="fa-lg pull-right fa fa-check-circle"></i>;
        }
        message2 = <div>{button}{message}</div>;
        return React.createElement('div', {
            className : 'alert alert-' + type + dismissString,
            role: 'alert'
        }, message2);
    },

    processInput: function(event) {
        var value = event.target.value;
        if (value.length < 2) {
            return;
        }

        var reg = new RegExp(/[^\d]/);
        // if 9 characters and all the characters are digits
        if (value.length == 9 && !reg.test(value)) {
            this.assignByBannerId(value);
        } else {
            this.assignByUsername(value);
        }
    },

    render: function () {
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
});

var AssignmentForm = React.createClass({
    render: function () {
        var tab = this.props.tab;

        var input = React.createElement('input', {
            placeholder: 'Banner Id or username',
            className: 'form-control form-inline',
            type: 'text',
            tabIndex: this.props.bed.tab,
            autoFocus: tab == 1,
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
});


var Assigned = React.createClass({
    propTypes: {
        student: React.PropTypes.string
    },

    getDefaultProps: function () {
        return {
            student: ''
        };
    },

    render: function () {
        return (
            <div className="alert alert-success">
                <i className="fa-lg pull-right fa fa-check-circle"></i>{this.props.student}
            </div>
        );
    }
});

var DropDown = React.createClass({
    propTypes: {
        listing: React.PropTypes.array,
        selected: React.PropTypes.bool,
        title: React.PropTypes.string,
        icon: React.PropTypes.string,
        disabled: React.PropTypes.bool
    },

    getDefaultProps: function () {
        return {
            listing: [],
            selected: false,
            title: 'Click here to choose',
            icon: 'fa-check',
            disabled: false
        };
    },

    render: function () {
        var buttonClass = this.props.selected ? 'btn-success' : 'btn-default';
        var buttonDisabled = this.props.disabled ? 'disabled' : '';
        var listing = this.props.listing;
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
});

var DropDownChoice = React.createClass({
    render: function () {
        return (
            <li onClick={this.props.onClick}>
                <a style={{cursor: 'pointer', fontSize: '1.3em'}}>{this.props.title}</a>
            </li>
        );
    }
});

// This script will not run after compiled UNLESS the below is wrapped in $(window).load(function(){...});
React.render(<AssignByFloor />, document.getElementById('assign-by-floor'));
