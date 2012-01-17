select
	Id,
	Fk_User_Type,
	(select Name
		from User_Type
		where User_Type.Id = Fk_User_Type) as N_User_Type,
	Fk_User_Department,
	(select Name
		from User_Department
		where User_Department.Id = Fk_User_Department) as N_User_Department,
	Username,
	FirstName,
	LastName,
	(select concat(FirstName, " ", LastName)) as FullName,
	Email,
	JobTitle,
	Telephone,
	TelephoneMobile,
	Gravatar
from User
where Username = :Username
limit 1;