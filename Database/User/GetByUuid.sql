select
	`Id`,
	`Username`
from `User`
where `Uuid` = :Uuid
limit 1;