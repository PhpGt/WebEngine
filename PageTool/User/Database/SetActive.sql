update User
set dateTimeLastActive = now(),
	activityCount = activityCount + 1
where ID = :ID
limit 1;