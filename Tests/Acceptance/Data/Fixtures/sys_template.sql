DELETE
FROM `sys_template`;

INSERT INTO `sys_template` (`pid`, `title`, `root`, `clear`, `include_static_file`)
VALUES (1, 'Home', 1, 3, 'EXT:seo/Configuration/TypoScript/XmlSitemap'),
			 (2, 'Root 2', 1, 3, '');
