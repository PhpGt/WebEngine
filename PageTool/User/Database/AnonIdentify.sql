# Upgrades an anonymous user to a regular user by associating a username and
# incrementing the user's type id.
update User
set Username = :Username,
	Fk_User_Type = (
		select User_Type.Id
		from User_Type
		where User_Type.Name = "User"
	),
	Dt_Identified = now()
where Uuid = :Uuid
limit 1;