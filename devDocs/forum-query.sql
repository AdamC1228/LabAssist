-- This query will select all questions for the current term in a specific
-- department.
WITH filt_classes AS (
	SELECT * FROM classes WHERE classes.dept = 'CS-IS'
)
SELECT filt_classes.name AS classname, questions.title, users.username AS author,
	questions.status, posts.added, questions.quid
	FROM questions
	JOIN term_sections ON questions.subject = term_sections.secid
	JOIN filt_classes  ON term_sections.cid = filt_classes.cid
	JOIN users         ON questions.asker   = users.idno
	JOIN posts         ON questions.quid    = posts.question
	WHERE posts.added = (SELECT MAX(posts.added) FROM posts WHERE posts.question = questions.quid);

-- This query will select all of the departments that have at least one question
-- attached to them
SELECT departments.deptid, departments.deptname,
	COUNT(questions.quid) AS question_count,
	COUNT(questions.quid) FILTER (WHERE questions.status = 'awaiting_response') AS unanswered_count
	FROM departments
	LEFT JOIN classes       ON departments.deptid  = classes.dept
	LEFT JOIN term_sections ON classes.cid         = term_sections.cid
	LEFT JOIN questions     ON term_sections.secid = questions.subject
	GROUP BY departments.deptid ORDER BY departments.deptname;

-- Select all of the posts in a thread for a particular question
SELECT users.role, users.username, users.email, posts.added, posts.body,
	user_avatars.avatar
	FROM posts
	JOIN users ON posts.author = users.idno
	LEFT OUTER JOIN user_avatars ON users.idno = user_avatars.idno
