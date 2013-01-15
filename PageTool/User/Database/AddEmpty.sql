# Creates a new user with only a username, with no other attributes.
insert into User (
	username,
	uuid,
	dateTimeCreated
)
values (
	:username,
	:uuid,
	now()
);