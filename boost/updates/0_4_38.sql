START TRANSACTION;
-- Change name of 'Heltzer Honors Program'
UPDATE hms_learning_communities SET community_name = 'The Honors College' WHERE id = 21;
UPDATE hms_learning_community_questions SET question_text = 'Why are you interested in The Honors College?' WHERE learning_community_id = 21;
COMMIT;