create table if not exists `Blog_Article__J__Blog_Tag` (
	`Fk_Blog_Article`		int			not null,
	`Fk_Blog_Tag`			int			not null,
	primary key(`Fk_Blog_Article`, `Fk_Blog_Tag`),
	index `Fk_Blog_Article__J__Blog_Tag__Blog_Article` (`Fk_Blog_Article` asc),
	index `Fk_Blog_Article__J__Blog_Tag__Blog_Tag` (`Fk_Blog_Tag` asc),

	constraint `Fk_Blog_Article__J__Blog_Tag__Blog_Article`
		foreign key (`Fk_Blog_Article`)
		references `Blog_Article` (`Id`)
		on delete cascade
		on update cascade,
	constraint `Fk_Blog_Article__J__Blog_Tag__Blog_Tag`
		foreign key (`Fk_Blog_Tag`)
		references `Blog_Tag` (`Id`)
		on delete cascade
		on update cascade
)
COMMENT = ""
ENGINE = InnoDB;