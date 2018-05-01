--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.8
-- Dumped by pg_dump version 9.6.8

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: deptid; Type: DOMAIN; Schema: public; Owner: labassist
--

CREATE DOMAIN public.deptid AS character varying(6);


ALTER DOMAIN public.deptid OWNER TO labassist;

--
-- Name: msgtype; Type: TYPE; Schema: public; Owner: labassist
--

CREATE TYPE public.msgtype AS ENUM (
    'PENDING_QUESTION',
    'SCHEDULE_CHANGED'
);


ALTER TYPE public.msgtype OWNER TO labassist;

--
-- Name: question_status; Type: TYPE; Schema: public; Owner: labassist
--

CREATE TYPE public.question_status AS ENUM (
    'awaiting_response',
    'answered'
);


ALTER TYPE public.question_status OWNER TO labassist;

--
-- Name: role; Type: TYPE; Schema: public; Owner: labassist
--

CREATE TYPE public.role AS ENUM (
    'sysadmin',
    'student',
    'tutor',
    'staff',
    'admin',
    'developer'
);


ALTER TYPE public.role OWNER TO labassist;

--
-- Name: termcode; Type: DOMAIN; Schema: public; Owner: labassist
--

CREATE DOMAIN public.termcode AS character(6);


ALTER DOMAIN public.termcode OWNER TO labassist;

--
-- Name: userid; Type: DOMAIN; Schema: public; Owner: labassist
--

CREATE DOMAIN public.userid AS character(9);


ALTER DOMAIN public.userid OWNER TO labassist;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: availability; Type: TABLE; Schema: public; Owner: labassist
--

CREATE TABLE public.availability (
    student public.userid NOT NULL,
    starttime timestamp without time zone NOT NULL,
    endtime timestamp without time zone NOT NULL,
    dept character varying(6) NOT NULL,
    term public.termcode NOT NULL
);


ALTER TABLE public.availability OWNER TO labassist;

--
-- Name: classes; Type: TABLE; Schema: public; Owner: labassist
--

CREATE TABLE public.classes (
    cid integer NOT NULL,
    dept character varying(6) NOT NULL,
    name character varying(255) NOT NULL
);


ALTER TABLE public.classes OWNER TO labassist;

--
-- Name: classes_cid_seq; Type: SEQUENCE; Schema: public; Owner: labassist
--

CREATE SEQUENCE public.classes_cid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.classes_cid_seq OWNER TO labassist;

--
-- Name: classes_cid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: labassist
--

ALTER SEQUENCE public.classes_cid_seq OWNED BY public.classes.cid;


--
-- Name: departments; Type: TABLE; Schema: public; Owner: labassist
--

CREATE TABLE public.departments (
    deptid character varying(6) NOT NULL,
    deptname character varying(255) NOT NULL
);


ALTER TABLE public.departments OWNER TO labassist;

--
-- Name: users; Type: TABLE; Schema: public; Owner: labassist
--

CREATE TABLE public.users (
    idno public.userid NOT NULL,
    deptid character varying(6),
    username character varying(255) NOT NULL,
    realname character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    role public.role NOT NULL
);


ALTER TABLE public.users OWNER TO labassist;

--
-- Name: dept_stats; Type: VIEW; Schema: public; Owner: labassist
--

CREATE VIEW public.dept_stats AS
 WITH class_counts AS (
         SELECT departments_1.deptid,
            count(classes.cid) AS classcount
           FROM (public.departments departments_1
             LEFT JOIN public.classes ON (((departments_1.deptid)::text = (classes.dept)::text)))
          GROUP BY departments_1.deptid
        ), prof_counts AS (
         SELECT departments_1.deptid,
            count(filt_users.idno) AS profcount
           FROM (public.departments departments_1
             LEFT JOIN ( SELECT users.idno,
                    users.deptid,
                    users.username,
                    users.realname,
                    users.email,
                    users.role
                   FROM public.users
                  WHERE (users.role >= 'staff'::public.role)) filt_users ON (((departments_1.deptid)::text = (filt_users.deptid)::text)))
          GROUP BY departments_1.deptid
        )
 SELECT departments.deptid,
    departments.deptname,
    class_counts.classcount,
    prof_counts.profcount
   FROM ((public.departments
     JOIN class_counts ON (((departments.deptid)::text = (class_counts.deptid)::text)))
     JOIN prof_counts ON (((departments.deptid)::text = (prof_counts.deptid)::text)));


