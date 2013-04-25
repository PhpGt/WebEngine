# Upgrades an anonymous user to a regular user by associating a username and
# incrementing the user's type id.
update User
set username = :username,
	dateTimeIdentified = now(),
	FK_User_Type = (
		select User_Type.ID
		from User_Type
		where User_Type.name = "User"
	)
where uuid = :uuid
limit 1;