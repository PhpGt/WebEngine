select
	*,
	`User`.`ID`,
	`User_Type`.`name` as `User_Type__name`
from `User`
inner join `User_Type`
	on (`User_Type`.`ID` = `User`.`FK_User_Type`)
where `User`.`ID` = :ID
limit 1;