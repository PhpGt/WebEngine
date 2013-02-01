select
	ID,
	FullName,
	Website,
	Email,
	isAuthor
from
	Blog_User
where
	FK_User = :ID
limit 1;