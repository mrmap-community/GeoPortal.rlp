CREATE TABLE search (
  id int(11) NOT NULL auto_increment,
  name varchar(255) NOT NULL default '',
  uid varchar(80) NOT NULL default '',
  searchtext varchar(255) NOT NULL default '',
  datetime int(11) NOT NULL default '0',
  lastsearch int(11) NOT NULL default '0',
  PRIMARY KEY(id)
);


CREATE TABLE search_stats (
  id int(11) NOT NULL auto_increment,
  searchtext varchar(255) NOT NULL default '',
  date varchar(80) NOT NULL default '',
  ip varchar(80) NOT NULL default '',
  PRIMARY KEY(id)
);
