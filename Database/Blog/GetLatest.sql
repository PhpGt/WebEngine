select
	`Blog_Article`.`Id`,
	`Fk_Blog`,
	`Fk_User`,
	`Blog`.`Name` as `BlogName`,
	`Title`,
	`Blog_Article`.`Dt_Created`,
	`Blog_Article`.`Dt_Publish`,
	`Blog_Article`.`Dt_Publish_End`,
	concat_ws(' ', `User`.`FirstName`, `User`.`LastName`
	)as `UserFullName`,
	`Content`,
	`Blog`.`PreviewLength`,
	`Is_Private`,
	`Is_Featured`
from `Blog_Article`
inner join (`Blog`)
	on `Blog`.`Id` = `Fk_Blog`
left join (`User`)
	on `User`.`Id` = `Fk_User`
order by `Dt_Publish` desc
limit :Limit;