create table if not exists `Blog` (
	`Id`					int			not null	auto_increment	primary key,
	`Name`					varchar(32)	not null	default "Blog",
	`AutoPublishComments`	bool		null,
	`PreviewLength`			int			not null	default 100,
	unique index `Name_Unique` (`Name` ASC)
)
COMMENT = "Holds settings for blog, allows multiple blogs per site."
ENGINE = InnoDB;