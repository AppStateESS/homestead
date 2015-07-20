var CheckOut = React.createClass({displayName: "CheckOut",
    getInitialState: function() {
        // damage_types, existing_damage and residents are plugged in by the CheckOut.html template
        return {
            previousKeyCode: previous_key_code,
            keyReturned: null,
            keyCode : null,
            existingDamage: existing_damage,
            newDamage: [],
            residents: residents,
            damageTypes: damage_types,
            properCheckout : null,
            improperNote : '',
            checkinId: checkin_id,
            bannerId: banner_id
        };
    },

    updateKeyReturned: function(key) {
        this.setState({
            keyReturned: key
        });
    },

    updateKeyCode: function(code)
    {
        this.setState({
            keyCode : code
        });
    },

    updateNewDamage: function(damage)
    {
        this.setState({
            newDamage : damage
        });
    },

    updateProperCheckout: function(value)
    {
        var checkout = Number(value);
        if (checkout === 0) {
            this.setState({
                properCheckout : checkout,
                improperNote : ''
            });
        } else {
            this.setState({
                properCheckout : checkout
            });
        }
    },

    updateImproperNote: function(note)
    {
        this.setState({
            improperNote : note.target.value
        });
    },

    isReadyToPost: function()
    {
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
    },

    postCheckOut: function() {
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
    },

    render: function() {
        var disable = !this.isReadyToPost();
        return (
            React.createElement("div", null,
                React.createElement(KeyReturn, {keyReturned: this.state.keyReturned, updateKeyReturned: this.updateKeyReturned, updateKeyCode: this.updateKeyCode, previousKeyCode: this.state.previousKeyCode}),
                React.createElement(ExistingDamages, {responsible: this.state.responsible, existingDamage: this.state.existingDamage, damageTypes: this.state.damageTypes}),
                React.createElement(NewRoomDamages, {damageTypes: this.state.damageTypes, residents: this.state.residents, updateNewDamage: this.updateNewDamage, newDamage: this.state.newDamage}),
                React.createElement(CheckOutCompletion, {updateProperCheckout: this.updateProperCheckout, updateImproperNote: this.updateImproperNote}),
                React.createElement("hr", null),
                React.createElement("p", {className: "text-center"}, React.createElement("button", {disabled: disable, className: "btn btn-primary btn-lg", onClick: this.postCheckOut}, React.createElement("i", {className: "fa fa-check"}), " Complete Checkout"))
            )
        );
    }
});

var KeyReturn = React.createClass({displayName: "KeyReturn",
    getInitialState: function() {
        return {
            codeAlert: false,
            keyCodeInput : null
        };
    },

    keyTurnIn: function(event) {
        var returned = event.target.value == 'true';
        this.props.updateKeyReturned(returned);
        if (!returned) {
            React.findDOMNode(this.refs.keyCode).value = '';
            this.setState({
                keyCodeInput : null,
                codeAlert:false
            });
            this.props.updateKeyCode(null);
        }
    },

    updateKeyCode: function(keycode) {
        var keyCodeInput = keycode.target.value;
        this.setState({
            keyCodeInput : keyCodeInput
        });

        this.props.updateKeyCode(keyCodeInput);
        this.checkAlert(keyCodeInput);
    },

    checkAlert: function(keyCodeInput) {
        if (keyCodeInput === null || keyCodeInput.length === 0 || String(keyCodeInput) === String(this.props.previousKeyCode)) {
            this.setState({
                codeAlert : false
            });
        } else {
            this.setState({
                codeAlert : true
            });
        }
    },

    render: function() {
        var codeAlertDiv = React.createElement("div", null);
        if (this.state.codeAlert) {
            codeAlertDiv = React.createElement("div", {className: "alert alert-warning"}, React.createElement("strong", null, "Note:"), " This keycode does not match the checkin key code: ", this.props.previousKeyCode);
        }

        return (
            React.createElement("div", null,
                React.createElement("h3", null, "Key status"),
                React.createElement("div", {className: "row"},
                    React.createElement("div", {className: "col-sm-3"},
                        React.createElement("div", null,
                            React.createElement("label", null,
                                React.createElement("input", {name: "keyReturned", onChange: this.keyTurnIn, type: "radio", value: "true"}), ' ', " Key returned"
                            )
                        ),
                        React.createElement("div", null,
                            React.createElement("label", null,
                                React.createElement("input", {name: "keyReturned", onChange: this.keyTurnIn, type: "radio", value: "false"}), ' ', " Key not returned"
                            )
                        )
                    ),
                    React.createElement("div", {className: "col-sm-3"},
                        React.createElement("input", {className: "form-control", disabled: !this.props.keyReturned, id: "keyCode", name: "keyCode", placeholder: "Enter key code", onBlur: this.updateKeyCode, type: "text", ref: "keyCode"})
                    ),
                    React.createElement("div", {className: "col-sm-6"},
                        codeAlertDiv
                    )
                )
            )
        );
    }

});

