create table if not exists `Blog_Tag` (
	`Id`					int			not null	auto_increment	primary key,
	`Name`					varchar(32)	not null,
	unique index `Name_Unique` (`Name` ASC)
)
COMMENT = ""
ENGINE = InnoDB;