select
	Blog_Article.ID,
	Blog.ID as ID_Blog,
	Blog_User.ID as ID_User,
	Blog_User.username,
	Blog_User.firstName,
	Blog_User.lastName,
	concat_ws(" ", Blog_User.firstName, Blog_User.lastName)as fullName,
	Blog_Article.title,
	Blog_Article.content,
	Blog_Article.coverImageUrl,
	Blog_Article.dateTimeCreated,
	ifnull(Blog_Article.dateTimePublished, Blog_Article.dateTimeCreated
		)as dateTimePublished,
	Blog_Article.dateTimeFeatured,
	Blog_Article.dateTimeUnfeatured,
	count(Blog_Article_Comment.ID) as commentCount
from
	Blog_Article

inner join Blog
on
	Blog_Article.FK_Blog = Blog.ID

inner join Blog_User
on
	Blog_Article.FK_Blog_User__author = Blog_User.ID

-- Left join because there may not necessarily be any comments.
left join Blog_Article_Comment
on
	Blog_Article.ID = Blog_Article_Comment.FK_Blog_Article

where
	Blog.name = :name_Blog
and
	dateTimeDeleted is null

-- We group on the comment's ID field so that the count() function doesn't
-- force an empty row on an empty result. 
group by Blog_Article_Comment.ID

limit :Limit