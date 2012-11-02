select
	*,
	(select `Name`
		from `User_Type`
		where `User_Type`.`Id` = `Fk_User_Type`
	) as `L_User_Type`,
	`Fk_User_Department`,
	(select `Name`
		from `User_Department`
		where `User_Department`.`Id` = `Fk_User_Department`
	) as `L_User_Department`,
	(select concat(`FirstName`, " ", `LastName`)
	) as `FullName`
from `User`
where `Username` = :Username
limit 1;