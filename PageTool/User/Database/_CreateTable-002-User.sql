create table if not exists `User` (
	`ID`					int	unsigned not null	auto_increment	primary key,
	`uuid`					varchar(128)null,
	`FK_User_Type`			int	unsigned not null	default 1,
	`FK_User__orphanedBy`	int	unsigned null,
	-- username and password are used as fallback when oauth not used.
	`username`				varchar(32)	null,
	-- password should always be a bcrypted password! Not a hashed password!
	`password`				varchar(128)null,
	`dateTimeCreated`		datetime	not null,
	`dateTimeIdentified`	datetime	null,
	`dateTimeDeleted`		datetime	null		default null,
	`dateTimeLastActive`	datetime	null,
	`activityCount`			int unsigned not null	default 0,
	unique index `UNIQUE_username` (`username` asc),
	index `INDEX_FK_User_Type` (`FK_User_Type` asc),
	index `INDEX_FK_User__orphanedBy` (`FK_User__orphanedBy` asc),
	constraint `INDEX_FK_User_Type`
		foreign key (`FK_User_Type`)
		references `User_Type` (`ID`)
		on delete restrict
		on update cascade,
	constraint `INDEX_FK_User__orphanedBy`
		foreign key (`FK_User__orphanedBy`)
		references `User` (`ID`)
		on delete set null
		on update cascade
)
COMMENT = ""
ENGINE = InnoDB;