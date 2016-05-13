
var RlcApplicantsBox = React.createClass({
    getInitialState: function()
    {
        return {communities         : [],
                communityFilter     : 0,
                studentTypeFilter   : '0',
                firstChoice         : true,
                secondChoice        : true,
                thirdChoice         : true,
                response            : undefined,
                showModal           : false,
                modalAppId          : -1
        };
    },
    componentWillMount: function()
    {
        this.getCommunities();
    },
    getCommunities: function()
    {
        $.ajax({
            url:        'index.php?module=hms&action=AjaxGetCommunities',
            type:       'GET',
            dataType:   'json',
            success: function(data)
            {
                this.setState({communities: data});
            }.bind(this),
            error: function(xhr, status, err)
            {

            }.bind(this)
        });
    },
    changeCFilter: function(newFilter)
    {
        this.setState({communityFilter: newFilter});
    },
    changeSTFilter: function(newFilter)
    {
        console.log(newFilter)
        this.setState({studentTypeFilter: newFilter});
    },
    toggleChoice: function(choice)
    {
        if(choice == 'first')
        {
            this.setState({firstChoice: !this.state.firstChoice})
        }
        else if(choice == 'second')
        {
            this.setState({secondChoice: !this.state.secondChoice})
        }
        else if(choice == 'third')
        {
            this.setState({thirdChoice: !this.state.thirdChoice})
        }
    },
    showModal: function(app_id)
    {
        this.setState({showModal    : true,
                       modalAppId   : app_id});
    },
    closeModal: function()
    {
        this.setState({showModal    : false});
    },
    render: function()
    {
        var choiceToggles = (<div></div>);
        var modalApp      = (<div></div>);

        console.log(this.state.showModal)
        var thisTerm = term;
        if(this.state.communityFilter != 0)
        {
            choiceToggles = (<ChoiceFilter firstChoice={this.state.firstChoice}
                                           secondChoice={this.state.secondChoice}
                                           thirdChoice={this.state.thirdChoice}
                                           toggleChoice={this.toggleChoice}/>
            );
        }
        if(this.state.showModal)
        {
            modalApp = (<ModalApp show={this.state.showModal}
                                  close={this.closeModal}
                                  appId={this.state.modalAppId} />);
        }


        return(
            <div>
                {modalApp}
                <div class="row">
                    <NotificationBox response={this.state.response}/>
                </div>
                <h2>RLC Assignments - {thisTerm}</h2>
                <h3>Applicants</h3>
                <div className="row">
                    <div className="col-md-6">
                        <CommunityFilter changeCFilter={this.changeCFilter} communities={this.state.communities}/>
                    </div>
                    <div className="col-md-6">
                        <StudentTypeFilter changeSTFilter={this.changeSTFilter}/>
                    </div>
                </div>
                <div>
                    {choiceToggles}
                </div>
                <hr></hr>
                <div>
                    <ApplicantsTable communities={this.state.communities}
                                     communityFilter={this.state.communityFilter}
                                     studentTypeFilter={this.state.studentTypeFilter}
                                     firstChoice={this.state.firstChoice}
                                     secondChoice={this.state.secondChoice}
                                     thirdChoice={this.state.thirdChoice}
                                     showModal={this.showModal}/>
                </div>
                <div>
                    <ApplicationExport communities={this.state.communities}/>
                </div>
            </div>
        );
    }
});

var NotificationBox = React.createClass({
    render: function()
    {
        if(this.props.response == undefined)
        {
            return (<div></div>);
        }
        var error = !this.props.response.success;
        var success = this.props.response.success;
        var message = this.props.response.message;

        var notificationClasses = classNames({
            'alert'         : true,
            'alert-danger'  : error,
            'alert-success' : success
        })
        return (
            <div className={notificationClasses} role="alert">
                <i className="fa fa-times fa-2x"></i> <span>{message}</span>
            </div>
        );
    }
});

