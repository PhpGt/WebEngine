select
	`Blog_Tag`.`Id`,
	`Blog_Tag`.`Name`,
	count(`Blog_Tag`.`Id`)as Count
from `Blog_Tag`
left join `Blog_Article__J__Blog_Tag` j
	on j.`Fk_Blog_Tag` = `Blog_Tag`.`Id`
left join `Blog_Article`
	on `Blog_Article`.`Id` = j.`Fk_Blog_Article`
where `Blog_Article`.`Fk_Blog` = :BlogId
group by `Blog_Tag`.`Id`
order by `Count` desc;