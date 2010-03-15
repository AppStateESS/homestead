ALTER TABLE hms_lottery_entry ADD COLUMN waiting_list_hide INTEGER NOT NULL DEFAULT 0;

ALTER TABLE hms_learning_communities ADD COLUMN hide INTEGER NOT NULL DEFAULT 0;

ALTER TABLE hms_learning_community_applications ALTER COLUMN why_specific_communities TYPE CHARACTER varying(4096);
ALTER TABLE hms_learning_community_applications ALTER COLUMN strengths_weaknesses TYPE CHARACTER varying(4096);
ALTER TABLE hms_learning_community_applications ALTER COLUMN rlc_question_0 TYPE CHARACTER varying(4096);
ALTER TABLE hms_learning_community_applications ALTER COLUMN rlc_question_1 TYPE CHARACTER varying(4096);
ALTER TABLE hms_learning_community_applications ALTER COLUMN rlc_question_2 TYPE CHARACTER varying(4096);
