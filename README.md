# TestLink 1.9.17 (Prague Bugfix 17) Alan Turing - Read me

## Contents
 1. Introduction
 2. Release notes / Critical Configuration Notes
 3. System Requirements
 4. Installation & SECURITY
 5. Upgrade and Migration
 6. TestLink Team
 7. Bug Reports and Feedback
 8. Supporting our work
 9. Regarding forum usage www.testlink.org
10. Changes regarding 1.9.6,...,1.9.12,1.9.13,1.9.14,1.9.15,1.9.16
11. Testlink & FreeTest
12. Security
13. JIRA DB interface changes
14. People/Companies supporting TestLink
15. Use forum.testlink.org
16. User cries: I WANT HELP !!!
17. Use Mantis documentation
18. Link to GITORIOUS COMMITS

## 1. Introduction

TestLink is a web based test management and test execution system.
It enables quality assurance teams to create and manage their test 
cases as well as to organize them into test plans. These test plans 
allow team members to execute test cases and track test results 
dynamically.

TestLink is a GPL licensed open source project. All of the source code 
behind TestLink is freely available for download via [SourceForge][sou]
or [GitHub][hub]. If you are interested in contributing to the TestLink
effort feel free to contact us. There is no hidden fee - 100% free for
using!

In an ideal world, testing would be a pretty straightforward process.
A test team takes the product requirements, writes a test specification
document, reviews the tests, and then runs them all for each version of
the product. The team is composed of full-time staff, and everyone knows
exactly what is expected of them.

In practice, few organisations have that luxury. There is not time to run
all the tests on every product version - especially on fix-releases that
need to be rolled out quickly. Requirements are constantly changing, and
the tests have to be changed in step. Test staff come and go. There are
misunderstandings over who was supposed to run which tests, so some get
missed. Management suddenly wants a status update at seven in the evening.

In these situations you need the support of a test management tool, such
as TestLink. The purpose of TestLink is to answer questions such as:

- For which requirements do we still need to write or update test cases?
- Which tests do you want me to run for this version?
- How much progress have we made on testing this release?
- Which test cases are currently failing, and what are the errors?
- On which version was this group of test cases last run, and is it time we ran them again?
- And ultimately: is this version of the product fit for release?

TestLink helps you to keep the test process under control. It forms a
repository for requirements and test cases, and relates these to builds,
platforms and staff. You allocate tests to staff who carry them out and
record the results. A wide variety of reports provide information on what
has been done and what still needs to be done.

## 2. Release notes / CRITICAL Configuration Notes

This release contains bugfixes for 1.9.16
See CHANGELOG file for detailed list of issues fixed.

### CRITICAL PHP.INI Settings

#### max_input_vars

**Available since PHP 5.3.9. Default value: 1000**

If you are going to have test plans with more than 100 test cases, it will
be CRITICAL to increase this value in order to avoid issues such as CRASH
or MALFUNCTION when adding test cases to test plan.

See [this forum post on max_input_vars][frm] or [this mantis issue][bug] for
details.

#### memory_limit

**Default value: 128MB**

If you are going to re-import an XML file to update its test case data, the
system might run out of memory. [The original issue][mem] was resolved with
a `memory_limit` value of 256MB.

### Changes on LDAP CONFIGURATION

Since 1.9.16 authentication against [Multiple LDAP Servers][ldap] is supported.
To implement this feature configuration parameters have been changed, as explained
here:

#### TestLink Version < 1.9.16

    $tlCfg->authentication['method'] = 'LDAP';

    $tlCfg->authentication['ldap_server'] = 'ldap.xyz.com';
    $tlCfg->authentication['ldap_port'] = '389';
    $tlCfg->authentication['ldap_version'] = '3';
    $tlCfg->authentication['ldap_root_dn'] = 'dc=xyz,dc=com';
    $tlCfg->authentication['ldap_bind_dn'] = 'uid=tl,ou=staff,dc=xyz,dc=com';
    $tlCfg->authentication['ldap_bind_passwd'] = 'XYZw';
    $tlCfg->authentication['ldap_tls'] = false; // true -> use tls

