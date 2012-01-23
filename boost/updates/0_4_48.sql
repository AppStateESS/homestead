alter table hms_residence_hall drop column rooms_for_lottery;

alter table hms_lottery_application add column early_release character varying(32);

alter table hms_learning_communities add column freshmen_question text;
alter table hms_learning_communities add column returning_question text;

update hms_learning_communities SET freshmen_question = question_text FROM hms_learning_communities AS rlc JOIN hms_learning_community_questions ON rlc.id = hms_learning_community_questions.learning_community_id WHERE hms_learning_communities.id = rlc.id;