INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (0, 0, 'Describe your current leadership and community service experience and the opportunities you are looking for.');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (1, 1, 'What outdoor opportunities would you like to be involved in and describe your current experience.');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (2, 2, 'What are your personal experienes with wellness and in what areas of wellness are you most interested?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (3, 3, 'What knowledge, skills, or talent could you offer other students in the Community of Scientific Interests?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (4, 4, 'In what languages are you proficient, learning to speak, or interested in learning?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (5, 5, 'How do you plan to be an active member of the ASU community?');

CREATE SEQUENCE hms_learning_community_questions_seq;
SELECT setval('hms_learning_community_questions_seq', max('hms_learning_community.id'));