#### TestLink Version >= 1.9.16

    $tlCfg->authentication['method'] = 'LDAP';

    $tlCfg->authentication['ldap'][1]['ldap_server'] = 'ldap.xyz.com';
    $tlCfg->authentication['ldap'][1]['ldap_port'] = '389';
    $tlCfg->authentication['ldap'][1]['ldap_version'] = '3';
    $tlCfg->authentication['ldap'][1]['ldap_root_dn'] = 'dc=xyz,dc=com';
    $tlCfg->authentication['ldap'][1]['ldap_bind_dn'] = 'uid=tl,ou=staff,dc=xyz,dc=com';
    $tlCfg->authentication['ldap'][1]['ldap_bind_passwd'] = 'XYZw';
    $tlCfg->authentication['ldap'][1]['ldap_tls'] = false;


## 3. System Requirements - server

Server environment should consist of:
- web-server: Apache 2.x
- PHP > 5.4
- DBMS: MySQL 5.6.x / MariaDB 10.1.x, Postgres 9.x, MS-SQL 2008/2012

Supported client web-browsers: 
- Firefox
- Internet Explorer 9.x or greater
- Chrome   

ATTENTION: we have not enough resources to test on all kind of browsers.
           Right now development is done using Chrome & Firefox.

## 4. Installation & SECURITY

The following details the basic steps for installation on any system. 
Instructions may seem unix-centric but should work on Windows systems.

Barring complications, it should take you about 10-20 minutes 
to install, configure, and start using TestLink.

Short summary:
 1. Transfer files
 2. Uncompress files
 3. Launch web based installer

1. First, transfer the file to your web-server using whatever method
you like best (ftp, scp, etc).

You will need to telnet/ssh into the server machine for the next steps.

2. Next, untar/gunzip it to the directory that you want.

The usual command is (1 step):

	tar zxvf <filename.tar.gz>

OR  (2 steps):

	gunzip <filename.tar.gz>
	tar xvf <filename.tar>

Total Commander, Winzip, and other programs should also be able 
to handle decompression of the archive.

At this point you may want to rename the directory to something 
different to 'testlink'. 

### SECURITY

You need to configure:

- log directory 	(`$tlCfg->log_path`)
- upload directory  (`$g_repositoryPath`)

According to your installation, default values provided. However, these are
examples **THAT DO NOT WORK OUT OF THE BOX**.

Take a look at [bug 5147][5147], [bug 5148][5148], [bug 4977][4977] and
[bug 4906][4906].

You should also need to configure write access for logging, upload and
template directories.

** FCKEDITOR UPLOAD **

**ATTENTION: We now use CKEDITOR** (see [forum post][cke])

3. Launch web based installer
We will create the necessary database tables and a basic configuration
file. From your web server, access http://yoursite/testlink/ 
or similar URL and follow instructions.

Check Installation manual and TestLink forum if you meet a problem.

## 5. Upgrade and Migration

When accessing Installer page you will find only the **new installation**
option. The migration **has to be done manually** for these special cases:

- Upgrade from 1.9.3 to 1.9.4/5/6/7/8/9/10/11/12/13/14/15/16/17
- Upgrade from 1.9.4/5 to 1.9.7
- Upgrade from 1.9.7 to 1.9.8
- Migration from other releases than 1.9.3

### General Steps
1. Make a backup of your current database.
2. Using a **new directory** (**DO NOT OVERWRITE** your old installation),
   do only following steps from Install procedure:
       - Transfer files
       - Uncompress files
 - Copy your old `config_db.inc.php` and `custom_config.inc.php` over to the
   **new directory**.
 - Launch TestLink
 - TestLink will check the database version. If some upgrade/migration is
   needed, it will launch automatically the installer.

 If you are updating a same major version (for example 1.7.0 to 1.7.1) you
 need to use *Upgrade Database*.

**Please look at [MANTIS 6594][6594]**: Migration scripts don't cover test
case steps and expected results, also test case ID's are empty in GUI

 If you are using a different major version detailed in options, you need
 to use the specific Migrations.

 If in some steps TestLink asks you for two databases, **never** use the
 same name for both.

 If you find nothing useful, post in the forum.

 Always before login, after an upgrade/migration, clear browser cookies.

### Special Cases

 1. Upgrade from 1.9.3 to 1.9.4/5/6/7/8/9/10/11/12/13/14/15/16/17

WARNING: if you are using a table prefix replace `prefix` with your prefix

  a. Execute `install/sql/alter_tables/1.9.4/<your_db>/DB.1.5/step1/db_schema_update.sql`
  b. Execute `install/sql/alter_tables/1.9.4/<your_db>/DB.1.5/stepZ/z_final_step.sql`

then look at sections: 'Upgrade from 1.9.4/5 to 1.9.7',
                       'Upgrade from 1.9.7 to 1.9.8'

