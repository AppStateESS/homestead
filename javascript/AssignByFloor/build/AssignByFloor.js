var AssignByFloor = React.createClass({displayName: "AssignByFloor",

    getInitialState: function () {
        return {
            hallList: [],
            mealPlanOptions: [],
            assignmentOptions: [],
            currentMealPlan: 0,
            currentAssignmentType: 0,
        };
    },

    updateMealPlan: function(value) {
        this.setState({
            currentMealPlan: value,
        });
    },

    updateAssignmentType: function(value) {
        this.setState({
            currentAssignmentType: value,
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
                    currentAssignmentType: data.default_assignment,
                });

            }.bind(this));
        }.bind(this));
    },

    render: function () {
        return (
            React.createElement("div", null,
                React.createElement(Options, React.__spread({},  this.state, {updateMealPlan: this.updateMealPlan, updateAssignmentType: this.updateAssignmentType})),
                React.createElement(Halls, {hallList: this.state.hallList, mealPlan: this.state.currentMealPlan, assignmentType: this.state.currentAssignmentType})
            )
        );
    }
});

var Options = React.createClass({displayName: "Options",
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
            React.createElement("div", {className: "panel panel-primary"},
                React.createElement("div", {className: "panel-heading"},
                    React.createElement("h3", {className: "panel-title"}, "Assignment settings")
                ),
                React.createElement("div", {className: "row panel-body"},
                    React.createElement("div", {className: "col-sm-6 form-group"},
                        React.createElement("label", {htmlFor: "mealPlan"}, "Meal Plan:"),
                        React.createElement(DropSelect, {options: this.props.mealPlanOptions, selectId: "mealPlan", default: this.state.currentMealPlan, ref: "mealPlan", onChange: this.updateMealPlan})
                    ),
                    React.createElement("div", {className: "col-sm-6 form-group"},
                        React.createElement("label", {htmlFor: "assignmentType"}, "Assignment type:"),
                        React.createElement(DropSelect, {options: this.props.assignmentOptions, selectId: "assignmentType", default: this.state.currentAssignmentType, ref: "assignmentType", onChange: this.updateAssignmentType})
                    )
                )
            )
        );
    }
});

var DropSelect = React.createClass({displayName: "DropSelect",

    getDefaultProps: function () {
        return {
            options: [],
            selectId: null,
            default: null
        };
    },

    render: function() {
        return (
            React.createElement("select", {className: "form-control", id: this.props.selectId, value: this.props.default, onChange: this.props.onChange},
                this.props.options.map(function(value, i){
                    return React.createElement("option", {key: i, value: value.id}, value.value);
                })
            )
        );
    }
});

var Halls = React.createClass({displayName: "Halls",
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
            React.createElement("div", null,
                React.createElement(DropDown, {floorList: this.state.floorList, icon: this.state.icon, listing: this.props.hallList, onClick: this.updateHall, selected: this.state.selected, title: this.state.hallName}),
                React.createElement(Floors, {key: this.state.timestamp, floorDisabled: this.state.floorDisabled, floorList: this.state.floors, mealPlan: this.props.mealPlan, assignmentType: this.props.assignmentType})
            )
        );
    }
});

var Floors = React.createClass({displayName: "Floors",
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
            React.createElement("div", null,
                React.createElement(DropDown, {disabled: this.props.floorDisabled, icon: this.state.icon, listing: this.props.floorList, onClick: this.updateFloor, selected: this.state.selected, title: this.state.floorName}),
                React.createElement("div", {className: "room-list"},
                    React.createElement(Rooms, {roomList: this.state.rooms, display: this.state.displayStatus, mealPlan: this.props.mealPlan, assignmentType: this.props.assignmentType})
                )
            )
        );
    }

});

var Rooms = React.createClass({displayName: "Rooms",
    render: function () {
        var icon = React.createElement('img', {
            src: sourceHttp + 'mod/hms/img/loading.gif',
            width: '200px'
        });

        if (this.props.display == 'show') {
            if (this.props.roomList.length === 0) {
                return (React.createElement("p", {className: "well text-center"}, React.createElement("big", null, "No rooms found for this floor.")));
            }
            return (
                React.createElement("div", null,
                    this.props.roomList.map(function (room, i) {
                        return (
                            React.createElement(Room, {key: i, room: room, mealPlan: this.props.mealPlan, assignmentType: this.props.assignmentType, tab: i})
                        );
                    }, this)
                )
            );
        } else if (this.props.display == 'loading') {
            return (
                React.createElement("div", {className: "text-center well"}, icon)
            );
        } else {
            return (
                React.createElement("div", null)
            );
        }
    }
});

