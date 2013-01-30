select
	User.ID,
	User.uuid,
	User.username,
	User.dateTimeIdentified,
	User.dateTimeLastActive,
	User_Type.name as User_Type__name
from User
inner join User_Type
	on (User_Type.ID = User.FK_User_Type)
where username = :username
and User.dateTimeDeleted is null
limit 1;