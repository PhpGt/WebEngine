update User set
	`Fk_User_Type` 			= `Fk_User_Type` + 1,
	`Fk_User_Department` 	= :Fk_User_Department,
	`JobTitle` 				= :JobTitle,
	`Telephone` 			= :Telephone,
	`TelephoneMobile` 		= :TelephoneMobile,
	`FirstName`				= :FirstName,
	`LastName`				= :LastName
where
	Id = :Id
limit 1;