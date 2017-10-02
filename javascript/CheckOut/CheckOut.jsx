import React from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import PropTypes from 'prop-types';

class CheckOut extends React.Component{
    constructor(props){
        super(props);
        // damage_types, existing_damage and residents are plugged in by the CheckOut.html template
        this.state = {
            previousKeyCode: this.props.previous_key_code,
            keyReturned: null,
            keyCode : null,
            existingDamage: this.props.existing_damage,
            newDamage: [],
            residents: this.props.residents,
            damageTypes: this.props.damage_types,
            properCheckout : null,
            improperNote : '',
            checkinId: this.props.checkin_id,
            bannerId: this.props.banner_id
        };
        this.updateKeyReturned = this.updateKeyReturned.bind(this);
        this.updateKeyCode = this.updateKeyCode.bind(this);
        this.updateNewDamage = this.updateNewDamage.bind(this);
        this.updateProperCheckout = this.updateProperCheckout.bind(this);
        this.updateImproperNote = this.updateImproperNote.bind(this);
        this.isReadyToPost = this.isReadyToPost.bind(this);
        this.postCheckOut = this.postCheckOut.bind(this);
    }
    updateKeyReturned(key) {
        this.setState({keyReturned: key});
    }
    updateKeyCode(code){
        this.setState({keyCode : code});
    }
    updateNewDamage(damage){
        this.setState({newDamage : damage});
    }
    updateProperCheckout(value){
        var checkout = Number(value);
        if (checkout === 0) {
            this.setState({
                properCheckout : checkout,
                improperNote : ''
            });
        } else {
            this.setState({properCheckout : checkout});
        }
    }
    updateImproperNote(note){
        this.setState({improperNote : note.target.value});
    }
    isReadyToPost(){
        if (this.state.keyReturned === null) {
            return false;
        }
        if (this.state.keyReturned && this.state.keyCode === null) {
            return false;
        }
        if (this.state.properCheckout === null) {
            return false;
        }
        if (this.state.properCheckout === 0 && this.state.improperNote.length === 0) {
            return false;
        }

        return true;
    }
    postCheckOut() {
        var forward_url = 'index.php?module=hms&action=ShowCheckoutDocument&checkinId=' + this.state.checkinId;
        var damages = this.state.newDamage;

        // if damage array has a form at the end, pop it off
        if (damages.length > 0 && damages[damages.length - 1].damage_type === 0) {
            damages.pop();
        }
        $.post('index.php', {
            module: 'hms',
            action: 'CheckoutFormSubmit',
            checkinId  : this.state.checkinId,
            bannerId : this.state.bannerId,
            keyReturned: this.state.keyReturned,
            keyCode: this.state.keyCode,
            newDamage:  damages,
            properCheckout: this.state.properCheckout,
            improperNote: this.state.improperNote
        }).done(function(data) {
            window.location.href = forward_url;
        });
    }
    render() {
        var disable = !this.isReadyToPost();
        return (
            <div>
                <KeyReturn keyReturned={this.state.keyReturned} updateKeyReturned={this.updateKeyReturned} updateKeyCode={this.updateKeyCode} previousKeyCode={this.state.previousKeyCode}/>
                <ExistingDamages responsible={this.state.responsible} existingDamage={this.state.existingDamage} damageTypes={this.state.damageTypes}/>
                <NewRoomDamages damageTypes={this.state.damageTypes} residents={this.state.residents} updateNewDamage={this.updateNewDamage} newDamage={this.state.newDamage}/>
                <CheckOutCompletion updateProperCheckout={this.updateProperCheckout} updateImproperNote={this.updateImproperNote} />
                <hr />
                <p className="text-center"><button disabled={disable} className="btn btn-primary btn-lg" onClick={this.postCheckOut}><i className="fa fa-check"></i> Complete Checkout</button></p>
            </div>
        );
    }
}

