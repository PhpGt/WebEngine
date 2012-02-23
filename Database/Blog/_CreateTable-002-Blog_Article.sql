create table if not exists `Blog_Article` (
	`Id`					int			not null	auto_increment	primary key,
	`Fk_Blog`				int			not null,
	`Title`					varchar(128)not null,
	`Dt_Created`			datetime	not null,
	`Dt_Publish`			datetime	not null,
	`Dt_Publish_End`		datetime	null,
	`Fk_User`				int			not null,
	`Content`				text		not null,
	`Is_Private`			bool		null,
	
	index `Fk_Blog_Article__Blog` (`Fk_Blog` asc),
	constraint `Fk_Blog_Article__Blog`
		foreign key (`Fk_Blog`)
		references `Blog` (`Id`)
		on delete cascade
		on update cascade
)
COMMENT = ""
ENGINE = InnoDB;