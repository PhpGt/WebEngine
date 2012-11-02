insert into User (
	Username,
	Uuid,
	Dt_Created
)
values (
	:Username,
	:Uuid,
	now()
);