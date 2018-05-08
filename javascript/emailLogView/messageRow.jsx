import React from 'react';
import {Button, Modal} from 'react-bootstrap';

class MessageRow extends React.Component {
    constructor(props) {
        super(props);
        this.state = {showModal: false};

        this.handleShowModal = this.handleShowModal.bind(this);
        this.onHideModal = this.onHideModal.bind(this);
    }
    handleShowModal() {
        this.setState({showModal: true});
    }
    onHideModal() {
        this.setState({showModal: false});
    }
    render(){
        let sentDate = null;
        let subject = null;
        let opened = null;

        if(this.props.mandrillInfo !== undefined){
            let dateObj =  new Date(this.props.mandrillInfo.ts * 1000);
            sentDate = dateObj.toDateString() + ' ' + dateObj.toLocaleTimeString();
            subject = this.props.mandrillInfo.subject;

            if(this.props.mandrillInfo.opens > 0){
                opened = <i className="fas fa-check text-success"></i>
            } else {
                opened = 'No';
            }

        } else {
            sentDate = '';
            subject = '';
            opened = '';
        }

        let modalContent = '';
        let messageContent = null;
        if(this.props.mandrillInfo !== undefined && this.props.mandrillContent !== undefined){
            messageContent = {__html: this.props.mandrillContent.html};
            modalContent = (
                <Modal show={this.state.showModal} onHide={this.onHideModal}>
                    <Modal.Header closeButton>
                        <Modal.Title>Message Details</Modal.Title>
                    </Modal.Header>
                    <Modal.Body>
                        <div>
                            <form className="form-horizontal">
                                    <label className="col-lg-2 control-label">To </label>
                                    <div className="col-lg-9">
                                        <p className="form-control-static">{this.props.mandrillInfo.email}</p>
                                    </div>

                                    <label className="col-lg-2 control-label">Sent </label>
                                    <div className="col-lg-9">
                                        <p className="form-control-static">{sentDate}</p>
                                    </div>

                                    <label className="col-lg-2 control-label">Subject </label>
                                    <div className="col-lg-9">
                                        <p className="form-control-static">{subject}</p>
                                    </div>

                                <div className="form-group">
                                    <div className="col-lg-9">
                                        <p className="form-control-static" dangerouslySetInnerHTML={messageContent}></p>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </Modal.Body>
                </Modal>
            );
        }

        return (
            <tr onClick={this.handleShowModal} className="row-link">
                <td>{this.props.messageType}</td>
                <td>{sentDate}</td>
                <td>{subject}</td>
                <td>{opened}</td>
                <td>
                    {modalContent}
                </td>
            </tr>
        )
    }
}

export default MessageRow;
