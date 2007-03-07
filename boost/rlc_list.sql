INSERT INTO hms_learning_communities (id, community_name) VALUES (0, 'Leadership & Service Community');
INSERT INTO hms_learning_communities (id, community_name) VALUES (1, 'Outdoor Community');
INSERT INTO hms_learning_communities (id, community_name) VALUES (2, 'Wellness Community');
INSERT INTO hms_learning_communities (id, community_name) VALUES (3, 'Community of Scientific Interest');
INSERT INTO hms_learning_communities (id, community_name) VALUES (4, 'Language & Culture Community');
INSERT INTO hms_learning_communities (id, community_name) VALUES (5, 'Black & Gold Community');

CREATE SEQUENCE hms_learning_communities_seq;
SELECT setval('hms_learning_communities_seq', max(hms_learning_communities.id));
