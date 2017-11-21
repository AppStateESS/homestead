import React from 'react';
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

          return (
            <div className="col-lg-3 col-md-6 col-xs-12" key={hall.id}>
                <div className="card card-user">
                    <div className="image">
                        <img src={hall.imageLink}/>
                    </div>
                    <div className="content">
                        <h4><a href={rosterUri}>{hall.hall_name}</a></h4>
                        <p><i className="fa fa-users"></i> {hall.numAssignees}</p>
                        <p><i className="fa fa-bed"></i> {hall.numBeds}</p>
                        <p>Free: {hall.numFree}</p>
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

ReactDOM.render(<HallCardList />, document.getElementById('HallCardList'));
