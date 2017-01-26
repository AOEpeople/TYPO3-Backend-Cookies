#
# Table structure for table 'tx_becookies_request'
#
CREATE TABLE tx_becookies_request (
  uid     INT(11)                 NOT NULL AUTO_INCREMENT,
  tstamp  INT(11) DEFAULT '0'     NOT NULL,
  beuser  INT(11) DEFAULT '0'     NOT NULL,
  session VARCHAR(40) DEFAULT ''  NOT NULL,
  domain  VARCHAR(255) DEFAULT '' NOT NULL,

  PRIMARY KEY (uid)
) ENGINE = InnoDB;

#
# Table structure for table 'sys_domain'
#
CREATE TABLE sys_domain (
  tx_becookies_login TINYINT(3) DEFAULT '0' NOT NULL
);
