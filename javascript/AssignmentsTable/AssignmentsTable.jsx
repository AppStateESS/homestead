import React from 'react';
import ReactDOM from 'react-dom';

import 'whatwg-fetch';

import ReactDataGrid from 'react-data-grid';
import {Toolbar, Data} from 'react-data-grid-addons';

const Selectors = Data.Selectors;

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
        { key: 'banner_id',
            name: 'Banner ID',
            formatter: StudentIdFormatter,
            filterable: true,
            locked: true},
        { key: 'first_name',
            name: 'First Name',
            filterable: true,
            sortable: true},
        { key: 'last_name',
            name: 'Last Name',
            filterable: true,
            sortable: true},
        { key: 'preferred_name',
            name:'Preferred Name',
            filterable: true,
            sortable: true},
        { key: 'asu_username',
            name: 'Username',
            filterable: true,
            sortable: true},
        { key: 'hall_name',
            name: 'Hall',
            filterable: true,
            sortable: true},
        { key: 'room_number',
            name: 'Room',
            filterable: true,
            sortable: true},
        { key: 'cell_phone',
            name:'Phone',
            filterable: true}
    ];

    this.getRows = this.getRows.bind(this);
    this.getSize = this.getSize.bind(this);
    this.rowGetter = this.rowGetter.bind(this);
    this.handleFilterChange = this.handleFilterChange.bind(this);
    this.onClearFilters = this.onClearFilters.bind(this);
    this.handleGridSort = this.handleGridSort.bind(this);

    let emptyRow = [{banner_id: '', first_name: '', last_name: '', preferred_name: '', asu_username: '', hall_name: '', room_number: '', cell_phone: ''}];

    //this.state = { rows: this.createRows(), filters: {} };
    this.state = {rows: emptyRow, originalRows: emptyRow, filters: {}};
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
              that.setState({rows: jsonData, originalRows: jsonData});

              // Always show the "filter" row.
              //that.refs.AssignmentDataGrid.setState({canFilter: true});
          })
          .catch(function(error){
              console.log('request failed', error);
          });
  }

  getRows(){
    return Selectors.getRows(this.state);
  };

  getSize(){
    return this.getRows().length;
  };

  rowGetter(rowIdx){
    let rows = this.getRows();
    return rows[rowIdx];
  };

  handleFilterChange(filter){
    let newFilters = Object.assign({}, this.state.filters);
    if (filter.filterTerm) {
      newFilters[filter.column.key] = filter;
    } else {
      delete newFilters[filter.column.key];
    }
    this.setState({ filters: newFilters });
  };

  onClearFilters(){
    // all filters removed
    this.setState({filters: {} });
  };

  handleGridSort(sortColumn, sortDirection){
    const comparer = (a, b) => {
      if (sortDirection === 'ASC') {
        return (a[sortColumn] > b[sortColumn]) ? 1 : -1;
      } else if (sortDirection === 'DESC') {
        return (a[sortColumn] < b[sortColumn]) ? 1 : -1;
      }
    };

    const rows = sortDirection === 'NONE' ? this.state.originalRows.slice(0) : this.state.rows.sort(comparer);

    this.setState({ rows });
  };

  render() {
    return (
      <ReactDataGrid
        ref="AssignmentDataGrid"
        columns={this._columns}
        rowGetter={this.rowGetter}
        enableCellSelect={true}
        rowsCount={this.getSize()}
        minHeight={500}
        toolbar={<Toolbar enableFilter={true}/>}
        onAddFilter={this.handleFilterChange}
        onClearFilters={this.onClearFilters}
        onGridSort={this.handleGridSort}/>);
  }
}

ReactDOM.render(<AssignmentsTable />, document.getElementById('assignmentsTable'));
