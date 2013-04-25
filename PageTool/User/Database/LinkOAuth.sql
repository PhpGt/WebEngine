insert into User_OAuth (
	FK_User,
	oauth_uuid,
	oauth_name
) values (
	:FK_User,
	:oauth_uuid,
	:oauth_name
);