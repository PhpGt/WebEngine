select 
	`User`.`Fk_User_Type` as `Id`,
	(select `Name` from `User_Type`
		where `User_Type`.`Id` = `User`.`Fk_User_Type`
	)as `Name`
from `User`
where `User`.`Id` = :Id
limit 1;