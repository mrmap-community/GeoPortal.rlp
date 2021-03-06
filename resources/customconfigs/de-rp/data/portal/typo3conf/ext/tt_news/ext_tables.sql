#
# Table structure for table 'tt_news'
#
CREATE TABLE tt_news (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  editlock tinyint(4) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(3) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  starttime int(11) unsigned DEFAULT '0' NOT NULL,
  endtime int(11) unsigned DEFAULT '0' NOT NULL,
  fe_group varchar(100) DEFAULT '0' NOT NULL,
  title text NOT NULL,
  datetime int(11) unsigned DEFAULT '0' NOT NULL,
  image text NOT NULL,
  imagecaption text NOT NULL,
  imagealttext text NOT NULL,
  imagetitletext text NOT NULL,
  related int(11) DEFAULT '0' NOT NULL,
  short text NOT NULL,
  bodytext mediumtext NOT NULL,
  author tinytext NOT NULL,
  author_email tinytext NOT NULL,
  category int(11) DEFAULT '0' NOT NULL,

  news_files text NOT NULL,
  links text NOT NULL,
  type tinyint(4) DEFAULT '0' NOT NULL,
  page int(11) DEFAULT '0' NOT NULL,
  keywords text NOT NULL,
  archivedate int(11) DEFAULT '0' NOT NULL,
  ext_url tinytext NOT NULL,
  
  sys_language_uid int(11) DEFAULT '0' NOT NULL,
  l18n_parent int(11) DEFAULT '0' NOT NULL,
  l18n_diffsource mediumblob NOT NULL,
  no_auto_pb tinyint(4) unsigned DEFAULT '0' NOT NULL,

  t3ver_oid int(11) DEFAULT '0' NOT NULL,
  t3ver_id int(11) DEFAULT '0' NOT NULL,
  t3ver_wsid int(11) DEFAULT '0' NOT NULL,
  t3ver_label varchar(30) DEFAULT '' NOT NULL,
  t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
  t3ver_stage tinyint(4) DEFAULT '0' NOT NULL,
  t3ver_count int(11) DEFAULT '0' NOT NULL,
  t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
  t3_origuid int(11) DEFAULT '0' NOT NULL,


  PRIMARY KEY (uid),
  KEY parent (pid),
  KEY t3ver_oid (t3ver_oid,t3ver_wsid)

);

#
# Table structure for table 'tt_news_cat'
#
CREATE TABLE tt_news_cat (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  starttime int(11) unsigned DEFAULT '0' NOT NULL,
  endtime int(11) unsigned DEFAULT '0' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  fe_group varchar(100) DEFAULT '0' NOT NULL,
  title tinytext NOT NULL,
  title_lang_ol tinytext NOT NULL,
  image tinytext NOT NULL,
  shortcut int(11) unsigned DEFAULT '0' NOT NULL,
  shortcut_target tinytext NOT NULL,
  deleted tinyint(3) unsigned DEFAULT '0' NOT NULL,
  single_pid int(11) unsigned DEFAULT '0' NOT NULL,
  parent_category int(11) unsigned DEFAULT '0' NOT NULL,
  description text NOT NULL,

  PRIMARY KEY (uid),
  KEY parent (pid)

);

#
# Table structure for table 'tt_news_related_mm'
#
CREATE TABLE tt_news_related_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  tablenames tinytext NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tt_news_cat_mm'
#
CREATE TABLE tt_news_cat_mm (
  uid_local int(11) unsigned DEFAULT '0' NOT NULL,
  uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'be_groups'
#
CREATE TABLE be_groups (
	tt_news_categorymounts tinytext NOT NULL,
# 	tt_news_cmounts_usesubcats tinyint(4) unsigned DEFAULT '0' NOT NULL
	
);

#
# Table structure for table 'be_users'
#
CREATE TABLE be_users (
	tt_news_categorymounts tinytext NOT NULL,
# 	tt_news_cmounts_usesubcats tinyint(4) unsigned DEFAULT '0' NOT NULL
);

