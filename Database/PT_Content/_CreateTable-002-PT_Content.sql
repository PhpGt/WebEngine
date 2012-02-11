create table if not exists `PT_Content` (
	`Id`					int			not null	auto_increment	primary key,
	`Name`					varchar(32)	not null,
	`Fk_PT_Content_Type`	int			null,
	unique index `Name_Unique` (`Name` asc),
	index `Fk_PT_Content__PT_Content_Type` (`Fk_PT_Content_Type` asc),
	constraint `Fk_PT_Content__PT_Content_Type`
		foreign key (`Fk_PT_Content_Type`)
		references `PT_Content_Type` (`Id`)
		on delete set null
		on update cascade
)
COMMENT = "Matches named content to their elements."
ENGINE = InnoDB;