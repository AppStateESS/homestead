<?php
  /**
   * Permissions file for users
   *
   * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
   * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
   */

    $use_permissions = TRUE;
    $item_permissions = TRUE;
    
    /***********************
     * General Permissions *
     ***********************/

    $permissions['search']              = _('Search for students');

    /*********
     * Terms *
     *********/
    $permissions['select_term']             = _('Select past/future terms');
    $permissions['activate_term']           = _('Set active term');
    $permissions['edit_terms']              = _('Create and delete terms');
    $permissions['banner_queue']            = _('Enable/disable banner queue');

    /*************
     * Deadlines *
     *************/
    $permissions['view_deadlines']          = _('Deadline Maintenance');
    $permissions['edit_deadlines']          = _('Edit Deadlines');

    /******************
     * Hall Structure *
     ******************/
    # Residence hall tasks
    $permissions['hall_structure']      = _('Add and delete halls');
    $permissions['hall_attributes']     = _('Edit hall attributes');

    # Floor tasks
    $permissions['floor_structure']     = _('Add and delete floors');
    $permissions['floor_attributes']    = _('Edit floor attributes');

    # Suite tasks
    $permissions['suite_structure']     = _('Add and delete suites');
    $permissions['suite_attributes']    = _('Edit suite attributes');

    # Room tasks
    $permissions['room_structure']      = _('Add and delete rooms');
    $permissions['room_attributes']     = _('Edit room attributes');

    # Bed tasks
    $permissions['bed_structure']       = _('Add and delete beds');
    $permissions['bed_attributes']      = _('Edit bed attributes');


    /*************
     * Roommates *
     *************/
    $permissions['roommate_maintenance']     = _('Create and crush roommate groups');
    
    /***************
     * Assignments *
     ***************/
    $permissions['assignment_maintenance']  = _('Create, move, and delete assignments');
    $permissions['autoassign']              = _('Run the auto-assigner');

    /*************
     * RLC tasks *
     *************/
    # Learning community tasks
    $permissions['learning_community_maintenance']  = _('Add, edit, and delete learning communities');

    # RLC application tasks
    $permissions['view_rlc_applications']           = _('View RLC applications');
    $permissions['approve_rlc_applications']        = _('Approve/Deny RLC applications');
    
    $permissions['view_rlc_members']                = _('View list of RLC members');

    # RLC assignment tasks
    $permissions['view_rlc_room_assignments']       = _('View RLC room assignments');
    $permissions['rlc_room_assignments']            = _('Create RLC room assignments');

    /********
     * Misc *
     ********/
    $permissions['edit_movein_times']               = _('Create/delete move-in times');

    $permissions['stats']                           =_('View statistics');
    $permissions['reports']                         =_('Execute and view reports');
?>
