create table if not exists `Content` (
	`Id`					int			not null	auto_increment	primary key,
	`Name`					varchar(32)	not null,
	`Fk_Content_Type`		int			null
	unique index `Name_Unique` (`Name` asc),
	index `Fk_Content__Content_Type` (Fk_Content_Type asc),
	constraint `Fk_Content__Content_Type`
		foreign key (`Fk_Content_Type`)
		references `Content_Type` (`Id`)
		on delete set null,
		on update cascade
)
COMMENT = "Matches named content to their elements."
ENGINE = InnoDB;