create table if not exists `User_Department` (
	`Id`					int			not null	auto_increment	primary key,
	`Name`					varchar(32)	not null,
	unique index `Name_Unique` (`Name` ASC)
)
COMMENT = "Optional table for use in some applications"
ENGINE = InnoDB;