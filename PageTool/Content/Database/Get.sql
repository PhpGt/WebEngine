select
	ID,
	dateTime,
	value
from
	Content

where
	ID = :ID
and
	dateTimeDeleted is null
	
order by 
	dateTime desc
limit 1