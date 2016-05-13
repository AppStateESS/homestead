/**
              IMPORTANT!
   ******************************
   *  The following component   *
   *    uses ReactBootstrap     *
   ******************************
**/
var Modal = ReactBootstrap.Modal;
var Button = ReactBootstrap.Button;


var ModalApp = React.createClass({
    getInitialState: function() {
        return {
            appData: []
        };
    },
    componentWillMount: function()
    {
        this.getAppData();
    },
    getAppData: function()
    {
        var inputData = {applicationId: this.props.appId};

        $.ajax({
            url: 'index.php?module=hms&action=AjaxGetRLCApplication',
            type: 'POST',
            dataType: 'json',
            data: inputData,
            success: function(data)
            {
                this.setState({appData: data});
            }.bind(this),
            error: function(xhr, status, err)
            {

            }.bind(this)
        });
    },
    render: function()
    {
        var secondChoiceListItem = (<div></div>);
        var secondChoice = (<div></div>);
        var thirdChoiceListItem = (<div></div>);
        var thirdChoice = (<div></div>);

        if(this.state.appData.secondChoice != undefined)
        {
            secondChoice = (<div>
                                    <label>Answers to {this.state.appData.secondChoice}&#39;s Question:</label>
                                    <p>{this.state.appData.secondChoiceAnswer}</p>
                                </div>);
            secondChoiceListItem = (<li>{this.state.appData.secondChoice}</li>)
        }
        if(this.state.appData.secondChoice != undefined)
        {
            thirdChoice = (<div>
                                    <label>Answers to {this.state.appData.thirdChoice}&#39;s Question:</label>
                                    <p>{this.state.appData.thirdChoiceAnswer}</p>
                                </div>);
            thirdChoiceListItem = (<li>{this.state.appData.thirdChoice}</li>)
        }

        return (
            <div className="col-md-12">
                  <Modal show={this.props.show} onHide={this.props.close} container={this} bsSize="large">
                    <Modal.Header closeButton>
                      <Modal.Title>{this.state.appData.name}&#39;s Application</Modal.Title>
                    </Modal.Header>
                    <Modal.Body>
                        <div>
                            <label>Community Choices:</label>
                            <ol>
                                <li>{this.state.appData.firstChoice}</li>
                                {secondChoiceListItem}

                            </ol>
                        </div>
                        <div>
                            <label>Reason for interested in these communities:</label>
                            <p>{this.state.appData.specificCommQuestion}</p>
                        </div>
                        <div>
                            <label>Strengths and Weaknesses:</label>
                            <p>{this.state.appData.strenthsWeaknesses}</p>
                        </div>
                        <div>
                            <label>Answers to {this.state.appData.firstChoice}&#39;s Question:</label>
                            <p>{this.state.appData.firstChoiceAnswer}</p>
                        </div>
                        {secondChoice}


                    </Modal.Body>

                    <Modal.Footer>
                      <Button onClick={this.props.close}>Close</Button>
                    </Modal.Footer>
                  </Modal>
            </div>
        );
    }
});