ALTER TABLE public.dept_stats OWNER TO labassist;

--
-- Name: deptlabs; Type: TABLE; Schema: public; Owner: labassist
--

CREATE TABLE public.deptlabs (
    dept character varying(6) NOT NULL,
    labstart time without time zone NOT NULL,
    labend time without time zone NOT NULL
);


ALTER TABLE public.deptlabs OWNER TO labassist;

--
-- Name: forum_overview; Type: TABLE; Schema: public; Owner: labassist
--

CREATE TABLE public.forum_overview (
    deptid character varying(6),
    deptname character varying(255),
    question_count bigint,
    unanswered_count bigint
);

ALTER TABLE ONLY public.forum_overview REPLICA IDENTITY NOTHING;


ALTER TABLE public.forum_overview OWNER TO labassist;

--
-- Name: pageaccess; Type: TABLE; Schema: public; Owner: labassist
--

CREATE TABLE public.pageaccess (
    role public.role NOT NULL,
    page text NOT NULL
);


ALTER TABLE public.pageaccess OWNER TO labassist;

--
-- Name: pendingmsgs; Type: TABLE; Schema: public; Owner: labassist
--

CREATE TABLE public.pendingmsgs (
    msgid integer NOT NULL,
    recipient public.userid NOT NULL,
    mstype public.msgtype NOT NULL,
    body text NOT NULL
);


ALTER TABLE public.pendingmsgs OWNER TO labassist;

--
-- Name: pendingmsgs_msgid_seq; Type: SEQUENCE; Schema: public; Owner: labassist
--

CREATE SEQUENCE public.pendingmsgs_msgid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pendingmsgs_msgid_seq OWNER TO labassist;

--
-- Name: pendingmsgs_msgid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: labassist
--

ALTER SEQUENCE public.pendingmsgs_msgid_seq OWNED BY public.pendingmsgs.msgid;


--
-- Name: posts; Type: TABLE; Schema: public; Owner: labassist
--

