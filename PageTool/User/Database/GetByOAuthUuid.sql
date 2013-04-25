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
inner join User_OAuth
	on (User_OAuth.FK_User = User.ID)
where User_OAuth.oauth_uuid = :oauth_uuid
and User.dateTimeDeleted is null
limit 1;