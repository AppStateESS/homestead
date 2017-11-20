import React from 'react';
import ReactDOM from 'react-dom';

import 'whatwg-fetch';

import MessageRow from './messageRow.jsx';

class EmailLogView extends React.Component {
    constructor(props){
        super(props);

        this.state = {
            fetchingIds: true,
            messages: null,

        }
    }
    componentDidMount(){
        let that = this;

        fetch('./index.php?module=hms&action=GetEmailLogMessageList&bannerId=' + this.props.studentId, {credentials: 'same-origin'})
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
                let messages = {};

                let i =0;
                for(i=0; i < jsonData.length; i++){
                    messages[jsonData[i].message_id] = jsonData[i];
                }

                that.setState({
                    messages: messages,
                    fetchingIds: false
                }, function(){
                    that.fetchAllMessageDetails();
                })

                return jsonData;
            })
            .catch(function(error){
                console.log('request failed', error);
            });
    }
    fetchAllMessageDetails(){
        let that = this;
        var i = 0;
        var keyList = Object.keys(this.state.messages);
        for(i = 0; i < keyList.length; i++){
            that.fetchMessageDetails(keyList[i]);
        }
    }
    fetchMessageDetails(messageId){
        let that = this;
        var messages = null;

        messages = that.state.messages;

        // Fetch the 'Info' endpoint for the given message
        fetch('https://mandrillapp.com/api/1.0/messages/info.json',{
            method: 'post',
            body: JSON.stringify({
                key: that.props.mandrillKey,
                id: messageId
                })
        }).then(function(response){
            if (response.status >= 200 && response.status < 300) {
                return response.json();
            } else {
                var error = new Error(response.statusText)
                error.response = response;
                throw error;
            }
        }).then(function(jsonData){
            messages[jsonData._id].mandrillInfo = jsonData;
            that.setState({messages: messages});
        });

        // Fetch the 'Content' endpoint for the given message
        fetch('https://mandrillapp.com/api/1.0/messages/content.json',{
            method: 'post',
            body: JSON.stringify({
                key: that.props.mandrillKey,
                id: messageId
                })
        }).then(function(response){
            if (response.status >= 200 && response.status < 300) {
                return response.json();
            } else {
                var error = new Error(response.statusText)
                error.response = response;
                throw error;
            }
        }).then(function(jsonData){
            messages[jsonData._id].mandrillContent = jsonData;
            that.setState({messages: messages});
        });

    }
    render() {
        if(this.state.fetchingIds){
            return (<div>
                            <i className="fa fa-spinner fa-spin fa-2x fa-fw"></i>Loading message list...
                            <span className="sr-only">Loading...</span>
                    </div>);
        }

        let messageRows = null;
        if(this.state.messages !== null){
            messageRows = Object.keys(this.state.messages).map(function(key){
                return <MessageRow
                        key={this.state.messages[key].message_id}
                        messageId={this.state.messages[key].message_id}
                        messageType={this.state.messages[key].message_type}
                        mandrillInfo={this.state.messages[key].mandrillInfo}
                        mandrillContent={this.state.messages[key].mandrillContent} />
                }.bind(this));
        } else {
            messageRows = '';
        }

        return (
            <div className="table-responsive">
                <table className="table table-striped table-hover">
                    <tbody>
                        <tr>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Subject</th>
                            <th>Opened</th>
                        </tr>
                        {messageRows}
                    </tbody>
                </table>
            </div>
        );
    }
}

ReactDOM.render(<EmailLogView studentId={window.emailLogParams.banner_id} mandrillKey={window.emailLogParams.mandrill_key}/>, document.getElementById('emailLogView'));
