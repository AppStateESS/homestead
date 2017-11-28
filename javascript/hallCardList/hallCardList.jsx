import React from 'react';
import { Row, Col } from 'react-bootstrap';
import ReactDOM from 'react-dom';

import 'whatwg-fetch';


class HallCardList extends React.Component {

  constructor(){
      super();

      this.state = { loading: true, halls: false};
  }

  componentDidMount() {
    fetch('index.php?module=hms&action=GetHallCardList', {credentials: 'same-origin'})
      .then(res => res.json())
      .then(
        halls => this.setState({ loading: false, halls }),
        error => this.setState({ loading: false, error })
      );
  }

  renderLoading() {
    return (<div>Loading...</div>);
  }

  renderError() {
    return (<div>I'm sorry! Please try again.</div>);
  }

  renderCards() {
      let cards = this.state.halls.map(function(hall){

          let rosterUri = 'index.php?module=hms&action=EditResidenceHallView&hallId=' + hall.id;
          //var data = {series:[hall.numAssignees,hall.numBeds,hall.numFree]};
          var percent = Math.round(((hall.numAssignees/hall.numBeds))*100)
          return (
            <div className="col-lg-3 col-md-6 col-sm-6 col-xs-12" key={hall.id}>
                <div className="card card-user">
                    <div className="image">
                        <img src={hall.imageLink}/>
                    </div>
                    <div className="content">
                        <h4><a href={rosterUri}>{hall.hall_name}</a></h4>
                        <Row>
                        <Col md={2} className="col"><br/><i className="fa fa-users"></i><h4> {hall.numAssignees}</h4></Col>
                        <Col md={2} className="col"><br/><i className="fa fa-bed"></i><h4> {hall.numBeds}</h4></Col>
                        <Col md={3} className="col"><br/>Vacant:<h4> {hall.numFree}</h4></Col>
                        <Col md={5}><DonutChart value={percent}/></Col></Row>
                    </div>
                </div>
            </div>
          );
      });

      return (<div className="row">{cards}</div>);
  }

  render() {
    if (this.state.loading) {
      return this.renderLoading();
  } else if (this.state.halls) {
      return this.renderCards();
    } else {
      return this.renderError();
    }
  }
}

class DonutChart extends React.Component{
  render() {
      const rsize = 15
      const fsize = 70
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
        <text className="donutchart-text" x={halfsize} y={halfsize} style={{textAnchor:'middle'}} >
          <tspan className="donutchart-text-val">{this.props.value}</tspan>
          <tspan className="donutchart-text-percent">%</tspan>
          <tspan className="donutchart-text-label" x={halfsize} y={halfsize+10}>Full</tspan>
        </text>
      </svg>
    );
  }
}

ReactDOM.render(<HallCardList />, document.getElementById('HallCardList'));
