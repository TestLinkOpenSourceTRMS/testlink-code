<?php
/** -------------------------------------------------------------------------------------
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/
 * 
 * Filename $RCSfile: description.php,v $
 * @version $Revision: 1.3 $
 * @modified $Date: 2010/06/24 17:25:55 $ $Author: asimon83 $
 * @author Martin Havlat
 *
 * LOCALIZATION:
 * === English (en_GB) strings === - default development localization (World-wide English)
 *
 * @ABSTRACT
 * The file contains global variables with html text. These variables are used as 
 * HELP or DESCRIPTION. To avoid override of other globals we are using "Test Link String" 
 * prefix '$TLS_hlp_' or '$TLS_txt_'. This must be a reserved prefix.
 * 
 * Contributors:
 * Add your localization to TestLink tracker as attachment to update the next release
 * for your language.
 *
 * No revision is stored for the the file - see CVS history
 * The initial data are based on help files stored in gui/help/<lang>/ directory. 
 * This directory is obsolete now. It serves as source for localization contributors only. 
 *
 * ---------------------------------------------------------------------------------- 
 * Korean translation
 *-------------------------------------------------------------------
 * Translated by Jiun PARK
 * (DQA Team, OPENTECH INC. R&D Center)
 * E-mail : rustyheart@gmail.com
 * Issued Date : 2009/05/27
 *
 *-------------------------------------------------------------------
 */

// printFilter.html
$TLS_hlp_generateDocOptions = "<h2>문서 생성의 옵션</h2>

<p>사용자는 문서를 보기전에 이 테이블을 사용하여 테스트 케이스를 선택 할 수 있습니다. 
선택한(체크한) 자료는 표시가 됩니다. 표시될 자료를 변경하려면, 필터를 선택 또는 해제한 후 
트리에서 원하는 레벨의 자료를 클릭하시면 됩니다.</p>

<p><b>문서 머릿말 : </b> 문서의 머릿말 정보를 선택할 수 있습니다.  
문서의 머릿말 정보에는 다음과 같은 것들이 있습니다 : 소개, 범위, 참고자료, 
테스트 방법론, 테스트 제약사항.</p>

<p><b>테스트 케이스 본문 : </b> 테스트 케이스의 본문 정보를 선택할 수 있습니다. 테스트 케이스 
본문 정보는 다음과 같은 것들이 있습니다 : 요약, 실행순서, 예상결과, 키워드.</p>

<p><b>테스트 케이스 요약 : </b> 사용자는 테스트 케이스 제목에 있는 테스트 케이스 
요약정보를 선택할 수 있지만, 테스트 케이스 본문에 있는 테스트 케이스 요약 정보는 선택할 
수 없습니다. 테스트 케이스 요약은 간단한 요약과 함께 제목을 보는 것을 지원하고, 
실행순서, 예상결과, 키워드를 생략할 수 있도록 하기 위해 부분적으로 테스트 케이스 본문에서 
분리 되어 있습니다. 만약 사용자가 테스트 케이스 본문을 보겠다고 선택하면, 테스트 케이스 
요약은 항상 포함됩니다.</p>

<p><b>목차 : </b> 이 값을 선택하면 TestLink는 모든 제목을 링크를 걸어서 표시합니다.</p>

<p><b>문서 형식 : </b> 다음 세 가지 형식이 가능합니다 : HTML, OpenOffice Writer, MS Word. HTML을 제외한 
나머지는 브라우저가 해당 S/W 컴포넌트를 호출합니다.</p>";

// testPlan.html
$TLS_hlp_testPlan = "<h2>테스트 계획</h2>

<h3>개요</h3>
<p>테스트 계획은 소프트웨어와 같은 시스템을 테스트 하기 위한 체계적인 접근이다. 적기에 제품의 특정 빌드에 대한
테스트 활동을 조직하고 결과를 추적할 수 있습니다.</p>

<h3>테스트 실행</h3>
<p>이 섹션에서 사용자는 테스트 케이스를 실행(결과를 기재)하고 테스트 계획을 인쇄할 수 있습니다. 
그리고 이 곳에서 사용자는 자신의 테스트 케이스 실행 결과를 추적할 수 있습니다.</p> 

