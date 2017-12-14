import React from 'react';
import ReactDOM from 'react-dom';

import 'whatwg-fetch';

class SuggestedRoommateList extends React.Component {
    constructor(){
        super();

        this.state = {loading: true, suggestedRoommateList: false};
        //this.renderRoommates = this.renderRoommates.bind(this);
    }

    componentDidMount() {

        console.log(this.props.bannerId);

        let bannerId = '';
        if(this.props.bannerId !== undefined && this.props.bannerId !== 'null'){
            bannerId = this.props.bannerId;
            console.log('in here');
        }else {
            console.log('is null or unef');
            bannerId = 'null';
        }

      fetch('index.php?module=hms&action=GetSuggestedRoommates&term=' + this.props.term + '&bannerId=' + bannerId, {credentials: 'same-origin'})
        .then(res => res.json())
        .then(
          suggestedRoommateList => this.setState({ loading: false, suggestedRoommateList }),
          error => this.setState({ loading: false, error })
        );
    }

    renderRoommates() {
        let suggestedRoommates = this.state.suggestedRoommateList.map(function(roommate){
            let profileUrl = 'index.php?module=hms&action=ShowRoommateProfile&term=' + this.props.term +'&banner_id=' + roommate.bannerId;

            return (
                <li key={roommate.bannerId} className="list-group-item">
                    <div className="row">
                        <div className="col-md-5">
                            <h5>{roommate.name}</h5>
                        </div>
                        <div className="col-md-4">
                            <a href={profileUrl} className="btn btn-fill btn-default btn-sm">View Profile</a>
                        </div>
                        <div className="col-md-3">
                            <RoommateDonutChart value={roommate.matchPercent}/>
                        </div>
                    </div>
                </li>
            );
        }.bind(this));

        return (<div>{suggestedRoommates}</div>);
    }

    renderLoading() {
      return (<div><h4><i className="fa fa-spin fa-spinner"></i> We're crunching the number to find your best matches. Hang in there...</h4></div>);
    }

    renderError() {
      return (<ul className="list-group">I'm sorry! Please try again.</ul>);
    }

    render() {
        if (this.state.loading) {
            return this.renderLoading();
        } else if (this.state.suggestedRoommateList) {
            return this.renderRoommates();
        } else {
            return this.renderError();
        }
    }
}

class RoommateDonutChart extends React.Component{
  render() {
      const rsize = 5
      const fsize = 50
      const halfsize = (fsize * 0.5);
      const radius = halfsize - (rsize * 0.5);
      const circumference = 2 * Math.PI * radius;
      const strokeval = ((this.props.value * circumference) / 100);
      const dashval = (strokeval + ' ' + circumference);
      const trackstyle = {strokeWidth: rsize};
      const indicatorstyle = {strokeWidth: rsize, strokeDasharray: dashval}
      const rotateval = 'rotate(-90 '+halfsize+','+halfsize+')';

    return (
      <svg width={fsize} height={fsize} className="donutchart">
        <circle r={radius} cx={halfsize} cy={halfsize} transform={rotateval} style={trackstyle} className="donutchart-track"/>
        <circle r={radius} cx={halfsize} cy={halfsize} transform={rotateval} style={indicatorstyle} className="donutchart-indicator"/>
        <text className="donutchart-text" x={27} y={30} style={{textAnchor:'middle'}} >
          <tspan className="donutchart-text-val">{this.props.value}</tspan>
          <tspan className="donutchart-text-percent">%</tspan>
        </text>
      </svg>
    );
  }
}

ReactDOM.render(<SuggestedRoommateList term={window.term} bannerId={window.bannerId}/>, document.getElementById('SuggestedRoommateList'));
