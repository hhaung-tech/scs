--
-- PostgreSQL database dump
--

\restrict UfjkOvVQGnHQoLsUj6hjhsNmJm09y6IO9ysbG8c9pI0KM9IRw68ZPVnzPuzcakY

-- Dumped from database version 17.2
-- Dumped by pg_dump version 17.7 (Homebrew)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

ALTER TABLE IF EXISTS ONLY public.responses DROP CONSTRAINT IF EXISTS responses_question_id_fkey;
ALTER TABLE IF EXISTS ONLY public.questions DROP CONSTRAINT IF EXISTS questions_category_id_fkey;
ALTER TABLE IF EXISTS ONLY public.survey_settings DROP CONSTRAINT IF EXISTS survey_settings_survey_type_key;
ALTER TABLE IF EXISTS ONLY public.survey_settings DROP CONSTRAINT IF EXISTS survey_settings_pkey;
ALTER TABLE IF EXISTS ONLY public.survey_codes DROP CONSTRAINT IF EXISTS survey_codes_pkey;
ALTER TABLE IF EXISTS ONLY public.responses DROP CONSTRAINT IF EXISTS responses_pkey;
ALTER TABLE IF EXISTS ONLY public.questions DROP CONSTRAINT IF EXISTS questions_pkey;
ALTER TABLE IF EXISTS ONLY public.categories DROP CONSTRAINT IF EXISTS categories_pkey;
ALTER TABLE IF EXISTS ONLY public.admin_users DROP CONSTRAINT IF EXISTS admin_users_username_key;
ALTER TABLE IF EXISTS ONLY public.admin_users DROP CONSTRAINT IF EXISTS admin_users_pkey;
ALTER TABLE IF EXISTS ONLY public.admin_users DROP CONSTRAINT IF EXISTS admin_users_email_key;
ALTER TABLE IF EXISTS public.survey_settings ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.survey_codes ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.responses ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.questions ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.categories ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.admin_users ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE IF EXISTS public.survey_settings_id_seq;
DROP TABLE IF EXISTS public.survey_settings;
DROP SEQUENCE IF EXISTS public.survey_codes_id_seq;
DROP TABLE IF EXISTS public.survey_codes;
DROP SEQUENCE IF EXISTS public.responses_id_seq;
DROP TABLE IF EXISTS public.responses;
DROP SEQUENCE IF EXISTS public.questions_id_seq;
DROP TABLE IF EXISTS public.questions;
DROP SEQUENCE IF EXISTS public.categories_id_seq;
DROP TABLE IF EXISTS public.categories;
DROP SEQUENCE IF EXISTS public.admin_users_id_seq;
DROP TABLE IF EXISTS public.admin_users;
SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: admin_users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.admin_users (
    id integer NOT NULL,
    username character varying(50) NOT NULL,
    email character varying(100) NOT NULL,
    password character varying(255) NOT NULL,
    first_name character varying(50),
    last_name character varying(50),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    last_login timestamp without time zone,
    reset_token character varying(255),
    reset_token_expiry timestamp without time zone
);


--
-- Name: admin_users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.admin_users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: admin_users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.admin_users_id_seq OWNED BY public.admin_users.id;


--
-- Name: categories; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.categories (
    id integer NOT NULL,
    name character varying(500) NOT NULL,
    type character varying(20)
);


--
-- Name: categories_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.categories_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.categories_id_seq OWNED BY public.categories.id;


--
-- Name: questions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.questions (
    id integer NOT NULL,
    category_id integer,
    question_text character varying(500) NOT NULL,
    question_type character varying(20),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    options text,
    sort_order integer DEFAULT 0,
    grade_level character varying(10),
    feedback_type character varying(20) DEFAULT 'core'::character varying,
    CONSTRAINT questions_question_type_check CHECK (((question_type)::text = ANY (ARRAY['likert_scale'::text, 'drop_down'::text, 'multiple_choice'::text, 'checkbox'::text, 'text'::text, 'content'::text])))
);


--
-- Name: questions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.questions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: questions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.questions_id_seq OWNED BY public.questions.id;


--
-- Name: responses; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.responses (
    id integer NOT NULL,
    question_id integer NOT NULL,
    answer text NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    submission_id character varying(100),
    teacher_id integer
);


--
-- Name: responses_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.responses_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: responses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.responses_id_seq OWNED BY public.responses.id;


--
-- Name: survey_codes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.survey_codes (
    id integer NOT NULL,
    type character varying(50) NOT NULL,
    code character varying(100) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    expires_at timestamp without time zone,
    active boolean DEFAULT true
);


--
-- Name: survey_codes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.survey_codes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: survey_codes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.survey_codes_id_seq OWNED BY public.survey_codes.id;


