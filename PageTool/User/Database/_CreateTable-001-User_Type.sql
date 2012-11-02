create table if not exists `User_Type` (
	`Id`					int			not null	auto_increment	primary key,
	`Name`					varchar(32)	not null,
	unique index `Name_Unique` (`Name` ASC)
)
COMMENT = "Used to differentiate user roles"
ENGINE = InnoDB;