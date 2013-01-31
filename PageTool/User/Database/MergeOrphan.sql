update User
set
	FK_User__orphanedBy = :ID
where
	ID = :orphanedID
limit 1;