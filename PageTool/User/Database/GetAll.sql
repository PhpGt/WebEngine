select
	*,
	`User`.`Id`,
	`User_Type`.`Name` as `User_Type_Name`
from `User`
inner join `User_Type`
	on (`User_Type`.`Id` = `User`.`Fk_User_Type`);