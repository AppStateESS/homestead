CREATE TABLE hms_meal_plan (
    id                  integer NOT NULL,
    banner_id           integer NOT NULL,
    term                integer NOT NULL REFERENCES hms_term(term),
    meal_plan_code      character varying NOT NULL,
    status              character varying NOT NULL,
    status_timestamp    integer NOT NULL,
    primary key(id)
);
create sequence hms_meal_plan_seq;
ALTER TABLE hms_meal_plan ADD CONSTRAINT hms_meal_plan_banner_term_uniq UNIQUE (banner_id, term);


INSERT INTO hms_meal_plan (id, banner_id, term, meal_plan_code, status, status_timestamp) (SELECT nextval('hms_meal_plan_seq'), banner_id, term, meal_option, 'new', extract(epoch from now()) FROM hms_assignment where term IN (201720, 201730, 201740) and meal_option IS NOT NULL and meal_option != '');

alter table hms_term add column meal_plan_queue smallint not null default 1;


alter table hms_assignment drop column meal_option;