class KeyReturn extends React.Component{
    constructor(props){
        super(props);

        this.state = {
            codeAlert: false,
            keyCodeInput : null
        };
        this.keyTurnIn = this.keyTurnIn.bind(this);
        this.updateKeyCode = this.updateKeyCode.bind(this);
        this.checkAlert = this.checkAlert.bind(this);
    }
    keyTurnIn(event) {
        var returned = event.target.value === 'true';
        this.props.updateKeyReturned(returned);
        if (!returned) {
            ReactDOM.findDOMNode(this.refs.keyCode).value = '';
            this.setState({
                keyCodeInput : null,
                codeAlert:false
            });
            this.props.updateKeyCode(null);
        }
    }
    updateKeyCode(keycode) {
        var keyCodeInput = keycode.target.value;
        this.setState({
            keyCodeInput : keyCodeInput
        });

        this.props.updateKeyCode(keyCodeInput);
        this.checkAlert(keyCodeInput);
    }
    checkAlert(keyCodeInput) {
        if (keyCodeInput === null || keyCodeInput.length === 0 || String(keyCodeInput) === String(this.props.previousKeyCode)) {
            this.setState({
                codeAlert : false
            });
        } else {
            this.setState({
                codeAlert : true
            });
        }
    }
    render() {
        var codeAlertDiv = <div></div>;
        if (this.state.codeAlert) {
            codeAlertDiv = <div className="alert alert-warning"><strong>Note:</strong> This keycode does not match the checkin key code: {this.props.previousKeyCode}</div>;
        }

        return (
            <div>
                <h3>Key status</h3>
                <div className="row">
                    <div className="col-sm-3">
                        <div>
                            <label>
                                <input name="keyReturned" onChange={this.keyTurnIn} type="radio" value='true'/>{' '} Key returned
                            </label>
                        </div>
                        <div>
                            <label>
                                <input name="keyReturned" onChange={this.keyTurnIn} type="radio" value='false'/>{' '} Key not returned
                            </label>
                        </div>
                    </div>
                    <div className="col-sm-3">
                        <input className="form-control" disabled={!this.props.keyReturned} id="keyCode" name="keyCode" placeholder="Enter key code" onBlur={this.updateKeyCode} type="text" ref="keyCode"/>
                    </div>
                    <div className="col-sm-6">
                        {codeAlertDiv}
                    </div>
                </div>
            </div>
        );
    }
}

class ExistingDamages extends React.Component{
    render() {
        if (this.props.existingDamage.length === 0) {
            return (
                <div>
                    <h3>Existing Room Damage</h3>
                    <p><em>No previous damage recorded.</em></p>
                </div>
            );
        }
        return (
            <div>
                <h3>Existing Room Damage</h3>
                {this.props.existingDamage.map(function(value, key){ return (
                <Damage category={this.props.damageTypes[value.damage_type].category} description={this.props.damageTypes[value.damage_type].description}
                    key={key} note={value.note} reportedOn={value.reported_on} side={value.side} residents={value.residents}/>
                ); }, this)}
            </div>
        );
    }
}

class NewRoomDamages extends React.Component{
    constructor(props){
        super(props);

        this.state = {formActive: false};
        this.addDamageForm = this.addDamageForm.bind(this);
        this.removeForm = this.removeForm.bind(this);
        this.pushDamage = this.pushDamage.bind(this);
    }
    addDamageForm() {
        var formObj = {};
        formObj.form = true;
        formObj.reportedOn = 0;
        formObj.side = '';
        formObj.damage_type = 0;
        formObj.category = '';
        formObj.description = '';
        formObj.note = '';
        formObj.residents = [];
        this.pushDamage(formObj);
    }
    removeForm() {
        var updatedDamages = this.props.newDamage;
        updatedDamages.pop();
        this.props.updateNewDamage(updatedDamages);
        this.setState({
            formActive: false
        });
    }
    pushDamage(damage) {
        var updatedDamages = this.props.newDamage;
        updatedDamages.push(damage);
        this.props.updateNewDamage(updatedDamages);
        this.setState({
            formActive: damage.form
        });
    }
    render() {
        var button = this.state.formActive ? null : <button className="btn btn-success" onClick={this.addDamageForm} autoFocus={true}>
                <i className="fa fa-plus"></i>{' '}Add damage</button>;
        return (
            <div>
                <h3>New Room Damages</h3>
                {this.props.newDamage.map(function(value, key){ if (value.form === true) { return (
                <DamageForm {...this.props} key={key} pushDamage={this.pushDamage} removeForm={this.removeForm}/>
                ); } else { return (
                <Damage category={value.category} description={value.description} key={key} note={value.note} reportedOn={value.reportedOn} side={value.side} residents={value.residents}/>
                ); } }, this)} {button}
            </div>
        );
    }
}

