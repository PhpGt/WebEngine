update User set
	Fk_User_Type = Fk_User_Type + 1
where
	Id = :Id
limit 1;