# Creates a new user without a unsername, identified by the Uuid.
insert into User (
	uuid,
	username,
	dateTimeCreated
)
values (
	:uuid,
	null,
	now()
);