var DamageForm = React.createClass({

    propTypes: {
        // contains _.name, _.studentId
        residents: React.PropTypes.array
    },

    getInitialState: function() {

        var resTemp = [];
        this.props.residents.map(function(value, i){
            resTemp.push({studentId:value.studentId, selected:false});
        });
        return {
            damage_type: null,
            side: null,
            note: null,
            residents: resTemp,
            error: []
        };
    },

    errorFree: function() {
        var all_clear = true;
        var errors = [];
        var resident_selected;

        if (this.state.damage_type === null || this.state.damage_type.length === 0) {
            errors.push('category');
            all_clear = false;
        } else {
            var catError = $.inArray('category', errors);
            if (catError !== -1) {
                errors.splice(catError, 1);
            }
        }
        resident_selected = this.state.residents.some(function(value,key){
            return value.selected;
        });

        if (!resident_selected) {
            all_clear = false;
            errors.push('resident');
        } else {
            var resError = $.inArray('resident', errors);
            if (resError !== -1) {
                errors.splice(resError, 1);
            }
        }

        if (this.state.side === null || this.state.side.length === 0) {
            errors.push('side');
            all_clear = false;
        } else {
            var sideError = $.inArray('side', errors);
            if (sideError !== -1) {
                errors.splice(sideError, 1);
            }
        }

        if (this.state.note === null || this.state.note.length === 0) {
            errors.push('note');
            all_clear = false;
        } else {
            var noteError = $.inArray('note', errors);
            if (noteError !== -1) {
                errors.splice(noteError, 1);
            }
        }

        this.setState({
            error : errors,
            render: true
        });

        return all_clear;
    },

    shouldComponentUpdate: function(nextProps, nextState) {
        if (nextState.render !== undefined) {
            return nextState.render;
        } else {
            return true;
        }
    },


// category is saved as damage_type, which is the actual id, not the
// category/description that is displayed
    categorySelected: function(selected) {
        this.setState({
            render: false,
            damage_type: selected.target.value
        });
    },

    sideSelected: function(selected) {
        this.setState({
            render: false,
            side: selected.target.value
        });
    },

    residentSelected: function(selected) {
        var updatedResidents = this.state.residents;
        updatedResidents.map(function(value,key){
            if (value.studentId === selected.target.value) {
                updatedResidents[key].selected = selected.target.checked;
            }
        });
        this.setState({
            render: false,
            residents: updatedResidents
        });
    },

    updateNote: function(note) {
        this.setState({
            render: false,
            note: note.target.value
        });
    },

    saveDamage: function() {
        if (this.errorFree()) {
            var damage = {};
            damage.form = false;
            damage.reportedOn = Math.floor(Date.now() / 1000);
            damage.side = this.state.side;
            damage.damage_type = this.state.damage_type;
            damage.category = this.props.damageTypes[damage.damage_type].category;
            damage.description = this.props.damageTypes[damage.damage_type].description;
            damage.note = this.state.note;
            damage.residents = [];
            this.state.residents.map(function(val, key){
                if (val.selected) {
                    this.props.residents.map(function(subval, i){
                        if(subval.studentId === val.studentId) {
                            val.name = subval.name;
                            damage.residents.push(val);
                        }
                    });
                }
            }, this);
            this.props.removeForm();
            this.props.pushDamage(damage);
        }

    },

    render: function() {
        var alert = null;
        var residentClass = 'col-sm-3 ' + ($.inArray('resident', this.state.error) !== -1 ? 'checkout-error-border' : null);
        var noteClass = 'form-control ' + ($.inArray('note', this.state.error) !== -1 ? 'checkout-error-border' : null);
        if (this.state.error.length) {
            alert = <div className="alert alert-danger">Please complete all highlighted fields.</div>;
        }

        return (
            <div className="panel panel-default" id="newDamageForm">
                <div className="panel-body">
                    <div className="row">
                        <div className={residentClass}>
                            <h4>Responsible</h4>
                            {this.props.residents.map(function(val, key){return (
                            <ResidentCheckbox handleChange={this.residentSelected} key={key} name={val.name} value={val.studentId} autoFocus={key === 0}/>
                            ); }, this)}
                        </div>
                        <div className="col-sm-9">
                            <h4>Details</h4>
                            <div className="row">
                                <div className='col-sm-3'>
                                    <SideSelect onChange={this.sideSelected} error={this.state.error} />
                                </div>
                                <div className='col-sm-9'>
                                    <DamageTypeSelect damage_types={this.props.damage_types} damageTypes={this.props.damageTypes} onChange={this.categorySelected} error={this.state.error}/>
                                </div>
                            </div>
                            <div className="row" style={{marginTop: '1em'}}>
                                <div className="col-sm-12">
                                    <input className={noteClass} maxLength="200" name="note" onChange={this.updateNote} placeholder="Brief description of damage..." type="text"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr/>
                    <div className="text-center">
                        {alert}
                        <button className="btn btn-primary" onClick={this.saveDamage}>
                            <i className="fa fa-floppy-o"></i>{' '}Save damages
                        </button>{' '}
                        <button className="btn btn-danger-hover" onClick={this.props.removeForm}>
                            <i className="fa fa-times"></i>{' '}Remove
                        </button>
                    </div>
                </div>
            </div>
        );
    }
});

