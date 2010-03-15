ALTER TABLE hms_cached_student_info
            DROP roommate_number;
ALTER TABLE hms_cached_student_info
            ADD roommate_user character varying(32);
ALTER TABLE hms_cached_student_info
            ADD movein_time character varying(64);
