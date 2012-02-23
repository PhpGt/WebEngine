create table if not exists `Content_Element` (
	`Id`					int			not null	auto_increment	primary key,
	`Fk_Content`			int			not null,
	`Value`					text		not null,
	`Dt_Modified`			datetime	not null,
	`Dt_RolledBack`			datetime	null,
	index `Fk_Content_Element__Content` (`Fk_Content` asc),
	constraint `Fk_Content_Element__Content`
		foreign key (`Fk_Content`)
		references `Content` (`Id`)
		on delete cascade
		on update cascade
)
COMMENT = "Multiple versions of content."
ENGINE = InnoDB;