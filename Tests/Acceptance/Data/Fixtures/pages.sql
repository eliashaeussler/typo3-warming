DELETE
FROM `pages`;

INSERT INTO `pages` (`uid`, `pid`, `title`, `slug`, `sys_language_uid`, `l10n_parent`, `l10n_source`, `perms_userid`,
										 `perms_groupid`, `perms_user`, `perms_group`, `perms_everybody`, `doktype`, `is_siteroot`)
VALUES (1, 0, 'Main', '/', 0, 0, 0, 1, 1, 31, 31, 1, 1, 1),
			 (2, 1, 'Subsite 1', '/subsite-1', 0, 0, 0, 1, 1, 31, 31, 1, 1, 0),
			 (3, 1, 'Subsite 2', '/subsite-2', 0, 0, 0, 1, 1, 31, 31, 1, 1, 0),
			 (4, 3, 'Subsite 2-1', '/subsite-1/subsite-2-1', 0, 0, 0, 1, 1, 31, 31, 1, 1, 0),
			 (5, 0, 'Main L=1', '/', 1, 1, 1, 1, 1, 31, 31, 1, 1, 1),
			 (6, 1, 'Subsite 1 L=1', '/subsite-1-l-1', 1, 2, 2, 1, 1, 31, 31, 1, 1, 0),

			 (7, 0, 'Root 2', '/', 0, 0, 0, 1, 1, 31, 31, 1, 1, 1),
			 (8, 7, 'Subsite 1', '/subsite-1', 0, 0, 0, 1, 1, 31, 31, 1, 1, 0),
			 (9, 7, 'Subsite 2', '/subsite-2', 0, 0, 0, 1, 1, 31, 31, 1, 1, 0),
			 (10, 9, 'Subsite 2-1', '/subsite-1/subsite-2-1', 0, 0, 0, 1, 1, 31, 31, 1, 1, 0),
			 (11, 0, 'Root 2 L=1', '/', 1, 7, 7, 1, 1, 31, 31, 1, 1, 1),
			 (12, 7, 'Subsite 1 L=1', '/subsite-1-l-1', 1, 8, 8, 1, 1, 31, 31, 1, 1, 0);
