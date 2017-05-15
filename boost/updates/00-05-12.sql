CREATE TABLE hms_meal_plan (
    id                  integer NOT NULL,
    banner_id           integer NOT NULL,
    term                integer NOT NULL REFERENCES hms_term(term),
    meal_plan_code      character(2) NOT NULL,
    status              character varying NOT NULL,
    status_timestamp    integer NOT NULL,
    primary key(id)
);


ALTER TABLE hms_meal_plan ADD CONSTRAINT hms_meal_plan_banner_term_uniq UNIQUE (banner_id, term);

create sequence hms_meal_plan_seq;


INSERT INTO hms_meal_plan (id, banner_id, term, meal_plan_code, status, status_timestamp) (SELECT (SELECT nextval('hms_meal_plan_seq')), banner_id, term, meal_option, 'new', extract(epoch from now()) FROM hms_assignment where term IN (201720, 201730, 201740) and meal_option IS NOT NULL and meal_option != '');
