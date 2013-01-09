update User
set Dt_LastActive = now(),
	ActivityCount = ActivityCount + 1
where Id = :Id
limit 1;