create table if not exists `User` (
	`Id`					int			not null	auto_increment	primary key,
	`Uuid`					varchar(128)null,
	`Fk_User_Type`			int			not null	default 1,
	`Username`				varchar(32)	null,
	`Dt_Created`			datetime	not null,
	`Dt_Identified`			datetime	null,
	`Dt_Deleted`			datetime	null		default null,
	`Dt_LastActive`			datetime	null,
	`ActivityCount`			int unsigned not null	default 0,
	unique index `Username_Unique` (`Username` asc),
	index `Fk_User__User_Type` (`Fk_User_Type` asc),
	constraint `Fk_User__User_Type`
		foreign key (`Fk_User_Type`)
		references `User_Type` (`Id`)
		on delete restrict
		on update cascade
)
COMMENT = ""
ENGINE = InnoDB;