# Creates a new user with only a username, with no other attributes.
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