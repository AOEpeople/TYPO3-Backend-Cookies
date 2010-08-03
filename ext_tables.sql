#
# Table structure for table 'tx_becookies_request'
#
CREATE TABLE tx_becookies_request (
    uid int(11) NOT NULL auto_increment,
    tstamp int(11) DEFAULT '0' NOT NULL,
    beuser int(11) DEFAULT '0' NOT NULL,
	session varchar(40) DEFAULT '' NOT NULL,
	domain varchar(255) DEFAULT '' NOT NULL,
    
    PRIMARY KEY (uid)
);

#
# Table structure for table 'sys_domain'
#
CREATE TABLE sys_domain (
    tx_becookies_login tinyint(3) DEFAULT '0' NOT NULL
);