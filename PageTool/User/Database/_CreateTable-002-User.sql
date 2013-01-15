create table if not exists `User` (
	`ID`					int			not null	auto_increment	primary key,
	`uuid`					varchar(128)null,
	`FK_User_Type`			int			not null	default 1,
	`username`				varchar(32)	null,
	`dateTimeCreated`		datetime	not null,
	`dateTimeIdentified`	datetime	null,
	`dateTimeDeleted`		datetime	null		default null,
	`dateTimeLastActive`	datetime	null,
	`activityCount`			int unsigned not null	default 0,
	unique index `UNIQUE_username` (`username` asc),
	index `FK_User__User_Type` (`FK_User_Type` asc),
	constraint `FK_User__User_Type`
		foreign key (`FK_User_Type`)
		references `User_Type` (`ID`)
		on delete restrict
		on update cascade
)
COMMENT = ""
ENGINE = InnoDB;