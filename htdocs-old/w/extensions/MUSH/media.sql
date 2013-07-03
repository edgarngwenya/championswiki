create table media_info (
page_id int(11) unsigned primary key,
media_id int(11) unsigned NOT NULL UNIQUE,
title varchar(255) NOT NULL UNIQUE,
source varchar(16) NOT NULL,
reporter varchar(32) NOT NULL,
ooc_date date NOT NULL,
ic_date date NOT NULL
);

