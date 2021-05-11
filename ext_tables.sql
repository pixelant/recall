CREATE TABLE tx_recall_data (
	hash    varchar(32)      DEFAULT ''  NOT NULL,
	data    longblob         DEFAULT ''  NOT NULL,
	tstamp  int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (hash),
	KEY tstamp (tstamp)
);
