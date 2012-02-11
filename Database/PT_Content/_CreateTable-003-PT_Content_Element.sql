create table if not exists `PT_Content_Element` (
	`Id`					int			not null	auto_increment	primary key,
	`Fk_PT_Content`			int			not null,
	`Value`					text		not null,
	`Dt_Modified`			datetime	not null,
	`Dt_RolledBack`			datetime	null,
	index `Fk_PT_Content_Element__PT_Content` (`Fk_PT_Content` asc),
	constraint `Fk_PT_Content_Element__PT_Content`
		foreign key (`Fk_PT_Content`)
		references `PT_Content` (`Id`)
		on delete cascade
		on update cascade
)
COMMENT = "Multiple versions of content."
ENGINE = InnoDB;