var Room = React.createClass({displayName: "Room",
    render: function () {
        var bedCount = this.props.room.beds ? this.props.room.beds.length : 0;
        return (
            React.createElement("div", null,
                React.createElement("h3", null, "Room# ", this.props.room.room_number, " - ", this.props.room.gender),
                this.props.room.beds.map(function (bed, i) {
                    bed.tab = i + (this.props.tab * bedCount) + 1;
                    return (
                        React.createElement(Bed, {bed: bed, key: i, mealPlan: this.props.mealPlan, assignmentType: this.props.assignmentType})
                    );
                }, this)
            )
        );
    }
});

var Bed = React.createClass({displayName: "Bed",
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
            this.setAssignment(React.createElement(AssignmentForm, {update: this.processInput, bed: this.state.bed}));
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
        this.setAssignment(React.createElement("div", {className: "alert alert-info"}, React.createElement("i", {className: "fa fa-cog fa-spin fa-lg"}), " Searching for student..."));
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
        this.setAssignment(React.createElement(AssignmentForm, {tab: this.state.tab, update: this.processInput, bed: this.props.bed}));
    },

    alertTag: function(message, type, dismiss) {
        var dismissString = '';
        var button = null;
        if (dismiss) {
            dismissString = ' alert-dismissible';
            button = React.createElement("button", {type: "button", className: "close", onClick: this.resetForm, "aria-label": "Close"}, React.createElement("span", {"aria-hidden": "true"}, "×"));
        } else {
            button = React.createElement("i", {className: "fa-lg pull-right fa fa-check-circle"});
        }
        message2 = React.createElement("div", null, button, message);
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
            React.createElement("div", {className: "row bed-list-item"},
                React.createElement("div", {className: "col-sm-2"},
                    React.createElement("big", null, React.createElement("i", {className: "fa fa-bed fa-lg"}), " ", this.props.bed.bedroom_label, this.props.bed.bed_letter)
                ),
                React.createElement("div", {className: "col-sm-10"},
                    this.state.assignment
                )
            )
        );
    }
});

var AssignmentForm = React.createClass({displayName: "AssignmentForm",
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
            React.createElement("div", {className: "input-group"}, input,
                React.createElement("span", {className: "input-group-btn"}, button)
            )
        );
    }
});


var Assigned = React.createClass({displayName: "Assigned",
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
            React.createElement("div", {className: "alert alert-success"},
                React.createElement("i", {className: "fa-lg pull-right fa fa-check-circle"}), this.props.student
            )
        );
    }
});

var DropDown = React.createClass({displayName: "DropDown",
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
            React.createElement("div", {className: "btn-group btn-group-justified"},
                React.createElement("div", {className: "btn-group", role: "group"},
                    React.createElement("button", {"aria-expanded": "false", className: buttonClass + ' btn btn-lg dropdown-toggle', "data-toggle": "dropdown", disabled: buttonDisabled, type: "button"},
                        React.createElement("i", {className: 'fa ' + this.props.icon}), ' ',
                        this.props.title,
                        ' ',
                        React.createElement("span", {className: "caret"})
                    ),
                    React.createElement("ul", {className: "dropdown-menu", role: "menu"},
                        this.props.listing.map(function (listItem, i) {
                    return (
                            React.createElement(DropDownChoice, {key: i, onClick: this.props.onClick.bind(null, i), title: listItem.title})
                    );
                }, this)
                    )
                )
            )
        );
    }
});

var DropDownChoice = React.createClass({displayName: "DropDownChoice",
    render: function () {
        return (
            React.createElement("li", {onClick: this.props.onClick},
                React.createElement("a", {style: {cursor: 'pointer', fontSize: '1.3em'}}, this.props.title)
            )
        );
    }
});

$(window).load(function(){
    React.render(React.createElement(AssignByFloor, null), document.getElementById('assign-by-floor'));
});
