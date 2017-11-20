import React from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import {Button, Modal} from 'react-bootstrap';

class Note extends React.Component {
    constructor(props) {
        super(props);
        this.state = {showModal: false};

        this.closeModal = this.closeModal.bind(this);
        this.openModal = this.openModal.bind(this);
        this.handleSaveNote = this.handleSaveNote.bind(this);
    }
    closeModal() {
        this.setState({ showModal: false });
    }
    openModal() {
        this.setState({ showModal: true });
    }
    handleSaveNote(note, username){
        this.closeModal(); // Close the modal box
        var studentName
        if (this.props.activityLog === 'hidden'){
            studentName = this.props.studentUsername
        }else{
            studentName = username
        }
        $.ajax({
            url: 'index.php?module=hms&action=AddNote',
            method: 'POST',
            dataType: 'text',
            data: {note: note, username: studentName},
            success: function() {
                window.location.reload()
            },
            error: function(xhr, status, err) {
                alert("Failed to save note.")
                console.error(this.props.url, status, err.toString());
            }.bind(this)
        });
    }
    render() {
        return (
            <div>
                <Button className="addNote" onClick={this.openModal}><i className="fa fa-plus"></i> Add a Note</Button>
                <NoteModalForm show={this.state.showModal} hide={this.closeModal} edit={true} handleSaveNote={this.handleSaveNote}{...this.props} />
            </div>
        );
    }
}

class NoteModalForm extends React.Component {
    constructor(props) {
        super(props);
        this.state = {showError: false, showMessage: ''};

        this.handleSave = this.handleSave.bind(this);
    }
    handleSave() {
        if (this.refs.message.value === '') {
            this.setState({showError: true, showMessage: 'Please input a message.'});
            return;
        }else if (this.props.activityLog !== 'hidden' && this.refs.username.value === ''){
            this.setState({showError: true, showMessage: 'Please input a username.'});
            return;
        }
        else{
            this.setState({showError: false});
        }

        this.props.handleSaveNote(this.refs.message.value, this.refs.username.value);
    }
    render() {
        var warning = <div id="warningError" className="alert alert-warning alert-dismissable" role="alert">
                        <button type="button"  className="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">x;</span></button>
                        <strong>Warning!</strong> {this.state.showMessage}
                      </div>
        var className = "form-group " + this.props.activityLog;
        return (
            <Modal show={this.props.show} onHide={this.props.hide} backdrop='static'>
                <Modal.Header closeButton>
                  <Modal.Title>Add a new note:</Modal.Title>
                  {this.state.showError ? warning : null}
                </Modal.Header>
                <Modal.Body>
                    <form className="form-horizontal">
                        <div className={className}>
                            <label className="col-lg-3">Username: </label>
                            <div className="col-lg-9"><input  type="text" className="form-control" ref="username"/></div>
                        </div>
                        <div className="form-group">
                            <div className="col-lg-12"><textarea cols="67" rows="15" name="note" ref="message"></textarea></div>
                        </div>
                    </form>
                </Modal.Body>
                <Modal.Footer>
                    <Button onClick={this.handleSave}>Save</Button>
                </Modal.Footer>
            </Modal>
        );
    }
}

ReactDOM.render(<Note studentUsername={window.noteParamsStudent} activityLog={window.userActivity}/>, document.getElementById('note-box'));
