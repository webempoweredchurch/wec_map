--
-- Table structure for table `tx_wecmap_cache`
--

CREATE TABLE `tx_wecmap_cache` (
  address_hash varchar(50) NOT NULL default '',
  address varchar(100) NOT NULL default '',
  latitude float default 0,
  longitude float default 0,
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
