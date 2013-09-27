create table if not exists `User_Type` (
	`ID`					int	not null	auto_increment	primary key,
	`name`					varchar(32)	not null,
	unique index `UNIQUE_name` (`name` ASC)
)
ENGINE = InnoDB;