var ResidentCheckbox = React.createClass({
    render: function() {
        return (
            <div className="checkbox">
                <label>
                    <input onChange={this.props.handleChange} type="checkbox" value={this.props.value} autoFocus={this.props.autoFocus}/>{' '}{this.props.name}
                </label>
            </div>
        );
    }
});

var DamageTypeSelect = React.createClass({
    getInitialState: function() {
        var damageOptions = this.buildDamageOptions(this.props.damage_types);
        return {
            damageOptions: damageOptions
        };
    },

    buildDamageOptions : function(damageTypes) {
        var damageOptions = {};
        Object.keys(this.props.damage_types).map(function(key, idx){
            var opt = {};
            var value = this.props.damage_types[key];
            opt.description = value.description;
            opt.id = value.id;
            if (damageOptions[value.category] === undefined) {
                damageOptions[value.category] = [];
            }
            damageOptions[value.category].push(opt);
        });
        return damageOptions;
    },

    render: function() {
        var damageClass = 'form-control ' + ($.inArray('category', this.props.error) !== -1 ? 'checkout-error-border' : null);
        return (
            <select className={damageClass} onChange={this.props.onChange}>
                <option></option>
                {Object.keys(this.state.damageOptions).map(function(category, idx){ return(
                <optgroup key={idx} label={category}>
                    {this.state.damageOptions[category].map(function(optValue, key){ return (
                    <option key={key} value={optValue.id}>{optValue.description}</option>
                    ); })}
                </optgroup>
                ); }, this)}
            </select>
        );
    }
});

var SideSelect = React.createClass({
    render: function() {
        var sideClass = 'form-control damage-select ' + ($.inArray('side', this.props.error) !== -1 ? 'checkout-error-border' : null);
        return (
            <select className={sideClass} onChange={this.props.onChange}>
                <option></option>
                <option value="Left">Left side</option>
                <option value="Right">Right side</option>
                <option value="Both">Both sides</option>
            </select>
        );
    }
});

var Damage = React.createClass({

    render: function() {
        var residentList = '';
        if (this.props.residents !== undefined) {
            this.props.residents.map(function(val){
                residentList = residentList.length ? residentList + ', ' + val.name : val.name;
            });
        }
        var reportedOn = new Date();
        var month = 1;
        reportedOn.setTime(this.props.reportedOn * 1000);
        month = reportedOn.getMonth() + 1;
        return (
            <div className="panel panel-danger">
                <div className="panel-heading">
                    <div className="row">
                        <div className="col-sm-6"><strong>Damage:</strong> {this.props.category} - {this.props.description}</div>
                        <div className="col-sm-2"><strong>Side:</strong> {this.props.side}</div>
                        <div className="col-sm-4"><strong>Reported on:</strong> {reportedOn.getFullYear() + '/' + month + '/' + reportedOn.getDate()}</div>
                    </div>
                </div>
                <div className="panel-body">
                    <p><strong>Description:</strong> {this.props.note}</p>
                    {residentList.length ? <p><strong>Responsibility:</strong> {residentList}</p> : null}
                    {this.props.removable ? <button>Remove</button> : null}
                </div>
            </div>
        );
    }
});

var CheckOutCompletion = React.createClass({
    getInitialState: function() {
        return {
            noteDisabled : true
        };
    },

    handleChange: function(event) {
        this.props.updateProperCheckout(event.target.value);
        if (event.target.value === '0') {
            this.setState({
                noteDisabled : false
            });
        } else {
            ReactDOM.findDOMNode(this.refs.checkoutNotes).value = '';
            this.setState({
                noteDisabled : true
            });
        }
    },

    render: function() {
        return (
            <div>
                <h3>Final Checkout</h3>
                <div className="radio">
                    <label>
                        <input onChange={this.handleChange} type="radio" name="properCheckout" value="1"/>{' '}Proper Checkout
                    </label>
                </div>
                <div className="radio">
                    <label>
                        <input onChange={this.handleChange} type="radio"  name="properCheckout" value="0"/>{' '}Improper Checkout
                    </label>
                </div>
                <textarea ref="checkoutNotes" className="form-control" name="improperCheckoutNote" placeholder="Please explain why this was an improper checkout" onChange={this.props.updateImproperNote} disabled={this.state.noteDisabled} />
            </div>
        );
    }

});

// This script will not run after compiled UNLESS the below is wrapped in $(window).load(function(){...});
ReactDOM.render(<CheckOut residents={window.residents} existing_damage={window.existing_damage} previous_key_code={window.previous_key_code}
damage_types={window.damage_types} checkin_id={window.checkin_id} banner_id={window.banner_id}/>, document.getElementById('checkout'));
