#
# Table structure for table 'prometheus_metrics'
#
CREATE TABLE prometheus_metrics (
    uid int(11) NOT NULL auto_increment,
    metric_key varchar(128) NOT NULL DEFAULT '',
    metric_value int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY metric_key (metric_key(100)),
    KEY tstamp (tstamp)
) ENGINE=InnoDB;
