CREATE TABLE tx_warming_domain_model_log (
	request_id varchar(50) default '' not null,
	date int(10) unsigned default '0' not null,
	url text default '' not null,
	message mediumtext,
	state varchar(20) default '' not null,
	sitemap text,
	site int(10) unsigned,
	site_language int(10) unsigned,
);
