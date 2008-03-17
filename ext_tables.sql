--
-- Table structure for table `tx_wecmap_cache`
--

CREATE TABLE `tx_wecmap_cache` (
  	address_hash varchar(50) NOT NULL default '',
  	address varchar(100) NOT NULL default '',
  	latitude double default '0',
  	longitude double default '0',
	
	PRIMARY KEY (address_hash)
);

CREATE TABLE `tx_wecmap_external` (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	url tinytext NOT NULL,
	
	PRIMARY KEY (uid)
);