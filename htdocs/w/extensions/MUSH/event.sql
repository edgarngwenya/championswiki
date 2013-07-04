DROP TABLE IF EXISTS `event_info`;
CREATE TABLE `event_info` (
  `page_id` int(11) unsigned NOT NULL,
  `date` date NOT NULL,
  `time` varchar(5) NOT NULL,
  `summary` text,
  PRIMARY KEY  (`page_id`)
) ENGINE=INNODB DEFAULT CHARSET=latin1;