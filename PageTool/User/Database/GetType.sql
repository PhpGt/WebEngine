select 
	`User_Type`.`Id` as `Id`,
	`User_Type`.`Name` as `Name`
from `User`
inner join `User_Type`
	on (`User_Type`.`Id` = `User`.`Fk_User_Type`)
where `User`.`Id` = :Id
limit 1;