var CommunityFilter = React.createClass({
    changeFilter: function()
    {
        var newFilter = this.refs.communityFilter.getDOMNode().value;
        this.props.changeCFilter(newFilter);
    },
    render: function()
    {
        var communities = this.props.communities;
        var data = Array({cId: 0, cName: 'All'});
        var i = 0;
        for(i; i < communities.length; i++)
        {
            data.push(communities[i]);
        }

        var communityOptions = data.map(function(node){
            return(
                <option value={node.cId}>{node.cName}</option>
            );
        });
        return(
            <div>
                <label><h4>Limit to Community:</h4></label>
                <select onChange={this.changeFilter} className="form-control" ref="communityFilter">
                    {communityOptions}
                </select>
            </div>
        );
    }
});

var StudentTypeFilter = React.createClass({
    changeFilter: function()
    {
        var newFilter = this.refs.studentTypeFilter.getDOMNode().value;
        this.props.changeSTFilter(newFilter);
    },
    render: function()
    {
        return(
            <div>
                <label><h4>Student Type:</h4></label>
                <select onChange={this.changeFilter} className="form-control" ref="studentTypeFilter">
                    <option value='0'>All</option>
                    <option value='C'>Continuing</option>
                    <option value='F'>Freshmen</option>
                </select>
            </div>
        );
    }
});

var ChoiceFilter = React.createClass({
    firstToggle: function()
    {
        this.props.toggleChoice('first');
    },
    secondToggle: function()
    {
        this.props.toggleChoice('second');
    },
    thirdToggle: function()
    {
        this.props.toggleChoice('third');
    },
    render: function()
    {
        var first = this.props.firstChoice;
        var second = this.props.secondChoice;
        var third = this.props.thirdChoice;

        // Set the list toggle class via classNames
        var firstClasses = classNames({
            'btn'           : true,
            'btn-default'   : true,
            'active'    : first
        });

        // Set the add toggle class via classNames
        var secondClasses = classNames({
            'btn'           : true,
            'btn-default'   : true,
            'active'        : second
        });

        // Set the remove toggle class via classNames
        var thirdClasses = classNames({
            'btn'           : true,
            'btn-default'   : true,
            'active'        : third
        });

        return(
            <div>
                    <label><h4>Limit by Choice:</h4></label>
                    <div className="row">
                    <div className="btn-group col-md-6">
                        <label onClick={this.firstToggle} className={firstClasses}>
                            First Choice
                        </label>
                        <label onClick={this.secondToggle} className={secondClasses}>
                            Second Choice
                        </label>
                        <label onClick={this.thirdToggle} className={thirdClasses}>
                            Third Choice
                        </label>
                    </div>
                </div>
            </div>
        );
    }
})

