ALTER TABLE hms_lottery_entry ADD COLUMN waiting_list_hide INTEGER NOT NULL DEFAULT 0;

ALTER TABLE hms_learning_communities ADD COLUMN hide INTEGER NOT NULL DEFAULT 0;
