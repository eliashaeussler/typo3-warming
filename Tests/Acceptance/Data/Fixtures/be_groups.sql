DELETE
FROM `be_groups`;

INSERT INTO `be_groups` (`title`, `db_mountpoints`, `pagetypes_select`, `tables_select`, `tables_modify`, `groupMods`)
VALUES ('editor', 1, 1, 'pages,tt_content', 'pages,tt_content', 'web_layout,web_list');
