create table if not exists `User` (
	`Id`					int			not null	auto_increment	primary key,
	`Uuid`					varchar(128)null,
	`Fk_User_Type`			int			not null	default 1,
	`Fk_User_Department`	int			null		default null,
	`Username`				varchar(32)	not null,
	`Hash`					varchar(32) null		default null,
	`Dt_Created`			datetime	not null,
	`Dt_Deleted`			datetime	null		default null,
	`FirstName`				varchar(32)	null		default null,
	`LastName`				varchar(32)	null		default null,
	`Email`					varchar(64)	null		default null,
	`JobTitle`				varchar(32)	null		default null,
	`Telephone`				varchar(32)	null		default null,
	`TelephoneMobile`		varchar(32)	null		default null,
	`Gravatar`				varchar(32)	null		default null,
	unique index `Username_Unique` (`Username` asc),
	index `Fk_User__User_Type` (`Fk_User_Type` asc),
	index `Fk_User__User_Department` (`Fk_User_Department` asc),
	constraint `Fk_User__User_Type`
		foreign key (`Fk_User_Type`)
		references `User_Type` (`Id`)
		on delete restrict
		on update cascade,
	constraint `Fk_User__User_Department`
		foreign key (`Fk_User_Department`)
		references `User_Department` (`Id`)
		on delete restrict
		on update cascade
)
COMMENT = "Hash is stored if password needed, otherwise null for OpenId"
ENGINE = InnoDB;