**Hint**: When using MySQL Query Browser make sure you are not using single
          command execution. (open script or use special script tab to
          execute the whole script at once)

 2. Upgrade from 1.9.4/5 to 1.9.7

WARNING: if you are using a table prefix replace `prefix` with your prefix

  a. Execute `install/sql/alter_tables/1.9.6/<your_db>/DB.1.6/step1/db_schema_update.sql`
  b. Execute `install/sql/alter_tables/1.9.6/<your_db>/DB.1.6/stepZ/z_final_step.sql`

then look at sections: 'Upgrade from 1.9.4/5 to 1.9.7',
                       'Upgrade from 1.9.7 to 1.9.8',
                       'Upgrade from 1.9.8 to 1.9.9',
                       'Upgrade from 1.9.9 to 1.9.10',
                       'Upgrade from 1.9.10 to 1.9.11',
                       'Upgrade from 1.9.11 to 1.9.12',
                       'Upgrade from 1.9.12 to 1.9.13',
                       'Upgrade from 1.9.13 to 1.9.14',
                       'Upgrade from 1.9.14 to 1.9.15',
                       'Upgrade from 1.9.15 to 1.9.16',
                       'Upgrade from 1.9.16 to 1.9.17'

 3. Upgrade from 1.9.7 to 1.9.8

WARNING: if you are using a table prefix replace `prefix` with your prefix

  a. Execute `install/sql/alter_tables/1.9.8/<your_db>/DB.1.9.8/step1/db_schema_update.sql`
  b. Execute `install/sql/alter_tables/1.9.8/<your_db>/DB.1.9.8/stepZ/z_final_step.sql`

 4. Upgrade from 1.9.8 to 1.9.9

WARNING: if you are using a table prefix replace `prefix` with your prefix

  a. Execute `install/sql/alter_tables/1.9.9/<your_db>/DB.1.9.9/step1/db_schema_update.sql`
  b. Execute `install/sql/alter_tables/1.9.9/<your_db>/DB.1.9.9/stepZ/z_final_step.sql`

 5. Upgrade from 1.9.9 to 1.9.10

WARNING: if you are using a table prefix replace `prefix` with your prefix

  a. Execute `install/sql/alter_tables/1.9.10/<your_db>/DB.1.9.10/step1/db_data_update.sql`

 6. Upgrade from 1.9.10 to 1.9.11

WARNING: if you are using a table prefix replace `prefix` with your prefix

  a. Execute `install/sql/alter_tables/1.9.11/<your_db>/DB.1.9.11/step1/db_schema_update.sql`
  b. Execute `install/sql/alter_tables/1.9.11/<your_db>/DB.1.9.11/stepZ/z_final_step.sql`

 7. Upgrade from 1.9.11 to 1.9.12

WARNING: if you are using a table prefix replace `prefix` with your prefix

  a. Execute `install/sql/alter_tables/1.9.12/<your_db>/DB.1.9.12/step1/db_schema_update.sql`
  b. Execute `install/sql/alter_tables/1.9.12/<your_db>/DB.1.9.12/stepZ/z_final_step.sql`

 8. Upgrade from 1.9.12 to 1.9.13

WARNING: if you are using a table prefix replace `prefix` with your prefix

  a. Execute `install/sql/alter_tables/1.9.13/<your_db>/DB.1.9.13/step1/db_schema_update.sql`
  b. Execute `install/sql/alter_tables/1.9.13/<your_db>/DB.1.9.13/stepZ/z_final_step.sql`

 9. Upgrade from 1.9.13 to 1.9.14

WARNING: if you are using a table prefix replace `prefix` with your prefix

  a. Execute `install/sql/alter_tables/1.9.14/<your_db>/DB.1.9.14/step1/db_schema_update.sql`
  b. Execute `install/sql/alter_tables/1.9.14/<your_db>/DB.1.9.14/stepZ/z_final_step.sql`

10. Upgrade from 1.9.14 to 1.9.15

WARNING: if you are using a table prefix replace `prefix` with your prefix

  a. Execute `install/sql/alter_tables/1.9.15/<your_db>/DB.1.9.15/step1/db_schema_update.sql`
  b. Execute (IF EXISTS) `install/sql/alter_tables/1.9.15/<your_db>/DB.1.9.15/stepZ/z_final_step.sql`

11. Upgrade from 1.9.15 to 1.9.16