var ApplicantsTable = React.createClass({
    getInitialState: function()
    {
        return {applicants: [], currentSort: '', sortDirection: ''};
    },
    componentWillMount: function()
    {
        this.getApplicants(this.props);
    },
    componentWillReceiveProps: function(nextProps)
    {
        this.getApplicants(nextProps);
    },
    denyApplicant: function(appId)
    {
        $.ajax({
            url: 'index.php?module=hms&action=AjaxDenyRlcApplication&applicationId=' + appId,
            type: 'POST',
            success: function()
            {
                this.getApplicants(this.props);
            }.bind(this),
            error: function(xhr, status, err)
            {

            }.bind(this)
        });
    },

    // The ajax request gets different props based on whether it is initial mount or
    // if the props are changing, if they are changing then this.props returns the old props
    // so it needs to be passed as a param from the componentWillReceiveProps function which
    // has access to the new props.
    getApplicants: function(props)
    {
        var inputData = {communityFilter    : props.communityFilter,
                         studentTypeFilter  : props.studentTypeFilter,
                         firstChoice        : props.firstChoice,
                         secondChoice       : props.secondChoice,
                         thirdChoice        : props.thirdChoice
        };

        $.ajax({
            url:        'index.php?module=hms&action=AjaxGetRLCApplicants',
            type:       'POST',
            dataType:   'json',
            data:       inputData,
            success: function(data)
            {
                this.setState({applicants: data});
            }.bind(this),
            error: function(xhr, status, err)
            {

            }.bind(this)
        });
    },
    nameSort: function()
    {
        var applicantsArr = this.state.applicants;
        var currentSort = this.state.currentSort;
        var sortDirection = this.state.sortDirection;
        var newSortDirection = '';

        if (sortDirection == 'DESCENDING' || currentSort != 'names')
        {
            applicantsArr.sort(function(a, b)
            {
                var lastNameA = a.last_name.toLowerCase();
                var lastNameB = b.last_name.toLowerCase();
                if (lastNameA < lastNameB)
                { //sort string ascending
                    return -1;
                }
                else if (lastNameA > lastNameB)
                {
                    return 1;
                }
                else {
                    var firstNameA = a.first_name.toLowerCase();
                    var firstNameB = b.first_name.toLowerCase();
                    if(firstNameA < firstNameB)
                    {
                        return -1;
                    }
                    else if(firstNameA > firstNameB)
                    {
                        return 1;
                    }
                    else
                    {
                        return 0;
                    }
                }
            });

            newSortDirection = 'ASCENDING';
        }
        else
        {
            applicantsArr.sort(function(a, b)
            {
                var lastNameA = a.last_name.toLowerCase();
                var lastNameB = b.last_name.toLowerCase();
                if (lastNameA > lastNameB)
                { //sort string descending
                    return -1;
                }
                else if (lastNameA < lastNameB)
                {
                    return 1;
                }
                else {
                    var firstNameA = a.first_name.toLowerCase();
                    var firstNameB = b.first_name.toLowerCase();
                    if(firstNameA > firstNameB)
                    {
                        return -1;
                    }
                    else if(firstNameA < firstNameB)
                    {
                        return 1;
                    }
                    else
                    {
                        return 0;
                    }
                }
            });
            newSortDirection = 'DESCENDING';
        }

        this.setState({applicants: applicantsArr, currentSort: 'names', sortDirection: newSortDirection});
    },
    firstChoiceSort: function()
    {
        var applicantsArr = this.state.applicants;
        var currentSort = this.state.currentSort;
        var sortDirection = this.state.sortDirection;
        var newSortDirection = '';

        if (sortDirection == 'DESCENDING' || currentSort != 'firstChoice')
        {
            applicantsArr.sort(function(a, b)
            {
                var firstChoiceA = a.first_choice.toLowerCase();
                var firstChoiceB = b.first_choice.toLowerCase();
                if (firstChoiceA < firstChoiceB)
                { //sort string ascending
                    return -1;
                }
                else if (firstChoiceA > firstChoiceB)
                {
                    return 1;
                }
                else {
                    return 0;
                }
            });

            newSortDirection = 'ASCENDING';
        }
        else
        {
            applicantsArr.sort(function(a, b)
            {
                var firstChoiceA = a.first_choice.toLowerCase();
                var firstChoiceB = b.first_choice.toLowerCase();
                if (firstChoiceA > firstChoiceB)
                { //sort string descending
                    return -1;
                }
                else if (firstChoiceA < firstChoiceB)
                {
                    return 1;
                }
                else {
                    return 0;
                }
            });
            newSortDirection = 'DESCENDING';
        }

        this.setState({applicants: applicantsArr, currentSort: 'firstChoice', sortDirection: newSortDirection});
    },
    secondChoiceSort: function()
    {
        var applicantsArr = this.state.applicants;
        var currentSort = this.state.currentSort;
        var sortDirection = this.state.sortDirection;
        var newSortDirection = '';

        if (sortDirection == 'DESCENDING' || currentSort != 'secondChoice')
        {
            applicantsArr.sort(function(a, b)
            {
                var secondChoiceA = a.second_choice.toLowerCase();
                var secondChoiceB = b.second_choice.toLowerCase();
                if (secondChoiceA < secondChoiceB)
                { //sort string ascending
                    return -1;
                }
                else if (secondChoiceA > secondChoiceB)
                {
                    return 1;
                }
                else {
                    return 0;
                }
            });

            newSortDirection = 'ASCENDING';
        }
        else
        {
            applicantsArr.sort(function(a, b)
            {
                var secondChoiceA = a.second_choice.toLowerCase();
                var secondChoiceB = b.second_choice.toLowerCase();
                if (secondChoiceA > secondChoiceB)
                { //sort string descending
                    return -1;
                }
                else if (secondChoiceA < secondChoiceB)
                {
                    return 1;
                }
                else {
                    return 0;
                }
            });
            newSortDirection = 'DESCENDING';
        }

        this.setState({applicants: applicantsArr, currentSort: 'secondChoice', sortDirection: newSortDirection});
    },
    thirdChoiceSort: function()
    {
        var applicantsArr = this.state.applicants;
        var currentSort = this.state.currentSort;
        var sortDirection = this.state.sortDirection;
        var newSortDirection = '';

        if (sortDirection == 'DESCENDING' || currentSort != 'thirdChoice')
        {
            applicantsArr.sort(function(a, b)
            {
                var thirdChoiceA = a.third_choice.toLowerCase();
                var thirdChoiceB = b.third_choice.toLowerCase();
                if (thirdChoiceA < thirdChoiceB)
                { //sort string ascending
                    return -1;
                }
                else if (thirdChoiceA > thirdChoiceB)
                {
                    return 1;
                }
                else {
                    return 0;
                }
            });

            newSortDirection = 'ASCENDING';
        }
        else
        {
            applicantsArr.sort(function(a, b)
            {
                var thirdChoiceA = a.third_choice.toLowerCase();
                var thirdChoiceB = b.third_choice.toLowerCase();
                if (thirdChoiceA > thirdChoiceB)
                { //sort string descending
                    return -1;
                }
                else if (thirdChoiceA < thirdChoiceB)
                {
                    return 1;
                }
                else {
                    return 0;
                }
            });
            newSortDirection = 'DESCENDING';
        }

        this.setState({applicants: applicantsArr, currentSort: 'thirdChoice', sortDirection: newSortDirection});
    },
    genderSort: function()
    {
        var applicantsArr = this.state.applicants;
        var currentSort = this.state.currentSort;
        var sortDirection = this.state.sortDirection;
        var newSortDirection = '';

        if (sortDirection == 'DESCENDING' || currentSort != 'gender')
        {
            applicantsArr.sort(function(a, b)
            {
                var genderA = a.gender.toLowerCase();
                var genderB = b.gender.toLowerCase();
                if (genderA < genderB)
                { //sort string ascending
                    return -1;
                }
                else if (genderA > genderB)
                {
                    return 1;
                }
                else {
                    return 0;
                }
            });

            newSortDirection = 'ASCENDING';
        }
        else
        {
            applicantsArr.sort(function(a, b)
            {
                var genderA = a.gender.toLowerCase();
                var genderB = b.gender.toLowerCase();
                if (genderA > genderB)
                { //sort string descending
                    return -1;
                }
                else if (genderA < genderB)
                {
                    return 1;
                }
                else {
                    return 0;
                }
            });
            newSortDirection = 'DESCENDING';
        }

        this.setState({applicants: applicantsArr, currentSort: 'gender', sortDirection: newSortDirection});
    },
    appDateSort: function()
    {

        var applicantsArr = this.state.applicants;
        var currentSort = this.state.currentSort;
        var sortDirection = this.state.sortDirection;
        var newSortDirection = '';


        if (sortDirection == 'DESCENDING' || currentSort != 'appDate')
        {
            applicantsArr.sort(function(a, b){
                var dateA = new Date(a.unix_date);
                var dateB = new Date(b.unix_date);
                return dateA - dateB; //sort by date ascending
            });

            newSortDirection = 'ASCENDING';
        }
        else
        {
            applicantsArr.sort(function(a, b){
                var dateA = new Date(a.unix_date);
                var dateB = new Date(b.unix_date);
                return dateB - dateA; //sort by date descending
            });
            newSortDirection = 'DESCENDING';
        }

        this.setState({applicants: applicantsArr, currentSort: 'appDate', sortDirection: newSortDirection});
    },
    render: function()
    {
        var data = this.state.applicants;

        console.log(data)

        if(data.length == 0)
        {
            return (<p>No applicants found using the current filters.</p>)
        }

        var rlcs = this.props.communities;
        var applicantRows = data.map(function(node){
            return (
                <ApplicantRow node={node}
                              communities={rlcs}
                              denyApp={this.denyApplicant}
                              saveCommunity={this.saveCommunityToApplicant}
                              showModal={this.props.showModal}/>
            );
        }.bind(this));

        var nameSortLink = (<i className="fa fa-sort"></i>);
        if(this.state.currentSort == 'names')
        {
            if(this.state.sortDirection == 'ASCENDING')
            {
                var nameSortLink = (<i className="fa fa-caret-up"></i>);
            }
            else if(this.state.sortDirection == 'DESCENDING')
            {
                var nameSortLink = (<i className="fa fa-caret-down"></i>);
            }
        }

        var firstChoiceSortLink = (<i className="fa fa-sort"></i>);
        if(this.state.currentSort == 'firstChoice')
        {
            if(this.state.sortDirection == 'ASCENDING')
            {
                var firstChoiceSortLink = (<i className="fa fa-caret-up"></i>);
            }
            else if(this.state.sortDirection == 'DESCENDING')
            {
                var firstChoiceSortLink = (<i className="fa fa-caret-down"></i>);
            }
        }

        var secondChoiceSortLink = (<i className="fa fa-sort"></i>);
        if(this.state.currentSort == 'secondChoice')
        {
            if(this.state.sortDirection == 'ASCENDING')
            {
                var secondChoiceSortLink = (<i className="fa fa-caret-up"></i>);
            }
            else if(this.state.sortDirection == 'DESCENDING')
            {
                var secondChoiceSortLink = (<i className="fa fa-caret-down"></i>);
            }
        }

        var thirdChoiceSortLink = (<i className="fa fa-sort"></i>);
        if(this.state.currentSort == 'thirdChoice')
        {
            if(this.state.sortDirection == 'ASCENDING')
            {
                var thirdChoiceSortLink = (<i className="fa fa-caret-up"></i>);
            }
            else if(this.state.sortDirection == 'DESCENDING')
            {
                var thirdChoiceSortLink = (<i className="fa fa-caret-down"></i>);
            }
        }

        var genderSortLink = (<i className="fa fa-sort"></i>);
        if(this.state.currentSort == 'gender')
        {
            if(this.state.sortDirection == 'ASCENDING')
            {
                var genderSortLink = (<i className="fa fa-caret-up"></i>);
            }
            else if(this.state.sortDirection == 'DESCENDING')
            {
                var genderSortLink = (<i className="fa fa-caret-down"></i>);
            }
        }

        var appDateSortLink = (<i className="fa fa-sort"></i>);
        if(this.state.currentSort == 'appDate')
        {
            if(this.state.sortDirection == 'ASCENDING')
            {
                var appDateSortLink = (<i className="fa fa-caret-up"></i>);
            }
            else if(this.state.sortDirection == 'DESCENDING')
            {
                var appDateSortLink = (<i className="fa fa-caret-down"></i>);
            }
        }

        return(
            <div>
                <table className="table table-striped table-hover">
                    <thead>
                        <th>Name <a onClick={this.nameSort}>{nameSortLink}</a></th>
                        <th>1st <a onClick={this.firstChoiceSort}>{firstChoiceSortLink}</a></th>
                        <th>2nd <a onClick={this.secondChoiceSort}>{secondChoiceSortLink}</a></th>
                        <th>3rd <a onClick={this.thirdChoiceSort}>{thirdChoiceSortLink}</a></th>
                        <th>Sex <a onClick={this.genderSort}>{genderSortLink}</a></th>
                        <th>Date <a onClick={this.appDateSort}>{appDateSortLink}</a></th>
                        <th>Final RLC</th>
                        <th>Action</th>
                    </thead>
                    <tbody>
                        {applicantRows}
                    </tbody>
                </table>
            </div>
        );
    }
});