<h2>테스트 계획 관리</h2>
<p>리드 권한이 있는 사용자는, 이 섹션에서 테스트 계획을 관리할 수 있습니다. 
테스트 계획의 관리는 계획을 생성/편집/삭제하고, 계획에 테스트 케이스들을 
추가/편집/삭제/업데이트 하고, 빌드들을 생성하는 것으로 구성 되어 있습니다. <br />
리드 권한이 있는 사용자는 우선순위/위험도와 테스트 스위트의 담당자 지정, 테스팅 
마일스톤 지정을 할 수 있습니다.</p> 

<p>노트: 드롭다운 상자에 어떤 테스트 계획도 표시 되지 않을 수 있습니다. 
이런 경우 리드 권한이 있는 사람이 아니면 아래 링크를 사용할 수가 없습니다. 
당신이 지금 이런 상황이라면 적절한 프로젝트 권한을 가지고 있거나 테스트 계획을 
생성할 수 있는 리드 또는 관리자에게 연락 하시기 바랍니다.</p>"; 

// custom_fields.html
$TLS_hlp_customFields = "<h2>사용자 필드</h2>
<p>다음은 사용자 필드의 정의에 대한 몇가지 사실 입니다 : </p>
<ul>
<li>사용자 필드는 시스템 전체에 걸져 정의 됩니다.</li>
<li>사용자 필드는 테스트 스위트나 테스트 케이스와 같은 곳에 연결 됩니다.</li>
<li>사용자 필드는 여러 테스트 프로젝트에 연결 될 수 있습니다.</li>
<li>사용자 필드의 표시순서는 테스트 프로젝트에 따라 다르게 할 수 있습니다.</li>
<li>사용자 필드는 특정 테스트 프로젝트에서 사용안함으로 바뀔 수 있습니다.</li>
<li>사용자 필드의 개수는 제한 없습니다.</li>
</ul>

<p>사용자 필드의 정의에는 다음과 같은 논리적인 속성이 포함되어 있습니다. : </p>
<ul>
<li>사용자 필드 이름</li>
<li>변수 이름 (예: 이것은 lang_get API에 의해 제공되는 값이거나, 언어 파일에서 찾지 
못할 경우 그대로 표시 되는 값입니다).</li>
<li>사용자 필드 종류 (string, numeric, float, enum, email)</li>
<li>열거 가능한 값들 (예: RED|YELLOW|BLUE), 목록, 다중 선택 가능한 목록,  
콤보 박스들.<br />
<i>열거 가능한 값들을 구분하기 위해 파이프 문자('|')를 사용할 수 있습니다. 
값들 중 하나는 공백 문자열을 사용할 수도 있습니다.</i>
</li>
<li>기본 값 : *아직 구현되지 않았습니다*</li>
<li>사용자 필드 값의 최소/최대값 (0은 사용안함 입니다). (*아직 구현되지 않았습니다*)</li>
<li>사용자의 입력을 검증하기 위한 정규직 (<a href=\"http://au.php.net/manual/en/function.ereg.php\">ereg()</a>
 문법 사용). <b>(*아직 구현되지 않았습니다*)</b></li>
<li>모든 사용자 필드들은 DB에 VARCHAR(255) 형식으로 저장됩니다.</li>
<li>테스트 명세에서 표시.</li>
<li>테스트 명세에서 사용. 사용자는 테스트 케이스 명세를 설계하면서 사용자 필드의 값을 변경할 수 있습니다.</li>
<li>테스트 실행에서 표시.</li>
<li>테스트 실행에서 사용. 사용자는 테스트 실행 도중 사용자 필드의 값을 변경할 수 있습니다.</li>
<li>테스트 계획 설계에서 표시.</li>
<li>테스트 계획 설계에서 사용. 사용자는 테스트 계획을 설계하면서 사용자 필드의 값을 변경할 수 있습니다 (테스트 계획에 테스트 케이스 추가)</li>
<li>사용할 곳. 사용자는 사용자 필드를 어떤 항목 아래에서 사용할 지 선택할 수 있습니다.</li>
</ul>
";

