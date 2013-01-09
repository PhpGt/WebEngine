# Creates a new user without a unsername, identified by the Uuid.
insert into User (
	Uuid,
	Username,
	Dt_Created
)
values (
	:Uuid,
	null,
	now()
);