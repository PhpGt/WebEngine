update User set
	`Fk_User_Type` 			= `Fk_User_Type` + 1,
	`JobTitle` 				= :JobTitle,
	`Telephone` 			= :Telephone,
	`TelephoneMobile` 		= :TelephoneMobile,
	`FirstName`				= :FirstName,
	`LastName`				= :LastName
where
	Id = :Id
limit 1;