CREATE TABLE public.posts (
    postid integer NOT NULL,
    question integer NOT NULL,
    author public.userid NOT NULL,
    body text NOT NULL,
    is_question boolean NOT NULL,
    added timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE public.posts OWNER TO labassist;

--
-- Name: posts_postid_seq; Type: SEQUENCE; Schema: public; Owner: labassist
--

CREATE SEQUENCE public.posts_postid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.posts_postid_seq OWNER TO labassist;

--
-- Name: posts_postid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: labassist
--

ALTER SEQUENCE public.posts_postid_seq OWNED BY public.posts.postid;


--
-- Name: questions; Type: TABLE; Schema: public; Owner: labassist
--

CREATE TABLE public.questions (
    quid integer NOT NULL,
    subject integer NOT NULL,
    title character varying(255) NOT NULL,
    asker public.userid NOT NULL,
    status public.question_status NOT NULL,
    added timestamp without time zone DEFAULT now() NOT NULL,
    term public.termcode NOT NULL
);


ALTER TABLE public.questions OWNER TO labassist;

--
-- Name: questions_quid_seq; Type: SEQUENCE; Schema: public; Owner: labassist
--

CREATE SEQUENCE public.questions_quid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.questions_quid_seq OWNER TO labassist;

--
-- Name: questions_quid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: labassist
--

ALTER SEQUENCE public.questions_quid_seq OWNED BY public.questions.quid;


--
-- Name: schedules; Type: TABLE; Schema: public; Owner: labassist
--

CREATE TABLE public.schedules (
    student public.userid NOT NULL,
    starttime timestamp without time zone NOT NULL,
    endtime timestamp without time zone NOT NULL,
    dept public.deptid NOT NULL,
    term public.termcode NOT NULL
);


ALTER TABLE public.schedules OWNER TO labassist;

--
-- Name: sections; Type: TABLE; Schema: public; Owner: labassist
--

CREATE TABLE public.sections (
    secid integer NOT NULL,
    code character(3) NOT NULL,
    cid integer NOT NULL,
    term public.termcode NOT NULL,
    teacher public.userid NOT NULL
);


ALTER TABLE public.sections OWNER TO labassist;

--
-- Name: sections_secid_seq; Type: SEQUENCE; Schema: public; Owner: labassist
--

CREATE SEQUENCE public.sections_secid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.sections_secid_seq OWNER TO labassist;

--
-- Name: sections_secid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: labassist
--

ALTER SEQUENCE public.sections_secid_seq OWNED BY public.sections.secid;


--
-- Name: staff_users; Type: VIEW; Schema: public; Owner: labassist
--

CREATE VIEW public.staff_users AS
 SELECT users.idno,
    users.deptid,
    users.username,
    users.realname,
    users.email,
    users.role
   FROM public.users
  WHERE (users.role >= 'staff'::public.role);


ALTER TABLE public.staff_users OWNER TO labassist;

--
-- Name: terms; Type: TABLE; Schema: public; Owner: labassist
--

CREATE TABLE public.terms (
    code public.termcode NOT NULL,
    activeterm boolean
);


ALTER TABLE public.terms OWNER TO labassist;

--
-- Name: term_sections; Type: VIEW; Schema: public; Owner: labassist
--

CREATE VIEW public.term_sections AS
 SELECT sections.secid,
    sections.code,
    sections.cid,
    sections.term,
    sections.teacher
   FROM public.sections
  WHERE ((sections.term)::bpchar = (( SELECT terms.code
           FROM public.terms
          WHERE (terms.activeterm = true)))::bpchar);


ALTER TABLE public.term_sections OWNER TO labassist;

--
-- Name: usage; Type: TABLE; Schema: public; Owner: labassist
--

CREATE TABLE public.usage (
    student public.userid NOT NULL,
    secid integer NOT NULL,
    markin timestamp without time zone NOT NULL,
    markout timestamp without time zone,
    CONSTRAINT chk_markin_before_markout CHECK ((markin < markout))
);


ALTER TABLE public.usage OWNER TO labassist;

--
-- Name: student_total_usage; Type: VIEW; Schema: public; Owner: labassist
--

CREATE VIEW public.student_total_usage AS
 SELECT users.idno,
    users.realname,
    users.role,
    sum((usage.markout - usage.markin)) AS total_hours
   FROM ((public.usage
     JOIN public.term_sections ON ((usage.secid = term_sections.secid)))
     JOIN public.users ON (((usage.student)::bpchar = (users.idno)::bpchar)))
  WHERE (usage.markout IS NOT NULL)
  GROUP BY usage.student, users.realname, users.role, users.idno
  ORDER BY users.role, users.realname;


ALTER TABLE public.student_total_usage OWNER TO labassist;

--
-- Name: user_avatars; Type: TABLE; Schema: public; Owner: labassist
--

CREATE TABLE public.user_avatars (
    idno public.userid NOT NULL,
    avatar bytea NOT NULL
);


ALTER TABLE public.user_avatars OWNER TO labassist;

--
-- Name: classes cid; Type: DEFAULT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.classes ALTER COLUMN cid SET DEFAULT nextval('public.classes_cid_seq'::regclass);


--
-- Name: pendingmsgs msgid; Type: DEFAULT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.pendingmsgs ALTER COLUMN msgid SET DEFAULT nextval('public.pendingmsgs_msgid_seq'::regclass);


--
-- Name: posts postid; Type: DEFAULT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.posts ALTER COLUMN postid SET DEFAULT nextval('public.posts_postid_seq'::regclass);


--
-- Name: questions quid; Type: DEFAULT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.questions ALTER COLUMN quid SET DEFAULT nextval('public.questions_quid_seq'::regclass);


--
-- Name: sections secid; Type: DEFAULT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.sections ALTER COLUMN secid SET DEFAULT nextval('public.sections_secid_seq'::regclass);


--
-- Name: availability availability_pkey; Type: CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.availability
    ADD CONSTRAINT availability_pkey PRIMARY KEY (student, starttime, endtime, dept);


--
-- Name: classes classes_pkey; Type: CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT classes_pkey PRIMARY KEY (cid);


--
-- Name: departments departments_deptname_key; Type: CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.departments
    ADD CONSTRAINT departments_deptname_key UNIQUE (deptname);


--
-- Name: departments departments_pkey; Type: CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.departments
    ADD CONSTRAINT departments_pkey PRIMARY KEY (deptid);


--
-- Name: deptlabs deptlabs_pkey; Type: CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.deptlabs
    ADD CONSTRAINT deptlabs_pkey PRIMARY KEY (dept);


--
-- Name: pageaccess pageaccess_pkey; Type: CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.pageaccess
    ADD CONSTRAINT pageaccess_pkey PRIMARY KEY (role, page);


--
-- Name: pendingmsgs pendingmsgs_pkey; Type: CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.pendingmsgs
    ADD CONSTRAINT pendingmsgs_pkey PRIMARY KEY (msgid);


--
-- Name: posts posts_pkey; Type: CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.posts
    ADD CONSTRAINT posts_pkey PRIMARY KEY (postid, question);


--
-- Name: questions questions_pkey; Type: CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT questions_pkey PRIMARY KEY (quid);


--
-- Name: schedules schedules_pkey; Type: CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.schedules
    ADD CONSTRAINT schedules_pkey PRIMARY KEY (student, starttime, endtime, dept);


--
-- Name: sections sections_pkey; Type: CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.sections
    ADD CONSTRAINT sections_pkey PRIMARY KEY (secid);


--
-- Name: terms terms_pkey; Type: CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.terms
    ADD CONSTRAINT terms_pkey PRIMARY KEY (code);


--
-- Name: usage usage_pkey; Type: CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.usage
    ADD CONSTRAINT usage_pkey PRIMARY KEY (student, secid, markin);


--
-- Name: user_avatars user_avatars_pkey; Type: CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.user_avatars
    ADD CONSTRAINT user_avatars_pkey PRIMARY KEY (idno);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (idno);


--
-- Name: forum_overview _RETURN; Type: RULE; Schema: public; Owner: labassist
--

CREATE RULE "_RETURN" AS
    ON SELECT TO public.forum_overview DO INSTEAD  WITH filt_questions AS (
         SELECT questions.quid,
            questions.subject,
            questions.title,
            questions.asker,
            questions.status,
            questions.added,
            questions.term
           FROM public.questions
          WHERE ((questions.term)::bpchar = (( SELECT terms.code
                   FROM public.terms
                  WHERE (terms.activeterm = true)))::bpchar)
        )
 SELECT departments.deptid,
    departments.deptname,
    count(filt_questions.quid) AS question_count,
    count(filt_questions.quid) FILTER (WHERE (filt_questions.status = 'awaiting_response'::public.question_status)) AS unanswered_count
   FROM ((public.departments
     LEFT JOIN public.classes ON (((departments.deptid)::text = (classes.dept)::text)))
     LEFT JOIN filt_questions ON ((classes.cid = filt_questions.subject)))
  GROUP BY departments.deptid
  ORDER BY departments.deptname;


--
-- Name: availability availability_dept_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.availability
    ADD CONSTRAINT availability_dept_fkey FOREIGN KEY (dept) REFERENCES public.departments(deptid);


--
-- Name: availability availability_student_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.availability
    ADD CONSTRAINT availability_student_fkey FOREIGN KEY (student) REFERENCES public.users(idno) ON UPDATE CASCADE;


--
-- Name: classes classes_dept_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.classes
    ADD CONSTRAINT classes_dept_fkey FOREIGN KEY (dept) REFERENCES public.departments(deptid) ON UPDATE CASCADE;


--
-- Name: deptlabs deptlabs_dept_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.deptlabs
    ADD CONSTRAINT deptlabs_dept_fkey FOREIGN KEY (dept) REFERENCES public.departments(deptid);


--
-- Name: pendingmsgs pendingmsgs_recipient_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.pendingmsgs
    ADD CONSTRAINT pendingmsgs_recipient_fkey FOREIGN KEY (recipient) REFERENCES public.users(idno) ON UPDATE CASCADE;


--
-- Name: posts posts_author_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.posts
    ADD CONSTRAINT posts_author_fkey FOREIGN KEY (author) REFERENCES public.users(idno) ON UPDATE CASCADE;


--
-- Name: posts posts_question_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.posts
    ADD CONSTRAINT posts_question_fkey FOREIGN KEY (question) REFERENCES public.questions(quid) ON UPDATE CASCADE;


--
-- Name: questions questions_asker_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT questions_asker_fkey FOREIGN KEY (asker) REFERENCES public.users(idno) ON UPDATE CASCADE;


--
-- Name: questions questions_subject_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT questions_subject_fkey FOREIGN KEY (subject) REFERENCES public.classes(cid) ON UPDATE CASCADE;


--
-- Name: questions questions_term_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT questions_term_fkey FOREIGN KEY (term) REFERENCES public.terms(code) NOT VALID;


--
-- Name: schedules schedule_availability_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.schedules
    ADD CONSTRAINT schedule_availability_fkey FOREIGN KEY (student, starttime, endtime, dept) REFERENCES public.availability(student, starttime, endtime, dept);


--
-- Name: schedules schedules_dept_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.schedules
    ADD CONSTRAINT schedules_dept_fkey FOREIGN KEY (dept) REFERENCES public.departments(deptid);


--
-- Name: schedules schedules_student_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.schedules
    ADD CONSTRAINT schedules_student_fkey FOREIGN KEY (student) REFERENCES public.users(idno);


--
-- Name: sections sections_cid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.sections
    ADD CONSTRAINT sections_cid_fkey FOREIGN KEY (cid) REFERENCES public.classes(cid) ON UPDATE CASCADE;


--
-- Name: sections sections_teacher_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.sections
    ADD CONSTRAINT sections_teacher_fkey FOREIGN KEY (teacher) REFERENCES public.users(idno) ON UPDATE CASCADE;


--
-- Name: sections sections_term_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.sections
    ADD CONSTRAINT sections_term_fkey FOREIGN KEY (term) REFERENCES public.terms(code) ON UPDATE CASCADE;


--
-- Name: usage usage_secid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.usage
    ADD CONSTRAINT usage_secid_fkey FOREIGN KEY (secid) REFERENCES public.sections(secid) ON UPDATE CASCADE;


--
-- Name: usage usage_student_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.usage
    ADD CONSTRAINT usage_student_fkey FOREIGN KEY (student) REFERENCES public.users(idno) ON UPDATE CASCADE;


--
-- Name: user_avatars user_avatars_idno_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.user_avatars
    ADD CONSTRAINT user_avatars_idno_fkey FOREIGN KEY (idno) REFERENCES public.users(idno) ON UPDATE CASCADE;


--
-- Name: users users_deptid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: labassist
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_deptid_fkey FOREIGN KEY (deptid) REFERENCES public.departments(deptid) ON UPDATE CASCADE;


--
-- PostgreSQL database dump complete
--

