insert into User (
	Username,
	Dt_Created
)
values (
	:Username,
	now()
);