<?php
  /**
   * Permissions file for users
   *
   * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
   */

    $use_permissions = TRUE;
    $item_permissions = TRUE;

    $permissions['admin']               = _('Full Administrative access');

    $permissions['maintenance']         = _('Perform general maintenance tasks');
    
    $permissions['hall_maintenance']    = _('Add, Edit or Delete Halls');
    $permissions['add_halls']           = _('Add Residence Halls');
    $permissions['edit_halls']          = _('Edit Residence Halls');
    $permissions['delete_halls']        = _('Delete Residence Halls');

    $permissions['floor_maintenance']   = _('Add, Edit or Delete Floors');
    $permissions['add_floors']          = _('Add Floors');
    $permissions['edit_floors']         = _('Edit Floors');
    $permissions['delete_floors']       = _('Delete Floors');

    $permissions['room_maintenance']    = _('Add, Edit or Delete Rooms');
    $permissions['edit_rooms']          = _('Edit Rooms');

    $permissions['learning_community_maintenance']  = _('Add, Edit or Delete Learning Communities');
    $permissions['add_learning_communities']        = _('Add Learning Communities');
    $permissions['edit_learning_communities']       = _('Edit Learning Communities');
    $permissions['delete_learning_communities']     = _('Delete Learning Communities');

    $permissions['rlc_applicant_options']   = _('RLC-specific Tasks');
    $permissions['assign_to_rlcs']          = _('Assign to RLCs');
    $permissions['rlc_room_assignments']    = _('Assign RLC members to rooms');

    $permissions['deadline_maintenance']    = _('Deadline Maintenance');
    $permissions['edit_deadlines']          = _('Edit Deadlines');

    $permissions['assignment_maintenance']  = _('Assignment Maintenance');
    $permissions['create_assignment']       = _('Create Assignment');
    $permissions['delete_assignment']       = _('Delete Assignment');

    $permissions['roommate_maintenance']    = _('Roommate Maintenance');
    $permissions['create_roommate_group']   = _('Group Roommates');
    $permissions['edit_roommate_group']     = _('Edit Roommates');

?>
