alter table hms_lottery_application drop column waiting_list_hide;
alter table hms_lottery_application add column waiting_list_date integer;
alter table hms_waitlist_application drop column waiting_list_hide;