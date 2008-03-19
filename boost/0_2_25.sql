INSERT INTO hms_learning_communities VALUES (10, 'Community of Servant Leaders', 'CSL', 50);
select setval('hms_learning_communities_seq', max(hms_learning_communities.id));
UPDATE hms_learning_community_applications SET rlc_first_choice_id = 10 WHERE rlc_first_choice_id = 0;
UPDATE hms_learning_community_applications SET rlc_second_choice_id = 10 WHERE rlc_second_choice_id = 0;
UPDATE hms_learning_community_applications SET rlc_third_choice_id = 10 WHERE rlc_third_choice_id = 0;
INSERT INTO hms_learning_community_questions VALUES (10, 10, 'Describe your current leadership and community service experience and the opportunities you are looking for.');
select setval('hms_learning_community_questions_seq', max(hms_learning_community_questions.id));

delete from hms_learning_community_questions WHERE id = 0;
DELETE from hms_learning_communities WHERE id = 0;

ALTER TABLE hms_floor ADD COLUMN rlc_id integer REFERENCES hms_learning_communities(id);
