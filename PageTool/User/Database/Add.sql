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