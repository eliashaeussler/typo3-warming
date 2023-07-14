# Password: password
SET @password := '$argon2i$v=19$m=65536,t=16,p=1$REUwOURSemYvT2IwajFuLw$a7yglpvvX8YELIKEWIYWFC9akT+krlVg6Rm2joAHBMs';

DELETE
FROM `be_users`;

INSERT INTO `be_users` (`username`, `password`, `admin`, `usergroup`, `options`, `allowed_languages`, `TSconfig`)
VALUES ('admin', @password, 1, NULL, 0, '', NULL),
			 ('editor.1', @password, 0, 1, 3, '', NULL),
			 ('editor.2', @password, 0, 1, 3, '0', 'options.cacheWarmup.allowedSites = main
options.cacheWarmup.allowedPages = 1+');
