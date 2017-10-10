CREATE TABLE hms_email_log (
    banner_id   character varying not null,
    message_id  character varying not null,
    PRIMARY KEY (banner_id, message_id)
);
