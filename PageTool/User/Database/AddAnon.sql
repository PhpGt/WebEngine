# Creates a new user without a unsername, identified by the Uuid.
insert into User (
	Uuid,
	Dt_Created
)
values (
	:Uuid,
	now()
);