// execMain.html
$TLS_hlp_executeMain = "<h2>테스트 케이스 실행하기</h2>
<p>사용자가 테스트 케이스를 '실행' 할 수 있습니다. 실행은 선택한 빌드의 테스트 케이스에 
결과(통과, 실패, 중단)를 지정하는 것을 말합니다.</p>
<p>설정이 되어 있다면 버그 추적 시스템에 접속이 가능합니다. 사용자는 새 버그를 직접 
추가하고 검색할 수 있습니다.</p>";

//bug_add.html
$TLS_hlp_btsIntegration = "<h2>테스트 케이스에 버그 추가하기</h2>
<p><i>(설정 되어 있을 경우에만)</i>
TestLink는 버그 추적 시스템(BTS)와 아주 단순한 수준으로 통합합니다. 
즉, BTS에 버그 생성 요구를 보낼 수도 없고, 버그 ID값을 받아 올 수도 없습니다. 
이 통합은 BTS의 페이지에 대한 링크를 사용하는 것을 말하며, 다음과 같은 기능이 있습니다 : 
<ul>
	<li>새 버그 추가하기.</li>
	<li>기존 버그 정보 표시하기. </li>
</ul>
</p>  

<h3>버그를 추가하는 순서</h3>
<p>
   <ul>
   <li>1 단계 : 새 버그를 추가하기 위해 BTS 열기 링크를 사용합니다. </li>
   <li>2 단계 : BTS에서 할당된 BUGID를 적습니다.</li>
   <li>3 단계 : 입력 필드에 BUGID를 적습니다.</li>
   <li>4 단계 : 버그 추가 버튼을 클릭합니다.</li>
   </ul>  

버그 추가 화면을 닫으면, 실행 화면에 관련 버그가 표시 됩니다.
</p>";

// execFilter.html
$TLS_hlp_executeFilter = "<h2>테스트 실행을 위한 필터와 빌드 설정하기</h2>

<p>왼쪽 화면은 네비게이터, 현재 테스트 계획에 지정된 테스트 케이스들과 필터 & 설정" .
"으로 이루어져 있습니다. 이 필터들은 사용자가 실행하기 전 제공된 테스트 케이스를 " .
"구별하기 위해 사용할 수 있습니다." .
"필터를 설정하고, \"적용\" 버튼을 누르면 트리 메뉴에 적절한 테스트 케이스들이 " .
"선택됩니다.</p>

<h3>빌드</h3>
<p>사용자는 테스트 결과와 연결될 빌드를 반드시 선택해야 합니다. " .
"빌드는 현재 테스트 계획의 기본 요소입니다. 각각의 테스트 케이스는 " .
"빌드에서 여러번 실행될 수 있습니다. 하지만 마지막 결과는 하나 입니다.
<br />빌드는 새 빌드 생성 화면에서 리드가 생성할 수 있습니다.</p>

<h3>테스트 케이스 ID 필터</h3>
<p>사용자는 유일한 ID로 테스트 케이스를 선택할 수도 있습니다. 이 ID는 
테스트 케이스를 생성할 때 자동으로 부여 됩니다. 이 필드를 공란으로 두면 
필터를 적용하지 않겠다는 의미가 됩니다.</p> 

<h3>우선순위 필터</h3>
<p>사용자는 우선순위를 사용하여 테스트 케이스들을 선택할 수 있습니다. 각각의 테스트 
케이스 중요도는 현재 테스트 계획의 테스트 긴급도와 함께 조합됩니다. 예를 들어, 
중요도 또는 긴급도가 높음이고 두번째 속성이 적어도 보통 레벨이면, 이  테스트 케이스의 
우선순위는 '높음'이 됩니다. </p> 

<h2>결과 필터</h2>
<p>사용자는 결과에 따라 테스트 케이스를 선택할 수 있습니다. 결과는 개별 빌드의 테스트 
케이스에 일어난 것을 말합니다. 테스트 케이스는 통과, 실패, 중단 또는 실행안함이 될 수 
있습니다." .
"이 필터의 기본값은 모두 선택 입니다.</p>

