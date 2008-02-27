BEGIN;

DELETE FROM hms_learning_community_assignment;
DELETE FROM hms_learning_community_applications;
DELETE FROM hms_learning_community_questions;
DELETE FROM hms_learning_communities;

INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (0, 'Community of Servant Leaders', 'LSC', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (1, 'Outdoor Community', 'OC', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (2, 'Community of Scientific Interest', 'CSI', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (3, 'Language & Culture Community', 'LCC', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (4, 'Black & Gold Community', 'BGC', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (5, 'Community for Future Educators', 'FE', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (6, 'Quiet Study Community', 'QS', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (7, 'Living Free Community', 'LF', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (8, 'Entrepreneurs Community', 'EN', 50);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (9, 'The Man Floor', 'TMF', 50);

SELECT setval('hms_learning_communities_seq', max(hms_learning_communities.id));

INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (0, 0, 'Describe your current leadership and community service experience and the opportunities you are looking for.');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (1, 1, 'What outdoor opportunities would you like to be involved in and describe your current experience.');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (2, 2, 'What knowledge, skills, or talent could you offer other students in the Community of Scientific Interests?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (3, 3, 'In what languages are you proficient, learning to speak, or interested in learning?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (4, 4, 'How do you plan to be an active member of the ASU community?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (5, 5, 'What are your future education goals and how will this community be of benefit to you?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (6, 6, 'What are your study goals and how will this community help you to reach them?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (7, 7, 'What lifestyle choices have you made that will help you contribute to this community?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (8, 8, 'What are your goals for joining this community and how do you plan to be an active member in this community?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (9, 9, 'What goals do you hope to reach by living on the Man Floor?');

SELECT setval('hms_learning_community_questions_seq', max(hms_learning_community_questions.id));

COMMIT;