--
-- Name: survey_settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.survey_settings (
    id integer NOT NULL,
    survey_type character varying(50) NOT NULL,
    is_active boolean DEFAULT true,
    display_name character varying(100) NOT NULL,
    display_order integer DEFAULT 0,
    icon_class character varying(50) DEFAULT 'fa-file-text'::character varying,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: survey_settings_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.survey_settings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: survey_settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.survey_settings_id_seq OWNED BY public.survey_settings.id;


--
-- Name: admin_users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_users ALTER COLUMN id SET DEFAULT nextval('public.admin_users_id_seq'::regclass);


--
-- Name: categories id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categories ALTER COLUMN id SET DEFAULT nextval('public.categories_id_seq'::regclass);


--
-- Name: questions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.questions ALTER COLUMN id SET DEFAULT nextval('public.questions_id_seq'::regclass);


--
-- Name: responses id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.responses ALTER COLUMN id SET DEFAULT nextval('public.responses_id_seq'::regclass);


--
-- Name: survey_codes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.survey_codes ALTER COLUMN id SET DEFAULT nextval('public.survey_codes_id_seq'::regclass);


--
-- Name: survey_settings id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.survey_settings ALTER COLUMN id SET DEFAULT nextval('public.survey_settings_id_seq'::regclass);


--
-- Data for Name: admin_users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.admin_users (id, username, email, password, first_name, last_name, created_at, last_login, reset_token, reset_token_expiry) FROM stdin;
2	hhaung	hhaung@isyedu.org	$2y$12$zHiuTLJl6GHk/mAPnFJa9uThqVxTr.0FmcA0MBta/4J.Q31Z0/KMG	Htet Htet 	Aung	2025-02-03 14:48:42.523833	2026-01-14 22:04:08.981083	062adbbc6510f57080bce558363f2edeef4a29dbaed767ad4c6bb58655e5058a	2026-01-14 05:39:20
12	htet	htethtetaung720@gmail.com	$2y$10$70Ez1RR.8xokBqxuQKjMmOBdwR57dyO1ENeXLTwws78h.KwTx1.wK	Htet 2 	Htet	2025-02-25 01:13:17.54322	\N	\N	\N
\.


--
-- Data for Name: categories; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.categories (id, name, type) FROM stdin;
333	Section 1 	alumni
334	Section 2 	alumni
528	Section 2 	alumni
15	Background Information	guardian
16	Please mark how much you AGREE with the following statements about your child's school. School Environment...	guardian
17	Please mark how much you AGREE with these statements about your child's school. Teachers at this school...	guardian
18	Please mark how much you AGREE with these statements about your child's school. School Communication...	guardian
19	Please mark how much you AGREE with these statements about your child's school. School Rules and Safety...	guardian
539	Section 2 	alumni
332	Section 1	staff
335	Section 33	alumni
567	Board Governance	board
568	Strategic Planning	board
569	Financial Oversight	board
570	Policy Development	board
571	School Leadership	board
499	Section 1	student
572	Community Relations	board
573	School Leadership	board
575	Nothgin	board
576	Eating	board
578	Testing One	guardian
580	Schools	student
581	Section 2	staff
\.


--
-- Data for Name: questions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.questions (id, category_id, question_text, question_type, created_at, options, sort_order, grade_level, feedback_type) FROM stdin;
617	581	Testin 1	checkbox	2026-01-14 19:58:18.378806	["Poor","Fair","Good","Very Good","Excellent"]	0	\N	core
59	16	 I feel welcome at my child's school	likert_scale	2025-02-06 08:36:34.14124	\N	0	HS	teacher
60	16	The school provides a safe place for my child to learn.\t	likert_scale	2025-02-06 08:36:44.826107	\N	0	HS	teacher
61	16	The school encourages parents/guardians to be involved in school activities.	likert_scale	2025-02-06 08:36:54.010169	\N	0	HS	teacher
62	16	The principal and other office staff show respect toward parents/guardians.	likert_scale	2025-02-06 08:37:03.862769	\N	0	HS	teacher
63	16	 My child's teachers listen to my concerns.	likert_scale	2025-02-06 08:37:14.882001	\N	0	HS	teacher
64	16	The school gives me useful information about how to help my child do well at school.	likert_scale	2025-02-06 08:37:31.127386	\N	0	HS	teacher
65	16	I trust the principal at the school.	likert_scale	2025-02-06 08:37:40.518596	\N	0	HS	teacher
66	16	I trust the teacher(s) at this school.	likert_scale	2025-02-06 08:37:55.490532	\N	0	HS	teacher
67	16	I trust the office staff at this school.\t	likert_scale	2025-02-06 08:38:07.271447	\N	0	HS	teacher
79	17	show respect towards parents.	likert_scale	2025-02-06 08:40:42.733168	\N	0	HS	teacher
68	16	The school provides my child with a good education.	likert_scale	2025-02-06 08:38:22.001685	\N	0	HS	teacher
69	16	The school has adequate resources (books, computers, etc.) for my child to learn to the best of his or her abilities.	likert_scale	2025-02-06 08:38:35.547163	\N	0	HS	teacher
70	16	 The school informs parents/guardians about their child's progress and successes.	likert_scale	2025-02-06 08:38:45.316589	\N	0	HS	teacher
71	16	The school promotes respect for students of different races, ethnicities, religions, disabilities, and other differences.	likert_scale	2025-02-06 08:38:55.73256	\N	0	HS	teacher
72	16	My child is treated with respect by other students at school.	likert_scale	2025-02-06 08:39:08.455739	\N	0	HS	teacher
73	16	My child feels like he or she is a part of the school community.	likert_scale	2025-02-06 08:39:17.453925	\N	0	HS	teacher
609	499	test	checkbox	2026-01-14 09:36:19.16642	1,2,3	0	ES	core
605	499	Test Views	text	2025-10-15 11:16:54.484554		0	ES	core
74	16	The school's buildings and grounds are clean and well-kept.	likert_scale	2025-02-06 08:39:29.573653	\N	0	HS	teacher
75	16	 I would recommend this school to family and friends with children.	likert_scale	2025-02-06 08:39:41.338448	\N	0	HS	teacher
76	17	assign the right amount of schoolwork to my child.\t	likert_scale	2025-02-06 08:40:10.420367	\N	0	HS	teacher
77	17	encourage my child to do his or her best.\t\t\t\t\t\r\n	likert_scale	2025-02-06 08:40:20.175518	\N	0	HS	teacher
78	17	give my child positive attention when he or she does something well.	likert_scale	2025-02-06 08:40:29.203513	\N	0	HS	teacher
58	15	Which of these best describes your ethnic/racial identity? (Mark all that apply.)	checkbox	2025-02-06 08:28:10.085169	American Indian or Alaska Native,  Black or African American,  East or Southeast Asian , Hispanic or Latino,  Native Hawaiian or Pacific Islander,  Middle Eastern,  White- Not Hispanic , Two or More Ethnicities/Races,  Prefer not to answer. Other	0	HS	teacher
81	18	 I would feel comfortable contacting my child's teacher(s).	likert_scale	2025-02-06 08:46:28.000381	\N	0	HS	teacher
82	18	I would feel comfortable contacting other school staff members regarding my child.	likert_scale	2025-02-06 08:46:40.607072	\N	0	HS	teacher
83	18	My child's teachers are available when I need to talk to them	likert_scale	2025-02-06 08:46:48.935625	\N	0	HS	teacher
84	18	The school principal is available when I need to talk to him or her	likert_scale	2025-02-06 08:46:56.398728	\N	0	HS	teacher
85	18	The school would let me know right away if there was some kind of problem with my child.	likert_scale	2025-02-06 08:47:03.415188	\N	0	HS	teacher
86	18	The school would let me know if my child was getting low grades.\t\t\t\t\t\r\n	likert_scale	2025-02-06 08:47:14.401415	\N	0	HS	teacher
87	18	The school would let me know if my child had a discipline/behavior problem.\t	likert_scale	2025-02-06 08:47:21.939562	\N	0	HS	teacher
88	18	The school would let me know if my child was absent from school or skipping classes.\t	likert_scale	2025-02-06 08:47:29.452078	\N	0	HS	teacher
90	19	The rules at this school are fair to students.	likert_scale	2025-02-06 08:47:52.483378	\N	0	HS	teacher
91	19	The principal, teachers, and other school staff apply the rules equally to all students.	likert_scale	2025-02-06 08:47:59.14722	\N	0	HS	teacher
92	19	 The punishments for student misbehavior are fair and appropriate.	likert_scale	2025-02-06 08:48:07.996982	\N	0	HS	teacher
93	19	When students get in trouble, teachers give them a chance to explain their side of the story.\t	likert_scale	2025-02-06 08:48:16.389527	\N	0	HS	teacher
56	15	What is your child’s gender?	drop_down	2025-02-06 08:25:02.393234	Male,Female,Prefer not to answer	0	HS	teacher
89	19	The school has communicated to me clearly what the school rules aree.	likert_scale	2025-02-06 08:47:44.93967	\N	0	HS	teacher
55	15	What is your relationship to this student? (Mark one.)	checkbox	2025-02-06 08:24:26.336152	Parent (biological or adoptive),Stepparent,Grandparent,Other adult relative,Other guardian	0	HS	teacher
610	499	fefefe	text	2026-01-14 09:36:27.301504	\N	0	ES	core
369	332	How satisfied are you with the overall work environment at this school?	likert_scale	2025-02-25 22:31:19.800277	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
370	332	Do you feel supported by the administration in your role?	likert_scale	2025-02-25 22:31:29.986542	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
371	332	How effective are the professional development opportunities provided by the school?	likert_scale	2025-02-25 22:31:40.550096	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
372	332	Do you feel that your workload is manageable?	likert_scale	2025-02-25 22:31:50.778173	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
373	332	How well do students respect and engage with staff members?	likert_scale	2025-02-25 22:32:01.647747	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
374	332	What challenges do you face in your role that the school should address?	likert_scale	2025-02-25 22:32:12.837542	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
375	332	How satisfied are you with the available teaching resources and facilities?	likert_scale	2025-02-25 22:32:25.057409	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
376	332	Do you feel the school fosters a culture of collaboration among staff?	likert_scale	2025-02-25 22:32:35.909265	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
377	332	How would you rate the effectiveness of communication within the school?	likert_scale	2025-02-25 22:32:46.420539	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
384	333	The school’s mission included attention to the development of global awareness and respect for diversity.	likert_scale	2025-02-28 14:36:30.60338	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
385	333	The school prepared me well for interacting with people from different cultures and nations.	likert_scale	2025-02-28 14:36:42.974127	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
387	333	I was exposed to the use of information technology as a tool for instruction, a means of communication, and a way to access information.	likert_scale	2025-02-28 14:37:18.526821	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
386	333	What I learned in school provided a good base for what I’m doing now.	likert_scale	2025-02-28 14:37:03.579516	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
378	332	How likely are you to recommend this school as a workplace to others?	likert_scale	2025-02-25 22:32:56.45426	["a","b","c","d","e"]	0	HS	teacher
382	333	What year did you graduate from high school.	drop_down	2025-02-28 14:35:46.098108	2018-2023, 2010-2017, 2000-2009, 1990-1999, Before 1990	0	HS	teacher
115	15	School Year	drop_down	2025-02-10 10:50:21.333194	2024-2025,2025-2026	0	HS	teacher
383	333	I was aware of the major focus of the school’s mission when I was a student there.	likert_scale	2025-02-28 14:36:13.185883	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
388	333	I left this school feeling prepared academically, emotionally, and socially to pursue my goals.	likert_scale	2025-02-28 14:37:38.135902	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
207	15	Testing one	likert_scale	2025-02-17 21:23:15.371512	\N	0	HS	teacher
389	333	The cultural diversity of the students and staff in the school community was used to enrich my experience.	likert_scale	2025-02-28 14:37:48.393403	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
390	333	The curriculum included formal instruction in the processes of gathering, organizing, presenting and applying ideas and information.	likert_scale	2025-02-28 14:37:57.812838	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
391	333	The school provided opportunities for me to learn to think critically and solve problems and to apply those skills.	likert_scale	2025-02-28 14:38:09.582313	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
392	333	The school helped me to develop awareness of my learning strengths and style.	likert_scale	2025-02-28 14:38:23.338403	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
393	334	My teachers used a variety of methods to teach concepts and skills.	likert_scale	2025-02-28 14:38:47.504083	["Never","Rarely","Sometimes","Often","Always"]	13	HS	teacher
394	334	My classes were interesting and engaging.	likert_scale	2025-02-28 14:39:08.279397	["Never","Rarely","Sometimes","Often","Always"]	12	HS	teacher
395	334	The school provided support for students who did not speak English or other languages of instruction.	likert_scale	2025-02-28 14:39:25.775539	["Never","Rarely","Sometimes","Often","Always"]	11	HS	teacher
396	334	My teachers used a variety of methods to assess student learning.	likert_scale	2025-02-28 14:39:37.49037	["Never","Rarely","Sometimes","Often","Always"]	10	HS	teacher
397	334	I had opportunities to assess my own learning.	likert_scale	2025-02-28 14:39:52.302412	["Never","Rarely","Sometimes","Often","Always"]	9	HS	teacher
398	334	I felt as if my teachers had enough time for me.	likert_scale	2025-02-28 14:40:06.475844	["Never","Rarely","Sometimes","Often","Always"]	8	HS	teacher
399	334	I have remained in contact with at least one staff member from my years at this school.	likert_scale	2025-02-28 14:40:22.465912	["Never","Rarely","Sometimes","Often","Always"]	6	HS	teacher
400	334	Personal counseling and academic support services were available from the school.	likert_scale	2025-02-28 14:40:35.333977	["Never","Rarely","Sometimes","Often","Always"]	7	HS	teacher
401	334	The guidance counselor(s) gave me valuable help with college/university applications and processing and with accessing the testing required for post secondary planning.	likert_scale	2025-02-28 14:40:49.401235	["Never","Rarely","Sometimes","Often","Always"]	5	HS	teacher
615	499	Testing	likert_scale	2026-01-14 13:15:07.401642	["Never","Rarely","Sometimes","Often","Always"]	0	ES	core
493	332	Section 1 2	likert_scale	2025-04-03 15:23:16.632737	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
411	335	The school fostered opportunities for students to share their ethnic/cultural heritages.	likert_scale	2025-02-28 14:43:35.467869	["Never","Rarely","Sometimes","Often","Always"]	4	HS	teacher
412	335	The school enhanced the development of my international mindedness and intercultural awareness.	likert_scale	2025-02-28 14:44:06.321949	["Never","Rarely","Sometimes","Often","Always"]	1	HS	teacher
413	335	Thinking back to my first year at University, I was better prepared than my peers in the use of technology to support learning.	likert_scale	2025-02-28 14:44:23.340456	["1","2","3","4","5"]	3	HS	teacher
414	335	Thinking back to my first year at University, I had had more exposure to cultural diversity than my peers.	likert_scale	2025-02-28 14:46:40.274853	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	5	HS	teacher
415	335	Thinking back to my first year at University, I felt more conscientious towards the environment than my peers.	likert_scale	2025-02-28 14:47:47.46909	["Very Unsatisfied","Unsatisfied","Neutral","Neutral","Very Satisfied"]	8	HS	teacher
416	335	Thinking back to my first year at University, I had had more opportunities to engage in Service Learning than my peers.	likert_scale	2025-02-28 14:49:02.606214	["Very Low","Low","Average","High","Very High"]	6	HS	teacher
464	335	Sample Questions 	likert_scale	2025-04-03 11:32:28.081918	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	7	HS	teacher
405	334	Students demonstrated respect for teachers.	likert_scale	2025-02-28 14:41:52.318006	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	1	HS	teacher
406	334	Students demonstrated respect for one another at this school.	likert_scale	2025-02-28 14:42:08.003151	["Strongly Disagree","Disagree","Neutral","Agree","Strongly Agree"]	0	HS	teacher
616	580	Hybrid	checkbox	2026-01-14 15:06:05.951441	["Red","Blue","Green","Yellow","Ekki"]	0	\N	core
409	335	I felt proud of my school when I was a student there..	likert_scale	2025-02-28 14:43:03.040964	["1","2","3","4","5"]	2	HS	teacher
410	335	The program of student activities enriched the program and met the needs and interests of the students.	likert_scale	2025-02-28 14:43:13.305387	["1","2","3","4","5"]	0	HS	teacher
402	334	I was given a library orientation and instruction in how to use the library and to access the resources there.	likert_scale	2025-02-28 14:41:04.554937	["Never","Rarely","Sometimes","Often","Always"]	4	HS	teacher
403	334	The library resources supported my learning and research.	likert_scale	2025-02-28 14:41:20.535293	["Never","Rarely","Sometimes","Often","Always"]	3	HS	teacher
404	334	Teachers demonstrated respect for students at this school.	likert_scale	2025-02-28 14:41:38.664147	["Never","Rarely","Sometimes","Often","Always"]	2	HS	teacher
\.


--
-- Data for Name: responses; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.responses (id, question_id, answer, created_at, submission_id, teacher_id) FROM stdin;
2216	382	2018-2023	2025-03-09 22:19:57.472444	survey_67cdb8a5724302.04785316	\N
2217	383	3	2025-03-09 22:19:57.48248	survey_67cdb8a5724302.04785316	\N
2218	384	3	2025-03-09 22:19:57.483289	survey_67cdb8a5724302.04785316	\N
2219	385	3	2025-03-09 22:19:57.484009	survey_67cdb8a5724302.04785316	\N
2220	386	3	2025-03-09 22:19:57.484798	survey_67cdb8a5724302.04785316	\N
2221	387	3	2025-03-09 22:19:57.485353	survey_67cdb8a5724302.04785316	\N
2222	388	3	2025-03-09 22:19:57.485738	survey_67cdb8a5724302.04785316	\N
2223	389	3	2025-03-09 22:19:57.486033	survey_67cdb8a5724302.04785316	\N
2224	390	3	2025-03-09 22:19:57.486338	survey_67cdb8a5724302.04785316	\N
2225	391	3	2025-03-09 22:19:57.486628	survey_67cdb8a5724302.04785316	\N
2226	392	3	2025-03-09 22:19:57.487072	survey_67cdb8a5724302.04785316	\N
2227	393	3	2025-03-09 22:19:57.487616	survey_67cdb8a5724302.04785316	\N
2228	394	3	2025-03-09 22:19:57.488147	survey_67cdb8a5724302.04785316	\N
2229	395	3	2025-03-09 22:19:57.488714	survey_67cdb8a5724302.04785316	\N
2230	396	3	2025-03-09 22:19:57.489233	survey_67cdb8a5724302.04785316	\N
2231	397	3	2025-03-09 22:19:57.489848	survey_67cdb8a5724302.04785316	\N
2232	398	3	2025-03-09 22:19:57.490555	survey_67cdb8a5724302.04785316	\N
2233	399	3	2025-03-09 22:19:57.491148	survey_67cdb8a5724302.04785316	\N
2234	400	3	2025-03-09 22:19:57.49209	survey_67cdb8a5724302.04785316	\N
2235	401	4	2025-03-09 22:19:57.492703	survey_67cdb8a5724302.04785316	\N
2236	402	3	2025-03-09 22:19:57.493352	survey_67cdb8a5724302.04785316	\N
2237	403	3	2025-03-09 22:19:57.494012	survey_67cdb8a5724302.04785316	\N
2238	404	3	2025-03-09 22:19:57.494914	survey_67cdb8a5724302.04785316	\N
2239	405	3	2025-03-09 22:19:57.495439	survey_67cdb8a5724302.04785316	\N
2240	406	3	2025-03-09 22:19:57.49617	survey_67cdb8a5724302.04785316	\N
2241	415	4	2025-03-09 22:19:57.496783	survey_67cdb8a5724302.04785316	\N
2242	416	5	2025-03-09 22:19:57.497383	survey_67cdb8a5724302.04785316	\N
2243	414	4	2025-03-09 22:19:57.498028	survey_67cdb8a5724302.04785316	\N
2244	411	4	2025-03-09 22:19:57.498643	survey_67cdb8a5724302.04785316	\N
2245	413	4	2025-03-09 22:19:57.499222	survey_67cdb8a5724302.04785316	\N
2246	410	3	2025-03-09 22:19:57.499741	survey_67cdb8a5724302.04785316	\N
1541	369	4	2024-02-26 09:00:25.851901	survey_67be7cc1cd0fd0.71562121	\N
1542	370	4	2024-02-26 09:00:25.863238	survey_67be7cc1cd0fd0.71562121	\N
1543	371	4	2024-02-26 09:00:25.863926	survey_67be7cc1cd0fd0.71562121	\N
1544	372	4	2024-02-26 09:00:25.864342	survey_67be7cc1cd0fd0.71562121	\N
1545	373	4	2024-02-26 09:00:25.866398	survey_67be7cc1cd0fd0.71562121	\N
1546	374	3	2024-02-26 09:00:25.867448	survey_67be7cc1cd0fd0.71562121	\N
1547	375	3	2024-02-26 09:00:25.8679	survey_67be7cc1cd0fd0.71562121	\N
216	72	5	2024-02-06 08:54:43.97021	\N	\N
2247	412	2	2025-03-09 22:19:57.500074	survey_67cdb8a5724302.04785316	\N
2248	409	3	2025-03-09 22:19:57.50041	survey_67cdb8a5724302.04785316	\N
2475	382	2000-2009	2025-04-10 08:17:32.762248	survey_67f72334b85327.81462142	\N
2476	383	3	2025-04-10 08:17:32.77295	survey_67f72334b85327.81462142	\N
2477	384	3	2025-04-10 08:17:32.77545	survey_67f72334b85327.81462142	\N
2478	385	3	2025-04-10 08:17:32.775936	survey_67f72334b85327.81462142	\N
2479	386	3	2025-04-10 08:17:32.776673	survey_67f72334b85327.81462142	\N
2480	387	3	2025-04-10 08:17:32.777326	survey_67f72334b85327.81462142	\N
2481	388	3	2025-04-10 08:17:32.777712	survey_67f72334b85327.81462142	\N
2482	389	3	2025-04-10 08:17:32.778071	survey_67f72334b85327.81462142	\N
2483	390	3	2025-04-10 08:17:32.778544	survey_67f72334b85327.81462142	\N
2484	391	3	2025-04-10 08:17:32.779442	survey_67f72334b85327.81462142	\N
2485	392	3	2025-04-10 08:17:32.779971	survey_67f72334b85327.81462142	\N
2486	406	3	2025-04-10 08:17:32.780475	survey_67f72334b85327.81462142	\N
2487	405	3	2025-04-10 08:17:32.781219	survey_67f72334b85327.81462142	\N
2488	404	3	2025-04-10 08:17:32.781804	survey_67f72334b85327.81462142	\N
2489	403	3	2025-04-10 08:17:32.782311	survey_67f72334b85327.81462142	\N
2490	402	3	2025-04-10 08:17:32.782632	survey_67f72334b85327.81462142	\N
2491	401	3	2025-04-10 08:17:32.782915	survey_67f72334b85327.81462142	\N
2492	399	3	2025-04-10 08:17:32.783372	survey_67f72334b85327.81462142	\N
2493	400	3	2025-04-10 08:17:32.783855	survey_67f72334b85327.81462142	\N
2494	398	3	2025-04-10 08:17:32.784547	survey_67f72334b85327.81462142	\N
2495	397	3	2025-04-10 08:17:32.784832	survey_67f72334b85327.81462142	\N
2496	396	3	2025-04-10 08:17:32.785161	survey_67f72334b85327.81462142	\N
2497	395	3	2025-04-10 08:17:32.78549	survey_67f72334b85327.81462142	\N
2498	394	3	2025-04-10 08:17:32.786606	survey_67f72334b85327.81462142	\N
2499	393	3	2025-04-10 08:17:32.786965	survey_67f72334b85327.81462142	\N
2500	410	3	2025-04-10 08:17:32.787343	survey_67f72334b85327.81462142	\N
2501	412	3	2025-04-10 08:17:32.78762	survey_67f72334b85327.81462142	\N
2502	409	3	2025-04-10 08:17:32.787915	survey_67f72334b85327.81462142	\N
2503	413	3	2025-04-10 08:17:32.788188	survey_67f72334b85327.81462142	\N
166	65	4	2022-01-01 00:00:00	\N	\N
167	66	4	2022-01-01 00:00:00	\N	\N
168	67	3	2022-01-01 00:00:00	\N	\N
169	68	4	2022-01-01 00:00:00	\N	\N
170	69	4	2022-01-01 00:00:00	\N	\N
171	70	4	2022-01-01 00:00:00	\N	\N
172	71	4	2022-01-01 00:00:00	\N	\N
173	72	4	2022-01-01 00:00:00	\N	\N
174	73	4	2022-01-01 00:00:00	\N	\N
175	74	4	2022-01-01 00:00:00	\N	\N
156	55	Parent (biological or adoptive)	2022-01-01 00:00:00	\N	\N
157	56	Male	2022-01-01 00:00:00	\N	\N
159	58	["American Indian or Alaska Native"]	2022-01-01 00:00:00	\N	\N
160	59	5	2022-01-01 00:00:00	\N	\N
161	60	5	2022-01-01 00:00:00	\N	\N
162	61	4	2022-01-01 00:00:00	\N	\N
163	62	4	2022-01-01 00:00:00	\N	\N
164	63	3	2022-01-01 00:00:00	\N	\N
165	64	4	2022-01-01 00:00:00	\N	\N
186	85	4	2023-01-01 00:00:00	\N	\N
187	86	4	2023-01-01 00:00:00	\N	\N
188	87	4	2023-01-01 00:00:00	\N	\N
189	88	4	2023-01-01 00:00:00	\N	\N
190	89	5	2023-01-01 00:00:00	\N	\N
191	90	4	2023-01-01 00:00:00	\N	\N
192	91	4	2023-01-01 00:00:00	\N	\N
193	92	4	2023-01-01 00:00:00	\N	\N
194	93	4	2023-01-01 00:00:00	\N	\N
2251	382	Before 1990	2025-03-09 22:31:16.123503	survey_67cdbb4c1c4670.64069347	\N
2252	383	5	2025-03-09 22:31:16.134965	survey_67cdbb4c1c4670.64069347	\N
2253	384	5	2025-03-09 22:31:16.135864	survey_67cdbb4c1c4670.64069347	\N
2254	385	5	2025-03-09 22:31:16.13667	survey_67cdbb4c1c4670.64069347	\N
2255	386	5	2025-03-09 22:31:16.137394	survey_67cdbb4c1c4670.64069347	\N
2256	387	5	2025-03-09 22:31:16.138571	survey_67cdbb4c1c4670.64069347	\N
2257	388	5	2025-03-09 22:31:16.140123	survey_67cdbb4c1c4670.64069347	\N
2258	389	5	2025-03-09 22:31:16.14095	survey_67cdbb4c1c4670.64069347	\N
2259	390	5	2025-03-09 22:31:16.141902	survey_67cdbb4c1c4670.64069347	\N
2260	391	5	2025-03-09 22:31:16.142575	survey_67cdbb4c1c4670.64069347	\N
2261	392	5	2025-03-09 22:31:16.143868	survey_67cdbb4c1c4670.64069347	\N
2262	393	5	2025-03-09 22:31:16.144702	survey_67cdbb4c1c4670.64069347	\N
2263	394	5	2025-03-09 22:31:16.145329	survey_67cdbb4c1c4670.64069347	\N
2264	395	5	2025-03-09 22:31:16.145861	survey_67cdbb4c1c4670.64069347	\N
2265	396	5	2025-03-09 22:31:16.146342	survey_67cdbb4c1c4670.64069347	\N
2266	397	5	2025-03-09 22:31:16.146725	survey_67cdbb4c1c4670.64069347	\N
2267	398	5	2025-03-09 22:31:16.147631	survey_67cdbb4c1c4670.64069347	\N
2268	399	5	2025-03-09 22:31:16.148096	survey_67cdbb4c1c4670.64069347	\N
2269	400	5	2025-03-09 22:31:16.148387	survey_67cdbb4c1c4670.64069347	\N
2270	401	5	2025-03-09 22:31:16.148798	survey_67cdbb4c1c4670.64069347	\N
2271	402	5	2025-03-09 22:31:16.149145	survey_67cdbb4c1c4670.64069347	\N
2272	403	5	2025-03-09 22:31:16.149483	survey_67cdbb4c1c4670.64069347	\N
2273	404	5	2025-03-09 22:31:16.149888	survey_67cdbb4c1c4670.64069347	\N
2274	405	5	2025-03-09 22:31:16.150621	survey_67cdbb4c1c4670.64069347	\N
2275	406	5	2025-03-09 22:31:16.150964	survey_67cdbb4c1c4670.64069347	\N
2276	415	5	2025-03-09 22:31:16.151316	survey_67cdbb4c1c4670.64069347	\N
2277	416	5	2025-03-09 22:31:16.151711	survey_67cdbb4c1c4670.64069347	\N
2278	414	5	2025-03-09 22:31:16.152368	survey_67cdbb4c1c4670.64069347	\N
2279	411	5	2025-03-09 22:31:16.152708	survey_67cdbb4c1c4670.64069347	\N
2280	413	5	2025-03-09 22:31:16.153003	survey_67cdbb4c1c4670.64069347	\N
2281	410	5	2025-03-09 22:31:16.153296	survey_67cdbb4c1c4670.64069347	\N
2282	412	5	2025-03-09 22:31:16.153585	survey_67cdbb4c1c4670.64069347	\N
2283	409	5	2025-03-09 22:31:16.155435	survey_67cdbb4c1c4670.64069347	\N
2504	411	3	2025-04-10 08:17:32.788454	survey_67f72334b85327.81462142	\N
2505	414	3	2025-04-10 08:17:32.78873	survey_67f72334b85327.81462142	\N
2506	416	3	2025-04-10 08:17:32.789112	survey_67f72334b85327.81462142	\N
2507	464	3	2025-04-10 08:17:32.789543	survey_67f72334b85327.81462142	\N
2508	415	3	2025-04-10 08:17:32.78985	survey_67f72334b85327.81462142	\N
2562	369	2	2026-01-14 20:20:06.621912	survey_69679f0e95ab78.33076552	\N
2563	370	2	2026-01-14 20:20:06.631052	survey_69679f0e95ab78.33076552	\N
2564	371	2	2026-01-14 20:20:06.633211	survey_69679f0e95ab78.33076552	\N
2565	372	3	2026-01-14 20:20:06.634093	survey_69679f0e95ab78.33076552	\N
2566	373	3	2026-01-14 20:20:06.634969	survey_69679f0e95ab78.33076552	\N
2567	374	3	2026-01-14 20:20:06.635726	survey_69679f0e95ab78.33076552	\N
2568	375	3	2026-01-14 20:20:06.636516	survey_69679f0e95ab78.33076552	\N
2569	376	3	2026-01-14 20:20:06.637276	survey_69679f0e95ab78.33076552	\N
2570	377	3	2026-01-14 20:20:06.637963	survey_69679f0e95ab78.33076552	\N
2571	378	3	2026-01-14 20:20:06.638695	survey_69679f0e95ab78.33076552	\N
2572	493	3	2026-01-14 20:20:06.639466	survey_69679f0e95ab78.33076552	\N
2573	617	3	2026-01-14 20:20:06.640881	survey_69679f0e95ab78.33076552	\N
176	75	4	2023-01-01 00:00:00	\N	\N
177	76	3	2023-01-01 00:00:00	\N	\N
178	77	4	2023-01-01 00:00:00	\N	\N
179	78	4	2023-01-01 00:00:00	\N	\N
180	79	5	2023-01-01 00:00:00	\N	\N
182	81	4	2023-01-01 00:00:00	\N	\N
183	82	3	2023-01-01 00:00:00	\N	\N
184	83	4	2023-01-01 00:00:00	\N	\N
185	84	4	2023-01-01 00:00:00	\N	\N
199	55	Grandparent	2024-01-01 00:00:00	\N	\N
200	56	Female	2024-01-01 00:00:00	\N	\N
274	87	3	2024-02-06 09:03:58.302324	\N	\N
202	58	["American Indian or Alaska Native"]	2024-01-01 00:00:00	\N	\N
203	59	5	2024-01-01 00:00:00	\N	\N
204	60	5	2024-01-01 00:00:00	\N	\N
205	61	5	2024-01-01 00:00:00	\N	\N
206	62	5	2024-01-01 00:00:00	\N	\N
207	63	5	2024-01-01 00:00:00	\N	\N
208	64	5	2024-01-01 00:00:00	\N	\N
209	65	5	2024-01-01 00:00:00	\N	\N
210	66	5	2024-01-01 00:00:00	\N	\N
211	67	5	2024-01-01 00:00:00	\N	\N
212	68	5	2024-01-01 00:00:00	\N	\N
213	69	5	2024-01-01 00:00:00	\N	\N
214	70	5	2024-01-01 00:00:00	\N	\N
215	71	5	2024-01-01 00:00:00	\N	\N
275	88	3	2024-02-06 09:03:58.303783	\N	\N
276	89	3	2024-02-06 09:03:58.304211	\N	\N
277	90	3	2024-02-06 09:03:58.304596	\N	\N
278	91	3	2024-02-06 09:03:58.304924	\N	\N
279	92	3	2024-02-06 09:03:58.305344	\N	\N
280	93	3	2024-02-06 09:03:58.306	\N	\N
285	55	Parent (biological or adoptive)	2024-02-06 09:12:05.513569	\N	\N
286	56	Female	2024-02-06 09:12:05.527219	\N	\N
288	58	["East or Southeast Asian"]	2024-02-06 09:12:05.528965	\N	\N
289	59	2	2024-02-06 09:12:05.529593	\N	\N
290	60	2	2024-02-06 09:12:05.530159	\N	\N
291	61	2	2024-02-06 09:12:05.53077	\N	\N
2286	382	2018-2023	2025-03-10 10:53:48.309388	survey_67ce6954478827.42131449	\N
2287	383	4	2025-03-10 10:53:48.324588	survey_67ce6954478827.42131449	\N
2288	384	4	2025-03-10 10:53:48.325307	survey_67ce6954478827.42131449	\N
2289	385	4	2025-03-10 10:53:48.327312	survey_67ce6954478827.42131449	\N
2290	386	4	2025-03-10 10:53:48.327965	survey_67ce6954478827.42131449	\N
2291	387	4	2025-03-10 10:53:48.328609	survey_67ce6954478827.42131449	\N
2292	388	4	2025-03-10 10:53:48.329163	survey_67ce6954478827.42131449	\N
2293	389	4	2025-03-10 10:53:48.329691	survey_67ce6954478827.42131449	\N
2294	390	4	2025-03-10 10:53:48.330333	survey_67ce6954478827.42131449	\N
2295	391	4	2025-03-10 10:53:48.330937	survey_67ce6954478827.42131449	\N
2296	392	4	2025-03-10 10:53:48.331929	survey_67ce6954478827.42131449	\N
2297	393	4	2025-03-10 10:53:48.332252	survey_67ce6954478827.42131449	\N
2298	394	4	2025-03-10 10:53:48.332532	survey_67ce6954478827.42131449	\N
2299	395	4	2025-03-10 10:53:48.332827	survey_67ce6954478827.42131449	\N
2300	396	4	2025-03-10 10:53:48.333411	survey_67ce6954478827.42131449	\N
2301	397	4	2025-03-10 10:53:48.333811	survey_67ce6954478827.42131449	\N
2302	398	4	2025-03-10 10:53:48.334408	survey_67ce6954478827.42131449	\N
2303	399	4	2025-03-10 10:53:48.335408	survey_67ce6954478827.42131449	\N
2304	400	4	2025-03-10 10:53:48.336339	survey_67ce6954478827.42131449	\N
2305	401	4	2025-03-10 10:53:48.33691	survey_67ce6954478827.42131449	\N
2306	402	4	2025-03-10 10:53:48.337515	survey_67ce6954478827.42131449	\N
2307	403	4	2025-03-10 10:53:48.338329	survey_67ce6954478827.42131449	\N
2308	404	4	2025-03-10 10:53:48.339097	survey_67ce6954478827.42131449	\N
2309	405	4	2025-03-10 10:53:48.339776	survey_67ce6954478827.42131449	\N
2310	406	4	2025-03-10 10:53:48.340451	survey_67ce6954478827.42131449	\N
2311	415	4	2025-03-10 10:53:48.341124	survey_67ce6954478827.42131449	\N
2312	416	4	2025-03-10 10:53:48.341748	survey_67ce6954478827.42131449	\N
2313	414	4	2025-03-10 10:53:48.342345	survey_67ce6954478827.42131449	\N
2314	411	4	2025-03-10 10:53:48.342946	survey_67ce6954478827.42131449	\N
2315	413	4	2025-03-10 10:53:48.344152	survey_67ce6954478827.42131449	\N
2316	410	4	2025-03-10 10:53:48.344738	survey_67ce6954478827.42131449	\N
2317	412	4	2025-03-10 10:53:48.345338	survey_67ce6954478827.42131449	\N
2318	409	4	2025-03-10 10:53:48.345627	survey_67ce6954478827.42131449	\N
2515	369	2	2026-01-14 09:21:15.215514	survey_696704a3322f88.70826113	\N
2516	370	3	2026-01-14 09:21:15.228866	survey_696704a3322f88.70826113	\N
2517	371	3	2026-01-14 09:21:15.230652	survey_696704a3322f88.70826113	\N
2518	372	3	2026-01-14 09:21:15.23145	survey_696704a3322f88.70826113	\N
2519	373	3	2026-01-14 09:21:15.232844	survey_696704a3322f88.70826113	\N
2520	374	3	2026-01-14 09:21:15.233616	survey_696704a3322f88.70826113	\N
2521	375	3	2026-01-14 09:21:15.234662	survey_696704a3322f88.70826113	\N
2522	376	3	2026-01-14 09:21:15.235897	survey_696704a3322f88.70826113	\N
2523	377	3	2026-01-14 09:21:15.237106	survey_696704a3322f88.70826113	\N
2524	378	3	2026-01-14 09:21:15.23923	survey_696704a3322f88.70826113	\N
2525	493	3	2026-01-14 09:21:15.240145	survey_696704a3322f88.70826113	\N
2574	369	3	2026-01-14 20:21:15.265398	survey_69679f533fb089.16083782	\N
2575	370	3	2026-01-14 20:21:15.273662	survey_69679f533fb089.16083782	\N
2576	371	3	2026-01-14 20:21:15.275207	survey_69679f533fb089.16083782	\N
2577	372	3	2026-01-14 20:21:15.276748	survey_69679f533fb089.16083782	\N
2578	373	3	2026-01-14 20:21:15.277559	survey_69679f533fb089.16083782	\N
2579	374	3	2026-01-14 20:21:15.278017	survey_69679f533fb089.16083782	\N
2580	375	3	2026-01-14 20:21:15.278327	survey_69679f533fb089.16083782	\N
2581	376	3	2026-01-14 20:21:15.278947	survey_69679f533fb089.16083782	\N
2582	377	3	2026-01-14 20:21:15.280314	survey_69679f533fb089.16083782	\N
2583	378	3	2026-01-14 20:21:15.28161	survey_69679f533fb089.16083782	\N
2584	493	3	2026-01-14 20:21:15.282829	survey_69679f533fb089.16083782	\N
2585	617	["Poor"]	2026-01-14 20:21:15.284802	survey_69679f533fb089.16083782	\N
393	59	3	2024-02-10 10:52:55.996922	\N	\N
2321	382	2010-2017	2025-03-10 10:56:26.411509	survey_67ce69f2623d86.68207463	\N
2322	383	5	2025-03-10 10:56:26.425885	survey_67ce69f2623d86.68207463	\N
2323	384	5	2025-03-10 10:56:26.426746	survey_67ce69f2623d86.68207463	\N
2324	385	5	2025-03-10 10:56:26.427447	survey_67ce69f2623d86.68207463	\N
2325	386	5	2025-03-10 10:56:26.42776	survey_67ce69f2623d86.68207463	\N
2326	387	4	2025-03-10 10:56:26.428084	survey_67ce69f2623d86.68207463	\N
2327	388	4	2025-03-10 10:56:26.42849	survey_67ce69f2623d86.68207463	\N
2328	389	5	2025-03-10 10:56:26.429223	survey_67ce69f2623d86.68207463	\N
2329	390	4	2025-03-10 10:56:26.429961	survey_67ce69f2623d86.68207463	\N
2330	391	5	2025-03-10 10:56:26.430744	survey_67ce69f2623d86.68207463	\N
2331	392	4	2025-03-10 10:56:26.431464	survey_67ce69f2623d86.68207463	\N
2332	393	4	2025-03-10 10:56:26.432164	survey_67ce69f2623d86.68207463	\N
2333	394	4	2025-03-10 10:56:26.432724	survey_67ce69f2623d86.68207463	\N
2334	395	4	2025-03-10 10:56:26.433239	survey_67ce69f2623d86.68207463	\N
2335	396	5	2025-03-10 10:56:26.433697	survey_67ce69f2623d86.68207463	\N
2336	397	5	2025-03-10 10:56:26.434146	survey_67ce69f2623d86.68207463	\N
2337	398	5	2025-03-10 10:56:26.43466	survey_67ce69f2623d86.68207463	\N
2338	399	5	2025-03-10 10:56:26.435249	survey_67ce69f2623d86.68207463	\N
2339	400	5	2025-03-10 10:56:26.435683	survey_67ce69f2623d86.68207463	\N
2340	401	5	2025-03-10 10:56:26.43594	survey_67ce69f2623d86.68207463	\N
2341	402	5	2025-03-10 10:56:26.436232	survey_67ce69f2623d86.68207463	\N
2342	403	5	2025-03-10 10:56:26.436497	survey_67ce69f2623d86.68207463	\N
2343	404	5	2025-03-10 10:56:26.436818	survey_67ce69f2623d86.68207463	\N
2344	405	5	2025-03-10 10:56:26.437369	survey_67ce69f2623d86.68207463	\N
2345	406	5	2025-03-10 10:56:26.437729	survey_67ce69f2623d86.68207463	\N
2346	415	5	2025-03-10 10:56:26.438426	survey_67ce69f2623d86.68207463	\N
2347	416	5	2025-03-10 10:56:26.438787	survey_67ce69f2623d86.68207463	\N
2348	414	5	2025-03-10 10:56:26.439124	survey_67ce69f2623d86.68207463	\N
2349	411	5	2025-03-10 10:56:26.439478	survey_67ce69f2623d86.68207463	\N
2350	413	5	2025-03-10 10:56:26.439815	survey_67ce69f2623d86.68207463	\N
2351	410	5	2025-03-10 10:56:26.440635	survey_67ce69f2623d86.68207463	\N
2352	412	5	2025-03-10 10:56:26.440996	survey_67ce69f2623d86.68207463	\N
2353	409	5	2025-03-10 10:56:26.441923	survey_67ce69f2623d86.68207463	\N
2513	605	1	2025-10-15 11:17:32.397667	survey_68ef27645f6ce5.63194264	\N
2529	605	3	2026-01-14 09:23:33.786051	survey_6967052dba02b8.44318409	\N
2586	605	est	2026-01-14 20:28:03.053831	survey_6967a0eb0b5d00.47370057	\N
2587	609	["3"]	2026-01-14 20:28:03.06324	survey_6967a0eb0b5d00.47370057	\N
2588	610	test	2026-01-14 20:28:03.063995	survey_6967a0eb0b5d00.47370057	\N
2589	615	3	2026-01-14 20:28:03.064794	survey_6967a0eb0b5d00.47370057	\N
2590	616	3	2026-01-14 20:28:03.065215	survey_6967a0eb0b5d00.47370057	\N
1430	369	4	2024-02-25 22:36:33.898338	survey_67bdea89d8cd19.81746660	\N
1431	370	5	2024-02-25 22:36:33.906843	survey_67bdea89d8cd19.81746660	\N
1432	371	5	2024-02-25 22:36:33.907205	survey_67bdea89d8cd19.81746660	\N
1433	372	5	2024-02-25 22:36:33.90752	survey_67bdea89d8cd19.81746660	\N
2356	382	2018-2023	2025-03-10 10:59:21.623393	survey_67ce6aa1946a63.34598944	\N
2357	383	3	2025-03-10 10:59:21.639006	survey_67ce6aa1946a63.34598944	\N
2358	384	3	2025-03-10 10:59:21.639869	survey_67ce6aa1946a63.34598944	\N
2359	385	3	2025-03-10 10:59:21.640594	survey_67ce6aa1946a63.34598944	\N
2360	386	2	2025-03-10 10:59:21.641067	survey_67ce6aa1946a63.34598944	\N
2361	387	2	2025-03-10 10:59:21.641691	survey_67ce6aa1946a63.34598944	\N
2362	388	3	2025-03-10 10:59:21.642004	survey_67ce6aa1946a63.34598944	\N
2363	389	3	2025-03-10 10:59:21.642423	survey_67ce6aa1946a63.34598944	\N
2364	390	2	2025-03-10 10:59:21.643444	survey_67ce6aa1946a63.34598944	\N
2365	391	3	2025-03-10 10:59:21.644085	survey_67ce6aa1946a63.34598944	\N
2366	392	3	2025-03-10 10:59:21.645036	survey_67ce6aa1946a63.34598944	\N
2367	393	2	2025-03-10 10:59:21.645667	survey_67ce6aa1946a63.34598944	\N
2368	394	3	2025-03-10 10:59:21.646265	survey_67ce6aa1946a63.34598944	\N
2369	395	4	2025-03-10 10:59:21.646949	survey_67ce6aa1946a63.34598944	\N
2370	396	3	2025-03-10 10:59:21.647545	survey_67ce6aa1946a63.34598944	\N
2371	397	4	2025-03-10 10:59:21.648163	survey_67ce6aa1946a63.34598944	\N
2372	398	3	2025-03-10 10:59:21.648866	survey_67ce6aa1946a63.34598944	\N
2373	399	4	2025-03-10 10:59:21.649634	survey_67ce6aa1946a63.34598944	\N
2374	400	3	2025-03-10 10:59:21.650311	survey_67ce6aa1946a63.34598944	\N
2375	401	4	2025-03-10 10:59:21.650831	survey_67ce6aa1946a63.34598944	\N
2376	402	4	2025-03-10 10:59:21.651285	survey_67ce6aa1946a63.34598944	\N
2377	403	3	2025-03-10 10:59:21.652011	survey_67ce6aa1946a63.34598944	\N
2378	404	3	2025-03-10 10:59:21.652577	survey_67ce6aa1946a63.34598944	\N
2379	405	4	2025-03-10 10:59:21.653055	survey_67ce6aa1946a63.34598944	\N
2380	406	3	2025-03-10 10:59:21.653566	survey_67ce6aa1946a63.34598944	\N
2381	415	3	2025-03-10 10:59:21.654021	survey_67ce6aa1946a63.34598944	\N
2382	416	3	2025-03-10 10:59:21.654413	survey_67ce6aa1946a63.34598944	\N
2383	414	2	2025-03-10 10:59:21.654737	survey_67ce6aa1946a63.34598944	\N
2384	411	3	2025-03-10 10:59:21.65505	survey_67ce6aa1946a63.34598944	\N
2385	413	3	2025-03-10 10:59:21.655372	survey_67ce6aa1946a63.34598944	\N
2386	410	3	2025-03-10 10:59:21.655651	survey_67ce6aa1946a63.34598944	\N
2387	412	4	2025-03-10 10:59:21.655971	survey_67ce6aa1946a63.34598944	\N
2388	409	3	2025-03-10 10:59:21.656348	survey_67ce6aa1946a63.34598944	\N
2531	605	test	2026-01-14 09:24:31.261509	survey_696705673bef82.53073132	\N
2591	605	test	2026-01-14 20:57:43.51818	survey_6967a7df77ee58.65187906	\N
2592	609	["1"]	2026-01-14 20:57:43.578886	survey_6967a7df77ee58.65187906	\N
2593	610	test	2026-01-14 20:57:43.579424	survey_6967a7df77ee58.65187906	\N
2594	615	2	2026-01-14 20:57:43.585674	survey_6967a7df77ee58.65187906	\N
2595	616	2	2026-01-14 20:57:43.586181	survey_6967a7df77ee58.65187906	\N
2391	382	2010-2017	2025-03-10 21:17:12.100784	survey_67cefb701281d9.30340307	\N
2392	383	5	2025-03-10 21:17:12.156164	survey_67cefb701281d9.30340307	\N
2393	384	4	2025-03-10 21:17:12.158012	survey_67cefb701281d9.30340307	\N
2394	385	4	2025-03-10 21:17:12.158446	survey_67cefb701281d9.30340307	\N
2395	386	3	2025-03-10 21:17:12.15943	survey_67cefb701281d9.30340307	\N
2396	387	4	2025-03-10 21:17:12.159756	survey_67cefb701281d9.30340307	\N
2397	388	2	2025-03-10 21:17:12.160692	survey_67cefb701281d9.30340307	\N
2398	389	3	2025-03-10 21:17:12.160964	survey_67cefb701281d9.30340307	\N
2399	390	2	2025-03-10 21:17:12.161349	survey_67cefb701281d9.30340307	\N
2400	391	1	2025-03-10 21:17:12.161663	survey_67cefb701281d9.30340307	\N
2401	392	2	2025-03-10 21:17:12.161954	survey_67cefb701281d9.30340307	\N
2402	393	4	2025-03-10 21:17:12.162316	survey_67cefb701281d9.30340307	\N
2403	394	4	2025-03-10 21:17:12.163726	survey_67cefb701281d9.30340307	\N
2404	395	3	2025-03-10 21:17:12.163977	survey_67cefb701281d9.30340307	\N
2405	396	3	2025-03-10 21:17:12.164248	survey_67cefb701281d9.30340307	\N
2406	397	2	2025-03-10 21:17:12.164565	survey_67cefb701281d9.30340307	\N
2407	398	1	2025-03-10 21:17:12.165239	survey_67cefb701281d9.30340307	\N
2408	399	2	2025-03-10 21:17:12.167007	survey_67cefb701281d9.30340307	\N
2409	400	2	2025-03-10 21:17:12.167603	survey_67cefb701281d9.30340307	\N
2410	401	3	2025-03-10 21:17:12.168201	survey_67cefb701281d9.30340307	\N
2411	402	3	2025-03-10 21:17:12.168847	survey_67cefb701281d9.30340307	\N
2412	403	3	2025-03-10 21:17:12.16936	survey_67cefb701281d9.30340307	\N
2413	404	3	2025-03-10 21:17:12.169895	survey_67cefb701281d9.30340307	\N
2414	405	3	2025-03-10 21:17:12.17039	survey_67cefb701281d9.30340307	\N
2415	406	3	2025-03-10 21:17:12.170886	survey_67cefb701281d9.30340307	\N
2416	415	4	2025-03-10 21:17:12.171315	survey_67cefb701281d9.30340307	\N
2417	416	4	2025-03-10 21:17:12.171724	survey_67cefb701281d9.30340307	\N
2418	414	4	2025-03-10 21:17:12.172169	survey_67cefb701281d9.30340307	\N
2419	411	3	2025-03-10 21:17:12.172614	survey_67cefb701281d9.30340307	\N
2420	413	3	2025-03-10 21:17:12.173013	survey_67cefb701281d9.30340307	\N
2421	410	4	2025-03-10 21:17:12.173535	survey_67cefb701281d9.30340307	\N
2422	412	3	2025-03-10 21:17:12.17385	survey_67cefb701281d9.30340307	\N
2423	409	4	2025-03-10 21:17:12.174201	survey_67cefb701281d9.30340307	\N
2533	605	test	2026-01-14 09:36:52.847557	survey_6967084cca5828.25102692	\N
2534	609	["1","3"]	2026-01-14 09:36:52.862872	survey_6967084cca5828.25102692	\N
2535	610	test	2026-01-14 09:36:52.863328	survey_6967084cca5828.25102692	\N
2426	382	2010-2017	2025-03-10 21:20:14.674373	survey_67cefc269edb88.41021169	\N
2427	383	4	2025-03-10 21:20:14.690798	survey_67cefc269edb88.41021169	\N
2428	384	2	2025-03-10 21:20:14.693216	survey_67cefc269edb88.41021169	\N
2429	385	1	2025-03-10 21:20:14.693711	survey_67cefc269edb88.41021169	\N
2430	386	4	2025-03-10 21:20:14.694261	survey_67cefc269edb88.41021169	\N
2431	387	4	2025-03-10 21:20:14.694831	survey_67cefc269edb88.41021169	\N
2432	388	5	2025-03-10 21:20:14.695379	survey_67cefc269edb88.41021169	\N
2433	389	3	2025-03-10 21:20:14.695886	survey_67cefc269edb88.41021169	\N
2434	390	3	2025-03-10 21:20:14.696247	survey_67cefc269edb88.41021169	\N
2435	391	2	2025-03-10 21:20:14.698058	survey_67cefc269edb88.41021169	\N
2436	392	3	2025-03-10 21:20:14.698772	survey_67cefc269edb88.41021169	\N
2437	393	1	2025-03-10 21:20:14.699192	survey_67cefc269edb88.41021169	\N
2438	394	2	2025-03-10 21:20:14.699556	survey_67cefc269edb88.41021169	\N
2439	395	1	2025-03-10 21:20:14.699978	survey_67cefc269edb88.41021169	\N
2440	396	2	2025-03-10 21:20:14.700403	survey_67cefc269edb88.41021169	\N
2441	397	2	2025-03-10 21:20:14.700814	survey_67cefc269edb88.41021169	\N
2442	398	3	2025-03-10 21:20:14.701247	survey_67cefc269edb88.41021169	\N
2443	399	2	2025-03-10 21:20:14.701717	survey_67cefc269edb88.41021169	\N
2444	400	5	2025-03-10 21:20:14.722704	survey_67cefc269edb88.41021169	\N
2445	401	3	2025-03-10 21:20:14.723711	survey_67cefc269edb88.41021169	\N
2446	402	5	2025-03-10 21:20:14.724094	survey_67cefc269edb88.41021169	\N
2447	403	5	2025-03-10 21:20:14.72446	survey_67cefc269edb88.41021169	\N
2448	404	3	2025-03-10 21:20:14.724816	survey_67cefc269edb88.41021169	\N
2449	405	2	2025-03-10 21:20:14.726638	survey_67cefc269edb88.41021169	\N
2450	406	2	2025-03-10 21:20:14.726909	survey_67cefc269edb88.41021169	\N
2451	415	1	2025-03-10 21:20:14.72719	survey_67cefc269edb88.41021169	\N
2452	416	5	2025-03-10 21:20:14.727569	survey_67cefc269edb88.41021169	\N
2453	414	5	2025-03-10 21:20:14.727876	survey_67cefc269edb88.41021169	\N
2454	411	5	2025-03-10 21:20:14.728145	survey_67cefc269edb88.41021169	\N
2455	413	4	2025-03-10 21:20:14.728439	survey_67cefc269edb88.41021169	\N
2456	410	3	2025-03-10 21:20:14.728726	survey_67cefc269edb88.41021169	\N
2457	412	2	2025-03-10 21:20:14.72899	survey_67cefc269edb88.41021169	\N
2458	409	1	2025-03-10 21:20:14.73136	survey_67cefc269edb88.41021169	\N
2536	605	t	2026-01-14 09:41:06.561362	survey_6967094a841a48.05625338	\N
2537	609	["2"]	2026-01-14 09:41:06.571212	survey_6967094a841a48.05625338	\N
2538	610	test	2026-01-14 09:41:06.571709	survey_6967094a841a48.05625338	\N
340	67	3	2024-02-06 09:19:54.757864	\N	\N
341	68	3	2024-02-06 09:19:54.758258	\N	\N
342	69	3	2024-02-06 09:19:54.758631	\N	\N
343	70	3	2024-02-06 09:19:54.758962	\N	\N
344	71	3	2024-02-06 09:19:54.759314	\N	\N
345	72	3	2024-02-06 09:19:54.759674	\N	\N
346	73	3	2024-02-06 09:19:54.760047	\N	\N
347	74	3	2024-02-06 09:19:54.760429	\N	\N
348	75	3	2024-02-06 09:19:54.760797	\N	\N
349	76	3	2024-02-06 09:19:54.761155	\N	\N
350	77	3	2024-02-06 09:19:54.761805	\N	\N
351	78	3	2024-02-06 09:19:54.762308	\N	\N
352	79	3	2024-02-06 09:19:54.762738	\N	\N
354	81	3	2024-02-06 09:19:54.763549	\N	\N
355	82	3	2024-02-06 09:19:54.763956	\N	\N
356	83	3	2024-02-06 09:19:54.764348	\N	\N
357	84	3	2024-02-06 09:19:54.764734	\N	\N
2539	605	test	2026-01-14 09:43:34.206732	survey_696709de2ebe09.68588083	\N
2540	609	["1"]	2026-01-14 09:43:34.221884	survey_696709de2ebe09.68588083	\N
2541	610	test	2026-01-14 09:43:34.222816	survey_696709de2ebe09.68588083	\N
358	85	3	2024-02-06 09:19:54.765124	\N	\N
359	86	3	2024-02-06 09:19:54.765571	\N	\N
360	87	3	2024-02-06 09:19:54.766048	\N	\N
361	88	3	2024-02-06 09:19:54.766424	\N	\N
362	89	3	2024-02-06 09:19:54.766774	\N	\N
363	90	3	2024-02-06 09:19:54.767185	\N	\N
2542	605	Goode	2026-01-14 09:49:39.745699	survey_69670b4bb324f6.84213335	\N
2543	609	["3"]	2026-01-14 09:49:39.758837	survey_69670b4bb324f6.84213335	\N
2544	610	G	2026-01-14 09:49:39.759815	survey_69670b4bb324f6.84213335	\N
364	91	3	2024-02-06 09:19:54.767658	\N	\N
365	92	3	2024-02-06 09:19:54.768103	\N	\N
366	93	3	2024-02-06 09:19:54.768541	\N	\N
1390	55	["Parent (biological or adoptive)"]	2024-02-25 22:35:53.959853	survey_67bdea61e87126.24204344	\N
1391	56	Male	2024-02-25 22:35:53.970979	survey_67bdea61e87126.24204344	\N
2545	605	tetetetet	2026-01-14 09:50:13.028187	survey_69670b6d042ab8.99651570	\N
2546	609	["1"]	2026-01-14 09:50:13.034003	survey_69670b6d042ab8.99651570	\N
2547	610	tetetete	2026-01-14 09:50:13.037249	survey_69670b6d042ab8.99651570	\N
1490	55	["Other guardian"]	2024-02-25 23:36:39.925684	survey_67bdf89fdb1d50.11994798	\N
1491	56	Female	2024-02-25 23:36:39.94489	survey_67bdf89fdb1d50.11994798	\N
1492	58	["White- Not Hispanic"]	2024-02-25 23:36:39.945937	survey_67bdf89fdb1d50.11994798	\N
1493	115	2025-2026	2024-02-25 23:36:39.946764	survey_67bdf89fdb1d50.11994798	\N
1494	207	3	2024-02-25 23:36:39.947263	survey_67bdf89fdb1d50.11994798	\N
1495	59	4	2024-02-25 23:36:39.947867	survey_67bdf89fdb1d50.11994798	\N
1496	60	4	2024-02-25 23:36:39.94859	survey_67bdf89fdb1d50.11994798	\N
1497	61	4	2024-02-25 23:36:39.949191	survey_67bdf89fdb1d50.11994798	\N
1498	62	3	2024-02-25 23:36:39.949812	survey_67bdf89fdb1d50.11994798	\N
2548	605	Test	2026-01-14 15:07:24.757949	survey_696755c4b7aa78.38883663	\N
2549	609	["1"]	2026-01-14 15:07:24.76539	survey_696755c4b7aa78.38883663	\N
2550	610	test	2026-01-14 15:07:24.767077	survey_696755c4b7aa78.38883663	\N
2551	615	3	2026-01-14 15:07:24.768416	survey_696755c4b7aa78.38883663	\N
1901	382	Before 1990	2024-03-02 23:03:56.667816	survey_67c48874a1b3a4.20413445	\N
1902	383	4	2024-03-02 23:03:56.676721	survey_67c48874a1b3a4.20413445	\N
1903	384	4	2024-03-02 23:03:56.677557	survey_67c48874a1b3a4.20413445	\N
1904	385	3	2024-03-02 23:03:56.678266	survey_67c48874a1b3a4.20413445	\N
1905	386	3	2024-03-02 23:03:56.678619	survey_67c48874a1b3a4.20413445	\N
1906	387	2	2024-03-02 23:03:56.678951	survey_67c48874a1b3a4.20413445	\N
2552	605	re	2026-01-14 15:18:59.093064	survey_6967587b110487.36591682	\N
2553	609	["3"]	2026-01-14 15:18:59.108571	survey_6967587b110487.36591682	\N
2554	610	re	2026-01-14 15:18:59.109243	survey_6967587b110487.36591682	\N
2555	615	3	2026-01-14 15:18:59.110553	survey_6967587b110487.36591682	\N
2556	616	3	2026-01-14 15:18:59.111649	survey_6967587b110487.36591682	\N
1548	376	3	2024-02-26 09:00:25.868431	survey_67be7cc1cd0fd0.71562121	\N
1549	377	3	2024-02-26 09:00:25.869001	survey_67be7cc1cd0fd0.71562121	\N
1550	378	3	2024-02-26 09:00:25.869704	survey_67be7cc1cd0fd0.71562121	\N
1656	382	2018-2023	2024-02-28 15:07:21.134912	survey_67c175c11d32f0.10178696	\N
1657	383	5	2024-02-28 15:07:21.153915	survey_67c175c11d32f0.10178696	\N
1658	384	5	2024-02-28 15:07:21.154927	survey_67c175c11d32f0.10178696	\N
1659	385	5	2024-02-28 15:07:21.155364	survey_67c175c11d32f0.10178696	\N
1660	386	4	2024-02-28 15:07:21.155798	survey_67c175c11d32f0.10178696	\N
1661	387	4	2024-02-28 15:07:21.156188	survey_67c175c11d32f0.10178696	\N
1662	388	4	2024-02-28 15:07:21.157183	survey_67c175c11d32f0.10178696	\N
1663	389	3	2024-02-28 15:07:21.157524	survey_67c175c11d32f0.10178696	\N
1664	390	3	2024-02-28 15:07:21.158268	survey_67c175c11d32f0.10178696	\N
1665	391	3	2024-02-28 15:07:21.158748	survey_67c175c11d32f0.10178696	\N
1666	392	4	2024-02-28 15:07:21.159264	survey_67c175c11d32f0.10178696	\N
1667	393	4	2024-02-28 15:07:21.159616	survey_67c175c11d32f0.10178696	\N
1668	394	4	2024-02-28 15:07:21.160071	survey_67c175c11d32f0.10178696	\N
1669	395	5	2024-02-28 15:07:21.160469	survey_67c175c11d32f0.10178696	\N
1670	396	4	2024-02-28 15:07:21.160897	survey_67c175c11d32f0.10178696	\N
1671	397	4	2024-02-28 15:07:21.161381	survey_67c175c11d32f0.10178696	\N
1672	398	4	2024-02-28 15:07:21.161792	survey_67c175c11d32f0.10178696	\N
1673	399	4	2024-02-28 15:07:21.162131	survey_67c175c11d32f0.10178696	\N
1674	400	3	2024-02-28 15:07:21.162535	survey_67c175c11d32f0.10178696	\N
1675	401	2	2024-02-28 15:07:21.163003	survey_67c175c11d32f0.10178696	\N
1676	402	1	2024-02-28 15:07:21.163383	survey_67c175c11d32f0.10178696	\N
1677	403	1	2024-02-28 15:07:21.163729	survey_67c175c11d32f0.10178696	\N
1678	404	2	2024-02-28 15:07:21.16416	survey_67c175c11d32f0.10178696	\N
1679	405	3	2024-02-28 15:07:21.164545	survey_67c175c11d32f0.10178696	\N
217	73	5	2024-02-06 08:54:43.970773	\N	\N
218	74	5	2024-02-06 08:54:43.971314	\N	\N
219	75	5	2024-02-06 08:54:43.971849	\N	\N
220	76	4	2024-02-06 08:54:43.972317	\N	\N
221	77	4	2024-02-06 08:54:43.972769	\N	\N
222	78	4	2024-02-06 08:54:43.973212	\N	\N
223	79	4	2024-02-06 08:54:43.97367	\N	\N
225	81	4	2024-02-06 08:54:43.974603	\N	\N
226	82	4	2024-02-06 08:54:43.975077	\N	\N
227	83	4	2024-02-06 08:54:43.975594	\N	\N
228	84	4	2024-02-06 08:54:43.976011	\N	\N
229	85	4	2024-02-06 08:54:43.976439	\N	\N
230	86	4	2024-02-06 08:54:43.97683	\N	\N
231	87	4	2024-02-06 08:54:43.977222	\N	\N
232	88	4	2024-02-06 08:54:43.97761	\N	\N
233	89	4	2024-02-06 08:54:43.977975	\N	\N
234	90	4	2024-02-06 08:54:43.978343	\N	\N
235	91	4	2024-02-06 08:54:43.97872	\N	\N
236	92	4	2024-02-06 08:54:43.979117	\N	\N
237	93	4	2024-02-06 08:54:43.979506	\N	\N
242	55	Parent (biological or adoptive)	2024-02-06 09:03:58.278974	\N	\N
243	56	Male	2024-02-06 09:03:58.286405	\N	\N
245	58	["American Indian or Alaska Native"]	2024-02-06 09:03:58.287393	\N	\N
246	59	3	2024-02-06 09:03:58.28779	\N	\N
247	60	3	2024-02-06 09:03:58.289728	\N	\N
248	61	3	2024-02-06 09:03:58.290034	\N	\N
249	62	3	2024-02-06 09:03:58.290521	\N	\N
250	63	3	2024-02-06 09:03:58.290974	\N	\N
251	64	3	2024-02-06 09:03:58.291374	\N	\N
252	65	3	2024-02-06 09:03:58.291792	\N	\N
253	66	3	2024-02-06 09:03:58.292202	\N	\N
254	67	3	2024-02-06 09:03:58.292611	\N	\N
255	68	3	2024-02-06 09:03:58.293321	\N	\N
256	69	3	2024-02-06 09:03:58.293781	\N	\N
257	70	3	2024-02-06 09:03:58.294167	\N	\N
258	71	3	2024-02-06 09:03:58.294547	\N	\N
259	72	3	2024-02-06 09:03:58.294862	\N	\N
260	73	3	2024-02-06 09:03:58.295225	\N	\N
261	74	3	2024-02-06 09:03:58.295902	\N	\N
262	75	3	2024-02-06 09:03:58.296325	\N	\N
263	76	3	2024-02-06 09:03:58.296792	\N	\N
264	77	3	2024-02-06 09:03:58.297191	\N	\N
265	78	3	2024-02-06 09:03:58.297565	\N	\N
266	79	3	2024-02-06 09:03:58.297983	\N	\N
268	81	3	2024-02-06 09:03:58.299145	\N	\N
269	82	3	2024-02-06 09:03:58.299497	\N	\N
270	83	3	2024-02-06 09:03:58.299976	\N	\N
271	84	3	2024-02-06 09:03:58.300401	\N	\N
272	85	3	2024-02-06 09:03:58.301433	\N	\N
273	86	3	2024-02-06 09:03:58.301864	\N	\N
292	62	2	2024-02-06 09:12:05.531756	\N	\N
293	63	2	2024-02-06 09:12:05.532283	\N	\N
294	64	2	2024-02-06 09:12:05.532782	\N	\N
295	65	2	2024-02-06 09:12:05.533257	\N	\N
296	66	2	2024-02-06 09:12:05.533741	\N	\N
297	67	2	2024-02-06 09:12:05.534498	\N	\N
298	68	2	2024-02-06 09:12:05.534959	\N	\N
299	69	2	2024-02-06 09:12:05.535377	\N	\N
300	70	2	2024-02-06 09:12:05.535799	\N	\N
301	71	2	2024-02-06 09:12:05.536205	\N	\N
302	72	2	2024-02-06 09:12:05.536674	\N	\N
303	73	2	2024-02-06 09:12:05.537349	\N	\N
304	74	2	2024-02-06 09:12:05.537782	\N	\N
305	75	2	2024-02-06 09:12:05.538195	\N	\N
306	76	2	2024-02-06 09:12:05.538618	\N	\N
307	77	2	2024-02-06 09:12:05.539086	\N	\N
308	78	2	2024-02-06 09:12:05.539567	\N	\N
309	79	2	2024-02-06 09:12:05.54003	\N	\N
311	81	2	2024-02-06 09:12:05.540977	\N	\N
312	82	2	2024-02-06 09:12:05.541463	\N	\N
313	83	2	2024-02-06 09:12:05.541917	\N	\N
314	84	2	2024-02-06 09:12:05.542373	\N	\N
315	85	2	2024-02-06 09:12:05.542844	\N	\N
316	86	2	2024-02-06 09:12:05.543256	\N	\N
317	87	2	2024-02-06 09:12:05.543652	\N	\N
318	88	2	2024-02-06 09:12:05.544042	\N	\N
319	89	2	2024-02-06 09:12:05.544453	\N	\N
320	90	2	2024-02-06 09:12:05.544854	\N	\N
321	91	2	2024-02-06 09:12:05.545255	\N	\N
322	92	2	2024-02-06 09:12:05.545654	\N	\N
323	93	2	2024-02-06 09:12:05.546371	\N	\N
328	55	Parent (biological or adoptive)	2024-02-06 09:19:54.739248	\N	\N
329	56	Male	2024-02-06 09:19:54.748748	\N	\N
331	58	["Native Hawaiian or Pacific Islander"]	2024-02-06 09:19:54.751187	\N	\N
332	59	3	2024-02-06 09:19:54.753167	\N	\N
333	60	3	2024-02-06 09:19:54.753768	\N	\N
334	61	3	2024-02-06 09:19:54.75438	\N	\N
335	62	3	2024-02-06 09:19:54.755014	\N	\N
336	63	3	2024-02-06 09:19:54.755513	\N	\N
337	64	3	2024-02-06 09:19:54.755962	\N	\N
338	65	3	2024-02-06 09:19:54.756583	\N	\N
339	66	3	2024-02-06 09:19:54.757138	\N	\N
1392	58	["American Indian or Alaska Native"]	2024-02-25 22:35:53.97164	survey_67bdea61e87126.24204344	\N
1393	115	2024-2025	2024-02-25 22:35:53.97227	survey_67bdea61e87126.24204344	\N
1394	207	1	2024-02-25 22:35:53.97288	survey_67bdea61e87126.24204344	\N
1395	59	1	2024-02-25 22:35:53.973486	survey_67bdea61e87126.24204344	\N
1396	60	1	2024-02-25 22:35:53.974064	survey_67bdea61e87126.24204344	\N
1397	61	1	2024-02-25 22:35:53.97468	survey_67bdea61e87126.24204344	\N
1398	62	1	2024-02-25 22:35:53.975264	survey_67bdea61e87126.24204344	\N
1399	63	1	2024-02-25 22:35:53.975869	survey_67bdea61e87126.24204344	\N
1400	64	1	2024-02-25 22:35:53.976565	survey_67bdea61e87126.24204344	\N
1401	65	1	2024-02-25 22:35:53.977148	survey_67bdea61e87126.24204344	\N
1402	66	1	2024-02-25 22:35:53.977768	survey_67bdea61e87126.24204344	\N
1403	67	1	2024-02-25 22:35:53.978269	survey_67bdea61e87126.24204344	\N
388	55	Parent (biological or adoptive)	2024-02-10 10:52:55.984368	\N	\N
389	56	Male	2024-02-10 10:52:55.991816	\N	\N
391	58	["American Indian or Alaska Native"]	2024-02-10 10:52:55.995599	\N	\N
392	115	2024-2025	2024-02-10 10:52:55.996247	\N	\N
394	60	3	2024-02-10 10:52:55.999516	\N	\N
395	61	3	2024-02-10 10:52:56.000058	\N	\N
396	62	3	2024-02-10 10:52:56.000605	\N	\N
397	63	3	2024-02-10 10:52:56.001379	\N	\N
398	64	3	2024-02-10 10:52:56.002323	\N	\N
399	65	3	2024-02-10 10:52:56.002856	\N	\N
400	66	3	2024-02-10 10:52:56.003409	\N	\N
401	67	3	2024-02-10 10:52:56.003921	\N	\N
402	68	3	2024-02-10 10:52:56.004827	\N	\N
403	69	3	2024-02-10 10:52:56.00543	\N	\N
404	70	3	2024-02-10 10:52:56.006014	\N	\N
405	71	3	2024-02-10 10:52:56.006566	\N	\N
406	72	3	2024-02-10 10:52:56.007117	\N	\N
407	73	3	2024-02-10 10:52:56.007663	\N	\N
408	74	3	2024-02-10 10:52:56.008221	\N	\N
409	75	3	2024-02-10 10:52:56.008778	\N	\N
410	76	3	2024-02-10 10:52:56.009809	\N	\N
411	77	3	2024-02-10 10:52:56.010875	\N	\N
412	78	3	2024-02-10 10:52:56.011302	\N	\N
413	79	3	2024-02-10 10:52:56.011717	\N	\N
415	81	3	2024-02-10 10:52:56.012866	\N	\N
416	82	3	2024-02-10 10:52:56.013308	\N	\N
417	83	3	2024-02-10 10:52:56.013742	\N	\N
418	84	3	2024-02-10 10:52:56.014166	\N	\N
419	85	3	2024-02-10 10:52:56.01498	\N	\N
420	86	3	2024-02-10 10:52:56.015432	\N	\N
421	87	3	2024-02-10 10:52:56.01589	\N	\N
422	88	3	2024-02-10 10:52:56.016312	\N	\N
423	89	3	2024-02-10 10:52:56.016823	\N	\N
424	90	3	2024-02-10 10:52:56.017264	\N	\N
425	91	3	2024-02-10 10:52:56.017704	\N	\N
426	92	3	2024-02-10 10:52:56.018476	\N	\N
427	93	3	2024-02-10 10:52:56.018892	\N	\N
432	55	Parent (biological or adoptive)	2024-02-10 10:54:38.4692	\N	\N
433	56	Male	2024-02-10 10:54:38.475825	\N	\N
435	58	["American Indian or Alaska Native"]	2024-02-10 10:54:38.478218	\N	\N
436	115	2025-2026	2024-02-10 10:54:38.479131	\N	\N
437	59	4	2024-02-10 10:54:38.479969	\N	\N
438	60	4	2024-02-10 10:54:38.480456	\N	\N
439	61	4	2024-02-10 10:54:38.480877	\N	\N
440	62	4	2024-02-10 10:54:38.481289	\N	\N
441	63	4	2024-02-10 10:54:38.481676	\N	\N
442	64	4	2024-02-10 10:54:38.48217	\N	\N
443	65	4	2024-02-10 10:54:38.482789	\N	\N
444	66	4	2024-02-10 10:54:38.483362	\N	\N
445	67	4	2024-02-10 10:54:38.483968	\N	\N
446	68	4	2024-02-10 10:54:38.484582	\N	\N
447	69	4	2024-02-10 10:54:38.485142	\N	\N
448	70	4	2024-02-10 10:54:38.485641	\N	\N
449	71	4	2024-02-10 10:54:38.486101	\N	\N
450	72	4	2024-02-10 10:54:38.486536	\N	\N
451	73	4	2024-02-10 10:54:38.486956	\N	\N
452	74	4	2024-02-10 10:54:38.487446	\N	\N
453	75	4	2024-02-10 10:54:38.487915	\N	\N
454	76	4	2024-02-10 10:54:38.488424	\N	\N
455	77	4	2024-02-10 10:54:38.488891	\N	\N
456	78	4	2024-02-10 10:54:38.489346	\N	\N
457	79	4	2024-02-10 10:54:38.489653	\N	\N
459	81	4	2024-02-10 10:54:38.490476	\N	\N
460	82	4	2024-02-10 10:54:38.490741	\N	\N
461	83	4	2024-02-10 10:54:38.491016	\N	\N
462	84	4	2024-02-10 10:54:38.491388	\N	\N
463	85	4	2024-02-10 10:54:38.491702	\N	\N
464	86	4	2024-02-10 10:54:38.492035	\N	\N
465	87	4	2024-02-10 10:54:38.492729	\N	\N
466	88	4	2024-02-10 10:54:38.49299	\N	\N
467	89	4	2024-02-10 10:54:38.493277	\N	\N
468	90	4	2024-02-10 10:54:38.493556	\N	\N
469	91	4	2024-02-10 10:54:38.493827	\N	\N
470	92	4	2024-02-10 10:54:38.494117	\N	\N
471	93	4	2024-02-10 10:54:38.4944	\N	\N
1404	68	1	2024-02-25 22:35:53.978812	survey_67bdea61e87126.24204344	\N
1405	69	1	2024-02-25 22:35:53.979232	survey_67bdea61e87126.24204344	\N
1406	70	1	2024-02-25 22:35:53.979588	survey_67bdea61e87126.24204344	\N
1407	71	1	2024-02-25 22:35:53.979979	survey_67bdea61e87126.24204344	\N
1408	72	1	2024-02-25 22:35:53.980338	survey_67bdea61e87126.24204344	\N
1409	73	1	2024-02-25 22:35:53.981538	survey_67bdea61e87126.24204344	\N
1410	74	1	2024-02-25 22:35:53.981918	survey_67bdea61e87126.24204344	\N
1411	75	1	2024-02-25 22:35:53.98265	survey_67bdea61e87126.24204344	\N
1412	76	4	2024-02-25 22:35:53.983016	survey_67bdea61e87126.24204344	\N
1413	77	4	2024-02-25 22:35:53.983359	survey_67bdea61e87126.24204344	\N
1414	78	4	2024-02-25 22:35:53.983711	survey_67bdea61e87126.24204344	\N
1415	79	4	2024-02-25 22:35:53.984043	survey_67bdea61e87126.24204344	\N
1417	81	5	2024-02-25 22:35:53.98474	survey_67bdea61e87126.24204344	\N
1418	82	5	2024-02-25 22:35:53.985125	survey_67bdea61e87126.24204344	\N
1419	83	5	2024-02-25 22:35:53.985574	survey_67bdea61e87126.24204344	\N
1420	84	5	2024-02-25 22:35:53.985941	survey_67bdea61e87126.24204344	\N
1421	85	5	2024-02-25 22:35:53.986377	survey_67bdea61e87126.24204344	\N
1422	86	5	2024-02-25 22:35:53.986774	survey_67bdea61e87126.24204344	\N
1423	87	5	2024-02-25 22:35:53.987156	survey_67bdea61e87126.24204344	\N
1424	88	5	2024-02-25 22:35:53.987486	survey_67bdea61e87126.24204344	\N
1425	89	5	2024-02-25 22:35:53.987808	survey_67bdea61e87126.24204344	\N
1426	90	5	2024-02-25 22:35:53.988133	survey_67bdea61e87126.24204344	\N
1427	91	5	2024-02-25 22:35:53.988853	survey_67bdea61e87126.24204344	\N
1428	92	5	2024-02-25 22:35:53.989193	survey_67bdea61e87126.24204344	\N
1429	93	5	2024-02-25 22:35:53.989505	survey_67bdea61e87126.24204344	\N
1680	406	5	2024-02-28 15:07:21.164959	survey_67c175c11d32f0.10178696	\N
1681	415	1	2024-02-28 15:07:21.165285	survey_67c175c11d32f0.10178696	\N
1682	416	2	2024-02-28 15:07:21.165623	survey_67c175c11d32f0.10178696	\N
1683	414	3	2024-02-28 15:07:21.1659	survey_67c175c11d32f0.10178696	\N
1684	411	4	2024-02-28 15:07:21.166188	survey_67c175c11d32f0.10178696	\N
1685	413	5	2024-02-28 15:07:21.16699	survey_67c175c11d32f0.10178696	\N
1686	410	1	2024-02-28 15:07:21.16729	survey_67c175c11d32f0.10178696	\N
1687	412	2	2024-02-28 15:07:21.167571	survey_67c175c11d32f0.10178696	\N
1688	409	3	2024-02-28 15:07:21.167868	survey_67c175c11d32f0.10178696	\N
1434	373	5	2024-02-25 22:36:33.907805	survey_67bdea89d8cd19.81746660	\N
1435	374	5	2024-02-25 22:36:33.908118	survey_67bdea89d8cd19.81746660	\N
1436	375	5	2024-02-25 22:36:33.908432	survey_67bdea89d8cd19.81746660	\N
1437	376	5	2024-02-25 22:36:33.909027	survey_67bdea89d8cd19.81746660	\N
1438	377	5	2024-02-25 22:36:33.909308	survey_67bdea89d8cd19.81746660	\N
1439	378	5	2024-02-25 22:36:33.909584	survey_67bdea89d8cd19.81746660	\N
1929	411	2	2024-03-02 23:03:56.685099	survey_67c48874a1b3a4.20413445	\N
1930	413	3	2024-03-02 23:03:56.685339	survey_67c48874a1b3a4.20413445	\N
1931	410	4	2024-03-02 23:03:56.685569	survey_67c48874a1b3a4.20413445	\N
1932	412	5	2024-03-02 23:03:56.68585	survey_67c48874a1b3a4.20413445	\N
1933	409	5	2024-03-02 23:03:56.686153	survey_67c48874a1b3a4.20413445	\N
2111	382	2018-2023	2024-03-03 08:01:20.436996	survey_67c5066868f070.90617277	\N
2112	383	2	2024-03-03 08:01:20.44457	survey_67c5066868f070.90617277	\N
2113	384	2	2024-03-03 08:01:20.445455	survey_67c5066868f070.90617277	\N
2114	385	2	2024-03-03 08:01:20.446294	survey_67c5066868f070.90617277	\N
2115	386	2	2024-03-03 08:01:20.447076	survey_67c5066868f070.90617277	\N
2116	387	2	2024-03-03 08:01:20.447635	survey_67c5066868f070.90617277	\N
2117	388	2	2024-03-03 08:01:20.448095	survey_67c5066868f070.90617277	\N
2118	389	2	2024-03-03 08:01:20.448487	survey_67c5066868f070.90617277	\N
2119	390	2	2024-03-03 08:01:20.448831	survey_67c5066868f070.90617277	\N
2120	391	2	2024-03-03 08:01:20.449191	survey_67c5066868f070.90617277	\N
2121	392	2	2024-03-03 08:01:20.449546	survey_67c5066868f070.90617277	\N
2122	393	4	2024-03-03 08:01:20.449903	survey_67c5066868f070.90617277	\N
2123	394	4	2024-03-03 08:01:20.450236	survey_67c5066868f070.90617277	\N
2124	395	4	2024-03-03 08:01:20.450567	survey_67c5066868f070.90617277	\N
2125	396	4	2024-03-03 08:01:20.450921	survey_67c5066868f070.90617277	\N
2126	397	4	2024-03-03 08:01:20.451253	survey_67c5066868f070.90617277	\N
2127	398	4	2024-03-03 08:01:20.452093	survey_67c5066868f070.90617277	\N
2128	399	4	2024-03-03 08:01:20.452462	survey_67c5066868f070.90617277	\N
2129	400	4	2024-03-03 08:01:20.452825	survey_67c5066868f070.90617277	\N
2130	401	4	2024-03-03 08:01:20.453182	survey_67c5066868f070.90617277	\N
2131	402	4	2024-03-03 08:01:20.453523	survey_67c5066868f070.90617277	\N
2132	403	4	2024-03-03 08:01:20.453851	survey_67c5066868f070.90617277	\N
2133	404	4	2024-03-03 08:01:20.45423	survey_67c5066868f070.90617277	\N
2134	405	4	2024-03-03 08:01:20.454551	survey_67c5066868f070.90617277	\N
2135	406	4	2024-03-03 08:01:20.454806	survey_67c5066868f070.90617277	\N
2136	415	3	2024-03-03 08:01:20.455085	survey_67c5066868f070.90617277	\N
2137	416	3	2024-03-03 08:01:20.455366	survey_67c5066868f070.90617277	\N
2138	414	3	2024-03-03 08:01:20.455649	survey_67c5066868f070.90617277	\N
2139	411	3	2024-03-03 08:01:20.455969	survey_67c5066868f070.90617277	\N
2140	413	3	2024-03-03 08:01:20.456225	survey_67c5066868f070.90617277	\N
2141	410	3	2024-03-03 08:01:20.456533	survey_67c5066868f070.90617277	\N
2142	412	3	2024-03-03 08:01:20.456838	survey_67c5066868f070.90617277	\N
2143	409	3	2024-03-03 08:01:20.45718	survey_67c5066868f070.90617277	\N
2181	382	2000-2009	2024-03-03 08:05:54.977151	survey_67c5077aeb8e88.18366239	\N
2182	383	2	2024-03-03 08:05:54.986893	survey_67c5077aeb8e88.18366239	\N
2183	384	3	2024-03-03 08:05:54.98765	survey_67c5077aeb8e88.18366239	\N
2184	385	2	2024-03-03 08:05:54.988276	survey_67c5077aeb8e88.18366239	\N
2185	386	3	2024-03-03 08:05:54.989398	survey_67c5077aeb8e88.18366239	\N
2186	387	2	2024-03-03 08:05:54.989922	survey_67c5077aeb8e88.18366239	\N
2187	388	3	2024-03-03 08:05:54.990398	survey_67c5077aeb8e88.18366239	\N
2188	389	3	2024-03-03 08:05:54.991156	survey_67c5077aeb8e88.18366239	\N
2189	390	2	2024-03-03 08:05:54.99161	survey_67c5077aeb8e88.18366239	\N
2190	391	3	2024-03-03 08:05:54.992042	survey_67c5077aeb8e88.18366239	\N
2191	392	2	2024-03-03 08:05:54.992508	survey_67c5077aeb8e88.18366239	\N
2192	393	3	2024-03-03 08:05:54.992951	survey_67c5077aeb8e88.18366239	\N
2193	394	2	2024-03-03 08:05:54.993393	survey_67c5077aeb8e88.18366239	\N
2194	395	3	2024-03-03 08:05:54.993812	survey_67c5077aeb8e88.18366239	\N
2195	396	2	2024-03-03 08:05:54.99425	survey_67c5077aeb8e88.18366239	\N
2196	397	3	2024-03-03 08:05:54.994667	survey_67c5077aeb8e88.18366239	\N
2197	398	2	2024-03-03 08:05:54.995078	survey_67c5077aeb8e88.18366239	\N
2198	399	3	2024-03-03 08:05:54.995753	survey_67c5077aeb8e88.18366239	\N
2199	400	2	2024-03-03 08:05:54.996156	survey_67c5077aeb8e88.18366239	\N
2200	401	3	2024-03-03 08:05:54.996493	survey_67c5077aeb8e88.18366239	\N
2201	402	3	2024-03-03 08:05:54.99679	survey_67c5077aeb8e88.18366239	\N
2202	403	3	2024-03-03 08:05:54.997165	survey_67c5077aeb8e88.18366239	\N
2203	404	3	2024-03-03 08:05:54.997748	survey_67c5077aeb8e88.18366239	\N
2204	405	3	2024-03-03 08:05:54.998283	survey_67c5077aeb8e88.18366239	\N
2205	406	2	2024-03-03 08:05:54.998719	survey_67c5077aeb8e88.18366239	\N
1691	382	2018-2023	2024-03-02 22:30:30.305239	survey_67c4809e3cc987.85429375	\N
1692	383	5	2024-03-02 22:30:30.322203	survey_67c4809e3cc987.85429375	\N
1693	384	4	2024-03-02 22:30:30.322881	survey_67c4809e3cc987.85429375	\N
1694	385	4	2024-03-02 22:30:30.323473	survey_67c4809e3cc987.85429375	\N
1695	386	4	2024-03-02 22:30:30.324043	survey_67c4809e3cc987.85429375	\N
1696	387	4	2024-03-02 22:30:30.324551	survey_67c4809e3cc987.85429375	\N
1697	388	4	2024-03-02 22:30:30.325966	survey_67c4809e3cc987.85429375	\N
1698	389	4	2024-03-02 22:30:30.326725	survey_67c4809e3cc987.85429375	\N
1699	390	4	2024-03-02 22:30:30.327418	survey_67c4809e3cc987.85429375	\N
1700	391	4	2024-03-02 22:30:30.328153	survey_67c4809e3cc987.85429375	\N
1701	392	4	2024-03-02 22:30:30.328953	survey_67c4809e3cc987.85429375	\N
1702	393	3	2024-03-02 22:30:30.329732	survey_67c4809e3cc987.85429375	\N
1703	394	2	2024-03-02 22:30:30.330593	survey_67c4809e3cc987.85429375	\N
1704	395	3	2024-03-02 22:30:30.331103	survey_67c4809e3cc987.85429375	\N
1705	396	4	2024-03-02 22:30:30.331658	survey_67c4809e3cc987.85429375	\N
1706	397	4	2024-03-02 22:30:30.334901	survey_67c4809e3cc987.85429375	\N
1707	398	3	2024-03-02 22:30:30.335424	survey_67c4809e3cc987.85429375	\N
1708	399	4	2024-03-02 22:30:30.336191	survey_67c4809e3cc987.85429375	\N
1709	400	4	2024-03-02 22:30:30.336608	survey_67c4809e3cc987.85429375	\N
1710	401	4	2024-03-02 22:30:30.337529	survey_67c4809e3cc987.85429375	\N
1711	402	4	2024-03-02 22:30:30.337971	survey_67c4809e3cc987.85429375	\N
1712	403	4	2024-03-02 22:30:30.338437	survey_67c4809e3cc987.85429375	\N
1713	404	4	2024-03-02 22:30:30.339155	survey_67c4809e3cc987.85429375	\N
1714	405	4	2024-03-02 22:30:30.339821	survey_67c4809e3cc987.85429375	\N
1715	406	3	2024-03-02 22:30:30.340311	survey_67c4809e3cc987.85429375	\N
1716	415	2	2024-03-02 22:30:30.340612	survey_67c4809e3cc987.85429375	\N
1717	416	2	2024-03-02 22:30:30.352541	survey_67c4809e3cc987.85429375	\N
1718	414	3	2024-03-02 22:30:30.353191	survey_67c4809e3cc987.85429375	\N
1719	411	3	2024-03-02 22:30:30.353781	survey_67c4809e3cc987.85429375	\N
1720	413	4	2024-03-02 22:30:30.354405	survey_67c4809e3cc987.85429375	\N
1721	410	3	2024-03-02 22:30:30.355144	survey_67c4809e3cc987.85429375	\N
1722	412	4	2024-03-02 22:30:30.355832	survey_67c4809e3cc987.85429375	\N
1723	409	4	2024-03-02 22:30:30.356542	survey_67c4809e3cc987.85429375	\N
1936	382	2010-2017	2024-03-02 23:38:22.724484	survey_67c49086aadf93.94935671	\N
1937	383	1	2024-03-02 23:38:22.736364	survey_67c49086aadf93.94935671	\N
1938	384	2	2024-03-02 23:38:22.736857	survey_67c49086aadf93.94935671	\N
1939	385	2	2024-03-02 23:38:22.737275	survey_67c49086aadf93.94935671	\N
1940	386	2	2024-03-02 23:38:22.737635	survey_67c49086aadf93.94935671	\N
1941	387	3	2024-03-02 23:38:22.737993	survey_67c49086aadf93.94935671	\N
1942	388	3	2024-03-02 23:38:22.738358	survey_67c49086aadf93.94935671	\N
1943	389	3	2024-03-02 23:38:22.738683	survey_67c49086aadf93.94935671	\N
1944	390	3	2024-03-02 23:38:22.739	survey_67c49086aadf93.94935671	\N
1945	391	3	2024-03-02 23:38:22.739323	survey_67c49086aadf93.94935671	\N
1946	392	3	2024-03-02 23:38:22.739619	survey_67c49086aadf93.94935671	\N
1947	393	3	2024-03-02 23:38:22.740342	survey_67c49086aadf93.94935671	\N
1948	394	3	2024-03-02 23:38:22.740863	survey_67c49086aadf93.94935671	\N
1949	395	3	2024-03-02 23:38:22.741171	survey_67c49086aadf93.94935671	\N
1950	396	2	2024-03-02 23:38:22.74148	survey_67c49086aadf93.94935671	\N
1951	397	2	2024-03-02 23:38:22.742018	survey_67c49086aadf93.94935671	\N
1952	398	3	2024-03-02 23:38:22.742322	survey_67c49086aadf93.94935671	\N
1953	399	2	2024-03-02 23:38:22.742615	survey_67c49086aadf93.94935671	\N
1954	400	3	2024-03-02 23:38:22.742952	survey_67c49086aadf93.94935671	\N
1955	401	3	2024-03-02 23:38:22.743273	survey_67c49086aadf93.94935671	\N
1956	402	2	2024-03-02 23:38:22.743581	survey_67c49086aadf93.94935671	\N
1957	403	3	2024-03-02 23:38:22.743895	survey_67c49086aadf93.94935671	\N
1958	404	2	2024-03-02 23:38:22.744235	survey_67c49086aadf93.94935671	\N
1959	405	3	2024-03-02 23:38:22.744541	survey_67c49086aadf93.94935671	\N
1960	406	3	2024-03-02 23:38:22.744845	survey_67c49086aadf93.94935671	\N
1961	415	3	2024-03-02 23:38:22.74515	survey_67c49086aadf93.94935671	\N
1962	416	3	2024-03-02 23:38:22.74546	survey_67c49086aadf93.94935671	\N
1963	414	3	2024-03-02 23:38:22.745757	survey_67c49086aadf93.94935671	\N
1964	411	3	2024-03-02 23:38:22.74606	survey_67c49086aadf93.94935671	\N
1965	413	3	2024-03-02 23:38:22.746384	survey_67c49086aadf93.94935671	\N
1966	410	3	2024-03-02 23:38:22.746686	survey_67c49086aadf93.94935671	\N
1967	412	3	2024-03-02 23:38:22.747178	survey_67c49086aadf93.94935671	\N
1968	409	3	2024-03-02 23:38:22.747506	survey_67c49086aadf93.94935671	\N
2146	382	2018-2023	2024-03-03 08:02:44.63158	survey_67c506bc975f13.58340132	\N
2147	383	5	2024-03-03 08:02:44.641372	survey_67c506bc975f13.58340132	\N
2148	384	5	2024-03-03 08:02:44.641816	survey_67c506bc975f13.58340132	\N
2149	385	5	2024-03-03 08:02:44.642183	survey_67c506bc975f13.58340132	\N
2150	386	5	2024-03-03 08:02:44.642526	survey_67c506bc975f13.58340132	\N
2151	387	5	2024-03-03 08:02:44.642832	survey_67c506bc975f13.58340132	\N
2152	388	5	2024-03-03 08:02:44.643149	survey_67c506bc975f13.58340132	\N
2153	389	5	2024-03-03 08:02:44.643453	survey_67c506bc975f13.58340132	\N
2154	390	5	2024-03-03 08:02:44.643729	survey_67c506bc975f13.58340132	\N
2155	391	5	2024-03-03 08:02:44.644072	survey_67c506bc975f13.58340132	\N
2156	392	5	2024-03-03 08:02:44.645313	survey_67c506bc975f13.58340132	\N
2157	393	5	2024-03-03 08:02:44.645893	survey_67c506bc975f13.58340132	\N
2158	394	5	2024-03-03 08:02:44.646457	survey_67c506bc975f13.58340132	\N
1590	369	4	2024-02-27 23:33:42.792625	survey_67c09aeebf8846.15728677	\N
1591	370	4	2024-02-27 23:33:42.800027	survey_67c09aeebf8846.15728677	\N
1592	371	4	2024-02-27 23:33:42.800574	survey_67c09aeebf8846.15728677	\N
1593	372	4	2024-02-27 23:33:42.802205	survey_67c09aeebf8846.15728677	\N
1594	373	4	2024-02-27 23:33:42.802579	survey_67c09aeebf8846.15728677	\N
1595	374	4	2024-02-27 23:33:42.802951	survey_67c09aeebf8846.15728677	\N
1596	375	4	2024-02-27 23:33:42.803343	survey_67c09aeebf8846.15728677	\N
1597	376	4	2024-02-27 23:33:42.806129	survey_67c09aeebf8846.15728677	\N
1598	377	4	2024-02-27 23:33:42.806814	survey_67c09aeebf8846.15728677	\N
1599	378	4	2024-02-27 23:33:42.807539	survey_67c09aeebf8846.15728677	\N
1726	382	Before 1990	2024-03-02 22:46:20.06487	survey_67c484540ba4f0.45078816	\N
1727	383	4	2024-03-02 22:46:20.080351	survey_67c484540ba4f0.45078816	\N
1728	384	3	2024-03-02 22:46:20.081454	survey_67c484540ba4f0.45078816	\N
1729	385	4	2024-03-02 22:46:20.0821	survey_67c484540ba4f0.45078816	\N
1730	386	5	2024-03-02 22:46:20.082669	survey_67c484540ba4f0.45078816	\N
1731	387	4	2024-03-02 22:46:20.083145	survey_67c484540ba4f0.45078816	\N
1732	388	4	2024-03-02 22:46:20.08372	survey_67c484540ba4f0.45078816	\N
1733	389	3	2024-03-02 22:46:20.084295	survey_67c484540ba4f0.45078816	\N
1734	390	2	2024-03-02 22:46:20.084835	survey_67c484540ba4f0.45078816	\N
1735	391	2	2024-03-02 22:46:20.085392	survey_67c484540ba4f0.45078816	\N
1736	392	3	2024-03-02 22:46:20.085927	survey_67c484540ba4f0.45078816	\N
1737	393	3	2024-03-02 22:46:20.086507	survey_67c484540ba4f0.45078816	\N
1738	394	4	2024-03-02 22:46:20.087384	survey_67c484540ba4f0.45078816	\N
1739	395	4	2024-03-02 22:46:20.08828	survey_67c484540ba4f0.45078816	\N
1740	396	3	2024-03-02 22:46:20.08886	survey_67c484540ba4f0.45078816	\N
1741	397	3	2024-03-02 22:46:20.089893	survey_67c484540ba4f0.45078816	\N
1742	398	3	2024-03-02 22:46:20.090482	survey_67c484540ba4f0.45078816	\N
1743	399	4	2024-03-02 22:46:20.091212	survey_67c484540ba4f0.45078816	\N
1744	400	3	2024-03-02 22:46:20.092029	survey_67c484540ba4f0.45078816	\N
1745	401	4	2024-03-02 22:46:20.093082	survey_67c484540ba4f0.45078816	\N
1746	402	4	2024-03-02 22:46:20.09538	survey_67c484540ba4f0.45078816	\N
1747	403	3	2024-03-02 22:46:20.096222	survey_67c484540ba4f0.45078816	\N
1748	404	4	2024-03-02 22:46:20.096879	survey_67c484540ba4f0.45078816	\N
1749	405	4	2024-03-02 22:46:20.097596	survey_67c484540ba4f0.45078816	\N
1750	406	4	2024-03-02 22:46:20.097971	survey_67c484540ba4f0.45078816	\N
1751	415	3	2024-03-02 22:46:20.098324	survey_67c484540ba4f0.45078816	\N
1752	416	3	2024-03-02 22:46:20.098669	survey_67c484540ba4f0.45078816	\N
1753	414	4	2024-03-02 22:46:20.099012	survey_67c484540ba4f0.45078816	\N
1754	411	4	2024-03-02 22:46:20.099397	survey_67c484540ba4f0.45078816	\N
1755	413	4	2024-03-02 22:46:20.09974	survey_67c484540ba4f0.45078816	\N
1756	410	4	2024-03-02 22:46:20.100081	survey_67c484540ba4f0.45078816	\N
1757	412	3	2024-03-02 22:46:20.100416	survey_67c484540ba4f0.45078816	\N
1758	409	4	2024-03-02 22:46:20.100749	survey_67c484540ba4f0.45078816	\N
1971	382	1990-1999	2024-03-02 23:44:41.020064	survey_67c49200ed5d50.54429698	\N
1972	383	4	2024-03-02 23:44:41.034019	survey_67c49200ed5d50.54429698	\N
1973	384	4	2024-03-02 23:44:41.034748	survey_67c49200ed5d50.54429698	\N
1974	385	4	2024-03-02 23:44:41.035744	survey_67c49200ed5d50.54429698	\N
1975	386	4	2024-03-02 23:44:41.036407	survey_67c49200ed5d50.54429698	\N
1976	387	4	2024-03-02 23:44:41.03703	survey_67c49200ed5d50.54429698	\N
1977	388	4	2024-03-02 23:44:41.037715	survey_67c49200ed5d50.54429698	\N
1978	389	4	2024-03-02 23:44:41.039094	survey_67c49200ed5d50.54429698	\N
1979	390	4	2024-03-02 23:44:41.039694	survey_67c49200ed5d50.54429698	\N
1980	391	4	2024-03-02 23:44:41.040251	survey_67c49200ed5d50.54429698	\N
1981	392	4	2024-03-02 23:44:41.040914	survey_67c49200ed5d50.54429698	\N
1982	393	2	2024-03-02 23:44:41.041489	survey_67c49200ed5d50.54429698	\N
1983	394	3	2024-03-02 23:44:41.042099	survey_67c49200ed5d50.54429698	\N
1984	395	3	2024-03-02 23:44:41.042717	survey_67c49200ed5d50.54429698	\N
1985	396	3	2024-03-02 23:44:41.043398	survey_67c49200ed5d50.54429698	\N
1986	397	4	2024-03-02 23:44:41.043892	survey_67c49200ed5d50.54429698	\N
1987	398	4	2024-03-02 23:44:41.044258	survey_67c49200ed5d50.54429698	\N
1988	399	3	2024-03-02 23:44:41.044788	survey_67c49200ed5d50.54429698	\N
1989	400	3	2024-03-02 23:44:41.045041	survey_67c49200ed5d50.54429698	\N
1990	401	3	2024-03-02 23:44:41.045448	survey_67c49200ed5d50.54429698	\N
1991	402	3	2024-03-02 23:44:41.046063	survey_67c49200ed5d50.54429698	\N
1992	403	3	2024-03-02 23:44:41.046584	survey_67c49200ed5d50.54429698	\N
1993	404	3	2024-03-02 23:44:41.047113	survey_67c49200ed5d50.54429698	\N
1994	405	3	2024-03-02 23:44:41.047624	survey_67c49200ed5d50.54429698	\N
1995	406	3	2024-03-02 23:44:41.04814	survey_67c49200ed5d50.54429698	\N
1996	415	1	2024-03-02 23:44:41.048652	survey_67c49200ed5d50.54429698	\N
1997	416	2	2024-03-02 23:44:41.049097	survey_67c49200ed5d50.54429698	\N
1998	414	2	2024-03-02 23:44:41.049535	survey_67c49200ed5d50.54429698	\N
1999	411	2	2024-03-02 23:44:41.049966	survey_67c49200ed5d50.54429698	\N
2000	413	2	2024-03-02 23:44:41.050404	survey_67c49200ed5d50.54429698	\N
2001	410	3	2024-03-02 23:44:41.050915	survey_67c49200ed5d50.54429698	\N
2002	412	3	2024-03-02 23:44:41.051334	survey_67c49200ed5d50.54429698	\N
2003	409	3	2024-03-02 23:44:41.051763	survey_67c49200ed5d50.54429698	\N
2159	395	5	2024-03-03 08:02:44.647007	survey_67c506bc975f13.58340132	\N
2160	396	5	2024-03-03 08:02:44.647563	survey_67c506bc975f13.58340132	\N
2161	397	5	2024-03-03 08:02:44.648272	survey_67c506bc975f13.58340132	\N
2162	398	5	2024-03-03 08:02:44.648917	survey_67c506bc975f13.58340132	\N
2163	399	5	2024-03-03 08:02:44.64946	survey_67c506bc975f13.58340132	\N
2164	400	5	2024-03-03 08:02:44.649977	survey_67c506bc975f13.58340132	\N
1761	382	2010-2017	2024-03-02 22:47:36.312327	survey_67c484a04ab938.34111463	\N
1762	383	1	2024-03-02 22:47:36.323131	survey_67c484a04ab938.34111463	\N
1763	384	1	2024-03-02 22:47:36.324048	survey_67c484a04ab938.34111463	\N
1764	385	1	2024-03-02 22:47:36.324563	survey_67c484a04ab938.34111463	\N
1765	386	1	2024-03-02 22:47:36.325046	survey_67c484a04ab938.34111463	\N
1766	387	1	2024-03-02 22:47:36.325701	survey_67c484a04ab938.34111463	\N
1767	388	1	2024-03-02 22:47:36.32679	survey_67c484a04ab938.34111463	\N
1768	389	1	2024-03-02 22:47:36.327271	survey_67c484a04ab938.34111463	\N
1769	390	1	2024-03-02 22:47:36.327696	survey_67c484a04ab938.34111463	\N
1770	391	1	2024-03-02 22:47:36.328274	survey_67c484a04ab938.34111463	\N
1771	392	1	2024-03-02 22:47:36.32889	survey_67c484a04ab938.34111463	\N
1772	393	1	2024-03-02 22:47:36.329979	survey_67c484a04ab938.34111463	\N
1773	394	1	2024-03-02 22:47:36.330631	survey_67c484a04ab938.34111463	\N
1774	395	1	2024-03-02 22:47:36.331295	survey_67c484a04ab938.34111463	\N
1775	396	1	2024-03-02 22:47:36.33192	survey_67c484a04ab938.34111463	\N
1776	397	1	2024-03-02 22:47:36.332551	survey_67c484a04ab938.34111463	\N
1777	398	1	2024-03-02 22:47:36.333166	survey_67c484a04ab938.34111463	\N
1778	399	1	2024-03-02 22:47:36.334374	survey_67c484a04ab938.34111463	\N
1779	400	1	2024-03-02 22:47:36.334983	survey_67c484a04ab938.34111463	\N
1780	401	1	2024-03-02 22:47:36.335586	survey_67c484a04ab938.34111463	\N
1781	402	1	2024-03-02 22:47:36.336157	survey_67c484a04ab938.34111463	\N
1782	403	1	2024-03-02 22:47:36.33674	survey_67c484a04ab938.34111463	\N
1783	404	1	2024-03-02 22:47:36.337408	survey_67c484a04ab938.34111463	\N
1784	405	1	2024-03-02 22:47:36.338059	survey_67c484a04ab938.34111463	\N
1785	406	1	2024-03-02 22:47:36.338675	survey_67c484a04ab938.34111463	\N
1786	415	1	2024-03-02 22:47:36.33932	survey_67c484a04ab938.34111463	\N
1787	416	1	2024-03-02 22:47:36.339997	survey_67c484a04ab938.34111463	\N
1788	414	1	2024-03-02 22:47:36.340556	survey_67c484a04ab938.34111463	\N
1789	411	1	2024-03-02 22:47:36.341186	survey_67c484a04ab938.34111463	\N
1790	413	1	2024-03-02 22:47:36.341722	survey_67c484a04ab938.34111463	\N
1791	410	1	2024-03-02 22:47:36.342149	survey_67c484a04ab938.34111463	\N
1792	412	1	2024-03-02 22:47:36.342508	survey_67c484a04ab938.34111463	\N
1793	409	1	2024-03-02 22:47:36.34295	survey_67c484a04ab938.34111463	\N
2006	382	2010-2017	2024-03-03 07:53:24.756239	survey_67c5048cb303a0.75512212	\N
2007	383	5	2024-03-03 07:53:24.77624	survey_67c5048cb303a0.75512212	\N
2008	384	1	2024-03-03 07:53:24.776983	survey_67c5048cb303a0.75512212	\N
2009	385	5	2024-03-03 07:53:24.777763	survey_67c5048cb303a0.75512212	\N
2010	386	1	2024-03-03 07:53:24.778517	survey_67c5048cb303a0.75512212	\N
2011	387	5	2024-03-03 07:53:24.779703	survey_67c5048cb303a0.75512212	\N
2012	388	1	2024-03-03 07:53:24.780424	survey_67c5048cb303a0.75512212	\N
2013	389	2	2024-03-03 07:53:24.781081	survey_67c5048cb303a0.75512212	\N
2014	390	3	2024-03-03 07:53:24.781738	survey_67c5048cb303a0.75512212	\N
2015	391	3	2024-03-03 07:53:24.782982	survey_67c5048cb303a0.75512212	\N
2016	392	2	2024-03-03 07:53:24.783975	survey_67c5048cb303a0.75512212	\N
2017	393	3	2024-03-03 07:53:24.784579	survey_67c5048cb303a0.75512212	\N
2018	394	3	2024-03-03 07:53:24.785198	survey_67c5048cb303a0.75512212	\N
2019	395	2	2024-03-03 07:53:24.785996	survey_67c5048cb303a0.75512212	\N
2020	396	3	2024-03-03 07:53:24.786393	survey_67c5048cb303a0.75512212	\N
2021	397	2	2024-03-03 07:53:24.787069	survey_67c5048cb303a0.75512212	\N
2022	398	3	2024-03-03 07:53:24.788224	survey_67c5048cb303a0.75512212	\N
2023	399	4	2024-03-03 07:53:24.788911	survey_67c5048cb303a0.75512212	\N
2024	400	2	2024-03-03 07:53:24.789607	survey_67c5048cb303a0.75512212	\N
2025	401	4	2024-03-03 07:53:24.790642	survey_67c5048cb303a0.75512212	\N
2026	402	2	2024-03-03 07:53:24.791323	survey_67c5048cb303a0.75512212	\N
2027	403	4	2024-03-03 07:53:24.791942	survey_67c5048cb303a0.75512212	\N
2028	404	3	2024-03-03 07:53:24.792602	survey_67c5048cb303a0.75512212	\N
2029	405	2	2024-03-03 07:53:24.793238	survey_67c5048cb303a0.75512212	\N
2030	406	3	2024-03-03 07:53:24.793869	survey_67c5048cb303a0.75512212	\N
2031	415	1	2024-03-03 07:53:24.794507	survey_67c5048cb303a0.75512212	\N
2032	416	2	2024-03-03 07:53:24.795139	survey_67c5048cb303a0.75512212	\N
2033	414	3	2024-03-03 07:53:24.795732	survey_67c5048cb303a0.75512212	\N
2034	411	2	2024-03-03 07:53:24.796213	survey_67c5048cb303a0.75512212	\N
2035	413	3	2024-03-03 07:53:24.796768	survey_67c5048cb303a0.75512212	\N
2036	410	4	2024-03-03 07:53:24.79724	survey_67c5048cb303a0.75512212	\N
2037	412	5	2024-03-03 07:53:24.797721	survey_67c5048cb303a0.75512212	\N
2038	409	4	2024-03-03 07:53:24.798242	survey_67c5048cb303a0.75512212	\N
2165	401	5	2024-03-03 08:02:44.650481	survey_67c506bc975f13.58340132	\N
2166	402	5	2024-03-03 08:02:44.651519	survey_67c506bc975f13.58340132	\N
1470	369	4	2024-02-25 23:30:23.175889	survey_67bdf727266583.41166487	\N
1471	370	4	2024-02-25 23:30:23.188096	survey_67bdf727266583.41166487	\N
1472	371	3	2024-02-25 23:30:23.190138	survey_67bdf727266583.41166487	\N
1473	372	3	2024-02-25 23:30:23.190617	survey_67bdf727266583.41166487	\N
1474	373	3	2024-02-25 23:30:23.191069	survey_67bdf727266583.41166487	\N
1475	374	3	2024-02-25 23:30:23.193303	survey_67bdf727266583.41166487	\N
1476	375	3	2024-02-25 23:30:23.193638	survey_67bdf727266583.41166487	\N
1477	376	3	2024-02-25 23:30:23.193932	survey_67bdf727266583.41166487	\N
1478	377	3	2024-02-25 23:30:23.194226	survey_67bdf727266583.41166487	\N
1479	378	5	2024-02-25 23:30:23.194475	survey_67bdf727266583.41166487	\N
1613	369	5	2024-02-28 09:58:46.534165	survey_67c12d6e8190f1.43007261	\N
1614	370	5	2024-02-28 09:58:46.545556	survey_67c12d6e8190f1.43007261	\N
1615	371	5	2024-02-28 09:58:46.546222	survey_67c12d6e8190f1.43007261	\N
1616	372	5	2024-02-28 09:58:46.546696	survey_67c12d6e8190f1.43007261	\N
1617	373	4	2024-02-28 09:58:46.54706	survey_67c12d6e8190f1.43007261	\N
1618	374	4	2024-02-28 09:58:46.54825	survey_67c12d6e8190f1.43007261	\N
1619	375	4	2024-02-28 09:58:46.548622	survey_67c12d6e8190f1.43007261	\N
1620	376	4	2024-02-28 09:58:46.548955	survey_67c12d6e8190f1.43007261	\N
1621	377	4	2024-02-28 09:58:46.549502	survey_67c12d6e8190f1.43007261	\N
1622	378	4	2024-02-28 09:58:46.550189	survey_67c12d6e8190f1.43007261	\N
1796	382	2018-2023	2024-03-02 22:53:32.745623	survey_67c48604b1b707.70920098	\N
1797	383	3	2024-03-02 22:53:32.760067	survey_67c48604b1b707.70920098	\N
1798	384	3	2024-03-02 22:53:32.760904	survey_67c48604b1b707.70920098	\N
1799	385	3	2024-03-02 22:53:32.761642	survey_67c48604b1b707.70920098	\N
1800	386	3	2024-03-02 22:53:32.762394	survey_67c48604b1b707.70920098	\N
1801	387	3	2024-03-02 22:53:32.763112	survey_67c48604b1b707.70920098	\N
1802	388	3	2024-03-02 22:53:32.763887	survey_67c48604b1b707.70920098	\N
1803	389	3	2024-03-02 22:53:32.76451	survey_67c48604b1b707.70920098	\N
1804	390	3	2024-03-02 22:53:32.765146	survey_67c48604b1b707.70920098	\N
1805	391	3	2024-03-02 22:53:32.765785	survey_67c48604b1b707.70920098	\N
1806	392	3	2024-03-02 22:53:32.76639	survey_67c48604b1b707.70920098	\N
1807	393	3	2024-03-02 22:53:32.767034	survey_67c48604b1b707.70920098	\N
1808	394	3	2024-03-02 22:53:32.767629	survey_67c48604b1b707.70920098	\N
1809	395	3	2024-03-02 22:53:32.768103	survey_67c48604b1b707.70920098	\N
1810	396	3	2024-03-02 22:53:32.768589	survey_67c48604b1b707.70920098	\N
1811	397	3	2024-03-02 22:53:32.769058	survey_67c48604b1b707.70920098	\N
1812	398	3	2024-03-02 22:53:32.769507	survey_67c48604b1b707.70920098	\N
1813	399	3	2024-03-02 22:53:32.770002	survey_67c48604b1b707.70920098	\N
1814	400	3	2024-03-02 22:53:32.770489	survey_67c48604b1b707.70920098	\N
1815	401	3	2024-03-02 22:53:32.770981	survey_67c48604b1b707.70920098	\N
1816	402	3	2024-03-02 22:53:32.771456	survey_67c48604b1b707.70920098	\N
1817	403	3	2024-03-02 22:53:32.771845	survey_67c48604b1b707.70920098	\N
1818	404	3	2024-03-02 22:53:32.773049	survey_67c48604b1b707.70920098	\N
1819	405	3	2024-03-02 22:53:32.773438	survey_67c48604b1b707.70920098	\N
1820	406	3	2024-03-02 22:53:32.773817	survey_67c48604b1b707.70920098	\N
1821	415	3	2024-03-02 22:53:32.774185	survey_67c48604b1b707.70920098	\N
1822	416	3	2024-03-02 22:53:32.774556	survey_67c48604b1b707.70920098	\N
1823	414	3	2024-03-02 22:53:32.774942	survey_67c48604b1b707.70920098	\N
1824	411	3	2024-03-02 22:53:32.775317	survey_67c48604b1b707.70920098	\N
1825	413	3	2024-03-02 22:53:32.775709	survey_67c48604b1b707.70920098	\N
1826	410	3	2024-03-02 22:53:32.776084	survey_67c48604b1b707.70920098	\N
1827	412	3	2024-03-02 22:53:32.776481	survey_67c48604b1b707.70920098	\N
1828	409	3	2024-03-02 22:53:32.776861	survey_67c48604b1b707.70920098	\N
2041	382	2018-2023	2024-03-03 07:56:43.204591	survey_67c505532d92c7.53474142	\N
2042	383	4	2024-03-03 07:56:43.221289	survey_67c505532d92c7.53474142	\N
2043	384	3	2024-03-03 07:56:43.223793	survey_67c505532d92c7.53474142	\N
2044	385	2	2024-03-03 07:56:43.224559	survey_67c505532d92c7.53474142	\N
2045	386	3	2024-03-03 07:56:43.225263	survey_67c505532d92c7.53474142	\N
2046	387	2	2024-03-03 07:56:43.225874	survey_67c505532d92c7.53474142	\N
2047	388	3	2024-03-03 07:56:43.226583	survey_67c505532d92c7.53474142	\N
2048	389	2	2024-03-03 07:56:43.227524	survey_67c505532d92c7.53474142	\N
2049	390	3	2024-03-03 07:56:43.228112	survey_67c505532d92c7.53474142	\N
2050	391	2	2024-03-03 07:56:43.229118	survey_67c505532d92c7.53474142	\N
2051	392	3	2024-03-03 07:56:43.229884	survey_67c505532d92c7.53474142	\N
2052	393	3	2024-03-03 07:56:43.230652	survey_67c505532d92c7.53474142	\N
2053	394	2	2024-03-03 07:56:43.231758	survey_67c505532d92c7.53474142	\N
2054	395	3	2024-03-03 07:56:43.23238	survey_67c505532d92c7.53474142	\N
2055	396	3	2024-03-03 07:56:43.233072	survey_67c505532d92c7.53474142	\N
2056	397	3	2024-03-03 07:56:43.233791	survey_67c505532d92c7.53474142	\N
2057	398	3	2024-03-03 07:56:43.234496	survey_67c505532d92c7.53474142	\N
2058	399	3	2024-03-03 07:56:43.235075	survey_67c505532d92c7.53474142	\N
2059	400	3	2024-03-03 07:56:43.235458	survey_67c505532d92c7.53474142	\N
2060	401	3	2024-03-03 07:56:43.235777	survey_67c505532d92c7.53474142	\N
2061	402	2	2024-03-03 07:56:43.23612	survey_67c505532d92c7.53474142	\N
2062	403	2	2024-03-03 07:56:43.236667	survey_67c505532d92c7.53474142	\N
2063	404	3	2024-03-03 07:56:43.237214	survey_67c505532d92c7.53474142	\N
2064	405	3	2024-03-03 07:56:43.237695	survey_67c505532d92c7.53474142	\N
2065	406	3	2024-03-03 07:56:43.238172	survey_67c505532d92c7.53474142	\N
2066	415	1	2024-03-03 07:56:43.238691	survey_67c505532d92c7.53474142	\N
2067	416	2	2024-03-03 07:56:43.239165	survey_67c505532d92c7.53474142	\N
2068	414	2	2024-03-03 07:56:43.239629	survey_67c505532d92c7.53474142	\N
2069	411	3	2024-03-03 07:56:43.240073	survey_67c505532d92c7.53474142	\N
2070	413	3	2024-03-03 07:56:43.240554	survey_67c505532d92c7.53474142	\N
2071	410	2	2024-03-03 07:56:43.241016	survey_67c505532d92c7.53474142	\N
2072	412	3	2024-03-03 07:56:43.241483	survey_67c505532d92c7.53474142	\N
2073	409	2	2024-03-03 07:56:43.241934	survey_67c505532d92c7.53474142	\N
2167	403	5	2024-03-03 08:02:44.652048	survey_67c506bc975f13.58340132	\N
2168	404	5	2024-03-03 08:02:44.652595	survey_67c506bc975f13.58340132	\N
2169	405	5	2024-03-03 08:02:44.65295	survey_67c506bc975f13.58340132	\N
1480	369	3	2024-02-25 23:33:36.123217	survey_67bdf7e818bb18.50118382	\N
1481	370	3	2024-02-25 23:33:36.136946	survey_67bdf7e818bb18.50118382	\N
1482	371	3	2024-02-25 23:33:36.137708	survey_67bdf7e818bb18.50118382	\N
1483	372	2	2024-02-25 23:33:36.138338	survey_67bdf7e818bb18.50118382	\N
1484	373	2	2024-02-25 23:33:36.139268	survey_67bdf7e818bb18.50118382	\N
1485	374	3	2024-02-25 23:33:36.140376	survey_67bdf7e818bb18.50118382	\N
1486	375	3	2024-02-25 23:33:36.141186	survey_67bdf7e818bb18.50118382	\N
1487	376	2	2024-02-25 23:33:36.143877	survey_67bdf7e818bb18.50118382	\N
1488	377	3	2024-02-25 23:33:36.144676	survey_67bdf7e818bb18.50118382	\N
1489	378	3	2024-02-25 23:33:36.145308	survey_67bdf7e818bb18.50118382	\N
1831	382	2000-2009	2024-03-02 23:00:13.866844	survey_67c48795cec4d0.16345953	\N
1832	383	2	2024-03-02 23:00:13.888257	survey_67c48795cec4d0.16345953	\N
1833	384	4	2024-03-02 23:00:13.888922	survey_67c48795cec4d0.16345953	\N
1834	385	2	2024-03-02 23:00:13.889656	survey_67c48795cec4d0.16345953	\N
1835	386	4	2024-03-02 23:00:13.890062	survey_67c48795cec4d0.16345953	\N
1836	387	2	2024-03-02 23:00:13.890441	survey_67c48795cec4d0.16345953	\N
1837	388	4	2024-03-02 23:00:13.890805	survey_67c48795cec4d0.16345953	\N
1838	389	2	2024-03-02 23:00:13.891098	survey_67c48795cec4d0.16345953	\N
1839	390	4	2024-03-02 23:00:13.89141	survey_67c48795cec4d0.16345953	\N
1840	391	2	2024-03-02 23:00:13.891724	survey_67c48795cec4d0.16345953	\N
1841	392	4	2024-03-02 23:00:13.892093	survey_67c48795cec4d0.16345953	\N
1842	393	1	2024-03-02 23:00:13.892449	survey_67c48795cec4d0.16345953	\N
1843	394	5	2024-03-02 23:00:13.89276	survey_67c48795cec4d0.16345953	\N
1844	395	1	2024-03-02 23:00:13.893101	survey_67c48795cec4d0.16345953	\N
1845	396	5	2024-03-02 23:00:13.893497	survey_67c48795cec4d0.16345953	\N
1846	397	1	2024-03-02 23:00:13.893849	survey_67c48795cec4d0.16345953	\N
1847	398	5	2024-03-02 23:00:13.894609	survey_67c48795cec4d0.16345953	\N
1848	399	1	2024-03-02 23:00:13.894948	survey_67c48795cec4d0.16345953	\N
1849	400	5	2024-03-02 23:00:13.895351	survey_67c48795cec4d0.16345953	\N
1850	401	1	2024-03-02 23:00:13.895805	survey_67c48795cec4d0.16345953	\N
1851	402	5	2024-03-02 23:00:13.896267	survey_67c48795cec4d0.16345953	\N
1852	403	1	2024-03-02 23:00:13.896725	survey_67c48795cec4d0.16345953	\N
1853	404	5	2024-03-02 23:00:13.897694	survey_67c48795cec4d0.16345953	\N
1854	405	1	2024-03-02 23:00:13.898144	survey_67c48795cec4d0.16345953	\N
1855	406	5	2024-03-02 23:00:13.898586	survey_67c48795cec4d0.16345953	\N
1856	415	2	2024-03-02 23:00:13.899034	survey_67c48795cec4d0.16345953	\N
1857	416	4	2024-03-02 23:00:13.899471	survey_67c48795cec4d0.16345953	\N
1858	414	2	2024-03-02 23:00:13.899935	survey_67c48795cec4d0.16345953	\N
1859	411	4	2024-03-02 23:00:13.900375	survey_67c48795cec4d0.16345953	\N
1860	413	2	2024-03-02 23:00:13.900815	survey_67c48795cec4d0.16345953	\N
1861	410	4	2024-03-02 23:00:13.901264	survey_67c48795cec4d0.16345953	\N
1862	412	2	2024-03-02 23:00:13.90174	survey_67c48795cec4d0.16345953	\N
1863	409	4	2024-03-02 23:00:13.902169	survey_67c48795cec4d0.16345953	\N
2076	382	2018-2023	2024-03-03 07:59:07.899367	survey_67c505e3da62d8.95739531	\N
2077	383	3	2024-03-03 07:59:07.902866	survey_67c505e3da62d8.95739531	\N
2078	384	3	2024-03-03 07:59:07.903332	survey_67c505e3da62d8.95739531	\N
2079	385	3	2024-03-03 07:59:07.903872	survey_67c505e3da62d8.95739531	\N
2080	386	3	2024-03-03 07:59:07.904401	survey_67c505e3da62d8.95739531	\N
2081	387	3	2024-03-03 07:59:07.904947	survey_67c505e3da62d8.95739531	\N
2082	388	3	2024-03-03 07:59:07.905443	survey_67c505e3da62d8.95739531	\N
2083	389	3	2024-03-03 07:59:07.907256	survey_67c505e3da62d8.95739531	\N
2084	390	3	2024-03-03 07:59:07.907755	survey_67c505e3da62d8.95739531	\N
2085	391	3	2024-03-03 07:59:07.908246	survey_67c505e3da62d8.95739531	\N
2086	392	3	2024-03-03 07:59:07.90875	survey_67c505e3da62d8.95739531	\N
2087	393	3	2024-03-03 07:59:07.909235	survey_67c505e3da62d8.95739531	\N
2088	394	3	2024-03-03 07:59:07.90972	survey_67c505e3da62d8.95739531	\N
2089	395	3	2024-03-03 07:59:07.910233	survey_67c505e3da62d8.95739531	\N
2090	396	3	2024-03-03 07:59:07.910758	survey_67c505e3da62d8.95739531	\N
2091	397	3	2024-03-03 07:59:07.911294	survey_67c505e3da62d8.95739531	\N
2092	398	3	2024-03-03 07:59:07.911833	survey_67c505e3da62d8.95739531	\N
2093	399	3	2024-03-03 07:59:07.912172	survey_67c505e3da62d8.95739531	\N
2094	400	3	2024-03-03 07:59:07.912485	survey_67c505e3da62d8.95739531	\N
2095	401	3	2024-03-03 07:59:07.91295	survey_67c505e3da62d8.95739531	\N
2096	402	3	2024-03-03 07:59:07.913501	survey_67c505e3da62d8.95739531	\N
2097	403	3	2024-03-03 07:59:07.913992	survey_67c505e3da62d8.95739531	\N
2098	404	3	2024-03-03 07:59:07.914464	survey_67c505e3da62d8.95739531	\N
2099	405	3	2024-03-03 07:59:07.914844	survey_67c505e3da62d8.95739531	\N
2100	406	3	2024-03-03 07:59:07.91521	survey_67c505e3da62d8.95739531	\N
2101	415	3	2024-03-03 07:59:07.915586	survey_67c505e3da62d8.95739531	\N
2102	416	3	2024-03-03 07:59:07.915923	survey_67c505e3da62d8.95739531	\N
2103	414	3	2024-03-03 07:59:07.916281	survey_67c505e3da62d8.95739531	\N
2104	411	3	2024-03-03 07:59:07.916628	survey_67c505e3da62d8.95739531	\N
2105	413	3	2024-03-03 07:59:07.916978	survey_67c505e3da62d8.95739531	\N
2106	410	3	2024-03-03 07:59:07.91785	survey_67c505e3da62d8.95739531	\N
2107	412	3	2024-03-03 07:59:07.918213	survey_67c505e3da62d8.95739531	\N
2108	409	3	2024-03-03 07:59:07.918594	survey_67c505e3da62d8.95739531	\N
1499	63	3	2024-02-25 23:36:39.950338	survey_67bdf89fdb1d50.11994798	\N
1500	64	3	2024-02-25 23:36:39.950867	survey_67bdf89fdb1d50.11994798	\N
1501	65	3	2024-02-25 23:36:39.951376	survey_67bdf89fdb1d50.11994798	\N
1502	66	3	2024-02-25 23:36:39.951893	survey_67bdf89fdb1d50.11994798	\N
1503	67	3	2024-02-25 23:36:39.95236	survey_67bdf89fdb1d50.11994798	\N
1504	68	3	2024-02-25 23:36:39.952854	survey_67bdf89fdb1d50.11994798	\N
1505	69	3	2024-02-25 23:36:39.953318	survey_67bdf89fdb1d50.11994798	\N
1506	70	3	2024-02-25 23:36:39.954184	survey_67bdf89fdb1d50.11994798	\N
1507	71	3	2024-02-25 23:36:39.954663	survey_67bdf89fdb1d50.11994798	\N
1508	72	3	2024-02-25 23:36:39.955157	survey_67bdf89fdb1d50.11994798	\N
1509	73	4	2024-02-25 23:36:39.955693	survey_67bdf89fdb1d50.11994798	\N
1510	74	4	2024-02-25 23:36:39.956165	survey_67bdf89fdb1d50.11994798	\N
1511	75	4	2024-02-25 23:36:39.956683	survey_67bdf89fdb1d50.11994798	\N
1512	76	4	2024-02-25 23:36:39.957207	survey_67bdf89fdb1d50.11994798	\N
1513	77	4	2024-02-25 23:36:39.958392	survey_67bdf89fdb1d50.11994798	\N
1514	78	5	2024-02-25 23:36:39.958937	survey_67bdf89fdb1d50.11994798	\N
1515	79	4	2024-02-25 23:36:39.959483	survey_67bdf89fdb1d50.11994798	\N
1517	81	2	2024-02-25 23:36:39.960399	survey_67bdf89fdb1d50.11994798	\N
1518	82	3	2024-02-25 23:36:39.960838	survey_67bdf89fdb1d50.11994798	\N
1519	83	2	2024-02-25 23:36:39.961268	survey_67bdf89fdb1d50.11994798	\N
1520	84	4	2024-02-25 23:36:39.961688	survey_67bdf89fdb1d50.11994798	\N
1521	85	3	2024-02-25 23:36:39.962099	survey_67bdf89fdb1d50.11994798	\N
1522	86	3	2024-02-25 23:36:39.962607	survey_67bdf89fdb1d50.11994798	\N
1523	87	3	2024-02-25 23:36:39.963	survey_67bdf89fdb1d50.11994798	\N
1524	88	3	2024-02-25 23:36:39.963409	survey_67bdf89fdb1d50.11994798	\N
1525	89	5	2024-02-25 23:36:39.963751	survey_67bdf89fdb1d50.11994798	\N
1526	90	5	2024-02-25 23:36:39.964073	survey_67bdf89fdb1d50.11994798	\N
1527	91	5	2024-02-25 23:36:39.964392	survey_67bdf89fdb1d50.11994798	\N
1528	92	5	2024-02-25 23:36:39.964736	survey_67bdf89fdb1d50.11994798	\N
1529	93	5	2024-02-25 23:36:39.965076	survey_67bdf89fdb1d50.11994798	\N
1866	382	2018-2023	2024-03-02 23:02:09.756505	survey_67c48809b59652.83429645	\N
1867	383	1	2024-03-02 23:02:09.768947	survey_67c48809b59652.83429645	\N
1868	384	4	2024-03-02 23:02:09.769967	survey_67c48809b59652.83429645	\N
1869	385	4	2024-03-02 23:02:09.77049	survey_67c48809b59652.83429645	\N
1870	386	4	2024-03-02 23:02:09.770901	survey_67c48809b59652.83429645	\N
1871	387	4	2024-03-02 23:02:09.771316	survey_67c48809b59652.83429645	\N
1872	388	4	2024-03-02 23:02:09.771686	survey_67c48809b59652.83429645	\N
1873	389	2	2024-03-02 23:02:09.772035	survey_67c48809b59652.83429645	\N
1874	390	4	2024-03-02 23:02:09.772397	survey_67c48809b59652.83429645	\N
1875	391	2	2024-03-02 23:02:09.772705	survey_67c48809b59652.83429645	\N
1876	392	4	2024-03-02 23:02:09.773096	survey_67c48809b59652.83429645	\N
1877	393	1	2024-03-02 23:02:09.77416	survey_67c48809b59652.83429645	\N
1878	394	5	2024-03-02 23:02:09.774543	survey_67c48809b59652.83429645	\N
1879	395	1	2024-03-02 23:02:09.774947	survey_67c48809b59652.83429645	\N
1880	396	5	2024-03-02 23:02:09.775669	survey_67c48809b59652.83429645	\N
1881	397	1	2024-03-02 23:02:09.776194	survey_67c48809b59652.83429645	\N
1882	398	5	2024-03-02 23:02:09.776651	survey_67c48809b59652.83429645	\N
1883	399	1	2024-03-02 23:02:09.777076	survey_67c48809b59652.83429645	\N
1884	400	5	2024-03-02 23:02:09.777429	survey_67c48809b59652.83429645	\N
1885	401	1	2024-03-02 23:02:09.777832	survey_67c48809b59652.83429645	\N
1886	402	5	2024-03-02 23:02:09.778731	survey_67c48809b59652.83429645	\N
1887	403	1	2024-03-02 23:02:09.779051	survey_67c48809b59652.83429645	\N
1888	404	4	2024-03-02 23:02:09.779382	survey_67c48809b59652.83429645	\N
1889	405	2	2024-03-02 23:02:09.780161	survey_67c48809b59652.83429645	\N
1890	406	3	2024-03-02 23:02:09.780524	survey_67c48809b59652.83429645	\N
1891	415	4	2024-03-02 23:02:09.780899	survey_67c48809b59652.83429645	\N
1892	416	5	2024-03-02 23:02:09.781476	survey_67c48809b59652.83429645	\N
1893	414	4	2024-03-02 23:02:09.786232	survey_67c48809b59652.83429645	\N
1894	411	3	2024-03-02 23:02:09.787008	survey_67c48809b59652.83429645	\N
1895	413	2	2024-03-02 23:02:09.787622	survey_67c48809b59652.83429645	\N
1896	410	1	2024-03-02 23:02:09.788145	survey_67c48809b59652.83429645	\N
1897	412	2	2024-03-02 23:02:09.788623	survey_67c48809b59652.83429645	\N
1898	409	3	2024-03-02 23:02:09.78912	survey_67c48809b59652.83429645	\N
2170	406	5	2024-03-03 08:02:44.653306	survey_67c506bc975f13.58340132	\N
2171	415	5	2024-03-03 08:02:44.65362	survey_67c506bc975f13.58340132	\N
2172	416	5	2024-03-03 08:02:44.653975	survey_67c506bc975f13.58340132	\N
2173	414	5	2024-03-03 08:02:44.654308	survey_67c506bc975f13.58340132	\N
2174	411	5	2024-03-03 08:02:44.654646	survey_67c506bc975f13.58340132	\N
2175	413	5	2024-03-03 08:02:44.654965	survey_67c506bc975f13.58340132	\N
2176	410	5	2024-03-03 08:02:44.655424	survey_67c506bc975f13.58340132	\N
2177	412	5	2024-03-03 08:02:44.655909	survey_67c506bc975f13.58340132	\N
2178	409	5	2024-03-03 08:02:44.656373	survey_67c506bc975f13.58340132	\N
1313	55	Stepparent	2024-02-17 08:20:58.862144	survey_67b29602d0dd73.81246140	\N
1314	56	Male	2024-02-17 08:20:58.875407	survey_67b29602d0dd73.81246140	\N
1315	58	["Prefer not to answer. Other"]	2024-02-17 08:20:58.876665	survey_67b29602d0dd73.81246140	\N
1316	115	2024-2025	2024-02-17 08:20:58.878309	survey_67b29602d0dd73.81246140	\N
1317	59	5	2024-02-17 08:20:58.879225	survey_67b29602d0dd73.81246140	\N
1318	60	5	2024-02-17 08:20:58.87977	survey_67b29602d0dd73.81246140	\N
1319	61	5	2024-02-17 08:20:58.880192	survey_67b29602d0dd73.81246140	\N
1320	62	5	2024-02-17 08:20:58.880854	survey_67b29602d0dd73.81246140	\N
1321	63	5	2024-02-17 08:20:58.881311	survey_67b29602d0dd73.81246140	\N
1322	64	5	2024-02-17 08:20:58.881734	survey_67b29602d0dd73.81246140	\N
1323	65	5	2024-02-17 08:20:58.882162	survey_67b29602d0dd73.81246140	\N
1324	66	5	2024-02-17 08:20:58.882598	survey_67b29602d0dd73.81246140	\N
1325	67	5	2024-02-17 08:20:58.883007	survey_67b29602d0dd73.81246140	\N
1326	68	5	2024-02-17 08:20:58.883783	survey_67b29602d0dd73.81246140	\N
1327	69	5	2024-02-17 08:20:58.88454	survey_67b29602d0dd73.81246140	\N
1328	70	5	2024-02-17 08:20:58.885023	survey_67b29602d0dd73.81246140	\N
1329	71	5	2024-02-17 08:20:58.885446	survey_67b29602d0dd73.81246140	\N
1330	72	5	2024-02-17 08:20:58.885828	survey_67b29602d0dd73.81246140	\N
1331	73	5	2024-02-17 08:20:58.886263	survey_67b29602d0dd73.81246140	\N
1332	74	5	2024-02-17 08:20:58.886683	survey_67b29602d0dd73.81246140	\N
1333	75	5	2024-02-17 08:20:58.887282	survey_67b29602d0dd73.81246140	\N
1334	76	5	2024-02-17 08:20:58.887728	survey_67b29602d0dd73.81246140	\N
1335	77	5	2024-02-17 08:20:58.888183	survey_67b29602d0dd73.81246140	\N
1336	78	5	2024-02-17 08:20:58.888606	survey_67b29602d0dd73.81246140	\N
1337	79	5	2024-02-17 08:20:58.889058	survey_67b29602d0dd73.81246140	\N
1339	81	5	2024-02-17 08:20:58.890602	survey_67b29602d0dd73.81246140	\N
1340	82	5	2024-02-17 08:20:58.891063	survey_67b29602d0dd73.81246140	\N
1341	83	5	2024-02-17 08:20:58.893983	survey_67b29602d0dd73.81246140	\N
1342	84	5	2024-02-17 08:20:58.894411	survey_67b29602d0dd73.81246140	\N
1343	85	5	2024-02-17 08:20:58.894877	survey_67b29602d0dd73.81246140	\N
1344	86	5	2024-02-17 08:20:58.895379	survey_67b29602d0dd73.81246140	\N
1345	87	5	2024-02-17 08:20:58.895856	survey_67b29602d0dd73.81246140	\N
1346	88	5	2024-02-17 08:20:58.89627	survey_67b29602d0dd73.81246140	\N
1347	89	5	2024-02-17 08:20:58.896786	survey_67b29602d0dd73.81246140	\N
1348	90	5	2024-02-17 08:20:58.897872	survey_67b29602d0dd73.81246140	\N
1349	91	5	2024-02-17 08:20:58.898385	survey_67b29602d0dd73.81246140	\N
1350	92	5	2024-02-17 08:20:58.898885	survey_67b29602d0dd73.81246140	\N
1351	93	5	2024-02-17 08:20:58.899398	survey_67b29602d0dd73.81246140	\N
1907	388	2	2024-03-02 23:03:56.679715	survey_67c48874a1b3a4.20413445	\N
1908	389	1	2024-03-02 23:03:56.680011	survey_67c48874a1b3a4.20413445	\N
1909	390	1	2024-03-02 23:03:56.680314	survey_67c48874a1b3a4.20413445	\N
1910	391	2	2024-03-02 23:03:56.680601	survey_67c48874a1b3a4.20413445	\N
1911	392	2	2024-03-02 23:03:56.680883	survey_67c48874a1b3a4.20413445	\N
1912	393	1	2024-03-02 23:03:56.681148	survey_67c48874a1b3a4.20413445	\N
1913	394	2	2024-03-02 23:03:56.681412	survey_67c48874a1b3a4.20413445	\N
1914	395	3	2024-03-02 23:03:56.681684	survey_67c48874a1b3a4.20413445	\N
1915	396	4	2024-03-02 23:03:56.681922	survey_67c48874a1b3a4.20413445	\N
1916	397	3	2024-03-02 23:03:56.682153	survey_67c48874a1b3a4.20413445	\N
1917	398	2	2024-03-02 23:03:56.682371	survey_67c48874a1b3a4.20413445	\N
1918	399	1	2024-03-02 23:03:56.682591	survey_67c48874a1b3a4.20413445	\N
1919	400	2	2024-03-02 23:03:56.682813	survey_67c48874a1b3a4.20413445	\N
1920	401	3	2024-03-02 23:03:56.683026	survey_67c48874a1b3a4.20413445	\N
1921	402	2	2024-03-02 23:03:56.683248	survey_67c48874a1b3a4.20413445	\N
1922	403	3	2024-03-02 23:03:56.68347	survey_67c48874a1b3a4.20413445	\N
1923	404	3	2024-03-02 23:03:56.683721	survey_67c48874a1b3a4.20413445	\N
1924	405	3	2024-03-02 23:03:56.683952	survey_67c48874a1b3a4.20413445	\N
1925	406	3	2024-03-02 23:03:56.684166	survey_67c48874a1b3a4.20413445	\N
1926	415	4	2024-03-02 23:03:56.684385	survey_67c48874a1b3a4.20413445	\N
1927	416	2	2024-03-02 23:03:56.684604	survey_67c48874a1b3a4.20413445	\N
1928	414	1	2024-03-02 23:03:56.68484	survey_67c48874a1b3a4.20413445	\N
2206	415	3	2024-03-03 08:05:54.999172	survey_67c5077aeb8e88.18366239	\N
2207	416	2	2024-03-03 08:05:55.000606	survey_67c5077aeb8e88.18366239	\N
2208	414	3	2024-03-03 08:05:55.001018	survey_67c5077aeb8e88.18366239	\N
2209	411	2	2024-03-03 08:05:55.001356	survey_67c5077aeb8e88.18366239	\N
2210	413	3	2024-03-03 08:05:55.001709	survey_67c5077aeb8e88.18366239	\N
2211	410	2	2024-03-03 08:05:55.002052	survey_67c5077aeb8e88.18366239	\N
2212	412	3	2024-03-03 08:05:55.002387	survey_67c5077aeb8e88.18366239	\N
2213	409	2	2024-03-03 08:05:55.002728	survey_67c5077aeb8e88.18366239	\N
2557	605	test	2026-01-14 15:29:47.507704	survey_69675b037a0cd3.34841257	\N
2558	609	["2"]	2026-01-14 15:29:47.516854	survey_69675b037a0cd3.34841257	\N
2559	610	tets	2026-01-14 15:29:47.519428	survey_69675b037a0cd3.34841257	\N
2560	615	3	2026-01-14 15:29:47.520281	survey_69675b037a0cd3.34841257	\N
2561	616	3	2026-01-14 15:29:47.521429	survey_69675b037a0cd3.34841257	\N
\.


--
-- Data for Name: survey_codes; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.survey_codes (id, type, code, created_at, expires_at, active) FROM stdin;
2	guardian	G_SCS_2025	2025-02-17 22:15:25.871843	2025-02-18 09:00:00	f
3	guardian	G_SCS_2025	2025-02-17 22:27:10.346049	2025-02-18 09:00:00	f
4	guardian	G_SCS_2025	2025-02-17 22:27:26.809728	2025-02-18 09:00:00	f
5	guardian	G_SCS_2025	2025-02-17 22:27:55.517493	2025-02-18 09:00:00	f
8	alumni	ALUMNI_2026	2025-02-18 13:40:48.491455	2025-02-20 10:00:00	f
9	alumni	ABC	2025-02-18 14:03:18.144979	2025-02-18 14:06:00	f
7	staff	S_2025	2025-02-18 09:29:40.768609	2025-02-19 16:00:00	f
10	student	123456	2025-02-25 08:06:04.928108	2025-02-26 08:05:00	f
1	student	isy-2025	2025-02-17 21:58:56.880309	2025-02-25 18:00:00	f
11	student	STU-2025	2025-02-25 10:37:36.459628	2025-02-28 12:59:00	f
13	alumni	A-2025	2025-02-25 22:24:20.746845	\N	f
15	alumni	A-2026	2025-02-28 12:48:26.384778	\N	f
6	guardian	G-2025	2025-02-18 09:24:15.398414	2025-02-28 19:24:00	f
12	staff	STF-2025	2025-02-25 22:21:31.062506	\N	f
14	student	student-2025	2025-02-28 09:53:30.548809	\N	f
16	alumni	A-2025	2025-02-28 12:49:20.65202	\N	t
17	staff	B-2025	2025-03-20 11:27:51.805765	\N	t
18	student	S-2025	2025-04-08 13:38:36.325822	\N	t
\.


--
-- Data for Name: survey_settings; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.survey_settings (id, survey_type, is_active, display_name, display_order, icon_class, description, created_at, updated_at) FROM stdin;
1	student	t	Student Survey	1	fa-child	Share your valuable insights about your school experience. Your feedback helps us create a better learning environment for everyone.	2026-01-14 08:25:24.341263	2026-01-14 20:57:09.996057
2	alumni	f	Alumni Survey	2	fa-graduation-cap	Your post-graduation experience matters. Help us understand how we can better prepare current students for their future.	2026-01-14 08:25:24.341263	2026-01-14 20:57:09.997079
5	guardian	f	Guardian Survey	5	fa-users	Parents and guardians, your perspective is crucial. Share your thoughts on your child's educational experience.	2026-01-14 08:25:24.341263	2026-01-14 20:57:09.989003
4	staff	f	Staff Survey	4	fa-briefcase	School staff insights are essential for improvement. Share your professional perspective on our educational environment.	2026-01-14 08:25:24.341263	2026-01-14 20:57:09.994832
3	board	f	Board Survey	3	fa-industry	Board members play a crucial role in shaping the institution's future. Share your insights to help guide strategic decisions and ensure continuous improvement.	2026-01-14 08:25:24.341263	2026-01-14 20:57:09.995286
\.


--
-- Name: admin_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.admin_users_id_seq', 12, true);


--
-- Name: categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.categories_id_seq', 581, true);


--
-- Name: questions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.questions_id_seq', 617, true);


--
-- Name: responses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.responses_id_seq', 2595, true);


--
-- Name: survey_codes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.survey_codes_id_seq', 18, true);


--
-- Name: survey_settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.survey_settings_id_seq', 5, true);


--
-- Name: admin_users admin_users_email_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_email_key UNIQUE (email);


--
-- Name: admin_users admin_users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_pkey PRIMARY KEY (id);


--
-- Name: admin_users admin_users_username_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_username_key UNIQUE (username);


--
-- Name: categories categories_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_pkey PRIMARY KEY (id);


--
-- Name: questions questions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT questions_pkey PRIMARY KEY (id);


--
-- Name: responses responses_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.responses
    ADD CONSTRAINT responses_pkey PRIMARY KEY (id);


--
-- Name: survey_codes survey_codes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.survey_codes
    ADD CONSTRAINT survey_codes_pkey PRIMARY KEY (id);


--
-- Name: survey_settings survey_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.survey_settings
    ADD CONSTRAINT survey_settings_pkey PRIMARY KEY (id);


--
-- Name: survey_settings survey_settings_survey_type_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.survey_settings
    ADD CONSTRAINT survey_settings_survey_type_key UNIQUE (survey_type);


--
-- Name: questions questions_category_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT questions_category_id_fkey FOREIGN KEY (category_id) REFERENCES public.categories(id);


--
-- Name: responses responses_question_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.responses
    ADD CONSTRAINT responses_question_id_fkey FOREIGN KEY (question_id) REFERENCES public.questions(id);


--
-- PostgreSQL database dump complete
--

\unrestrict UfjkOvVQGnHQoLsUj6hjhsNmJm09y6IO9ysbG8c9pI0KM9IRw68ZPVnzPuzcakY

