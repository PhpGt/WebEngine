select
	Blog_Article.ID,
	Blog.ID as ID_Blog,
	Blog_User.ID as ID_User,
	Blog_User.username,
	Blog_User.firstName,
	Blog_User.lastName,
	title,
	content,
	coverImageUrl,
	dateTimeCreated,
	dateTimePublished,
	dateTimeFeatured,
	dateTimeUnfeatured,
	count(Blog_Article_Comment) as commentCount
from
	Blog_Article

inner join Blog
on
	Blog_Article.FK_Blog = Blog.ID

inner join Blog_User
on
	Blog_Article.FK_Blog_User__author = Blog_User.ID

where
	Blog.name = :name_Blog
and
	dateTimeDeleted is null

limit :Limit