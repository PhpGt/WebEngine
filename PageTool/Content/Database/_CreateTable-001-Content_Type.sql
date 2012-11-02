create table if not exists `Content_Type` (
	`Id`					int			not null	auto_increment	primary key,
	`Name`					varchar(32)	not null,
	unique index `Name_Unique` (`Name` ASC)
)
COMMENT = "Semantic table used to describe content"
ENGINE = InnoDB;