WARNING: if you are using a table prefix replace `prefix` with your prefix

  a. Execute `install/sql/alter_tables/1.9.16/<your_db>/DB.1.9.16/step1/db_schema_update.sql`
  b. Execute (IF EXISTS) `install/sql/alter_tables/1.9.16/<your_db>/DB.1.9.16/stepZ/z_final_step.sql`

12. Upgrade from 1.9.16 to 1.9.17

WARNING: if you are using a table prefix replace `prefix` with your prefix

  a. Execute `install/sql/alter_tables/1.9.17/<your_db>/DB.1.9.17/step1/db_schema_update.sql`
  b. Execute (IF EXISTS) `install/sql/alter_tables/1.9.17/<your_db>/DB.1.9.17/stepZ/z_final_step.sql`

**Hint**: When using MySQL Query Browser make sure you are not using single
          command execution. (open script or use special script tab to
          execute the whole script at once)

**USE THE [FORUM SECTION][upgf] and the [USER UPGRADE SECTION][uupg]**

13. Migration from other releases before 1.9.3

You have always have to migrate one by one to each version that is newer
than yours. Extreme example: migration from 1.7.4

    1.7.4 => 1.7.5 => 1.8.1 => 1.8.2 => 1.8.3 => 1.8.4 => 1.8.5 => 1.9.0
    1.9.0 => 1.9.1 => 1.9.2 => 1.9.3 => 1.9.4 => 1.9.5 => 1.9.6 => 1.9.7 =>
    1.9.8 => 1.9.9 => 1.9.10 => 1.9.11 => 1.9.12 => 1.9.13 => 1.9.14 =>
    1.9.15 => 1.9.16 => 1.9.17

You have to read carefully README and instructions (if any) provided by
installer. Sometimes version changes do not require actions on DB structure
or data.

## 6. TestLink Team

This list comprises people who have helped:

### Most Active on this release

  * Francisco Mancardi - Project lead, builds, core developer, contributors
                         code reviewer (well, really the One Man Band ;) )
  * Asiel Brumfield - Infrastructure

### Contributors and developers active on older releases

  * Maradana Amardeep - Leader of testlink-qa group effort on 1.9.5
  * Bruno de Paula Kinoshita - some work on API, CSRF, Turn Key Linux
  * Julian Krien - Leader of testlink-qa group effort on 1.9.1,1.9.2,1.9.3
  * Andreas Simon
  * Erik Eloff
  * Martin Havlat - Project lead, builds, infrastructure, developer
  * Andreas Morsing - core developer
  * Amit Khullar

### TestLink - QA Team - for 1.9.4

  * Romoy Headly - QA Manager
  * Sujata Verma
  * Damien Mathieu
  * Amardeep Maradana
  * Amit Khullar
  * Andreas Simon
  * Ngoc Vu
  * Biache Benoit

### TestLink - QA - for 1.9 RC1

  * Andreia Balani
  * Andreas Simon
  * Biache Benoit
  * James Bohnert
  * Micky Zhang
  * Rocky Yang

  * Masami Ichikawa - Automated Testing

  * Toshiyuki Kawanishi - Japanese localization, developer
  * Chad Rosen - (Originator - version 1.0.x)
  * Kevin Levy - Developer
  * Asiel Brumfield - Infrastructure, developer
  * Jason B. Archibald - Developer

  * Tools R Us - contributing team
  * Oscar Castroviejo - trackplus interface
  * Seweryn Plywaczyk - text area custom field
  * grdscarabe@grdscarabe.net and Alexandre Da Costa - French localization
  * Walter Giaquinto/Alessandro Lia	and bruno.busco@gmail.com - Italian localization
  * Alessandro Lia - Javascript and CSS advice.
  * Leonardo Molinari - Portuguese (Brazil) localization
  * Hélio Guilherme - Portuguese localization
  * jorgesf@jsf.jazztel.es - Spanish localization
  * Jonas Fleer - search test case by custom field on test projects
  * Lightbulb Technology Services Pvt. Ltd. - techpartners: import test cases from XLS file
    abhishek.kulkarni@gmail.com and amit.dixit@lbtp.co.in
  * Kester Mielke <kmielke@pironet-ndh.com> (execution tree colouring and counters by tc status)
  * Peter Rooms - Bug coloring and labeling according status using same colors as Mantis.
  * Eugenia Drosdezki
      * Move/copy multiple testcases
      * Access to content of docs folder on combo box
      * Multiselect OR keywords filter
  * Japanese Testing Engineer's Forum (TEF) in Japan
    Working Group of [TestLink Japanese Translation Project][tjp]

    Atsushi Nagata,       AZMA Daisuke,         Hiromi Nishiyama,
    Kaname Mochizuki,     Kaoru Nakamura,       Kunio Murakami,
    Lumina Nishihara,     Marino Suda,          Masahide Katsumata,
    Masami Ichikawa,      Masataka Yoneta,      Sadahiko Hantani,
    Shinichi Sugiyama,    Shinsuke Matsuki,     Shizuka Ban,
    Takahiro Wada,        Toshinori Sawaguchi,  Toshiyuki Kawanishi,
    Yasuhiko Okada,       Yoichi Kunihiro,      Yoshihiro Yoshimura,
    Yukiko Kajino         Yasuharu Nishi

