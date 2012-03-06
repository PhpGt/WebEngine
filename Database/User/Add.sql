insert into User (
	Fk_User_Department,
	Username,
	Uuid,
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
	:Uuid,
	now(),
	:FirstName,
	:LastName,
	:Email,
	:JobTitle,
	:Telephone,
	:TelephoneMobile
);