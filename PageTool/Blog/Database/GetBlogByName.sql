select 
	ID,
	name
from
	Blog
where 
	name = :name
limit 1;