### Code reuse

We try to follow as much as possible the following principle:

***Do not reinvent the wheel.***

We use code and documentation from other Open Source Systems
(see `CODE_REUSE` document for details).


## 7. Bug Reports and Feedback

You may contact [TestLink User Community Forum][tucf].

If you found this software useful for your company please
post in forum on section "Companies using TestLink".

To submit a bug or a feature, please use ONLY our [Mantis installation][mbug].

You can follow us on twitter [@TLOpenSource][twt]

## 8. Supporting our work

if you find TestLink useful, think about a donation to support our work.

Contact us at [testlink.forum@gmail.com][gmail]

You can donate using PayPal or Flattr.

## 9. Regarding forum usage www.testlink.org

PLEASE: read these short hints before you write a topic:

  - :!: Use search forum before you add a new question.
  - :!: Did you search User or Installation manual before?
  - :!: Don't use the forum as a Bug Tracker, use [Mantis][mbug].
  **Bug issues reported here will be DELETED**
  - :!: Consider that some issues are related to Apache, browser or database
        instead of TestLink. Use Google first.

## 10. Changes regarding 1.9.6,...,1.9.12,1.9.13,1.9.14,1.9.15,1.9.16

### 1.9.16
  - issues on step are saved on TestLink DB wth step ID
  - redmine integration: reported will be testlink user creating issue.
  - ADODB upgraded
  - Ckeditor upgraded
    ... and more (read CHANGELOG file)

### 1.9.15
  - plugin system by Collabnet
    ... and more (read CHANGELOG file)

### 1.9.14
  - proxy config available for MANTISSOAP & JIRASOAP Integration

### 1.9.13
  - new tag to allow inline images in test case summary, preconditions, and steps
  - new tag to allow inline images in test suite details
  - new tag to allow inline images in requirement scope
  - Test Step execution - Attachment management
  - Automatically copy linked bugs from previous execution to the new one
  - Export Test Spec - add option to export external ID WITH PREFIX
  - Improvements on JIRA integration:
    - user can set values on GUI for Components, Priorities, Versions, IssueTypes
    - getting domain values from JIRA.
    ... and more (read CHANGELOG file)

### 1.9.12
  - Test case relations
  - Improvements on Issue Tracker integration (edit notes when linking)
  - Requirements Overview performance improvements

### 1.9.10
  - Long-awaited feature: execution notes & results for test steps

### 1.9.9
  - User can have two different (mutually exclusive) kinds of authentication

### 1.9.6
  - Admin role can not be edit any more

### 1.9.7
  - Reports do not use Custom fields any more:
    - `CF_ESTIMATED_EXEC_TIME`
    - `CF_EXEC_TIME`

   Specific columns have been added to tcversions and executions tables.

  - Smarty 3 is the default.


## 11. Testlink & FreeTest

There is a project in Brazil regarding the development of a method/process
for testing and delivery, focused on providing a method suitable for micro/mini companies.

If you are interested you can [get some info][free]:

[free]: http://www.freetest.net.br

## 12. Security

### 1.9.15
  - Multiple XSS and Blind SQL Injection by
    Netsparker Web Application Security Scanner.
    They have also provided a free account.

### 1.9.12
  - Research team of Portcullis Computer Security Ltd
    cedric (mantis.testlink.org user name)

### 1.9.10
  - We want to thank xistence (xistence@0x90.nl) for his tests.

## 13. JIRA DB interface changes

  - TICKET 6028: Integration with Jira 6.1 broken.
    (Due to JIRA schema changes)
    Contribution by adnkoks

You need to change your xml configuration in TestLink to add a **new MANDATORY
property**:

    <jiraversion></jiraversion>

