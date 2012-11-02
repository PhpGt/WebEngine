select
	`Blog_Article`.`Id`,
	`Fk_Blog`,
	(select `Blog`.`Name`
		from `Blog` 
		where `Blog`.`Id` = `Fk_Blog`
	)as `L_Blog_Name`,
	`Title`,
	`Dt_Created`,
	`Dt_Publish`,
	`Dt_Publish_End`,
	`Fk_User`,
	(select concat(`User`.`FirstName`, " ", `User`.`LastName`)
		from `User`
		where `User`.`Id` = `Fk_User`
	)as `L_User_FullName`,
	`Content`,
	(select `Blog`.`PreviewLength`
		from `Blog`
		where `Blog`.`Id` = `Blog_Article`.`Fk_Blog`
	)as `PreviewLength`,
	`Is_Private`
from `Blog_Article`
inner join (`Blog_Article__J__Blog_Tag`)
	on (`Blog_Article__J__Blog_Tag`.`Fk_Blog_Article` = `Blog_Article`.`Id`)
inner join (`Blog_Tag`)
	on (`Blog_Tag`.`Id` = `Blog_Article__J__Blog_Tag`.`Fk_Blog_Tag`)
where `Blog_Tag`.`Name` = :TagName
order by `Dt_Publish` desc;