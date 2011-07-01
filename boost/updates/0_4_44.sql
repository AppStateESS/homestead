CREATE TABLE hms_temp_assignment (room_number character(5) NOT NULL, banner_id character(9), PRIMARY KEY (room_number));
ALTER TABLE hms_temp_assignment ADD CONSTRAINT unique_banner_id UNIQUE (banner_id);
