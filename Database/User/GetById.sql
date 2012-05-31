select
	`Id`,
	`Username`,
	`FirstName`,
	`LastName`
from `User`
where `Id` = :Id
limit 1;