var ApplicantRow = React.createClass({
    getInitialState: function()
    {
        return {communityId: -1, response: undefined};
    },
    saveCommunityToApplicant: function(appId, communityId)
    {
        var inputData = {applicationId  : appId,
                         rlcId          : communityId
        };

        $.ajax({
            url:        'index.php?module=hms&action=AjaxSetRlcAssignment',
            type:       'POST',
            dataType:   'json',
            data:       inputData,
            success: function(data)
            {
                this.setState({response: data});
            }.bind(this),
            error: function()
            {

            }.bind(this)
        });
    },
    changeCommunity: function()
    {
        var newCommunity = this.refs.communityPicker.getDOMNode().value;
        this.setState({communityId: newCommunity});
    },
    saveCommunity: function()
    {
        this.saveCommunityToApplicant(this.props.node.app_id, this.state.communityId);
    },
    denyApp: function()
    {
        this.props.denyApp(this.props.node.app_id);
    },
    showModal: function()
    {
        this.props.showModal(this.props.node.app_id);
    },
    render: function()
    {
        var communities = this.props.communities;
        var data = Array({cId: -1, cName: 'None'});
        var i = 0;
        for(i; i < communities.length; i++)
        {
            data.push(communities[i]);
        }

        var communityOptions = data.map(function(node){
            return(
                <option value={node.cId}>{node.cName}</option>
            );
        });

        var selectRlc = (<div></div>);
        var denyLink  = (<div></div>);

        if(this.state.response != undefined)
        {
            var success = this.state.response.success;
            var error = !this.state.response.success;
            var notificationClasses = classNames({
                'text-success'  : success,
                'text-danger'   : error
            });
            selectRlc = (<td><p className={notificationClasses}>{this.state.response.message}</p></td>)
        }
        else {
            var saveBtn = (<div></div>);
            if(this.state.communityId != -1)
            {
                saveBtn = (<a onClick={this.saveCommunity} className="btn btn-primary">Save</a>);
            }

            var selectRlc = (<td><div className="col-md-10">
                    <select onChange={this.changeCommunity} className="form-control" ref="communityPicker">
                        {communityOptions}
                    </select>
                </div>
                {saveBtn}
            </td>)

            actions = (<td><ActionBox denyApp={this.denyApp}
                                      showModal={this.showModal}/>
                       </td>);
        }

        var profileLink = "index.php?module=hms&action=ShowStudentProfile&bannerId="+this.props.node.bannerId;

        return(
            <tr key={this.props.node.app_id}>
                <td><a href={profileLink}>{this.props.node.name}</a></td>
                <td>{this.props.node.first_choice}</td>
                <td>{this.props.node.second_choice}</td>
                <td>{this.props.node.third_choice}</td>
                <td>{this.props.node.gender}</td>
                <td>{this.props.node.app_date}</td>
                {selectRlc}
                {actions}
            </tr>
        )
    }
});

