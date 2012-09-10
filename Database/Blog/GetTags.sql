select
	`Blog_Tag`.`Id`,
	`Blog_Tag`.`Name`
from `Blog_Tag`
inner join `Blog_Article__J__Blog_Tag`
	on `Blog_Article__J__Blog_Tag`.`Fk_Blog_Tag` = `Blog_Tag`.`Id`
inner join `Blog_Article`
	on `Blog_Article`.`Id` = `Blog_Article__J__Blog_Tag`.`Fk_Blog_Article`
where `Blog_Article`.`Id` = :Id;