var ExistingDamages = React.createClass({displayName: "ExistingDamages",
    render: function() {
        console.log(this.props.existingDamage);
        if (this.props.existingDamage.length === 0) {
            return (
                React.createElement("div", null,
                    React.createElement("h3", null, "Existing Room Damage"),
                    React.createElement("p", null, React.createElement("em", null, "No previous damage recorded."))
                )
            );
        }
        return (
            React.createElement("div", null,
                React.createElement("h3", null, "Existing Room Damage"),
                this.props.existingDamage.map(function(value, key){ return (
                React.createElement(Damage, {category: this.props.damageTypes[value.damage_type].category, description: this.props.damageTypes[value.damage_type].description,
                    key: key, note: value.note, reportedOn: value.reported_on, side: value.side, residents: value.residents})
                ); }, this)
            )
        );
    }
});

var NewRoomDamages = React.createClass({displayName: "NewRoomDamages",
    getInitialState: function() {
        return {
            formActive: false
        };
    },

    addDamageForm: function() {
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
    },

    removeForm: function() {
        var updatedDamages = this.props.newDamage;
        updatedDamages.pop();
        this.props.updateNewDamage(updatedDamages);
        this.setState({
            formActive: false
        });
    },

    pushDamage: function(damage) {
        var updatedDamages = this.props.newDamage;
        updatedDamages.push(damage);
        this.props.updateNewDamage(updatedDamages);
        this.setState({
            formActive: damage.form
        });
    },

    render: function() {
        var button = this.state.formActive ? null : React.createElement("button", {className: "btn btn-success", onClick: this.addDamageForm, autoFocus: true},
                React.createElement("i", {className: "fa fa-plus"}), ' ', "Add damage");
        return (
            React.createElement("div", null,
                React.createElement("h3", null, "New Room Damages"),
                this.props.newDamage.map(function(value, key){ if (value.form === true) { return (
                React.createElement(DamageForm, React.__spread({},  this.props, {key: key, pushDamage: this.pushDamage, removeForm: this.removeForm}))
                ); } else { return (
                React.createElement(Damage, {category: value.category, description: value.description, key: key, note: value.note, reportedOn: value.reportedOn, side: value.side, residents: value.residents})
                ); } }, this), " ", button
            )
        );
    }

});

var DamageForm = React.createClass({displayName: "DamageForm",

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
            if (value.studentId == selected.target.value) {
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
                        if(subval.studentId == val.studentId) {
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
            alert = React.createElement("div", {className: "alert alert-danger"}, "Please complete all highlighted fields.");
        }

        return (
            React.createElement("div", {className: "panel panel-default", id: "newDamageForm"},
                React.createElement("div", {className: "panel-body"},
                    React.createElement("div", {className: "row"},
                        React.createElement("div", {className: residentClass},
                            React.createElement("h4", null, "Responsible"),
                            this.props.residents.map(function(val, key){return (
                            React.createElement(ResidentCheckbox, {handleChange: this.residentSelected, key: key, name: val.name, value: val.studentId, autoFocus: key === 0})
                            ); }, this)
                        ),
                        React.createElement("div", {className: "col-sm-9"},
                            React.createElement("h4", null, "Details"),
                            React.createElement("div", {className: "row"},
                                React.createElement("div", {className: "col-sm-3"},
                                    React.createElement(SideSelect, {onChange: this.sideSelected, error: this.state.error})
                                ),
                                React.createElement("div", {className: "col-sm-9"},
                                    React.createElement(DamageTypeSelect, {damageTypes: this.props.damageTypes, onChange: this.categorySelected, error: this.state.error})
                                )
                            ),
                            React.createElement("div", {className: "row", style: {marginTop: '1em'}},
                                React.createElement("div", {className: "col-sm-12"},
                                    React.createElement("input", {className: noteClass, maxLength: "200", name: "note", onChange: this.updateNote, placeholder: "Brief description of damage...", type: "text"})
                                )
                            )
                        )
                    ),
                    React.createElement("hr", null),
                    React.createElement("div", {className: "text-center"},
                        alert,
                        React.createElement("button", {className: "btn btn-primary", onClick: this.saveDamage},
                            React.createElement("i", {className: "fa fa-floppy-o"}), ' ', "Save damages"
                        ), ' ',
                        React.createElement("button", {className: "btn btn-danger-hover", onClick: this.props.removeForm},
                            React.createElement("i", {className: "fa fa-times"}), ' ', "Remove"
                        )
                    )
                )
            )
        );
    }
});

