create table if not exists `User_OAuth` (
	`FK_User`				int	unsigned not null,
	`oauth_uuid`			varchar(128)not null,
	`oauth_name`			varchar(32) not null,
	primary key (`FK_User`, `oauth_uuid`),
	index `INDEX_FK_User` (`FK_User` asc),
	unique index `UNIQUE_OAuth_uuid` (`oauth_uuid` asc),
	constraint `FK_User_OAuth__User`
		foreign key (`FK_User` )
		references `User` (`ID` )
		on delete cascade
		on update cascade
)
ENGINE = InnoDB