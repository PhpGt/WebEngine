create table if not exists `Blog_Article_Comment` (
	`Id`					int			not null	auto_increment	primary key,
	`Fk_Blog_Article`		int			not null,
	`Fk_Blog_Article_Comment__Reply` int null,
	`EmailReplies`			bool		null,
	`Dt_Created`			datetime	not null,
	`Dt_Deleted`			datetime	not null,
	`Is_Published`			bool		null,
	`Fk_User`				int			null,
	`Name`					varchar(32)	null,
	`Location`				varchar(32) null,
	`Email`					varchar(32)	null,
	`Website`				varchar(32)	null,
	`Gravatar`				varchar(32)	null,
	`Content`				text		not null,
	
	index `Fk_Blog_Article_Comment__Blog_Article` (`Fk_Blog_Article` asc),
	index `Fk_Blog_Article_Comment__Blog_Article_Comment` (`Fk_Blog_Article_Comment__Reply`),
	index `Fk_Blog_Article_Comment__User` (`Fk_User`),
	constraint `Fk_Blog_Article_Comment__Blog_Article`
		foreign key (`Fk_Blog_Article`)
		references `Blog_Article` (`Id`)
		on delete cascade
		on update cascade,
	constraint `Fk_Blog_Article_Comment__Blog_Article_Comment`
		foreign key (`Fk_Blog_Article_Comment__Reply`)
		references `Blog_Article_Comment` (`Id`)
		on delete set null
		on update cascade,
	constraint `Fk_Blog_Article_Comment__User`
		foreign key (`Fk_User`)
		references `User` (`Id`)
		on delete cascade
		on update cascade
)
COMMENT = "Optionally links to user table, or use anon users."
ENGINE = InnoDB;