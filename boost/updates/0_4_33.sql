alter table hms_lottery_application drop column roommate1_username;
alter table hms_lottery_application drop column roommate2_username;
alter table hms_lottery_application drop column roommate3_username;
alter table hms_lottery_application drop column roommate1_app_term;
alter table hms_lottery_application drop column roommate2_app_term;
alter table hms_lottery_application drop column roommate3_app_term;

alter table hms_lottery_application add column rlc_interest smallint not null default 0;
alter table hms_lottery_application add column sorority_pref character varying(32);
alter table hms_lottery_application add column tf_pref smallint;
alter table hms_lottery_application add column wg_pref smallint;
alter table hms_lottery_application add column honors_pref smallint;

UPDATE hms_lottery_application SET tf_pref = 0, wg_pref = 0, honors_pref = 0;
alter table hms_lottery_application alter column tf_pref SET NOT NULL;
alter table hms_lottery_application alter column wg_pref SET NOT NULL;
alter table hms_lottery_application alter column honors_pref SET NOT NULL;

alter table hms_learning_communities add column allowed_reapplication_student_types character varying(16);
alter table hms_learning_communities add column members_reapply smallint;