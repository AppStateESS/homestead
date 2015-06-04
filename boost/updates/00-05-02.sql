alter table hms_fall_application add column smoking_preference smallint;
update hms_fall_application set smoking_preference = 0;
alter table hms_fall_application alter column smoking_preference set default 0;
alter table hms_fall_application alter column smoking_preference set not null;


alter table hms_spring_application add column smoking_preference smallint;
update hms_spring_application set smoking_preference = 0;
alter table hms_spring_application alter column smoking_preference set default 0;
alter table hms_spring_application alter column smoking_preference set not null;

alter table hms_summer_application add column smoking_preference smallint;
update hms_summer_application set smoking_preference = 0;
alter table hms_summer_application alter column smoking_preference set default 0;
alter table hms_summer_application alter column smoking_preference set not null;
