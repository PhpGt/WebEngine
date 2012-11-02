create table if not exists `TestTool` (
	`Id`					int			not null	auto_increment	primary key,
	`Name`					varchar(32)	not null,
	unique index `Name_Unique` (`Name` ASC)
)
COMMENT = "Just a test..."
ENGINE = InnoDB;