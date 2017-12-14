import React from 'react';
import ReactDOM from 'react-dom';

import 'whatwg-fetch';


class RlcCardList extends React.Component {
  constructor(){
      super();

      this.state = { loading: true, rlcs: false};
  }

  componentDidMount() {
    fetch('index.php?module=hms&action=GetRlcCardList', {credentials: 'same-origin'})
      .then(res => res.json())
      .then(
        rlcs => this.setState({ loading: false, rlcs }),
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
      let cards = this.state.rlcs.map(function(rlc){

          let rosterUri = 'index.php?module=hms&action=ShowViewByRlc&rlc=' + rlc.id;
          let settingsUri = 'index.php?module=hms&action=ShowAddRlc&id='+ rlc.id;

          return (
            <div className="col-md-3" key={rlc.id}>
                <div className="card">
                    <div className="header">
                        <h4>{rlc.community_name}</h4>
                    </div>
                    <div className="content">
                        <p>
                            <a href={rosterUri}>{rlc.member_count} members</a>
                            <a href={settingsUri} className="pull-right"><i className="fa fa-cog"></i></a>
                        </p>
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
    } else if (this.state.rlcs) {
      return this.renderCards();
    } else {
      return this.renderError();
    }
  }
}

ReactDOM.render(<RlcCardList />, document.getElementById('RlcCardList'));
