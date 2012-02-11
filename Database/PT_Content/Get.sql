select
	`Id`,
	`Name`,
	`Fk_PT_Content_Type`,
	(select `PT_Content_Type`.`Name`
		from `PT_Content_Type`
		where `PT_Content_Type`.`Id` = `Fk_PT_Content_Type`
	) as `L_Type`,
	(select `PT_Content_Element`.`Value`
		from `PT_Content_Element`
		where `PT_Content_Element`.`Fk_PT_Content` = `PT_Content`.`Id`
			and `PT_Content_Element`.`Dt_RolledBack` is null
		order by `PT_Content_Element`.`Dt_Modified` desc
		limit 1
	) as `Value`
from `PT_Content`
where `Name` = :Name
limit 1