--
-- PostgreSQL database dump
--

-- Started on 2009-04-30 21:36:53

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 1606 (class 1259 OID 22281)
-- Dependencies: 2001 3
-- Name: assignment_status; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE assignment_status (
    id bigint NOT NULL,
    description character varying(100) DEFAULT 'unknown'::character varying NOT NULL
);


ALTER TABLE public.assignment_status OWNER TO postgres;

--
-- TOC entry 1605 (class 1259 OID 22279)
-- Dependencies: 1606 3
-- Name: assignment_status_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE assignment_status_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.assignment_status_id_seq OWNER TO postgres;

--
-- TOC entry 2292 (class 0 OID 0)
-- Dependencies: 1605
-- Name: assignment_status_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE assignment_status_id_seq OWNED BY assignment_status.id;


--
-- TOC entry 2293 (class 0 OID 0)
-- Dependencies: 1605
-- Name: assignment_status_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('assignment_status_id_seq', 1, false);


--
-- TOC entry 1608 (class 1259 OID 22290)
-- Dependencies: 2003 2004 3
-- Name: assignment_types; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE assignment_types (
    id bigint NOT NULL,
    fk_table character varying(30) DEFAULT ''::character varying,
    description character varying(100) DEFAULT 'unknown'::character varying NOT NULL
);


ALTER TABLE public.assignment_types OWNER TO postgres;

--
-- TOC entry 1607 (class 1259 OID 22288)
-- Dependencies: 3 1608
-- Name: assignment_types_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE assignment_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.assignment_types_id_seq OWNER TO postgres;

--
-- TOC entry 2294 (class 0 OID 0)
-- Dependencies: 1607
-- Name: assignment_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE assignment_types_id_seq OWNED BY assignment_types.id;


--
-- TOC entry 2295 (class 0 OID 0)
-- Dependencies: 1607
-- Name: assignment_types_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('assignment_types_id_seq', 1, false);


