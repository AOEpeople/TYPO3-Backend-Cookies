#
# Table structure for table 'tx_becookies_domain_model_request'
#
CREATE TABLE tx_becookies_domain_model_request (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    beuser int(11) DEFAULT '0' NOT NULL,
	session varchar(40) DEFAULT '' NOT NULL,
	domain varchar(255) DEFAULT '' NOT NULL,
    
    PRIMARY KEY (uid)
)	ENGINE=InnoDB;
