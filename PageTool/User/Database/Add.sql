insert into User (
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