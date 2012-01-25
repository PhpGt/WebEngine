select
	Id,
	Name,
	Fk_Content_Type,
	(select Content_Type.Name
		from Content_Type
		where Content_Type.Id = Fk_Content_Type
	) as L_Content_Type,
	(select Content_Element.Value
		from Content_Element
		where Content_Element.Fk_Content = Content.Id
			and Content_Element.Dt_RolledBack is null
		order by Content_Element.Dt_Modified desc
		limit 1
	) as Value
from Content
where Name = :Name
limit 1