select
	Blog_Article.ID,
	Blog_Article.title,
	Blog_Article.dateTimePublish,
	
	Blog_Article.content,
	-- If there is a <hr>, break the preview on it, otherwise use the Blog's
	-- default preview length.
	if(instr(Blog_Article.content, "<hr") > 0,
		substring_index(Blog_Article.content, "<hr", 1),
		concat(substring(Blog_Article.content from 1 for Blog.previewLength),
			"...")
	)as preview,
	
	Blog_Article.isFeatured,
	Blog_Article.isPrivate,
	count(distinct Blog_Article_Comment.ID)as num_Blog_Article_Comment,
	group_concat(Blog_Tag.name)as list_Blog_Article_Tag
from
	Blog_Article

inner join 
	Blog
on
	Blog_Article.FK_Blog = Blog.ID
left join
	Blog_Article_Comment
on
	Blog_Article.ID = Blog_Article_Comment.FK_Blog_Article

left join
	Blog_Article__Blog_Tag
on
	Blog_Article.ID = Blog_Article__Blog_Tag.FK_Blog_Article
left join
	Blog_Tag
on
	Blog_Article__Blog_Tag.FK_Blog_Tag = Blog_Tag.ID

where Blog.name = :name_Blog
and
	(
		dateTimePublishEnd is null
		or dateTimePublishEnd > now()
	)
group by
	Blog_Article.ID
order by
	dateTimePublish desc
limit :Limit;