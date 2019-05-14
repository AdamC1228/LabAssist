-- Report List
-- Tutor Scheduling
-- 	- Total number of hours assigned vs. available for each
-- 		* section
-- 		* tutor
-- Lab Usage
-- 	- Number of hours

-- Scheduled vs. Available Hours by student
-- 	This version works in all cases, but loses/gains minutes somewhere
--WITH scheduled_times AS (
--	SELECT 
--		SUM(schedules.endtime - schedules.starttime) +
--			make_time(0, (GREATEST(1, COUNT(schedules.endtime)/2)::integer), 0) AS scheduled_hours,
--		schedules.student AS student
--		FROM schedules
--		GROUP BY schedules.student
--), available_times AS (
--	SELECT
--		SUM(availability.endtime - availability.starttime) +
--			make_time(0, (GREATEST(1, COUNT(availability.endtime)/2)::integer), 0) AS available_hours,
--		availability.student AS student
--		FROM availability
--		GROUP BY availability.student
--)
--SELECT scheduled_times.scheduled_hours + interval '1 minute' AS scheduled_hours,
--	available_times.available_hours AS available_hours,
--	scheduled_times.student
--	FROM scheduled_times
--	JOIN available_times ON scheduled_times.student = available_times.student;

-- Scheduled vs. Available Hours by department
-- 	This version works in all cases, but loses/gains minutes somewhere
--WITH scheduled_times AS (
--	SELECT 
--		SUM(schedules.endtime - schedules.starttime) +
--			make_time(0, (GREATEST(1, COUNT(schedules.endtime)/2)::integer), 0) AS scheduled_hours,
--		schedules.dept AS dept
--		FROM schedules
--		GROUP BY schedules.dept
--), available_times AS (
--	SELECT
--		SUM(availability.endtime - availability.starttime) +
--			make_time(0, (GREATEST(1, COUNT(availability.endtime)/2)::integer), 0) AS available_hours,
--		availability.dept AS dept
--		FROM availability
--		GROUP BY availability.dept
--)
--SELECT scheduled_times.scheduled_hours + interval '1 minute' AS scheduled_hours,
--	available_times.available_hours AS available_hours,
--	scheduled_times.dept
--	FROM scheduled_times
--	JOIN available_times ON scheduled_times.dept = available_times.dept;

-- REVISED QUERIES
-- Scheduled vs. Available Hours by student
WITH scheduled as (
	SELECT COUNT(schedules.endtime) * interval '30 minutes' as hours,
		schedules.student FROM schedules GROUP BY schedules.student
), available as (
	SELECT COUNT(availability.endtime) * interval '30 minutes' as hours,
		availability.student FROM availability GROUP BY availability.student
)
SELECT scheduled.hours as "scheduled", available.hours as "available", scheduled.student
	FROM scheduled JOIN available ON scheduled.student = available.student;

-- Scheduled vs. Available Hours by department
WITH scheduled as (
	SELECT COUNT(schedules.endtime) * interval '30 minutes' as hours,
		schedules.dept FROM schedules GROUP BY schedules.dept
), available as (
	SELECT COUNT(availability.endtime) * interval '30 minutes' as hours,
		availability.dept FROM availability GROUP BY availability.dept
)
SELECT scheduled.hours as "scheduled", available.hours as "available", scheduled.dept
	FROM scheduled JOIN available ON scheduled.dept = available.dept;

-- Count number of unique students
-- 	Add where clauses/filtered tables as necessary
SELECT DISTINCT ON (usage.student)
	usage.student, term_sections.code, classes.cid 
	FROM usage
	JOIN term_sections ON usage.secid       = term_sections.secid
	JOIN classes       ON term_sections.cid = classes.cid;

-- Get the total number of hours each student is using per section
--	VIEW student_total_usage
SELECT users.idno, users.realname, users.role,
	SUM(usage.markout - usage.markin) as total_hours
	FROM usage
	JOIN term_sections ON usage.secid   = term_sections.secid
	JOIN users         ON usage.student = users.idno
	WHERE usage.markout IS NOT NULL
	GROUP BY usage.student, users.realname, users.role, users.idno
	ORDER BY users.role, users.realname;

-- Get the total number of hours by section
SELECT classes.cid, usage.secid, term_sections.teacher, SUM(usage.markout - usage.markin) as total_hours,
	GROUPING(classes.cid, usage.secid, term_sections.teacher) as level
	FROM usage
	JOIN term_sections ON usage.secid = term_sections.secid
	JOIN classes       ON classes.cid = term_sections.cid
	WHERE usage.markout IS NOT NULL
	GROUP BY CUBE(usage.secid, classes.cid, term_sections.teacher)
	ORDER BY level, classes.cid, usage.secid, term_sections.teacher;
