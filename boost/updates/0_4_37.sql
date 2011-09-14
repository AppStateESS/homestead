BEGIN;

alter table hms_student_cache rename international to international_old;
alter table hms_student_cache rename honors to honors_old;
alter table hms_student_cache rename teaching_fellow to teaching_fellow_old;
alter table hms_student_cache rename watauga_member to watauga_member_old;

alter table hms_student_cache add column international smallint;
alter table hms_student_cache add column honors smallint;
alter table hms_student_cache add column teaching_fellow smallint;
alter table hms_student_cache add column watauga_member smallint;

update hms_student_cache set international = 0 where international_old = '0';
update hms_student_cache set honors = 0 where honors_old = '0';
update hms_student_cache set watauga_member = 0 where watauga_member_old = '0';
update hms_student_cache set teaching_fellow = 0 where teaching_fellow_old = '0';

update hms_student_cache set international = 1 where international_old = '1';
update hms_student_cache set honors = 1 where honors_old = '1';
update hms_student_cache set watauga_member = 1 where watauga_member_old = '1';
update hms_student_cache set teaching_fellow = 1 where teaching_fellow_old = '1';

alter table hms_student_cache alter column international SET NOT NULL;
alter table hms_student_cache alter column honors SET NOT NULL;
alter table hms_student_cache alter column watauga_member SET NOT NULL;
alter table hms_student_cache alter column teaching_fellow SET NOT NULL;

alter table hms_student_cache drop column international_old;
alter table hms_student_cache drop column honors_old;
alter table hms_student_cache drop column teaching_fellow_old;
alter table hms_student_cache drop column watauga_member_old;

COMMIT;