var ActionBox = React.createClass({
    openModal: function() {
        this.props.showModal();
    },
    render: function()
    {
        return (
            <div className="btn-group">
                <button type="button" className="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i className="fa fa-gear"></i> <span className="caret"></span>
                </button>
                <ul className="dropdown-menu">
                    <li><a onClick={this.openModal} href="javascript:;">View Application</a></li>
                    <li><a onClick={this.props.denyApp} href="javascript:;">Deny Application</a></li>
                </ul>
            </div>
        )
    }
});

var ApplicationExport = React.createClass({
    getInitialState: function()
    {
        return {communityId: 0};
    },
    changeCFilter: function(newFilter)
    {
        this.setState({communityId: newFilter})
    },
    render: function()
    {
        var btnStyle = {marginTop: '45px'}
        var btnLink  = "index.php?module=hms&action=ExportRlcApps&communityId="+this.state.communityId;

        return(
            <div>
                <h2>Application Export</h2>
                <div className="col-md-6">
                <CommunityFilter changeCFilter={this.changeCFilter}
                                 communities={this.props.communities}/>
                </div>
                <a href={btnLink} style={btnStyle} className="btn btn-primary">Export</a>
            </div>
        );
    }
});




React.render(
    <RlcApplicantsBox/>,
    document.getElementById('rlcAssignments')
);