Without this property TestLink **WILL CRASH** => this is a desired behaviour

## 14. People/Companies supporting TestLink

  - Bitnami: provided a VM on Cloud to do tests

  - Team Cortado (Germany): paid for custom development of a long-awaited
    feature: execution notes & results for test steps, **donating** feature
    to community (it is not the first time they are doing this!)

  - MAMP PRO

  - [Hitek School][hitek]: Group of students helped to test TestLink

  - [Wellington Institute of Technology][welt]: Group of students working on
    creating automation infrastructure to test TestLink

  - [CSRF Prevention Cheat Sheet][csrf]

## 15. Use forum.testlink.org

Information has been collected with users' help

[FAQ & HINTS][faqh]

[TestLink 1.9.4 and greater, News, changes, etc][194n]

[How to get the answer (self service)][howa]

## 16. User cries: I WANT HELP !!!

Relax, as usual I've to say the resources are limited,
that this effort is not supported by a company or a foundation
but is result of usage of free time.

Guidelines for getting help and/or solving a situation are what I use everyday:
First try for yourself searching on:
  - mantis.testlink.org
  - forum.testlink.org
  - https://github.com/TestLinkOpenSourceTRMS/testlink-documentation/wiki/Execution-Feature---Configuration
  - https://github.com/TestLinkOpenSourceTRMS/testlink-documentation/wiki/Execution-Feature---Test-Step-Execution-configuration

Please do not operate on lazy mode: just asking.
First thing will be always asked will be:
- have you already did some searches ?

When you report a potential issue on a TestLink version,
first thing that will be requested will be the 30minTest:
- get latest code from github, do fresh install, retest & provide feedback.

Do not send PRIVATE email to ask for things that have to be PUBLIC, this is
a bad approach. Use PRIVATE CHANNELS only on Dev Team Request.

If you need more specialized help, it can be provided if you pay for it.

## 17. Use Mantis documentation

[CHANGE LOG][chgl]

[TICKET][7817] with available fixes for latest stable version (1.9.16)

## 18. Link to GITORIOUS COMMITS

Some time ago we **migrated from Gitorious** (thanks a lot for all the years
of free repo) **to Github**. On tickets or documentation that belong to the
Gitorious era, you will find **links to commits that are not accessible any
more as-is**.

But accessing **the same commits in Github** (the commit IDs do not change) is
just a matter of understanding **how to change the URL Part** that is present
BEFORE the commit ID. => then Nike => Just DO IT

[sou]: https://sourceforge.net/projects/testlink/
[hub]: https://github.com/TestLinkOpenSourceTRMS/testlink-code/
[frm]: http://forum.testlink.org/viewtopic.php?f=11&t=7124&p=17284&sid=e3552aca223ac1f6b3676812aa02f04c#p17284
[bug]: http://mantis.testlink.org/view.php?id=5372
[mem]: http://mantis.testlink.org/view.php?id=7178
[ldap]: http://mantis.testlink.org/view.php?id=2842
[cke]: http://forum.testlink.org/viewtopic.php?f=22&t=7098&sid=f4012a67d921cf5a2322c52fc38f21d6
[6594]: http://mantis.testlink.org/view.php?id=6594
[5147]: http://mantis.testlink.org/view.php?id=5147
[5148]: http://mantis.testlink.org/view.php?id=5148
[4977]: http://mantis.testlink.org/view.php?id=4977
[4906]: http://mantis.testlink.org/view.php?id=4906
[upgf]: http://forum.testlink.org/viewforum.php?f=11
[uupg]: http://forum.testlink.org/viewforum.php?f=58
[tucf]: http://www.testlink.org/
[mbug]: http://www.testlink.org/mantis/
[twt]: http://twitter.com/#!/TLOpenSource
[free]: http://www.freetest.net.br
[csrf]: https://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF)_Prevention_Cheat_Sheet
[hitek]: http://www.hitekschool.com/
[welt]: www.weltec.ac.nz
[faqh]: http://forum.testlink.org/viewforum.php?f=14
[194n]: http://forum.testlink.org/viewforum.php?f=25
[howa]: http://forum.testlink.org/viewtopic.php?f=50&t=7798
[chgl]: http://mantis.testlink.org/changelog_page.php
[7817]: http://mantis.testlink.org/view.php?id=7817
[gmail]: mailto:testlink.forum@gmail.com
[tjp]: http://sourceforge.jp/projects/testlinkjp/