<h3>테스터 필터</h3>
<p>사용자는 담당자에 따라 테스트 케이스를 선택할 수 있습니다. \"지정안된 테스트 케이스들 
포함\" 체크상자를 사용하면 담당자가 없는 테스트 케이스도 포함할 수 있습니다.</p>";
/*
<h2>Most Current Result</h2>
<p>By default or if the 'most current' checkbox is unchecked, the tree will be sorted 
by the build that is chosen from the dropdown box. In this state the tree will display 
the test cases status. 
<br />Example: User selects build 2 from the dropdown box and doesn't check the 'most 
current' checkbox. All test cases will be shown with their status from build 2. 
So, if test case 1 passed in build 2 it will be colored green.
<br />If the user decideds to check the 'most current' checkbox the tree will be 
colored by the test cases most recent result.
<br />Ex: User selects build 2 from the dropdown box and this time checks 
the 'most current' checkbox. All test cases will be shown with most current 
status. So, if test case 1 passed in build 3, even though the user has also selected 
build 2, it will be colored green.</p>
 */


// newest_tcversions.html
$TLS_hlp_planTcModified = "<h2>연결된 테스트 케이스들의 새 버전들</h2>
<p>테스트 계획에 연결된 모든 테스트 케이스들을 분석하여, 현재 테스트 계획에 
포함되지 않은 새 버전이 있는 테스트 케이스들을 표시합니다.
</p>";


// requirementsCoverage.html
$TLS_hlp_requirementsCoverage = "<h3>요구사항 커버리지</h3>
<br />
<p>이 기능을 사용하여 요구사항과 테스트 케이스를 매핑할 수 있습니다. 
메인화면에서 \"요구사항 명세\"를 선택하세요.</p>

<h3>요구사항 명세</h3>
<p>요구사항은 테스트 프로젝트와 관련된 '요구사항 명세' 문서들의 모음입니다.<br /> 
TestLink는 요구사항 명세와 요구사항 자체에 대해 버전을 지원하지 않습니다. 그래서, 
명세의 <b>제목</b>에 문서의 버전을 기재하는 방법을 사용하시기 바랍니다. 
사용자는 <b>범위</b> 필드에 간단한 설명이나 노트를 남길 수 있습니다.</p> 

<p><b><a name='total_count'>모든 요구사항의 개수</a></b>는 모든 요구사항이 TestLink에 
추가 되지 않았을 경우, 요구사항의 커버리지를 평가하기 위해 제공됩니다. 이 값이 <b>0</b> 
이면 TestLink에 등록된 요구사항의 현재 개수가 매트릭에 사용됩니다. </p> 
<p><i>예제) SRS에 모두 200개의 요구사항이 있는데, 그 중 50개만 TestLink에 추가 되었습니다. 
추가된 50개의 요구사항이 모두 테스트 되었다고 가정하면, 테스트 커버리지는 25%가 됩니다.</i></p>

<h3><a name=\"req\">요구사항</a></h3>
<p>생성된 요구사항 명세의 제목을 클릭하세요. 당신은 생성, 편집, 삭제, 문서로부터 
요구사항 가져오기를 할 수 있습니다. 각각의 요구사항은 제목, 범위, 상태를 가집니다. 
상태는 \"보통\" 또는 \"테스트 할 수 없음\" 중 하나 입니다. 테스트 할 수 없는 요구사항은 
매트릭 계산에 포함 되지 않습니다. 이 파라미터는 구현되지 않은 기능들과 잘 못 설계된 
요구사항들에도 사용됩니다.</p> 

<p>당신은 명세 화면에서 요구사항들을 여러개 선택하여 새로운 테스트 케이스들을 
생성할 수 있습니다. 이렇게 생성된 테스트 케이스들은 환경설정에 정의된 이름의 
테스트 스위트에 포함됩니다. <i>(기본값 : &#36;tlCfg->req_cfg->default_testsuite_name = 
\"Test suite created by Requirement - Auto\";)</i>. 제목과 범위가 테스트 케이스로 복사 됩니다.</p>
";


// planAddTC_m1.tpl
$TLS_hlp_planAddTC = "<h2>'사용자 필드 저장'에 관련하여</h2>
사용자 필드를 정의하여 테스트 프로젝트에 지정하려면 : <br />
 '테스트 계획 설계에 표시=예'<br />
 '테스트 계획 설계에 사용=예'<br />
이 사용자 필드는 테스트 계획에 연결된 테스트 케이스들에게만 표시 됩니다.
";

// xxx.html
//$TLS_hlp_xxx = "";

// ----- END ------------------------------------------------------------------
?>