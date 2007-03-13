INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (0, 'Leadership & Service Community', 'LSC', 100);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (1, 'Outdoor Community', 'OC', 200);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (2, 'Wellness Community', 'WC', 300);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (3, 'Community of Scientific Interest', 'CSI', 400);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (4, 'Language & Culture Community', 'LCC', 500);
INSERT INTO hms_learning_communities (id, community_name, abbreviation, capacity) VALUES (5, 'Black & Gold Community', 'BGC', 600);

CREATE SEQUENCE hms_learning_communities_seq;
SELECT setval('hms_learning_communities_seq', max(hms_learning_communities.id));
