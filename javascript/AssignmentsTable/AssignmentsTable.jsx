import React from 'react';
import ReactDOM from 'react-dom';

import 'whatwg-fetch';
import ReactDataGrid from 'react-data-grid';

//import 'prop-types';

class StudentIdFormatter extends React.Component {
    // static propTypes = {
    //      value: PropTypes.number.isRequired
    // };

    render() {
        return (
            <div>
                <a href={'index.php?module=hms&action=ShowStudentProfile&bannerId=' + this.props.value}>{this.props.value}</a>
            </div>);
        }
    }

class AssignmentsTable extends React.Component {
    constructor(props, context) {
        super(props, context);

        this._columns = [
            { key: 'banner_id', name: 'Banner ID', formatter: StudentIdFormatter},
            { key: 'first_name', name: 'First Name'},
            { key: 'last_name', name: 'Last Name'},
            { key: 'preferred_name', name:'Preferred Name'},
            { key: 'asu_username', name: 'Username' },
            { key: 'hall_name', name: 'Hall'},
            { key: 'room_number', name: 'Room'},
            { key: 'cell_phone', name:'Phone'}
        ];

        this.state = {rows: null};

        //this.state = {rows: [{banner_id: '900325006', asu_username: 'jb67803', hall_name: 'Summit Hall'}]};

        this.rowGetter = this.rowGetter.bind(this);
    }
    componentDidMount() {
        let that = this;

        fetch('./index.php?module=hms&action=GetAssignmentsList', {credentials: 'same-origin'})
            .then(function(response){
                if (response.status >= 200 && response.status < 300) {
                    return response.json();
                } else {
                    var error = new Error(response.statusText)
                    error.response = response;
                    throw error;
                }
            })
            .then(function(jsonData){
                that.setState({rows: jsonData});
            })
            .catch(function(error){
                console.log('request failed', error);
            });
    }

    rowGetter(i) {
        return this.state.rows[i];
    };

    render() {
        if(this.state.rows === null){
            return (<div>
                        <p>
                            <i className="fa fa-lg fa-spin fa-spinner"></i> Loading Assignments...
                        </p>
                    </div>);
        }

        return  (
            <ReactDataGrid
                columns={this._columns}
                rowGetter={this.rowGetter}
                rowsCount={this.state.rows.length}
                minHeight={500}
            />
        );
    }
}

ReactDOM.render(<AssignmentsTable />, document.getElementById('assignmentsTable'));
