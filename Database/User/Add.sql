insert into User (
	Fk_User_Department,
	Username,
	Dt_Created,
	FirstName,
	LastName,
	Email,
	JobTitle,
	Telephone,
	TelephoneMobile
)
values (
	:Fk_User_Department,
	:Username,
	now(),
	:FirstName,
	:LastName,
	:Email,
	:JobTitle,
	:Telephone,
	:TelephoneMobile
);