select
	`Blog_Article`.`Id`,
	`Fk_Blog`,
	`Blog`.`Name` as `BlogName`,
	`Title`,
	`Dt_Created`,
	`Dt_Publish`,
	`Dt_Publish_End`,
	`Fk_User`,
	concat_ws(' ', `User`.`FirstName`, `User`.`LastName`
	)as `UserFullName`,
	`Content`,
	`Blog`.`PreviewLength`,
	`Is_Private`,
	`Is_Featured`
from `Blog_Article`
inner join (`Blog`)
	on `Blog.Id` = `Fk_Blog`,
left join (`Blog_Article__J__Blog_Tag`)
	on `Blog_Article__J__Blog_Tag`.`Fk_Blog_Article` = `Blog_Article`.`Id`
left join (`Blog_Tag`)
	on `Blog_Tag`.`Id` = `Blog_Article__J__Blog_Tag`.`Fk_Blog_Tag`
left join (`User`)
	on `User`.`Id` = `Fk_User`
order by `Dt_Publish` desc
limit :Limit;