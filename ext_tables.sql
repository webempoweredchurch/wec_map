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