var ResidentCheckbox = React.createClass({displayName: "ResidentCheckbox",
    render: function() {
        return (
            React.createElement("div", {className: "checkbox"},
                React.createElement("label", null,
                    React.createElement("input", {onChange: this.props.handleChange, type: "checkbox", value: this.props.value, autoFocus: this.props.autoFocus}), ' ', this.props.name
                )
            )
        );
    }
});

var DamageTypeSelect = React.createClass({displayName: "DamageTypeSelect",
    getInitialState: function() {
        var damageOptions = this.buildDamageOptions(damage_types);
        return {
            damageOptions: damageOptions
        };
    },

    buildDamageOptions : function(damageTypes) {
        var damageOptions = {};
        Object.keys(damage_types).map(function(key, idx){
            var opt = {};
            var value = damage_types[key];
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
            React.createElement("select", {className: damageClass, onChange: this.props.onChange},
                React.createElement("option", null),
                Object.keys(this.state.damageOptions).map(function(category, idx){ return(
                React.createElement("optgroup", {key: idx, label: category},
                    this.state.damageOptions[category].map(function(optValue, key){ return (
                    React.createElement("option", {key: key, value: optValue.id}, optValue.description)
                    ); })
                )
                ); }, this)
            )
        );
    }
});

var SideSelect = React.createClass({displayName: "SideSelect",
    render: function() {
        var sideClass = 'form-control damage-select ' + ($.inArray('side', this.props.error) !== -1 ? 'checkout-error-border' : null);
        return (
            React.createElement("select", {className: sideClass, onChange: this.props.onChange},
                React.createElement("option", null),
                React.createElement("option", {value: "Left"}, "Left side"),
                React.createElement("option", {value: "Right"}, "Right side"),
                React.createElement("option", {value: "Both"}, "Both sides")
            )
        );
    }
});

var Damage = React.createClass({displayName: "Damage",

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
            React.createElement("div", {className: "panel panel-danger"},
                React.createElement("div", {className: "panel-heading"},
                    React.createElement("div", {className: "row"},
                        React.createElement("div", {className: "col-sm-6"}, React.createElement("strong", null, "Damage:"), " ", this.props.category, " - ", this.props.description),
                        React.createElement("div", {className: "col-sm-2"}, React.createElement("strong", null, "Side:"), " ", this.props.side),
                        React.createElement("div", {className: "col-sm-4"}, React.createElement("strong", null, "Reported on:"), " ", reportedOn.getFullYear() + '/' + month + '/' + reportedOn.getDate())
                    )
                ),
                React.createElement("div", {className: "panel-body"},
                    React.createElement("p", null, React.createElement("strong", null, "Description:"), " ", this.props.note),
                    residentList.length ? React.createElement("p", null, React.createElement("strong", null, "Responsibility:"), " ", residentList) : null,
                    this.props.removable ? React.createElement("button", null, "Remove") : null
                )
            )
        );
    }
});

var CheckOutCompletion = React.createClass({displayName: "CheckOutCompletion",
    getInitialState: function() {
        return {
            noteDisabled : true
        };
    },

    handleChange: function(event) {
        this.props.updateProperCheckout(event.target.value);
        if (event.target.value == '0') {
            this.setState({
                noteDisabled : false
            });
        } else {
            React.findDOMNode(this.refs.checkoutNotes).value = '';
            this.setState({
                noteDisabled : true
            });
        }
    },

    render: function() {
        return (
            React.createElement("div", null,
                React.createElement("h3", null, "Final Checkout"),
                React.createElement("div", {className: "radio"},
                    React.createElement("label", null,
                        React.createElement("input", {onChange: this.handleChange, type: "radio", name: "properCheckout", value: "1"}), ' ', "Proper Checkout"
                    )
                ),
                React.createElement("div", {className: "radio"},
                    React.createElement("label", null,
                        React.createElement("input", {onChange: this.handleChange, type: "radio", name: "properCheckout", value: "0"}), ' ', "Improper Checkout"
                    )
                ),
                React.createElement("textarea", {ref: "checkoutNotes", className: "form-control", name: "improperCheckoutNote", placeholder: "Please explain why this was an improper checkout", onChange: this.props.updateImproperNote, disabled: this.state.noteDisabled})
            )
        );
    }

});

// This script will not run after compiled UNLESS the below is wrapped in $(window).load(function(){...});
$(window).load(function(){
    React.render(React.createElement(CheckOut, null), document.getElementById('checkout'));
});
