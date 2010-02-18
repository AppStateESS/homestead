alter table hms_learning_communities alter column community_name type character varying(64);
alter table hms_learning_communities add column extra_info text;

delete from hms_learning_community_applications;
delete from hms_learning_community_assignment;

delete from hms_learning_community_questions;
update hms_floor SET rlc_id = null;
delete from hms_learning_communities;

INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (3, 'Language & Culture Community', 'LCC', 50, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (20, 'Watauga Global Community', 'WG', 50, 0, 'F', '<p>Watauga Global Community is where classes meet general education requirements in interdisciplinary team-taught (multiple professor) core classes that blend fact, fiction, culture, philosophy, motion, art, music, myth, and religion.</p><p><strong>This community requires a separate application in addition to marking it as a housing preference.  For more information, go to the <a href="http://wataugaglobal.appstate.edu/pagesmith/4" target="_blank" style="color: blue;">Watauga Global Community Website</a>.</strong></p>');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (21, 'Heltzer Honors Program', 'HN', 50, 0, 'F', '<p><strong>This community requires a separate application in addition to marking it as a housing preference.</strong></p><p>To apply for the Heltzer Honors Program, log into <a href="https://firstconnections.appstate.edu/ugaweb/" target="_blank" style="color: blue;">First Connections</a> and complete the on-line application accordingly.</p><p>For more information, go to the <a href="http://www.honors.appstate.edu/" target="_blank" style="color: blue;"> Heltzer Honors Program website</a>.</p>');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (2, 'Academy of Science', 'AS', 40, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (14, 'Art Haus', 'AC', 68, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (4, 'Black & Gold Community', 'BGC', 68, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (15, 'Brain Matters - A Psychology Community', 'PC', 41, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (8, 'Business Exploration', 'AE', 41, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (19, 'Cycling Community', 'CC', 28, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (5, 'Future Educators', 'FE', 38, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (7, 'Living Free Community', 'LF', 34, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (11, 'Living Green', 'LG', 38, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (1, 'Outdoor Community', 'OC', 42, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (6, 'Quiet Study Community', 'QS', 34, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (10, 'Service and Leadership Community', 'SL', 38, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (12, 'Sophomore Year Experience', 'SYE', 32, 0, 'C', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (13, 'Transfer Teacher Educators Community', 'TE', 38, 0, 'T', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (16, 'Sisterhood Experience', 'PC', 116, 0, 'F', '');
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity, hide, allowed_student_types, extra_info) VALUES (18, 'Band of Brothers Community for Men', 'MC', 114, 0, 'F', '');

INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (11, 1, 'What role have outdoor adventure experiences played in your life and how do you see these continuing in your college years? Are there more experiences you want to have or contribute to, and skills or abilities you want to develop?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (12, 10, 'What service and/or leadership experiences do you bring to the community and what do you hope to gain from involvement with service and/or leadership activities on campus?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (13, 5, 'What are your future education goals and how will this community be of benefit to you?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (14, 11, 'What do you hope to learn by living in the Living Green Community and what will you contribute to sustainability effors in the residence hall?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (15, 14, 'How do you feel an artist and/or the creative process can be supported though community?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (16, 15, 'What about psychology interests you and why are you interested in living in a residential community focused on exploring relationships between the brain, behavior, and mind?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (17, 8, 'Why do you think business would make a great career? Give one example of some business event that has been of interest to you.');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (18, 16, 'Why are you interested in living in an all female residence hall?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (19, 18, 'How will your involvement in a community of men enhance your college experience?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (20, 19, 'What is it about bicycling that you enjoy?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (21, 3, 'What language(s) do you know/want to learn, and how would you take action in this community to craete an environment that promotes language appreciation and cultural understanding?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (23, 13, 'How will this community be of benefit to you as a future teacher? How will this community be of benefit to you as a transfer student?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (22, 4, 'How do you plan to be an active member of the ASU community and the Black and Gold Community?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (24, 7, 'What lifestyle choices have you made that will help you contribute to the Living Free Community?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (25, 6, 'What are your study goals and how will the quiet study community help you to reach them?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (26, 2, 'This National Science Foundation-supported scholarship provides mentoring and research in the math and science disciplines of chemistry, computer science, geology, mathematics, physics, and astronomy. Which of these areas are you most interested in and why?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (27, 20, 'Why are you interested in the Watauga Global Community?');
INSERT INTO hms_learning_community_questions (id, learning_community_id, question_text) VALUES (28, 21, 'Why are you interested in the Heltzer Honors Program?');