--
-- TOC entry 1610 (class 1259 OID 22300)
-- Dependencies: 2006 2007 2008 2009 2010 2011 2012 2013 2014 2015 3
-- Name: attachments; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE attachments (
    id bigint NOT NULL,
    fk_id bigint DEFAULT 0::bigint NOT NULL,
    fk_table character varying(250) DEFAULT ''::character varying,
    title character varying(250) DEFAULT ''::character varying,
    description character varying(250) DEFAULT ''::character varying,
    file_name character varying(250) DEFAULT ''::character varying NOT NULL,
    file_path character varying(250) DEFAULT ''::character varying,
    file_size integer DEFAULT 0 NOT NULL,
    file_type character varying(250) DEFAULT ''::character varying NOT NULL,
    date_added timestamp without time zone DEFAULT now() NOT NULL,
    content bytea,
    compression_type integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.attachments OWNER TO postgres;

--
-- TOC entry 1609 (class 1259 OID 22298)
-- Dependencies: 3 1610
-- Name: attachments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE attachments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.attachments_id_seq OWNER TO postgres;

--
-- TOC entry 2296 (class 0 OID 0)
-- Dependencies: 1609
-- Name: attachments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE attachments_id_seq OWNED BY attachments.id;


--
-- TOC entry 2297 (class 0 OID 0)
-- Dependencies: 1609
-- Name: attachments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('attachments_id_seq', 1, false);


--
-- TOC entry 1592 (class 1259 OID 22041)
-- Dependencies: 1942 1943 1944 1945 3
-- Name: builds; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE builds (
    id bigint NOT NULL,
    testplan_id bigint DEFAULT 0::bigint NOT NULL,
    name character varying(100) DEFAULT 'undefined'::character varying NOT NULL,
    notes text,
    active smallint DEFAULT 1::smallint NOT NULL,
    is_open smallint DEFAULT 1::smallint NOT NULL
);


ALTER TABLE public.builds OWNER TO postgres;

--
-- TOC entry 1591 (class 1259 OID 22039)
-- Dependencies: 3 1592
-- Name: builds_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE builds_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.builds_id_seq OWNER TO postgres;

--
-- TOC entry 2298 (class 0 OID 0)
-- Dependencies: 1591
-- Name: builds_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE builds_id_seq OWNED BY builds.id;


--
-- TOC entry 2299 (class 0 OID 0)
-- Dependencies: 1591
-- Name: builds_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('builds_id_seq', 8, true);


--
-- TOC entry 1601 (class 1259 OID 22193)
-- Dependencies: 1987 1988 1989 3
-- Name: cfield_design_values; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cfield_design_values (
    field_id integer DEFAULT 0 NOT NULL,
    node_id integer DEFAULT 0 NOT NULL,
    value character varying(255) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE public.cfield_design_values OWNER TO postgres;

--
-- TOC entry 1602 (class 1259 OID 22212)
-- Dependencies: 1990 1991 1992 1993 1994 3
-- Name: cfield_execution_values; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cfield_execution_values (
    field_id integer DEFAULT 0 NOT NULL,
    execution_id integer DEFAULT 0 NOT NULL,
    testplan_id integer DEFAULT 0 NOT NULL,
    tcversion_id integer DEFAULT 0 NOT NULL,
    value character varying(255) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE public.cfield_execution_values OWNER TO postgres;

--
-- TOC entry 1604 (class 1259 OID 22261)
-- Dependencies: 1998 1999 3
-- Name: cfield_node_types; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cfield_node_types (
    field_id integer DEFAULT 0 NOT NULL,
    node_type_id integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.cfield_node_types OWNER TO postgres;

--
-- TOC entry 1603 (class 1259 OID 22242)
-- Dependencies: 1995 1996 1997 3
-- Name: cfield_testplan_design_values; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cfield_testplan_design_values (
    field_id integer DEFAULT 0 NOT NULL,
    link_id integer DEFAULT 0 NOT NULL,
    value character varying(255) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE public.cfield_testplan_design_values OWNER TO postgres;

--
-- TOC entry 1600 (class 1259 OID 22172)
-- Dependencies: 1981 1982 1983 1984 1985 1986 3
-- Name: cfield_testprojects; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE cfield_testprojects (
    field_id bigint DEFAULT 0::bigint NOT NULL,
    testproject_id bigint DEFAULT 0::bigint NOT NULL,
    display_order integer DEFAULT 1 NOT NULL,
    active smallint DEFAULT 1::smallint NOT NULL,
    required_on_design smallint DEFAULT 0::smallint NOT NULL,
    required_on_execution smallint DEFAULT 0::smallint NOT NULL
);


ALTER TABLE public.cfield_testprojects OWNER TO postgres;

--
-- TOC entry 1598 (class 1259 OID 22123)
-- Dependencies: 1960 1961 1962 1963 1964 1965 1966 1967 1968 1969 1970 1971 1972 1973 3
-- Name: custom_fields; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE custom_fields (
    id integer NOT NULL,
    name character varying(64) DEFAULT ''::character varying NOT NULL,
    label character varying(64) DEFAULT ''::character varying NOT NULL,
    type smallint DEFAULT 0::smallint NOT NULL,
    possible_values character varying(255) DEFAULT ''::character varying NOT NULL,
    default_value character varying(255) DEFAULT ''::character varying NOT NULL,
    valid_regexp character varying(255) DEFAULT ''::character varying NOT NULL,
    length_min integer DEFAULT 0 NOT NULL,
    length_max integer DEFAULT 0 NOT NULL,
    show_on_design smallint DEFAULT 1::smallint NOT NULL,
    enable_on_design smallint DEFAULT 1::smallint NOT NULL,
    show_on_execution smallint DEFAULT 0::smallint NOT NULL,
    enable_on_execution smallint DEFAULT 0::smallint NOT NULL,
    show_on_testplan_design smallint DEFAULT 0::smallint NOT NULL,
    enable_on_testplan_design smallint DEFAULT 0::smallint NOT NULL
);


ALTER TABLE public.custom_fields OWNER TO postgres;

--
-- TOC entry 1597 (class 1259 OID 22121)
-- Dependencies: 3 1598
-- Name: custom_fields_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE custom_fields_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.custom_fields_id_seq OWNER TO postgres;

--
-- TOC entry 2300 (class 0 OID 0)
-- Dependencies: 1597
-- Name: custom_fields_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE custom_fields_id_seq OWNED BY custom_fields.id;


--
-- TOC entry 2301 (class 0 OID 0)
-- Dependencies: 1597
-- Name: custom_fields_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('custom_fields_id_seq', 1, false);


--
-- TOC entry 1611 (class 1259 OID 22319)
-- Dependencies: 2016 2017 3
-- Name: db_version; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE db_version (
    version character varying(50) DEFAULT 'unknown'::character varying NOT NULL,
    upgrade_ts timestamp without time zone DEFAULT now() NOT NULL,
    notes text
);


ALTER TABLE public.db_version OWNER TO postgres;

--
-- TOC entry 1584 (class 1259 OID 21940)
-- Dependencies: 1916 1917 1918 3
-- Name: events; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE events (
    id bigint NOT NULL,
    transaction_id bigint DEFAULT 0::bigint NOT NULL,
    log_level smallint DEFAULT 0::smallint NOT NULL,
    source character varying(45),
    description text NOT NULL,
    fired_at integer DEFAULT 0 NOT NULL,
    activity character varying(45),
    object_id bigint,
    object_type character varying(45)
);


ALTER TABLE public.events OWNER TO postgres;

--
-- TOC entry 1583 (class 1259 OID 21938)
-- Dependencies: 1584 3
-- Name: events_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE events_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.events_id_seq OWNER TO postgres;

--
-- TOC entry 2302 (class 0 OID 0)
-- Dependencies: 1583
-- Name: events_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE events_id_seq OWNED BY events.id;


--
-- TOC entry 2303 (class 0 OID 0)
-- Dependencies: 1583
-- Name: events_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('events_id_seq', 18, true);


--
-- TOC entry 1612 (class 1259 OID 22327)
-- Dependencies: 2018 2019 3
-- Name: execution_bugs; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE execution_bugs (
    execution_id bigint DEFAULT 0::bigint NOT NULL,
    bug_id character varying(16) DEFAULT '0'::character varying NOT NULL
);


ALTER TABLE public.execution_bugs OWNER TO postgres;

--
-- TOC entry 1594 (class 1259 OID 22064)
-- Dependencies: 1947 1948 1949 1950 1951 1952 3
-- Name: executions; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE executions (
    id bigint NOT NULL,
    build_id integer DEFAULT 0 NOT NULL,
    tester_id bigint,
    execution_ts timestamp without time zone,
    status character(1) DEFAULT NULL::bpchar,
    testplan_id bigint DEFAULT 0::bigint NOT NULL,
    tcversion_id bigint DEFAULT 0::bigint NOT NULL,
    tcversion_number integer DEFAULT 1 NOT NULL,
    execution_type smallint DEFAULT 1::smallint NOT NULL,
    notes text
);


ALTER TABLE public.executions OWNER TO postgres;

--
-- TOC entry 1593 (class 1259 OID 22062)
-- Dependencies: 1594 3
-- Name: executions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE executions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.executions_id_seq OWNER TO postgres;

--
-- TOC entry 2304 (class 0 OID 0)
-- Dependencies: 1593
-- Name: executions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE executions_id_seq OWNED BY executions.id;


--
-- TOC entry 2305 (class 0 OID 0)
-- Dependencies: 1593
-- Name: executions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('executions_id_seq', 1, false);


--
-- TOC entry 1614 (class 1259 OID 22341)
-- Dependencies: 2021 2022 3
-- Name: keywords; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE keywords (
    id bigint NOT NULL,
    keyword character varying(100) DEFAULT ''::character varying NOT NULL,
    testproject_id bigint DEFAULT 0::bigint NOT NULL,
    notes text
);


ALTER TABLE public.keywords OWNER TO postgres;

--
-- TOC entry 1613 (class 1259 OID 22339)
-- Dependencies: 1614 3
-- Name: keywords_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE keywords_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.keywords_id_seq OWNER TO postgres;

--
-- TOC entry 2306 (class 0 OID 0)
-- Dependencies: 1613
-- Name: keywords_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE keywords_id_seq OWNED BY keywords.id;


--
-- TOC entry 2307 (class 0 OID 0)
-- Dependencies: 1613
-- Name: keywords_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('keywords_id_seq', 1, false);


--
-- TOC entry 1616 (class 1259 OID 22361)
-- Dependencies: 2024 2025 2026 2027 2028 3
-- Name: milestones; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE milestones (
    id bigint NOT NULL,
    testplan_id bigint DEFAULT 0::bigint NOT NULL,
    target_date date NOT NULL,
    a smallint DEFAULT 0::smallint NOT NULL,
    b smallint DEFAULT 0::smallint NOT NULL,
    c smallint DEFAULT 0::smallint NOT NULL,
    name character varying(100) DEFAULT 'undefined'::character varying NOT NULL
);


ALTER TABLE public.milestones OWNER TO postgres;

--
-- TOC entry 1615 (class 1259 OID 22359)
-- Dependencies: 3 1616
-- Name: milestones_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE milestones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.milestones_id_seq OWNER TO postgres;

--
-- TOC entry 2308 (class 0 OID 0)
-- Dependencies: 1615
-- Name: milestones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE milestones_id_seq OWNED BY milestones.id;


--
-- TOC entry 2309 (class 0 OID 0)
-- Dependencies: 1615
-- Name: milestones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('milestones_id_seq', 1, false);


--
-- TOC entry 1578 (class 1259 OID 21902)
-- Dependencies: 1905 3
-- Name: node_types; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE node_types (
    id bigint NOT NULL,
    description character varying(100) DEFAULT 'testproject'::character varying NOT NULL
);


ALTER TABLE public.node_types OWNER TO postgres;

--
-- TOC entry 1577 (class 1259 OID 21900)
-- Dependencies: 3 1578
-- Name: node_types_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE node_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.node_types_id_seq OWNER TO postgres;

--
-- TOC entry 2310 (class 0 OID 0)
-- Dependencies: 1577
-- Name: node_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE node_types_id_seq OWNED BY node_types.id;


--
-- TOC entry 2311 (class 0 OID 0)
-- Dependencies: 1577
-- Name: node_types_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('node_types_id_seq', 1, false);


--
-- TOC entry 1580 (class 1259 OID 21911)
-- Dependencies: 1907 1908 3
-- Name: nodes_hierarchy; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE nodes_hierarchy (
    id bigint NOT NULL,
    name character varying(100) DEFAULT NULL::character varying,
    parent_id bigint,
    node_type_id bigint DEFAULT 1::bigint NOT NULL,
    node_order bigint
);


ALTER TABLE public.nodes_hierarchy OWNER TO postgres;

--
-- TOC entry 1579 (class 1259 OID 21909)
-- Dependencies: 3 1580
-- Name: nodes_hierarchy_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE nodes_hierarchy_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.nodes_hierarchy_id_seq OWNER TO postgres;

--
-- TOC entry 2312 (class 0 OID 0)
-- Dependencies: 1579
-- Name: nodes_hierarchy_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE nodes_hierarchy_id_seq OWNED BY nodes_hierarchy.id;


--
-- TOC entry 2313 (class 0 OID 0)
-- Dependencies: 1579
-- Name: nodes_hierarchy_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('nodes_hierarchy_id_seq', 211, true);


--
-- TOC entry 1618 (class 1259 OID 22382)
-- Dependencies: 2030 2031 2032 3
-- Name: object_keywords; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE object_keywords (
    id bigint NOT NULL,
    fk_id bigint DEFAULT 0::bigint NOT NULL,
    fk_table character varying(30) DEFAULT ''::character varying,
    keyword_id bigint DEFAULT 0::bigint NOT NULL
);


ALTER TABLE public.object_keywords OWNER TO postgres;

--
-- TOC entry 1617 (class 1259 OID 22380)
-- Dependencies: 1618 3
-- Name: object_keywords_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE object_keywords_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.object_keywords_id_seq OWNER TO postgres;

--
-- TOC entry 2314 (class 0 OID 0)
-- Dependencies: 1617
-- Name: object_keywords_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE object_keywords_id_seq OWNED BY object_keywords.id;


--
-- TOC entry 2315 (class 0 OID 0)
-- Dependencies: 1617
-- Name: object_keywords_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('object_keywords_id_seq', 1, false);


--
-- TOC entry 1621 (class 1259 OID 22449)
-- Dependencies: 2047 2048 3
-- Name: req_coverage; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE req_coverage (
    req_id integer DEFAULT 0 NOT NULL,
    testcase_id integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.req_coverage OWNER TO postgres;

--
-- TOC entry 1619 (class 1259 OID 22396)
-- Dependencies: 2033 2034 2035 2036 2037 2038 3
-- Name: req_specs; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE req_specs (
    id bigint DEFAULT 0::bigint NOT NULL,
    testproject_id bigint DEFAULT 0::bigint NOT NULL,
    title character varying(100) DEFAULT ''::character varying NOT NULL,
    scope text,
    total_req integer DEFAULT 0 NOT NULL,
    type character(1) DEFAULT 'N'::bpchar,
    author_id bigint,
    creation_ts timestamp without time zone DEFAULT now() NOT NULL,
    modifier_id bigint,
    modification_ts timestamp without time zone
);


ALTER TABLE public.req_specs OWNER TO postgres;

--
-- TOC entry 1620 (class 1259 OID 22421)
-- Dependencies: 2039 2040 2041 2042 2043 2044 2045 2046 3
-- Name: requirements; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE requirements (
    id bigint DEFAULT 0::bigint NOT NULL,
    srs_id bigint DEFAULT 0::bigint NOT NULL,
    req_doc_id character varying(32) DEFAULT NULL::character varying,
    title character varying(100) DEFAULT ''::character varying NOT NULL,
    scope text,
    status character(1) DEFAULT 'V'::bpchar NOT NULL,
    type character(1) DEFAULT NULL::bpchar,
    node_order bigint DEFAULT 0 NOT NULL,
    author_id bigint,
    creation_ts timestamp without time zone DEFAULT now() NOT NULL,
    modifier_id bigint,
    modification_ts timestamp without time zone
);


ALTER TABLE public.requirements OWNER TO postgres;

--
-- TOC entry 1623 (class 1259 OID 22462)
-- Dependencies: 2050 3
-- Name: rights; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE rights (
    id bigint NOT NULL,
    description character varying(100) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE public.rights OWNER TO postgres;

--
-- TOC entry 1622 (class 1259 OID 22460)
-- Dependencies: 3 1623
-- Name: rights_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE rights_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.rights_id_seq OWNER TO postgres;

--
-- TOC entry 2316 (class 0 OID 0)
-- Dependencies: 1622
-- Name: rights_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE rights_id_seq OWNED BY rights.id;


--
-- TOC entry 2317 (class 0 OID 0)
-- Dependencies: 1622
-- Name: rights_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('rights_id_seq', 1, false);


--
-- TOC entry 1625 (class 1259 OID 22473)
-- Dependencies: 2052 2053 2054 2055 3
-- Name: risk_assignments; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE risk_assignments (
    id bigint NOT NULL,
    testplan_id bigint DEFAULT 0::bigint NOT NULL,
    node_id bigint DEFAULT 0::bigint NOT NULL,
    risk character(1) DEFAULT '2'::bpchar NOT NULL,
    importance character(1) DEFAULT 'M'::bpchar NOT NULL
);


ALTER TABLE public.risk_assignments OWNER TO postgres;

--
-- TOC entry 1624 (class 1259 OID 22471)
-- Dependencies: 1625 3
-- Name: risk_assignments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE risk_assignments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.risk_assignments_id_seq OWNER TO postgres;

--
-- TOC entry 2318 (class 0 OID 0)
-- Dependencies: 1624
-- Name: risk_assignments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE risk_assignments_id_seq OWNED BY risk_assignments.id;


--
-- TOC entry 2319 (class 0 OID 0)
-- Dependencies: 1624
-- Name: risk_assignments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('risk_assignments_id_seq', 1, false);


--
-- TOC entry 1626 (class 1259 OID 22495)
-- Dependencies: 2056 2057 3
-- Name: role_rights; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE role_rights (
    role_id integer DEFAULT 0 NOT NULL,
    right_id integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.role_rights OWNER TO postgres;

--
-- TOC entry 1586 (class 1259 OID 21956)
-- Dependencies: 1920 3
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE roles (
    id bigint NOT NULL,
    description character varying(100) DEFAULT ''::character varying NOT NULL,
    notes text
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- TOC entry 1585 (class 1259 OID 21954)
-- Dependencies: 3 1586
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE roles_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.roles_id_seq OWNER TO postgres;

--
-- TOC entry 2320 (class 0 OID 0)
-- Dependencies: 1585
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE roles_id_seq OWNED BY roles.id;


--
-- TOC entry 2321 (class 0 OID 0)
-- Dependencies: 1585
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('roles_id_seq', 9, true);


--
-- TOC entry 1589 (class 1259 OID 21991)
-- Dependencies: 1930 1931 1932 1933 1934 1935 1936 3
-- Name: tcversions; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE tcversions (
    id bigint DEFAULT 0::bigint NOT NULL,
    tc_external_id integer,
    version integer DEFAULT 1 NOT NULL,
    summary text,
    steps text,
    expected_results text,
    importance smallint DEFAULT 2::smallint NOT NULL,
    author_id bigint,
    creation_ts timestamp without time zone DEFAULT now() NOT NULL,
    updater_id bigint,
    modification_ts timestamp without time zone,
    active smallint DEFAULT 1::smallint NOT NULL,
    is_open smallint DEFAULT 1::smallint NOT NULL,
    execution_type smallint DEFAULT 1::smallint NOT NULL
);


ALTER TABLE public.tcversions OWNER TO postgres;

--
-- TOC entry 1627 (class 1259 OID 22512)
-- Dependencies: 2058 2059 3
-- Name: testcase_keywords; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE testcase_keywords (
    testcase_id bigint DEFAULT 0::bigint NOT NULL,
    keyword_id bigint DEFAULT 0::bigint NOT NULL
);


ALTER TABLE public.testcase_keywords OWNER TO postgres;

--
-- TOC entry 1596 (class 1259 OID 22098)
-- Dependencies: 1954 1955 1956 1957 1958 3
-- Name: testplan_tcversions; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE testplan_tcversions (
    id bigint NOT NULL,
    testplan_id bigint DEFAULT 0::bigint NOT NULL,
    tcversion_id bigint DEFAULT 0::bigint NOT NULL,
    node_order bigint DEFAULT 1 NOT NULL,
    urgency smallint DEFAULT 2::smallint NOT NULL,
    author_id bigint,
    creation_ts timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE public.testplan_tcversions OWNER TO postgres;

--
-- TOC entry 1595 (class 1259 OID 22096)
-- Dependencies: 3 1596
-- Name: testplan_tcversions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE testplan_tcversions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.testplan_tcversions_id_seq OWNER TO postgres;

--
-- TOC entry 2322 (class 0 OID 0)
-- Dependencies: 1595
-- Name: testplan_tcversions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE testplan_tcversions_id_seq OWNED BY testplan_tcversions.id;


--
-- TOC entry 2323 (class 0 OID 0)
-- Dependencies: 1595
-- Name: testplan_tcversions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('testplan_tcversions_id_seq', 1, false);


--
-- TOC entry 1590 (class 1259 OID 22021)
-- Dependencies: 1937 1938 1939 1940 3
-- Name: testplans; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE testplans (
    id bigint DEFAULT 0::bigint NOT NULL,
    testproject_id bigint DEFAULT 0::bigint NOT NULL,
    notes text,
    active smallint DEFAULT 1::smallint NOT NULL,
    is_open smallint DEFAULT 1::smallint NOT NULL
);


ALTER TABLE public.testplans OWNER TO postgres;

--
-- TOC entry 1599 (class 1259 OID 22149)
-- Dependencies: 1974 1975 1976 1977 1978 1979 1980 3
-- Name: testprojects; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE testprojects (
    id bigint DEFAULT 0::bigint NOT NULL,
    notes text,
    color character varying(12) DEFAULT '#9BD'::character varying NOT NULL,
    active smallint DEFAULT 1::smallint NOT NULL,
    option_reqs smallint DEFAULT 0::smallint NOT NULL,
    option_priority smallint DEFAULT 0::smallint NOT NULL,
    option_automation smallint DEFAULT 0::smallint NOT NULL,
    prefix character varying(16) NOT NULL,
    tc_counter integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.testprojects OWNER TO postgres;

--
-- TOC entry 1628 (class 1259 OID 22529)
-- Dependencies: 2060 3
-- Name: testsuites; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE testsuites (
    id bigint DEFAULT 0::bigint NOT NULL,
    details text
);


ALTER TABLE public.testsuites OWNER TO postgres;

--
-- TOC entry 1634 (class 1259 OID 22615)
-- Dependencies: 2074 2075 3
-- Name: text_templates; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE text_templates (
    id bigint NOT NULL,
    type integer NOT NULL,
    title character varying(100) NOT NULL,
    template_data text,
    author_id bigint,
    create_ts timestamp without time zone DEFAULT now() NOT NULL,
    is_public smallint DEFAULT 0::smallint NOT NULL
);


ALTER TABLE public.text_templates OWNER TO postgres;

--
-- TOC entry 2324 (class 0 OID 0)
-- Dependencies: 1634
-- Name: TABLE text_templates; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE text_templates IS 'Global Project Templates';


--
-- TOC entry 1633 (class 1259 OID 22613)
-- Dependencies: 1634 3
-- Name: text_templates_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE text_templates_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.text_templates_id_seq OWNER TO postgres;

--
-- TOC entry 2325 (class 0 OID 0)
-- Dependencies: 1633
-- Name: text_templates_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE text_templates_id_seq OWNED BY text_templates.id;


--
-- TOC entry 2326 (class 0 OID 0)
-- Dependencies: 1633
-- Name: text_templates_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('text_templates_id_seq', 1, false);


--
-- TOC entry 1582 (class 1259 OID 21927)
-- Dependencies: 1910 1911 1912 1913 1914 3
-- Name: transactions; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE transactions (
    id bigint NOT NULL,
    entry_point character varying(45) DEFAULT ''::character varying NOT NULL,
    start_time integer DEFAULT 0 NOT NULL,
    end_time integer DEFAULT 0 NOT NULL,
    user_id bigint DEFAULT 0,
    session_id character varying(45) DEFAULT NULL::character varying
);


ALTER TABLE public.transactions OWNER TO postgres;

--
-- TOC entry 1581 (class 1259 OID 21925)
-- Dependencies: 3 1582
-- Name: transactions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE transactions_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.transactions_id_seq OWNER TO postgres;

--
-- TOC entry 2327 (class 0 OID 0)
-- Dependencies: 1581
-- Name: transactions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE transactions_id_seq OWNED BY transactions.id;


--
-- TOC entry 2328 (class 0 OID 0)
-- Dependencies: 1581
-- Name: transactions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('transactions_id_seq', 18, true);


--
-- TOC entry 1630 (class 1259 OID 22545)
-- Dependencies: 2062 2063 2064 2065 2066 3
-- Name: user_assignments; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE user_assignments (
    id bigint NOT NULL,
    type bigint DEFAULT 0::bigint NOT NULL,
    feature_id bigint DEFAULT 0::bigint NOT NULL,
    user_id bigint,
    deadline_ts timestamp without time zone DEFAULT (now() + '10 days'::interval) NOT NULL,
    assigner_id bigint,
    creation_ts timestamp without time zone DEFAULT now() NOT NULL,
    status integer DEFAULT 1 NOT NULL
);


ALTER TABLE public.user_assignments OWNER TO postgres;

--
-- TOC entry 1629 (class 1259 OID 22543)
-- Dependencies: 3 1630
-- Name: user_assignments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE user_assignments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.user_assignments_id_seq OWNER TO postgres;

--
-- TOC entry 2329 (class 0 OID 0)
-- Dependencies: 1629
-- Name: user_assignments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE user_assignments_id_seq OWNED BY user_assignments.id;


--
-- TOC entry 2330 (class 0 OID 0)
-- Dependencies: 1629
-- Name: user_assignments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('user_assignments_id_seq', 1, false);


--
-- TOC entry 1636 (class 1259 OID 22635)
-- Dependencies: 3
-- Name: user_group; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE user_group (
    id bigint NOT NULL,
    title character varying(100) NOT NULL,
    description text,
    owner_id bigint NOT NULL,
    testproject_id bigint NOT NULL
);


ALTER TABLE public.user_group OWNER TO postgres;

--
-- TOC entry 1637 (class 1259 OID 22656)
-- Dependencies: 3
-- Name: user_group_assign; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE user_group_assign (
    usergroup_id bigint NOT NULL,
    user_id bigint NOT NULL
);


ALTER TABLE public.user_group_assign OWNER TO postgres;

--
-- TOC entry 1635 (class 1259 OID 22633)
-- Dependencies: 1636 3
-- Name: user_group_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE user_group_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.user_group_id_seq OWNER TO postgres;

--
-- TOC entry 2331 (class 0 OID 0)
-- Dependencies: 1635
-- Name: user_group_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE user_group_id_seq OWNED BY user_group.id;


--
-- TOC entry 2332 (class 0 OID 0)
-- Dependencies: 1635
-- Name: user_group_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('user_group_id_seq', 1, false);


--
-- TOC entry 1631 (class 1259 OID 22567)
-- Dependencies: 2067 2068 2069 3
-- Name: user_testplan_roles; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE user_testplan_roles (
    user_id integer DEFAULT 0 NOT NULL,
    testplan_id integer DEFAULT 0 NOT NULL,
    role_id integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.user_testplan_roles OWNER TO postgres;

--
-- TOC entry 1632 (class 1259 OID 22590)
-- Dependencies: 2070 2071 2072 3
-- Name: user_testproject_roles; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE user_testproject_roles (
    user_id integer DEFAULT 0 NOT NULL,
    testproject_id integer DEFAULT 0 NOT NULL,
    role_id integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.user_testproject_roles OWNER TO postgres;

--
-- TOC entry 1588 (class 1259 OID 21970)
-- Dependencies: 1922 1923 1924 1925 1926 1927 1928 1929 3
-- Name: users; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE users (
    id bigint NOT NULL,
    login character varying(30) DEFAULT ''::character varying NOT NULL,
    password character varying(32) DEFAULT ''::character varying NOT NULL,
    role_id smallint DEFAULT 0::smallint NOT NULL,
    email character varying(100) DEFAULT ''::character varying NOT NULL,
    first character varying(30) DEFAULT ''::character varying NOT NULL,
    last character varying(30) DEFAULT ''::character varying NOT NULL,
    locale character varying(10) DEFAULT 'en_GB'::character varying NOT NULL,
    default_testproject_id integer,
    active smallint DEFAULT 1::smallint NOT NULL,
    script_key character varying(32)
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 1587 (class 1259 OID 21968)
-- Dependencies: 1588 3
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE users_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO postgres;

--
-- TOC entry 2333 (class 0 OID 0)
-- Dependencies: 1587
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE users_id_seq OWNED BY users.id;


--
-- TOC entry 2334 (class 0 OID 0)
-- Dependencies: 1587
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('users_id_seq', 1, true);


--
-- TOC entry 2000 (class 2604 OID 22284)
-- Dependencies: 1605 1606 1606
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE assignment_status ALTER COLUMN id SET DEFAULT nextval('assignment_status_id_seq'::regclass);


--
-- TOC entry 2002 (class 2604 OID 22293)
-- Dependencies: 1608 1607 1608
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE assignment_types ALTER COLUMN id SET DEFAULT nextval('assignment_types_id_seq'::regclass);


--
-- TOC entry 2005 (class 2604 OID 22303)
-- Dependencies: 1609 1610 1610
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE attachments ALTER COLUMN id SET DEFAULT nextval('attachments_id_seq'::regclass);


--
-- TOC entry 1941 (class 2604 OID 22044)
-- Dependencies: 1592 1591 1592
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE builds ALTER COLUMN id SET DEFAULT nextval('builds_id_seq'::regclass);


--
-- TOC entry 1959 (class 2604 OID 22126)
-- Dependencies: 1598 1597 1598
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE custom_fields ALTER COLUMN id SET DEFAULT nextval('custom_fields_id_seq'::regclass);


--
-- TOC entry 1915 (class 2604 OID 21943)
-- Dependencies: 1584 1583 1584
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE events ALTER COLUMN id SET DEFAULT nextval('events_id_seq'::regclass);


--
-- TOC entry 1946 (class 2604 OID 22067)
-- Dependencies: 1594 1593 1594
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE executions ALTER COLUMN id SET DEFAULT nextval('executions_id_seq'::regclass);


--
-- TOC entry 2020 (class 2604 OID 22344)
-- Dependencies: 1614 1613 1614
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE keywords ALTER COLUMN id SET DEFAULT nextval('keywords_id_seq'::regclass);


--
-- TOC entry 2023 (class 2604 OID 22364)
-- Dependencies: 1616 1615 1616
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE milestones ALTER COLUMN id SET DEFAULT nextval('milestones_id_seq'::regclass);


--
-- TOC entry 1904 (class 2604 OID 21905)
-- Dependencies: 1577 1578 1578
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE node_types ALTER COLUMN id SET DEFAULT nextval('node_types_id_seq'::regclass);


--
-- TOC entry 1906 (class 2604 OID 21914)
-- Dependencies: 1579 1580 1580
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE nodes_hierarchy ALTER COLUMN id SET DEFAULT nextval('nodes_hierarchy_id_seq'::regclass);


--
-- TOC entry 2029 (class 2604 OID 22385)
-- Dependencies: 1617 1618 1618
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE object_keywords ALTER COLUMN id SET DEFAULT nextval('object_keywords_id_seq'::regclass);


--
-- TOC entry 2049 (class 2604 OID 22465)
-- Dependencies: 1623 1622 1623
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE rights ALTER COLUMN id SET DEFAULT nextval('rights_id_seq'::regclass);


--
-- TOC entry 2051 (class 2604 OID 22476)
-- Dependencies: 1625 1624 1625
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE risk_assignments ALTER COLUMN id SET DEFAULT nextval('risk_assignments_id_seq'::regclass);


--
-- TOC entry 1919 (class 2604 OID 21959)
-- Dependencies: 1585 1586 1586
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE roles ALTER COLUMN id SET DEFAULT nextval('roles_id_seq'::regclass);


--
-- TOC entry 1953 (class 2604 OID 22101)
-- Dependencies: 1596 1595 1596
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE testplan_tcversions ALTER COLUMN id SET DEFAULT nextval('testplan_tcversions_id_seq'::regclass);


--
-- TOC entry 2073 (class 2604 OID 22618)
-- Dependencies: 1634 1633 1634
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE text_templates ALTER COLUMN id SET DEFAULT nextval('text_templates_id_seq'::regclass);


--
-- TOC entry 1909 (class 2604 OID 21930)
-- Dependencies: 1582 1581 1582
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE transactions ALTER COLUMN id SET DEFAULT nextval('transactions_id_seq'::regclass);


--
-- TOC entry 2061 (class 2604 OID 22548)
-- Dependencies: 1629 1630 1630
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE user_assignments ALTER COLUMN id SET DEFAULT nextval('user_assignments_id_seq'::regclass);


--
-- TOC entry 2076 (class 2604 OID 22638)
-- Dependencies: 1636 1635 1636
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE user_group ALTER COLUMN id SET DEFAULT nextval('user_group_id_seq'::regclass);


--
-- TOC entry 1921 (class 2604 OID 21973)
-- Dependencies: 1588 1587 1588
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE users ALTER COLUMN id SET DEFAULT nextval('users_id_seq'::regclass);


--
-- TOC entry 2265 (class 0 OID 22281)
-- Dependencies: 1606
-- Data for Name: assignment_status; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY assignment_status (id, description) FROM stdin;
1	open
2	closed
3	completed
4	todo_urgent
5	todo
\.


--
-- TOC entry 2266 (class 0 OID 22290)
-- Dependencies: 1608
-- Data for Name: assignment_types; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY assignment_types (id, fk_table, description) FROM stdin;
1	testplan_tcversions	testcase_execution
2	tcversions	testcase_review
\.


--
-- TOC entry 2267 (class 0 OID 22300)
-- Dependencies: 1610
-- Data for Name: attachments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY attachments (id, fk_id, fk_table, title, description, file_name, file_path, file_size, file_type, date_added, content, compression_type) FROM stdin;
\.


--
-- TOC entry 2255 (class 0 OID 22041)
-- Dependencies: 1592
-- Data for Name: builds; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY builds (id, testplan_id, name, notes, active, is_open) FROM stdin;
1	3	Release 1.0		1	1
2	3	Release 2.0		1	1
3	4	Release 1.0		1	1
4	4	Release 2.0		1	1
5	5	Vulcan 1.0		1	1
6	5	Klyngon 3.0		1	1
7	6	Klyngon 3.0		1	1
8	6	Vulcan 1.0		1	1
\.


--
-- TOC entry 2261 (class 0 OID 22193)
-- Dependencies: 1601
-- Data for Name: cfield_design_values; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cfield_design_values (field_id, node_id, value) FROM stdin;
\.


--
-- TOC entry 2262 (class 0 OID 22212)
-- Dependencies: 1602
-- Data for Name: cfield_execution_values; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cfield_execution_values (field_id, execution_id, testplan_id, tcversion_id, value) FROM stdin;
\.


--
-- TOC entry 2264 (class 0 OID 22261)
-- Dependencies: 1604
-- Data for Name: cfield_node_types; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cfield_node_types (field_id, node_type_id) FROM stdin;
\.


--
-- TOC entry 2263 (class 0 OID 22242)
-- Dependencies: 1603
-- Data for Name: cfield_testplan_design_values; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cfield_testplan_design_values (field_id, link_id, value) FROM stdin;
\.


--
-- TOC entry 2260 (class 0 OID 22172)
-- Dependencies: 1600
-- Data for Name: cfield_testprojects; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY cfield_testprojects (field_id, testproject_id, display_order, active, required_on_design, required_on_execution) FROM stdin;
\.


--
-- TOC entry 2258 (class 0 OID 22123)
-- Dependencies: 1598
-- Data for Name: custom_fields; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY custom_fields (id, name, label, type, possible_values, default_value, valid_regexp, length_min, length_max, show_on_design, enable_on_design, show_on_execution, enable_on_execution, show_on_testplan_design, enable_on_testplan_design) FROM stdin;
\.


--
-- TOC entry 2268 (class 0 OID 22319)
-- Dependencies: 1611
-- Data for Name: db_version; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY db_version (version, upgrade_ts, notes) FROM stdin;
DB 1.2	2009-04-30 20:37:43.718	first version with API feature
\.


--
-- TOC entry 2250 (class 0 OID 21940)
-- Dependencies: 1584
-- Data for Name: events; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY events (id, transaction_id, log_level, source, description, fired_at, activity, object_id, object_type) FROM stdin;
1	1	16	GUI	O:18:"tlMetaStringHelper":4:{s:5:"label";s:21:"audit_login_succeeded";s:6:"params";a:2:{i:0;s:5:"admin";i:1;s:9:"127.0.0.1";}s:13:"bDontLocalize";b:0;s:14:"bDontFireEvent";b:0;}	1241116672	LOGIN	1	users
2	2	2	GUI	No project found: Assume a new installation and redirect to create it	1241116673	\N	\N	\N
3	3	16	GUI	O:18:"tlMetaStringHelper":4:{s:5:"label";s:25:"audit_testproject_created";s:6:"params";a:1:{i:0;s:8:"API TEST";}s:13:"bDontLocalize";b:0;s:14:"bDontFireEvent";b:0;}	1241116690	CREATE	1	testprojects
4	4	16	GUI	O:18:"tlMetaStringHelper":4:{s:5:"label";s:25:"audit_testproject_created";s:6:"params";a:1:{i:0;s:9:"Star Trek";}s:13:"bDontLocalize";b:0;s:14:"bDontFireEvent";b:0;}	1241116740	CREATE	2	testprojects
5	5	16	GUI	O:18:"tlMetaStringHelper":4:{s:5:"label";s:22:"audit_testplan_created";s:6:"params";a:2:{i:0;s:8:"API TEST";i:1;s:17:"PLAN 1 - API TEST";}s:13:"bDontLocalize";b:0;s:14:"bDontFireEvent";b:0;}	1241116770	CREATED	3	testplans
6	6	16	GUI	O:18:"tlMetaStringHelper":4:{s:5:"label";s:22:"audit_testplan_created";s:6:"params";a:2:{i:0;s:8:"API TEST";i:1;s:17:"PLAN 2 - API TEST";}s:13:"bDontLocalize";b:0;s:14:"bDontFireEvent";b:0;}	1241116785	CREATED	4	testplans
7	7	16	GUI	O:18:"tlMetaStringHelper":4:{s:5:"label";s:19:"audit_build_created";s:6:"params";a:3:{i:0;s:8:"API TEST";i:1;s:17:"PLAN 1 - API TEST";i:2;s:11:"Release 1.0";}s:13:"bDontLocalize";b:0;s:14:"bDontFireEvent";b:0;}	1241116807	CREATE	1	builds
8	8	16	GUI	O:18:"tlMetaStringHelper":4:{s:5:"label";s:19:"audit_build_created";s:6:"params";a:3:{i:0;s:8:"API TEST";i:1;s:17:"PLAN 1 - API TEST";i:2;s:11:"Release 2.0";}s:13:"bDontLocalize";b:0;s:14:"bDontFireEvent";b:0;}	1241116818	CREATE	2	builds
9	9	16	GUI	O:18:"tlMetaStringHelper":4:{s:5:"label";s:19:"audit_build_created";s:6:"params";a:3:{i:0;s:8:"API TEST";i:1;s:17:"PLAN 2 - API TEST";i:2;s:11:"Release 1.0";}s:13:"bDontLocalize";b:0;s:14:"bDontFireEvent";b:0;}	1241116842	CREATE	3	builds
10	10	16	GUI	O:18:"tlMetaStringHelper":4:{s:5:"label";s:19:"audit_build_created";s:6:"params";a:3:{i:0;s:8:"API TEST";i:1;s:17:"PLAN 2 - API TEST";i:2;s:11:"Release 2.0";}s:13:"bDontLocalize";b:0;s:14:"bDontFireEvent";b:0;}	1241116850	CREATE	4	builds
11	11	16	GUI	O:18:"tlMetaStringHelper":4:{s:5:"label";s:22:"audit_testplan_created";s:6:"params";a:2:{i:0;s:9:"Star Trek";i:1;s:12:"Deep Space 9";}s:13:"bDontLocalize";b:0;s:14:"bDontFireEvent";b:0;}	1241116877	CREATED	5	testplans
12	12	16	GUI	O:18:"tlMetaStringHelper":4:{s:5:"label";s:19:"audit_build_created";s:6:"params";a:3:{i:0;s:9:"Star Trek";i:1;s:12:"Deep Space 9";i:2;s:10:"Vulcan 1.0";}s:13:"bDontLocalize";b:0;s:14:"bDontFireEvent";b:0;}	1241116893	CREATE	5	builds
13	13	16	GUI	O:18:"tlMetaStringHelper":4:{s:5:"label";s:19:"audit_build_created";s:6:"params";a:3:{i:0;s:9:"Star Trek";i:1;s:12:"Deep Space 9";i:2;s:11:"Klyngon 3.0";}s:13:"bDontLocalize";b:0;s:14:"bDontFireEvent";b:0;}	1241116906	CREATE	6	builds
14	14	16	GUI	O:18:"tlMetaStringHelper":4:{s:5:"label";s:22:"audit_testplan_created";s:6:"params";a:2:{i:0;s:9:"Star Trek";i:1;s:15:"Next Generation";}s:13:"bDontLocalize";b:0;s:14:"bDontFireEvent";b:0;}	1241116933	CREATED	6	testplans
15	15	16	GUI	O:18:"tlMetaStringHelper":4:{s:5:"label";s:17:"audit_user_logout";s:6:"params";a:1:{i:0;s:5:"admin";}s:13:"bDontLocalize";b:0;s:14:"bDontFireEvent";b:0;}	1241119044	LOGOUT	1	users
16	16	16	GUI	O:18:"tlMetaStringHelper":4:{s:5:"label";s:21:"audit_login_succeeded";s:6:"params";a:2:{i:0;s:5:"admin";i:1;s:9:"127.0.0.1";}s:13:"bDontLocalize";b:0;s:14:"bDontFireEvent";b:0;}	1241119051	LOGIN	1	users
17	17	16	GUI	O:18:"tlMetaStringHelper":4:{s:5:"label";s:21:"audit_login_succeeded";s:6:"params";a:2:{i:0;s:5:"admin";i:1;s:9:"127.0.0.1";}s:13:"bDontLocalize";b:0;s:14:"bDontFireEvent";b:0;}	1241119105	LOGIN	1	users
18	18	16	GUI	O:18:"tlMetaStringHelper":4:{s:5:"label";s:17:"audit_user_logout";s:6:"params";a:1:{i:0;s:5:"admin";}s:13:"bDontLocalize";b:0;s:14:"bDontFireEvent";b:0;}	1241119504	LOGOUT	1	users
\.


--
-- TOC entry 2269 (class 0 OID 22327)
-- Dependencies: 1612
-- Data for Name: execution_bugs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY execution_bugs (execution_id, bug_id) FROM stdin;
\.


--
-- TOC entry 2256 (class 0 OID 22064)
-- Dependencies: 1594
-- Data for Name: executions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY executions (id, build_id, tester_id, execution_ts, status, testplan_id, tcversion_id, tcversion_number, execution_type, notes) FROM stdin;
\.


--
-- TOC entry 2270 (class 0 OID 22341)
-- Dependencies: 1614
-- Data for Name: keywords; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY keywords (id, keyword, testproject_id, notes) FROM stdin;
\.


--
-- TOC entry 2271 (class 0 OID 22361)
-- Dependencies: 1616
-- Data for Name: milestones; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY milestones (id, testplan_id, target_date, a, b, c, name) FROM stdin;
\.


--
-- TOC entry 2247 (class 0 OID 21902)
-- Dependencies: 1578
-- Data for Name: node_types; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY node_types (id, description) FROM stdin;
1	testproject
2	testsuite
3	testcase
4	testcase_version
5	testplan
6	requirement_spec
7	requirement
\.


--
-- TOC entry 2248 (class 0 OID 21911)
-- Dependencies: 1580
-- Data for Name: nodes_hierarchy; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY nodes_hierarchy (id, name, parent_id, node_type_id, node_order) FROM stdin;
1	API TEST	\N	1	1
2	Star Trek	\N	1	1
3	PLAN 1 - API TEST	1	5	0
4	PLAN 2 - API TEST	1	5	0
5	Deep Space 9	2	5	0
6	Next Generation	2	5	0
7	Communications	2	2	0
8	Handheld devices	7	2	0
9	medium range devices	8	2	0
10	100% moisture conditions	9	3	0
11		10	4	0
12	subatomic powered	9	2	0
13	nickel cadmiun powered	9	2	0
14	short range devices	8	2	0
15	100% moisture conditions	14	3	0
16		15	4	0
17	acid rain half power	14	3	0
18		17	4	0
19	Gamma Ray Storm	8	3	0
20		19	4	0
21	10 G shock	8	3	0
22		21	4	0
23	Subspace channels	7	2	0
24	short range devices	23	2	0
25	100% moisture conditions	24	3	0
26		25	4	0
27	acid rain half power	24	3	0
28		27	4	0
29	medium range devices	23	2	0
30	100% moisture conditions	29	3	0
31		30	4	0
32	subatomic powered	29	2	0
33	nickel cadmiun powered	29	2	0
34	Black hole test	23	3	0
35		34	4	0
36	Holodeck	2	2	0
37	Apollo 10  Simulation	36	2	0
38	Deploy	37	2	0
39	From Disk	38	3	0
40		39	4	0
41	From Network	38	3	0
42		41	4	0
43	From USB device	38	3	0
44		43	4	0
45	From flash device	38	3	0
46		45	4	0
47	Rewind	37	2	0
48	Full speed unload	47	3	0
49		48	4	0
50	Half speed unload	47	3	0
51		50	4	0
52	Reload	37	2	0
53	From USB device	52	3	0
54		53	4	0
55	From flash device	52	3	0
56		55	4	0
57	From Network	52	3	0
58		57	4	0
59	From Disk	52	3	0
60		59	4	0
61	Unload	37	2	0
62	Full speed unload	61	3	0
63		62	4	0
64	Half speed unload	61	3	0
65		64	4	0
66	Antartic Simulation	36	2	0
67	Deploy	66	2	0
68	From Disk	67	3	0
69		68	4	0
70	From Network	67	3	0
71		70	4	0
72	From USB device	67	3	0
73		72	4	0
74	From flash device	67	3	0
75		74	4	0
76	Rewind	66	2	0
77	Full speed unload	76	3	0
78		77	4	0
79	Half speed unload	76	3	0
80		79	4	0
81	Reload	66	2	0
82	From USB device	81	3	0
83		82	4	0
84	From flash device	81	3	0
85		84	4	0
86	From Network	81	3	0
87		86	4	0
88	From Disk	81	3	0
89		88	4	0
90	Unload	66	2	0
91	Full speed unload	90	3	0
92		91	4	0
93	Half speed unload	90	3	0
94		93	4	0
95	Wild West Simulation	36	2	0
96	Deploy	95	2	0
97	From Disk	96	3	0
98		97	4	0
99	From Network	96	3	0
100		99	4	0
101	From USB device	96	3	0
102		101	4	0
103	From flash device	96	3	0
104		103	4	0
105	Rewind	95	2	0
106	Full speed unload	105	3	0
107		106	4	0
108	Half speed unload	105	3	0
109		108	4	0
110	Reload	95	2	0
111	From USB device	110	3	0
112		111	4	0
113	From flash device	110	3	0
114		113	4	0
115	From Network	110	3	0
116		115	4	0
117	From Disk	110	3	0
118		117	4	0
119	Unload	95	2	0
120	Full speed unload	119	3	0
121		120	4	0
122	Half speed unload	119	3	0
123		122	4	0
124	UnderSea Life Simulation	36	2	0
125	Deploy	124	2	0
126	From Disk	125	3	0
127		126	4	0
128	From Network	125	3	0
129		128	4	0
130	From USB device	125	3	0
131		130	4	0
132	From flash device	125	3	0
133		132	4	0
134	Rewind	124	2	0
135	Full speed unload	134	3	0
136		135	4	0
137	Half speed unload	134	3	0
138		137	4	0
139	Reload	124	2	0
140	From USB device	139	3	0
141		140	4	0
142	From flash device	139	3	0
143		142	4	0
144	From Network	139	3	0
145		144	4	0
146	From Disk	139	3	0
147		146	4	0
148	Unload	124	2	0
149	Full speed unload	148	3	0
150		149	4	0
151	Half speed unload	148	3	0
152		151	4	0
153	Light settings	36	3	0
154		153	4	0
155	Sound Settings	36	3	0
156		155	4	0
157	3D Settings	36	3	0
158		157	4	0
159	Stop	36	3	0
160		159	4	0
161	Start	36	3	0
162		161	4	0
163	Propulsion Systems	2	2	0
164	Main engine	163	2	0
165	Emergency stop	164	3	0
166		165	4	0
167	Transportation	2	2	0
168	Individual	167	2	0
169	High speed	168	3	0
170		169	4	0
171	Half speed stop	168	3	0
172		171	4	0
173	Jump start	168	3	0
174		173	4	0
175	Terrestrial	167	2	0
176	Infrared guidance on moon eclipse	175	3	0
177		176	4	0
178	HyperSpace	167	2	0
179	Start gate connection	178	3	0
180		179	4	0
181	Stop gate connection	178	3	0
182		181	4	0
186	Test Project Management	1	2	1
187	Requirement management	1	2	1
188	User management	1	2	1
189	Test Specification	1	2	1
190	Create Test Project	186	3	100
191		190	4	0
192	Create Main Req Spec	187	3	100
193		192	4	0
194	Create Req Suites	187	3	100
195		194	4	0
196	Create Requirement	187	3	100
197		196	4	0
198	Create User	188	3	100
199		198	4	0
200	Create Role	188	3	100
201		200	4	0
202	Delete User	188	3	100
203		202	4	0
204	Delete Role	188	3	100
205		204	4	0
206	Generate document	189	3	100
207		206	4	0
208	Edit test case	189	3	100
209		208	4	0
210	Search test case	189	3	100
211		210	4	0
\.


--
-- TOC entry 2272 (class 0 OID 22382)
-- Dependencies: 1618
-- Data for Name: object_keywords; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY object_keywords (id, fk_id, fk_table, keyword_id) FROM stdin;
\.


--
-- TOC entry 2275 (class 0 OID 22449)
-- Dependencies: 1621
-- Data for Name: req_coverage; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY req_coverage (req_id, testcase_id) FROM stdin;
\.


--
-- TOC entry 2273 (class 0 OID 22396)
-- Dependencies: 1619
-- Data for Name: req_specs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY req_specs (id, testproject_id, title, scope, total_req, type, author_id, creation_ts, modifier_id, modification_ts) FROM stdin;
\.


--
-- TOC entry 2274 (class 0 OID 22421)
-- Dependencies: 1620
-- Data for Name: requirements; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY requirements (id, srs_id, req_doc_id, title, scope, status, type, node_order, author_id, creation_ts, modifier_id, modification_ts) FROM stdin;
\.


--
-- TOC entry 2276 (class 0 OID 22462)
-- Dependencies: 1623
-- Data for Name: rights; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY rights (id, description) FROM stdin;
1	testplan_execute
2	testplan_create_build
3	testplan_metrics
4	testplan_planning
5	testplan_user_role_assignment
6	mgt_view_tc
7	mgt_modify_tc
8	mgt_view_key
9	mgt_modify_key
10	mgt_view_req
11	mgt_modify_req
12	mgt_modify_product
13	mgt_users
14	role_management
15	user_role_assignment
16	mgt_testplan_create
17	cfield_view
18	cfield_management
19	system_configuration
20	mgt_view_events
21	mgt_view_usergroups
22	events_mgt
23	testproject_user_role_assignment
\.


--
-- TOC entry 2277 (class 0 OID 22473)
-- Dependencies: 1625
-- Data for Name: risk_assignments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY risk_assignments (id, testplan_id, node_id, risk, importance) FROM stdin;
\.


--
-- TOC entry 2278 (class 0 OID 22495)
-- Dependencies: 1626
-- Data for Name: role_rights; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY role_rights (role_id, right_id) FROM stdin;
8	1
8	2
8	3
8	4
8	5
8	6
8	7
8	8
8	9
8	10
8	11
8	12
8	13
8	14
8	15
8	16
8	17
8	18
8	19
8	20
8	21
8	22
8	23
5	3
5	6
5	8
4	3
4	6
4	7
4	8
4	9
4	10
4	11
7	1
7	3
7	6
7	8
6	1
6	2
6	3
6	6
6	7
6	8
6	9
6	11
9	1
9	2
9	3
9	4
9	5
9	6
9	7
9	8
9	9
9	10
9	11
9	15
9	16
\.


--
-- TOC entry 2251 (class 0 OID 21956)
-- Dependencies: 1586
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY roles (id, description, notes) FROM stdin;
3	<no rights>	\N
4	test designer	\N
5	guest	\N
6	senior tester	\N
7	tester	\N
8	admin	\N
9	leader	\N
\.


--
-- TOC entry 2253 (class 0 OID 21991)
-- Dependencies: 1589
-- Data for Name: tcversions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY tcversions (id, tc_external_id, version, summary, steps, expected_results, importance, author_id, creation_ts, updater_id, modification_ts, active, is_open, execution_type) FROM stdin;
11	1	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
16	2	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
18	3	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
20	4	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
22	5	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
26	6	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
28	7	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
31	8	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
35	9	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
40	10	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
42	11	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
44	12	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
46	13	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
49	14	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
51	15	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
54	16	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
56	17	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
58	18	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
60	19	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
63	20	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
65	21	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
69	22	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
71	23	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
73	24	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
75	25	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
78	26	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
80	27	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
83	28	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
85	29	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
87	30	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
89	31	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
92	32	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
94	33	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
98	34	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
100	35	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
102	36	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
104	37	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
107	38	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
109	39	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
112	40	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
114	41	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
116	42	1				2	1	2009-04-30 20:43:06	\N	\N	1	1	1
118	43	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
121	44	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
123	45	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
127	46	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
129	47	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
131	48	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
133	49	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
136	50	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
138	51	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
141	52	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
143	53	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
145	54	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
147	55	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
150	56	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
152	57	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
154	58	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
156	59	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
158	60	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
160	61	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
162	62	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
166	63	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
170	64	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
172	65	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
174	66	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
177	67	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
180	68	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
182	69	1				2	1	2009-04-30 20:43:07	\N	\N	1	1	1
191	1	1				2	1	2009-04-30 20:47:19	\N	\N	1	1	1
193	2	1				2	1	2009-04-30 20:47:42	\N	\N	1	1	1
195	3	1				2	1	2009-04-30 20:47:59	\N	\N	1	1	1
197	4	1				2	1	2009-04-30 20:48:07	\N	\N	1	1	1
199	5	1				2	1	2009-04-30 20:48:19	\N	\N	1	1	1
201	6	1				2	1	2009-04-30 20:48:27	\N	\N	1	1	1
203	7	1				2	1	2009-04-30 20:48:35	\N	\N	1	1	1
205	8	1				2	1	2009-04-30 20:48:41	\N	\N	1	1	1
207	9	1				2	1	2009-04-30 21:24:12	\N	\N	1	1	1
209	10	1				2	1	2009-04-30 21:24:50	\N	\N	1	1	1
211	11	1				2	1	2009-04-30 21:24:57	\N	\N	1	1	1
\.


--
-- TOC entry 2279 (class 0 OID 22512)
-- Dependencies: 1627
-- Data for Name: testcase_keywords; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY testcase_keywords (testcase_id, keyword_id) FROM stdin;
\.


--
-- TOC entry 2257 (class 0 OID 22098)
-- Dependencies: 1596
-- Data for Name: testplan_tcversions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY testplan_tcversions (id, testplan_id, tcversion_id, node_order, urgency, author_id, creation_ts) FROM stdin;
\.


--
-- TOC entry 2254 (class 0 OID 22021)
-- Dependencies: 1590
-- Data for Name: testplans; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY testplans (id, testproject_id, notes, active, is_open) FROM stdin;
3	1		1	1
4	1		1	1
5	2		1	1
6	2		1	1
\.


--
-- TOC entry 2259 (class 0 OID 22149)
-- Dependencies: 1599
-- Data for Name: testprojects; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY testprojects (id, notes, color, active, option_reqs, option_priority, option_automation, prefix, tc_counter) FROM stdin;
1			1	1	1	1	API	11
2			1	1	1	1	STK	69
\.


--
-- TOC entry 2280 (class 0 OID 22529)
-- Dependencies: 1628
-- Data for Name: testsuites; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY testsuites (id, details) FROM stdin;
7	<p>Communication Systems of all types</p>
8	
9	
12	
13	
14	
23	<p>Only basic subspace features</p>
24	
29	
32	
33	
36	
37	
38	
47	
52	
61	
66	
67	
76	
81	
90	
95	
96	
105	
110	
119	
124	
125	
134	
139	
148	
163	
164	
167	
168	
175	
178	
186	
187	
188	
189	
\.


--
-- TOC entry 2284 (class 0 OID 22615)
-- Dependencies: 1634
-- Data for Name: text_templates; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY text_templates (id, type, title, template_data, author_id, create_ts, is_public) FROM stdin;
\.


--
-- TOC entry 2249 (class 0 OID 21927)
-- Dependencies: 1582
-- Data for Name: transactions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY transactions (id, entry_point, start_time, end_time, user_id, session_id) FROM stdin;
1	/sourceforge/tl19/head_20090428/login.php	1241116672	1241116672	1	mkdos7lfan4kqkaebdofcf45c4
2	/tl19/head_20090428/lib/general/mainPage.php	1241116673	1241116673	1	mkdos7lfan4kqkaebdofcf45c4
3	/head_20090428/lib/project/projectEdit.php	1241116690	1241116691	1	mkdos7lfan4kqkaebdofcf45c4
4	/head_20090428/lib/project/projectEdit.php	1241116740	1241116740	1	mkdos7lfan4kqkaebdofcf45c4
5	/tl19/head_20090428/lib/plan/planEdit.php	1241116770	1241116770	1	mkdos7lfan4kqkaebdofcf45c4
6	/tl19/head_20090428/lib/plan/planEdit.php	1241116785	1241116785	1	mkdos7lfan4kqkaebdofcf45c4
7	/tl19/head_20090428/lib/plan/buildEdit.php	1241116807	1241116807	1	mkdos7lfan4kqkaebdofcf45c4
8	/tl19/head_20090428/lib/plan/buildEdit.php	1241116818	1241116819	1	mkdos7lfan4kqkaebdofcf45c4
9	/tl19/head_20090428/lib/plan/buildEdit.php	1241116842	1241116842	1	mkdos7lfan4kqkaebdofcf45c4
10	/tl19/head_20090428/lib/plan/buildEdit.php	1241116850	1241116850	1	mkdos7lfan4kqkaebdofcf45c4
11	/tl19/head_20090428/lib/plan/planEdit.php	1241116877	1241116877	1	mkdos7lfan4kqkaebdofcf45c4
12	/tl19/head_20090428/lib/plan/buildEdit.php	1241116893	1241116893	1	mkdos7lfan4kqkaebdofcf45c4
13	/tl19/head_20090428/lib/plan/buildEdit.php	1241116906	1241116906	1	mkdos7lfan4kqkaebdofcf45c4
14	/tl19/head_20090428/lib/plan/planEdit.php	1241116933	1241116933	1	mkdos7lfan4kqkaebdofcf45c4
15	/sourceforge/tl19/head_20090428/logout.php	1241119044	1241119044	1	mkdos7lfan4kqkaebdofcf45c4
16	/sourceforge/tl19/head_20090428/login.php	1241119051	1241119051	1	mkdos7lfan4kqkaebdofcf45c4
17	/sourceforge/tl19/head_20090428/login.php	1241119105	1241119105	1	e7hqohcch9tr4icsabh4bqqvv6
18	/sourceforge/tl19/head_20090428/logout.php	1241119504	1241119504	1	e7hqohcch9tr4icsabh4bqqvv6
\.


--
-- TOC entry 2281 (class 0 OID 22545)
-- Dependencies: 1630
-- Data for Name: user_assignments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY user_assignments (id, type, feature_id, user_id, deadline_ts, assigner_id, creation_ts, status) FROM stdin;
\.


--
-- TOC entry 2285 (class 0 OID 22635)
-- Dependencies: 1636
-- Data for Name: user_group; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY user_group (id, title, description, owner_id, testproject_id) FROM stdin;
\.


--
-- TOC entry 2286 (class 0 OID 22656)
-- Dependencies: 1637
-- Data for Name: user_group_assign; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY user_group_assign (usergroup_id, user_id) FROM stdin;
\.


--
-- TOC entry 2282 (class 0 OID 22567)
-- Dependencies: 1631
-- Data for Name: user_testplan_roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY user_testplan_roles (user_id, testplan_id, role_id) FROM stdin;
\.


--
-- TOC entry 2283 (class 0 OID 22590)
-- Dependencies: 1632
-- Data for Name: user_testproject_roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY user_testproject_roles (user_id, testproject_id, role_id) FROM stdin;
\.


--
-- TOC entry 2252 (class 0 OID 21970)
-- Dependencies: 1588
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY users (id, login, password, role_id, email, first, last, locale, default_testproject_id, active, script_key) FROM stdin;
1	admin	21232f297a57a5a743894a0e4a801fc3	8		Testlink	Administrator	en_GB	\N	1	CLIENTSAMPLEDEVKEY
\.


--
-- TOC entry 2138 (class 2606 OID 22287)
-- Dependencies: 1606 1606
-- Name: assignment_status_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY assignment_status
    ADD CONSTRAINT assignment_status_pkey PRIMARY KEY (id);


--
-- TOC entry 2140 (class 2606 OID 22297)
-- Dependencies: 1608 1608
-- Name: assignment_types_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY assignment_types
    ADD CONSTRAINT assignment_types_pkey PRIMARY KEY (id);


--
-- TOC entry 2142 (class 2606 OID 22318)
-- Dependencies: 1610 1610
-- Name: attachments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY attachments
    ADD CONSTRAINT attachments_pkey PRIMARY KEY (id);


--
-- TOC entry 2102 (class 2606 OID 22053)
-- Dependencies: 1592 1592
-- Name: builds_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY builds
    ADD CONSTRAINT builds_pkey PRIMARY KEY (id);


--
-- TOC entry 2105 (class 2606 OID 22055)
-- Dependencies: 1592 1592 1592
-- Name: builds_testplan_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY builds
    ADD CONSTRAINT builds_testplan_id_key UNIQUE (testplan_id, name);


--
-- TOC entry 2127 (class 2606 OID 22200)
-- Dependencies: 1601 1601 1601
-- Name: cfield_design_values_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cfield_design_values
    ADD CONSTRAINT cfield_design_values_pkey PRIMARY KEY (field_id, node_id);


--
-- TOC entry 2130 (class 2606 OID 22221)
-- Dependencies: 1602 1602 1602 1602 1602
-- Name: cfield_execution_values_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cfield_execution_values
    ADD CONSTRAINT cfield_execution_values_pkey PRIMARY KEY (field_id, execution_id, testplan_id, tcversion_id);


--
-- TOC entry 2136 (class 2606 OID 22267)
-- Dependencies: 1604 1604 1604
-- Name: cfield_node_types_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cfield_node_types
    ADD CONSTRAINT cfield_node_types_pkey PRIMARY KEY (field_id, node_type_id);


--
-- TOC entry 2132 (class 2606 OID 22249)
-- Dependencies: 1603 1603 1603
-- Name: cfield_testplan_design_values_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cfield_testplan_design_values
    ADD CONSTRAINT cfield_testplan_design_values_pkey PRIMARY KEY (field_id, link_id);


--
-- TOC entry 2125 (class 2606 OID 22182)
-- Dependencies: 1600 1600 1600
-- Name: cfield_testprojects_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY cfield_testprojects
    ADD CONSTRAINT cfield_testprojects_pkey PRIMARY KEY (field_id, testproject_id);


--
-- TOC entry 2116 (class 2606 OID 22147)
-- Dependencies: 1598 1598
-- Name: custom_fields_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY custom_fields
    ADD CONSTRAINT custom_fields_name_key UNIQUE (name);


--
-- TOC entry 2118 (class 2606 OID 22145)
-- Dependencies: 1598 1598
-- Name: custom_fields_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY custom_fields
    ADD CONSTRAINT custom_fields_pkey PRIMARY KEY (id);


--
-- TOC entry 2086 (class 2606 OID 21951)
-- Dependencies: 1584 1584
-- Name: events_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY events
    ADD CONSTRAINT events_pkey PRIMARY KEY (id);


--
-- TOC entry 2144 (class 2606 OID 22333)
-- Dependencies: 1612 1612 1612
-- Name: execution_bugs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY execution_bugs
    ADD CONSTRAINT execution_bugs_pkey PRIMARY KEY (execution_id, bug_id);


--
-- TOC entry 2109 (class 2606 OID 22078)
-- Dependencies: 1594 1594
-- Name: executions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY executions
    ADD CONSTRAINT executions_pkey PRIMARY KEY (id);


--
-- TOC entry 2147 (class 2606 OID 22351)
-- Dependencies: 1614 1614
-- Name: keywords_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY keywords
    ADD CONSTRAINT keywords_pkey PRIMARY KEY (id);


--
-- TOC entry 2150 (class 2606 OID 22373)
-- Dependencies: 1616 1616 1616
-- Name: milestones_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY milestones
    ADD CONSTRAINT milestones_name_key UNIQUE (name, testplan_id);


--
-- TOC entry 2152 (class 2606 OID 22371)
-- Dependencies: 1616 1616
-- Name: milestones_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY milestones
    ADD CONSTRAINT milestones_pkey PRIMARY KEY (id);


--
-- TOC entry 2078 (class 2606 OID 21908)
-- Dependencies: 1578 1578
-- Name: node_types_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY node_types
    ADD CONSTRAINT node_types_pkey PRIMARY KEY (id);


--
-- TOC entry 2081 (class 2606 OID 21918)
-- Dependencies: 1580 1580
-- Name: nodes_hierarchy_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY nodes_hierarchy
    ADD CONSTRAINT nodes_hierarchy_pkey PRIMARY KEY (id);


--
-- TOC entry 2155 (class 2606 OID 22390)
-- Dependencies: 1618 1618
-- Name: object_keywords_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY object_keywords
    ADD CONSTRAINT object_keywords_pkey PRIMARY KEY (id);


--
-- TOC entry 2157 (class 2606 OID 22409)
-- Dependencies: 1619 1619
-- Name: req_specs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY req_specs
    ADD CONSTRAINT req_specs_pkey PRIMARY KEY (id);


--
-- TOC entry 2160 (class 2606 OID 22436)
-- Dependencies: 1620 1620
-- Name: requirements_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY requirements
    ADD CONSTRAINT requirements_pkey PRIMARY KEY (id);


--
-- TOC entry 2165 (class 2606 OID 22470)
-- Dependencies: 1623 1623
-- Name: rights_description_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY rights
    ADD CONSTRAINT rights_description_key UNIQUE (description);


--
-- TOC entry 2167 (class 2606 OID 22468)
-- Dependencies: 1623 1623
-- Name: rights_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY rights
    ADD CONSTRAINT rights_pkey PRIMARY KEY (id);


--
-- TOC entry 2169 (class 2606 OID 22482)
-- Dependencies: 1625 1625
-- Name: risk_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY risk_assignments
    ADD CONSTRAINT risk_assignments_pkey PRIMARY KEY (id);


--
-- TOC entry 2171 (class 2606 OID 22484)
-- Dependencies: 1625 1625 1625
-- Name: risk_assignments_testplan_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY risk_assignments
    ADD CONSTRAINT risk_assignments_testplan_id_key UNIQUE (testplan_id, node_id);


--
-- TOC entry 2173 (class 2606 OID 22501)
-- Dependencies: 1626 1626 1626
-- Name: role_rights_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY role_rights
    ADD CONSTRAINT role_rights_pkey PRIMARY KEY (role_id, right_id);


--
-- TOC entry 2089 (class 2606 OID 21967)
-- Dependencies: 1586 1586
-- Name: roles_description_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY roles
    ADD CONSTRAINT roles_description_key UNIQUE (description);


--
-- TOC entry 2091 (class 2606 OID 21965)
-- Dependencies: 1586 1586
-- Name: roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- TOC entry 2097 (class 2606 OID 22005)
-- Dependencies: 1589 1589
-- Name: tcversions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY tcversions
    ADD CONSTRAINT tcversions_pkey PRIMARY KEY (id);


--
-- TOC entry 2175 (class 2606 OID 22518)
-- Dependencies: 1627 1627 1627
-- Name: testcase_keywords_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY testcase_keywords
    ADD CONSTRAINT testcase_keywords_pkey PRIMARY KEY (testcase_id, keyword_id);


--
-- TOC entry 2111 (class 2606 OID 22108)
-- Dependencies: 1596 1596
-- Name: testplan_tcversions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY testplan_tcversions
    ADD CONSTRAINT testplan_tcversions_pkey PRIMARY KEY (id);


--
-- TOC entry 2113 (class 2606 OID 22110)
-- Dependencies: 1596 1596 1596
-- Name: testplan_tcversions_testplan_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY testplan_tcversions
    ADD CONSTRAINT testplan_tcversions_testplan_id_key UNIQUE (testplan_id, tcversion_id);


--
-- TOC entry 2099 (class 2606 OID 22032)
-- Dependencies: 1590 1590
-- Name: testplans_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY testplans
    ADD CONSTRAINT testplans_pkey PRIMARY KEY (id);


--
-- TOC entry 2121 (class 2606 OID 22163)
-- Dependencies: 1599 1599
-- Name: testprojects_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY testprojects
    ADD CONSTRAINT testprojects_pkey PRIMARY KEY (id);


--
-- TOC entry 2123 (class 2606 OID 22165)
-- Dependencies: 1599 1599
-- Name: testprojects_prefix_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY testprojects
    ADD CONSTRAINT testprojects_prefix_key UNIQUE (prefix);


--
-- TOC entry 2177 (class 2606 OID 22537)
-- Dependencies: 1628 1628
-- Name: testsuites_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY testsuites
    ADD CONSTRAINT testsuites_pkey PRIMARY KEY (id);


--
-- TOC entry 2186 (class 2606 OID 22625)
-- Dependencies: 1634 1634
-- Name: text_templates_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY text_templates
    ADD CONSTRAINT text_templates_pkey PRIMARY KEY (id);


--
-- TOC entry 2188 (class 2606 OID 22627)
-- Dependencies: 1634 1634 1634
-- Name: text_templates_type_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY text_templates
    ADD CONSTRAINT text_templates_type_key UNIQUE (type, title);


--
-- TOC entry 2083 (class 2606 OID 21937)
-- Dependencies: 1582 1582
-- Name: transactions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY transactions
    ADD CONSTRAINT transactions_pkey PRIMARY KEY (id);


--
-- TOC entry 2180 (class 2606 OID 22555)
-- Dependencies: 1630 1630
-- Name: user_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY user_assignments
    ADD CONSTRAINT user_assignments_pkey PRIMARY KEY (id);


--
-- TOC entry 2190 (class 2606 OID 22643)
-- Dependencies: 1636 1636
-- Name: user_group_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY user_group
    ADD CONSTRAINT user_group_pkey PRIMARY KEY (id);


--
-- TOC entry 2192 (class 2606 OID 22645)
-- Dependencies: 1636 1636
-- Name: user_group_title_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY user_group
    ADD CONSTRAINT user_group_title_key UNIQUE (title);


--
-- TOC entry 2182 (class 2606 OID 22574)
-- Dependencies: 1631 1631 1631
-- Name: user_testplan_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY user_testplan_roles
    ADD CONSTRAINT user_testplan_roles_pkey PRIMARY KEY (user_id, testplan_id);


--
-- TOC entry 2184 (class 2606 OID 22597)
-- Dependencies: 1632 1632 1632
-- Name: user_testproject_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY user_testproject_roles
    ADD CONSTRAINT user_testproject_roles_pkey PRIMARY KEY (user_id, testproject_id);


--
-- TOC entry 2093 (class 2606 OID 21985)
-- Dependencies: 1588 1588
-- Name: users_login_key; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_login_key UNIQUE (login);


--
-- TOC entry 2095 (class 2606 OID 21983)
-- Dependencies: 1588 1588
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 2103 (class 1259 OID 22061)
-- Dependencies: 1592
-- Name: builds_testplan_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX builds_testplan_id ON builds USING btree (testplan_id);


--
-- TOC entry 2134 (class 1259 OID 22278)
-- Dependencies: 1604
-- Name: cfield_node_types_idx_custom_fields_assign; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX cfield_node_types_idx_custom_fields_assign ON cfield_node_types USING btree (node_type_id);


--
-- TOC entry 2114 (class 1259 OID 22148)
-- Dependencies: 1598
-- Name: custom_fields_idx_custom_fields_name; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX custom_fields_idx_custom_fields_name ON custom_fields USING btree (name);


--
-- TOC entry 2084 (class 1259 OID 21953)
-- Dependencies: 1584
-- Name: events_fired_at; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX events_fired_at ON events USING btree (fired_at);


--
-- TOC entry 2087 (class 1259 OID 21952)
-- Dependencies: 1584
-- Name: events_transaction_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX events_transaction_id ON events USING btree (transaction_id);


--
-- TOC entry 2106 (class 1259 OID 22094)
-- Dependencies: 1594 1594
-- Name: executions_idx1; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX executions_idx1 ON executions USING btree (testplan_id, tcversion_id);


--
-- TOC entry 2107 (class 1259 OID 22095)
-- Dependencies: 1594
-- Name: executions_idx2; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX executions_idx2 ON executions USING btree (execution_type);


--
-- TOC entry 2178 (class 1259 OID 22566)
-- Dependencies: 1630
-- Name: feature_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX feature_id ON user_assignments USING btree (feature_id);


--
-- TOC entry 2128 (class 1259 OID 22211)
-- Dependencies: 1601
-- Name: idx_cfield_design_values; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX idx_cfield_design_values ON cfield_design_values USING btree (node_id);


--
-- TOC entry 2133 (class 1259 OID 22260)
-- Dependencies: 1603
-- Name: idx_cfield_tplan_design_val; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX idx_cfield_tplan_design_val ON cfield_testplan_design_values USING btree (link_id);


--
-- TOC entry 2145 (class 1259 OID 22358)
-- Dependencies: 1614
-- Name: keywords_keyword; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX keywords_keyword ON keywords USING btree (keyword);


--
-- TOC entry 2148 (class 1259 OID 22357)
-- Dependencies: 1614
-- Name: keywords_testproject_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX keywords_testproject_id ON keywords USING btree (testproject_id);


--
-- TOC entry 2153 (class 1259 OID 22379)
-- Dependencies: 1616
-- Name: milestones_testplan_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX milestones_testplan_id ON milestones USING btree (testplan_id);


--
-- TOC entry 2079 (class 1259 OID 21924)
-- Dependencies: 1580 1580
-- Name: nodes_hierarchy_pid_m_nodeorder; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX nodes_hierarchy_pid_m_nodeorder ON nodes_hierarchy USING btree (parent_id, node_order);


--
-- TOC entry 2163 (class 1259 OID 22459)
-- Dependencies: 1621 1621
-- Name: req_coverage_req_testcase; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX req_coverage_req_testcase ON req_coverage USING btree (req_id, testcase_id);


--
-- TOC entry 2158 (class 1259 OID 22420)
-- Dependencies: 1619
-- Name: req_specs_testproject_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX req_specs_testproject_id ON req_specs USING btree (testproject_id);


--
-- TOC entry 2161 (class 1259 OID 22448)
-- Dependencies: 1620 1620
-- Name: requirements_req_doc_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX requirements_req_doc_id ON requirements USING btree (srs_id, req_doc_id);


--
-- TOC entry 2162 (class 1259 OID 22447)
-- Dependencies: 1620 1620
-- Name: requirements_srs_id; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX requirements_srs_id ON requirements USING btree (srs_id, status);


--
-- TOC entry 2100 (class 1259 OID 22038)
-- Dependencies: 1590 1590
-- Name: testplans_testproject_id_active; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX testplans_testproject_id_active ON testplans USING btree (testproject_id, active);


--
-- TOC entry 2119 (class 1259 OID 22171)
-- Dependencies: 1599 1599
-- Name: testprojects_id_active; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX testprojects_id_active ON testprojects USING btree (id, active);


--
-- TOC entry 2199 (class 2606 OID 22056)
-- Dependencies: 1592 1590 2098
-- Name: builds_testplan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY builds
    ADD CONSTRAINT builds_testplan_id_fkey FOREIGN KEY (testplan_id) REFERENCES testplans(id);


--
-- TOC entry 2208 (class 2606 OID 22201)
-- Dependencies: 1598 1601 2117
-- Name: cfield_design_values_field_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cfield_design_values
    ADD CONSTRAINT cfield_design_values_field_id_fkey FOREIGN KEY (field_id) REFERENCES custom_fields(id);


--
-- TOC entry 2209 (class 2606 OID 22206)
-- Dependencies: 1580 1601 2080
-- Name: cfield_design_values_node_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cfield_design_values
    ADD CONSTRAINT cfield_design_values_node_id_fkey FOREIGN KEY (node_id) REFERENCES nodes_hierarchy(id);


--
-- TOC entry 2211 (class 2606 OID 22227)
-- Dependencies: 1594 1602 2108
-- Name: cfield_execution_values_execution_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cfield_execution_values
    ADD CONSTRAINT cfield_execution_values_execution_id_fkey FOREIGN KEY (execution_id) REFERENCES executions(id);


--
-- TOC entry 2210 (class 2606 OID 22222)
-- Dependencies: 2117 1598 1602
-- Name: cfield_execution_values_field_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cfield_execution_values
    ADD CONSTRAINT cfield_execution_values_field_id_fkey FOREIGN KEY (field_id) REFERENCES custom_fields(id);


--
-- TOC entry 2213 (class 2606 OID 22237)
-- Dependencies: 1589 2096 1602
-- Name: cfield_execution_values_tcversion_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cfield_execution_values
    ADD CONSTRAINT cfield_execution_values_tcversion_id_fkey FOREIGN KEY (tcversion_id) REFERENCES tcversions(id);


--
-- TOC entry 2212 (class 2606 OID 22232)
-- Dependencies: 1602 1590 2098
-- Name: cfield_execution_values_testplan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cfield_execution_values
    ADD CONSTRAINT cfield_execution_values_testplan_id_fkey FOREIGN KEY (testplan_id) REFERENCES testplans(id);


--
-- TOC entry 2216 (class 2606 OID 22268)
-- Dependencies: 2117 1604 1598
-- Name: cfield_node_types_field_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cfield_node_types
    ADD CONSTRAINT cfield_node_types_field_id_fkey FOREIGN KEY (field_id) REFERENCES custom_fields(id);


--
-- TOC entry 2217 (class 2606 OID 22273)
-- Dependencies: 2077 1578 1604
-- Name: cfield_node_types_node_type_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cfield_node_types
    ADD CONSTRAINT cfield_node_types_node_type_id_fkey FOREIGN KEY (node_type_id) REFERENCES node_types(id);


--
-- TOC entry 2214 (class 2606 OID 22250)
-- Dependencies: 1603 2117 1598
-- Name: cfield_testplan_design_values_field_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cfield_testplan_design_values
    ADD CONSTRAINT cfield_testplan_design_values_field_id_fkey FOREIGN KEY (field_id) REFERENCES custom_fields(id);


--
-- TOC entry 2215 (class 2606 OID 22255)
-- Dependencies: 1596 1603 2110
-- Name: cfield_testplan_design_values_link_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cfield_testplan_design_values
    ADD CONSTRAINT cfield_testplan_design_values_link_id_fkey FOREIGN KEY (link_id) REFERENCES testplan_tcversions(id);


--
-- TOC entry 2206 (class 2606 OID 22183)
-- Dependencies: 1598 1600 2117
-- Name: cfield_testprojects_field_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cfield_testprojects
    ADD CONSTRAINT cfield_testprojects_field_id_fkey FOREIGN KEY (field_id) REFERENCES custom_fields(id);


--
-- TOC entry 2207 (class 2606 OID 22188)
-- Dependencies: 2120 1599 1600
-- Name: cfield_testprojects_testproject_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY cfield_testprojects
    ADD CONSTRAINT cfield_testprojects_testproject_id_fkey FOREIGN KEY (testproject_id) REFERENCES testprojects(id);


--
-- TOC entry 2218 (class 2606 OID 22334)
-- Dependencies: 2108 1594 1612
-- Name: execution_bugs_execution_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY execution_bugs
    ADD CONSTRAINT execution_bugs_execution_id_fkey FOREIGN KEY (execution_id) REFERENCES executions(id);


--
-- TOC entry 2200 (class 2606 OID 22079)
-- Dependencies: 2101 1592 1594
-- Name: executions_build_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY executions
    ADD CONSTRAINT executions_build_id_fkey FOREIGN KEY (build_id) REFERENCES builds(id);


--
-- TOC entry 2202 (class 2606 OID 22089)
-- Dependencies: 1594 1589 2096
-- Name: executions_tcversion_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY executions
    ADD CONSTRAINT executions_tcversion_id_fkey FOREIGN KEY (tcversion_id) REFERENCES tcversions(id);


--
-- TOC entry 2201 (class 2606 OID 22084)
-- Dependencies: 1590 1594 2098
-- Name: executions_testplan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY executions
    ADD CONSTRAINT executions_testplan_id_fkey FOREIGN KEY (testplan_id) REFERENCES testplans(id);


--
-- TOC entry 2219 (class 2606 OID 22352)
-- Dependencies: 2120 1599 1614
-- Name: keywords_testproject_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY keywords
    ADD CONSTRAINT keywords_testproject_id_fkey FOREIGN KEY (testproject_id) REFERENCES testprojects(id);


--
-- TOC entry 2220 (class 2606 OID 22374)
-- Dependencies: 1590 1616 2098
-- Name: milestones_testplan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY milestones
    ADD CONSTRAINT milestones_testplan_id_fkey FOREIGN KEY (testplan_id) REFERENCES testplans(id);


--
-- TOC entry 2193 (class 2606 OID 21919)
-- Dependencies: 1578 2077 1580
-- Name: nodes_hierarchy_node_type_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY nodes_hierarchy
    ADD CONSTRAINT nodes_hierarchy_node_type_id_fkey FOREIGN KEY (node_type_id) REFERENCES node_types(id);


--
-- TOC entry 2221 (class 2606 OID 22391)
-- Dependencies: 1614 2146 1618
-- Name: object_keywords_keyword_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY object_keywords
    ADD CONSTRAINT object_keywords_keyword_id_fkey FOREIGN KEY (keyword_id) REFERENCES keywords(id);


--
-- TOC entry 2226 (class 2606 OID 22454)
-- Dependencies: 1620 1621 2159
-- Name: req_coverage_req_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY req_coverage
    ADD CONSTRAINT req_coverage_req_id_fkey FOREIGN KEY (req_id) REFERENCES requirements(id);


--
-- TOC entry 2222 (class 2606 OID 22410)
-- Dependencies: 2080 1619 1580
-- Name: req_specs_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY req_specs
    ADD CONSTRAINT req_specs_id_fkey FOREIGN KEY (id) REFERENCES nodes_hierarchy(id);


--
-- TOC entry 2223 (class 2606 OID 22415)
-- Dependencies: 1619 1599 2120
-- Name: req_specs_testproject_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY req_specs
    ADD CONSTRAINT req_specs_testproject_id_fkey FOREIGN KEY (testproject_id) REFERENCES testprojects(id);


--
-- TOC entry 2224 (class 2606 OID 22437)
-- Dependencies: 2080 1580 1620
-- Name: requirements_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY requirements
    ADD CONSTRAINT requirements_id_fkey FOREIGN KEY (id) REFERENCES nodes_hierarchy(id);


--
-- TOC entry 2225 (class 2606 OID 22442)
-- Dependencies: 1620 1619 2156
-- Name: requirements_srs_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY requirements
    ADD CONSTRAINT requirements_srs_id_fkey FOREIGN KEY (srs_id) REFERENCES req_specs(id);


--
-- TOC entry 2228 (class 2606 OID 22490)
-- Dependencies: 1580 1625 2080
-- Name: risk_assignments_node_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY risk_assignments
    ADD CONSTRAINT risk_assignments_node_id_fkey FOREIGN KEY (node_id) REFERENCES nodes_hierarchy(id);


--
-- TOC entry 2227 (class 2606 OID 22485)
-- Dependencies: 2098 1590 1625
-- Name: risk_assignments_testplan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY risk_assignments
    ADD CONSTRAINT risk_assignments_testplan_id_fkey FOREIGN KEY (testplan_id) REFERENCES testplans(id);


--
-- TOC entry 2230 (class 2606 OID 22507)
-- Dependencies: 1626 2166 1623
-- Name: role_rights_right_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY role_rights
    ADD CONSTRAINT role_rights_right_id_fkey FOREIGN KEY (right_id) REFERENCES rights(id);


--
-- TOC entry 2229 (class 2606 OID 22502)
-- Dependencies: 1626 2090 1586
-- Name: role_rights_role_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY role_rights
    ADD CONSTRAINT role_rights_role_id_fkey FOREIGN KEY (role_id) REFERENCES roles(id);


--
-- TOC entry 2196 (class 2606 OID 22011)
-- Dependencies: 1588 1589 2094
-- Name: tcversions_author_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY tcversions
    ADD CONSTRAINT tcversions_author_id_fkey FOREIGN KEY (author_id) REFERENCES users(id);


--
-- TOC entry 2195 (class 2606 OID 22006)
-- Dependencies: 1589 1580 2080
-- Name: tcversions_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY tcversions
    ADD CONSTRAINT tcversions_id_fkey FOREIGN KEY (id) REFERENCES nodes_hierarchy(id);


--
-- TOC entry 2197 (class 2606 OID 22016)
-- Dependencies: 1589 1588 2094
-- Name: tcversions_updater_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY tcversions
    ADD CONSTRAINT tcversions_updater_id_fkey FOREIGN KEY (updater_id) REFERENCES users(id);


--
-- TOC entry 2232 (class 2606 OID 22524)
-- Dependencies: 1614 1627 2146
-- Name: testcase_keywords_keyword_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY testcase_keywords
    ADD CONSTRAINT testcase_keywords_keyword_id_fkey FOREIGN KEY (keyword_id) REFERENCES keywords(id);


--
-- TOC entry 2231 (class 2606 OID 22519)
-- Dependencies: 2080 1627 1580
-- Name: testcase_keywords_testcase_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY testcase_keywords
    ADD CONSTRAINT testcase_keywords_testcase_id_fkey FOREIGN KEY (testcase_id) REFERENCES nodes_hierarchy(id);


--
-- TOC entry 2204 (class 2606 OID 22116)
-- Dependencies: 1596 1589 2096
-- Name: testplan_tcversions_tcversion_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY testplan_tcversions
    ADD CONSTRAINT testplan_tcversions_tcversion_id_fkey FOREIGN KEY (tcversion_id) REFERENCES tcversions(id);


--
-- TOC entry 2203 (class 2606 OID 22111)
-- Dependencies: 1596 1590 2098
-- Name: testplan_tcversions_testplan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY testplan_tcversions
    ADD CONSTRAINT testplan_tcversions_testplan_id_fkey FOREIGN KEY (testplan_id) REFERENCES testplans(id);


--
-- TOC entry 2198 (class 2606 OID 22033)
-- Dependencies: 1580 2080 1590
-- Name: testplans_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY testplans
    ADD CONSTRAINT testplans_id_fkey FOREIGN KEY (id) REFERENCES nodes_hierarchy(id);


--
-- TOC entry 2205 (class 2606 OID 22166)
-- Dependencies: 1599 1580 2080
-- Name: testprojects_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY testprojects
    ADD CONSTRAINT testprojects_id_fkey FOREIGN KEY (id) REFERENCES nodes_hierarchy(id);


--
-- TOC entry 2233 (class 2606 OID 22538)
-- Dependencies: 1580 1628 2080
-- Name: testsuites_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY testsuites
    ADD CONSTRAINT testsuites_id_fkey FOREIGN KEY (id) REFERENCES nodes_hierarchy(id);


--
-- TOC entry 2242 (class 2606 OID 22628)
-- Dependencies: 2094 1634 1588
-- Name: text_templates_author_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY text_templates
    ADD CONSTRAINT text_templates_author_id_fkey FOREIGN KEY (author_id) REFERENCES users(id);


--
-- TOC entry 2235 (class 2606 OID 22561)
-- Dependencies: 2094 1630 1588
-- Name: user_assignments_assigner_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_assignments
    ADD CONSTRAINT user_assignments_assigner_id_fkey FOREIGN KEY (assigner_id) REFERENCES users(id);


--
-- TOC entry 2234 (class 2606 OID 22556)
-- Dependencies: 2094 1588 1630
-- Name: user_assignments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_assignments
    ADD CONSTRAINT user_assignments_user_id_fkey FOREIGN KEY (user_id) REFERENCES users(id);


--
-- TOC entry 2246 (class 2606 OID 22664)
-- Dependencies: 1637 2094 1588
-- Name: user_group_assign_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_group_assign
    ADD CONSTRAINT user_group_assign_user_id_fkey FOREIGN KEY (user_id) REFERENCES users(id);


--
-- TOC entry 2245 (class 2606 OID 22659)
-- Dependencies: 2189 1636 1637
-- Name: user_group_assign_usergroup_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_group_assign
    ADD CONSTRAINT user_group_assign_usergroup_id_fkey FOREIGN KEY (usergroup_id) REFERENCES user_group(id);


--
-- TOC entry 2243 (class 2606 OID 22646)
-- Dependencies: 1636 2094 1588
-- Name: user_group_owner_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_group
    ADD CONSTRAINT user_group_owner_id_fkey FOREIGN KEY (owner_id) REFERENCES users(id);


--
-- TOC entry 2244 (class 2606 OID 22651)
-- Dependencies: 2120 1636 1599
-- Name: user_group_testproject_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_group
    ADD CONSTRAINT user_group_testproject_id_fkey FOREIGN KEY (testproject_id) REFERENCES testprojects(id);


--
-- TOC entry 2238 (class 2606 OID 22585)
-- Dependencies: 1631 1586 2090
-- Name: user_testplan_roles_role_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_testplan_roles
    ADD CONSTRAINT user_testplan_roles_role_id_fkey FOREIGN KEY (role_id) REFERENCES roles(id);


--
-- TOC entry 2237 (class 2606 OID 22580)
-- Dependencies: 2098 1590 1631
-- Name: user_testplan_roles_testplan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_testplan_roles
    ADD CONSTRAINT user_testplan_roles_testplan_id_fkey FOREIGN KEY (testplan_id) REFERENCES testplans(id);


--
-- TOC entry 2236 (class 2606 OID 22575)
-- Dependencies: 1631 1588 2094
-- Name: user_testplan_roles_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_testplan_roles
    ADD CONSTRAINT user_testplan_roles_user_id_fkey FOREIGN KEY (user_id) REFERENCES users(id);


--
-- TOC entry 2241 (class 2606 OID 22608)
-- Dependencies: 2090 1586 1632
-- Name: user_testproject_roles_role_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_testproject_roles
    ADD CONSTRAINT user_testproject_roles_role_id_fkey FOREIGN KEY (role_id) REFERENCES roles(id);


--
-- TOC entry 2240 (class 2606 OID 22603)
-- Dependencies: 1599 2120 1632
-- Name: user_testproject_roles_testproject_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_testproject_roles
    ADD CONSTRAINT user_testproject_roles_testproject_id_fkey FOREIGN KEY (testproject_id) REFERENCES testprojects(id);


--
-- TOC entry 2239 (class 2606 OID 22598)
-- Dependencies: 1588 2094 1632
-- Name: user_testproject_roles_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY user_testproject_roles
    ADD CONSTRAINT user_testproject_roles_user_id_fkey FOREIGN KEY (user_id) REFERENCES users(id);


--
-- TOC entry 2194 (class 2606 OID 21986)
-- Dependencies: 2090 1586 1588
-- Name: users_role_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_role_id_fkey FOREIGN KEY (role_id) REFERENCES roles(id);


--
-- TOC entry 2291 (class 0 OID 0)
-- Dependencies: 3
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


-- Completed on 2009-04-30 21:36:54

--
-- PostgreSQL database dump complete
--

