<?php
/**
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: texts.php,v $
 * @version $Revision: 1.5 $
 * @modified $Date: 2010/06/24 17:25:53 $ $Author: asimon83 $
 * @modified $Date: 2010/06/24 17:25:53 $ by $Author: asimon83 $
 * @author Martin Havlat and reviewers from TestLink Community
 *
 * --------------------------------------------------------------------------------------
 *
 * Scope:
 * English (en_GB) texts for help/instruction pages. Strings for dynamic pages
 * are stored in strings.txt pages.
 *
 * Here we are defining GLOBAL variables. To avoid override of other globals
 * we are using reserved prefixes:
 * $TLS_help[<key>] and $TLS_help_title[<key>]
 * or
 * $TLS_instruct[<key>] and $TLS_instruct_title[<key>]
 *
 *
 * Revisions history is not stored for the file
 *
 * ------------------------------------------------------------------------------------ */
$TLS_htmltext_title['error']	= "Application error";
$TLS_htmltext['error'] 		= "<p>Unexpected error happens. Please check event viewer or " .
		"logs for details.</p><p>You are welcome to report the problem. Please visit our " .
		"<a href='http://www.teamst.org'>website</a>.</p>";



$TLS_htmltext_title['assignReqs']	= "分配需求给测试用例"; //已校对
$TLS_htmltext['assignReqs'] 		= "<h2>目的:</h2>
<p>用户可以设置测试套件和需求规约之间的关系. 设计者可以把此处的测试套件和需求规约一一关联
.例如:一个测试用例可以被关联到零个、一个、多个测试套件,反之亦然.
这些可追踪的模型帮助我们去研究测试用例对需求的覆盖情况,并且找出测试用例是否通过的情况.这些分析用来验证测试的覆盖程度是否达到预期的结果。</p>

<h2>开始:</h2>
<ol>
	<li>在左边的树状图中选择一个测试用例.工作区的上方列出了所有需求规约的选择框.</li>
	<li>如果有多个需求规约文档的话，从中选择一个.
        然后TestLink会自动加载关于该需求的页面.</li>
	<li>工作区中间会列出所有测试需求(对应于选择的需求规约),这些测试需求会关联到相应的测试用例.
        底部的'有效的需求'列出了所有尚未关联到当前测试用例的需求.
	    测试设计者可以点击'指派'按钮把需求指派到测试用例.这些新关联的
        测试用例会在工作区中间的'已指派的需求'中显示.</li>
</ol>";


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['editTc']	= "测试规范"; //已校对
$TLS_htmltext['editTc'] 		= "<h2>目的:</h2>
<h2>目的:</h2>
<p> <i>测试规范</i> 允许用户查看和编辑所有现有的" .
		"<i>测试套件</i> 和 <i>测试用例</i>. 测试用例默认使用当前版本.".
		"所有以前的历史版本都是可用的,并且可以在这里进行查看和管理.</p>

<h2>开始:</h2>
<ol>
	<li>从右上角的下拉菜单中选择你的测试项目. <i>注意: " .
	"你永远可以从右上角的下拉菜单选择改变当前的测试项目." .
	".</i></li>
    <li>点击\"测试规范\",然后从中选择一个测试套件</li>
	<li>点击 <b>新建测试套件</b>将创建一个新的测试套件的子集. " .
	"测试套件子集可以为你的测试文档归类,归类可以是按照你的需要来进行(功能/非功能,  产品部件, 产品功能, 需求更改, 等等)." .
	"测试套件子集的描述中中包含了相关的测试用例的作用域,默认的系统配置信息等,他们还可能包含和其他一些文档资料链接, 测试局限性, 或者其他信息.通常这些注释是测试套件子集所共同具有的. 他们构成了一个测试套件的文件夹的概念,测试套件子集是可以扩充的文件夹. 用户可以在同一个测试计划里移动或者复制它们.同时, 他们可以作为一个整体(包括其中的测试用例)输出或者输入到其他格式." .".</li>
	<li>在导航树中选择一个刚创建的新的测试套件" .
	"然后点击<b>创建测试用例</b>. 就可以在这个测试套件子集里创建一个新的测试用例." .
	"一个测试用例定义了一个特有的测试过程,它包括测试的环境, 步骤, 期望的结果, 测试项目中的自定义字段(参见用户手册), 还可以给测试用例指派一个" .
	"<b>关键字</b> 以方便跟踪查询.</li>
	<li>从左边的导航树里选择和编辑数据来实现导航功能. 测试用例可以保存自己的所有历史.</li>
	<li>测试用例编写完毕后, 你可以把它的测试规范关联到 <span class=\"help\" onclick=
	\"javascript:open_help_window('glosary','$locale');\">测试计划</span> .</li>
</ol>

    <p>TestLink可以帮你整理测试套件,可以把测试套件分类成为不同的测试套件子集. 测试套件子集还可以包含更下级的测试案例子集. 
       因此你可以把这些所有的信息打印成册." ."</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchTc']	= "测试用例搜索页"; //已校对
$TLS_htmltext['searchTc'] 		= "<h2>目的:</h2>

<p>按照关键字和搜索字符串来进行搜索. 英文搜索是不区分大小写. 结果只包括当前测试项目中已有的测试用例.</p>

<h2>搜索:</h2>

<ol>
	<li>在搜索栏中输入搜索字符串.不用的搜索框留空.</li>
	<li>选择必须的关键字或者让该栏目留空为'不使用'.</li>
	<li>点击“查找”.</li>
	<li>所有符合搜索条件的测试用例就会显示出来. 你可以点击'标题'链接开始对测试用例进行其它操作.</li>
</ol>";














/* contribution by asimon for 2976 */
// requirements search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReq']	= "Requirement Search Page";
$TLS_htmltext['searchReq'] 		= "<h2>Purpose:</h2>

<p>Navigation according to keywords and/or searched strings. The search is not
case sensitive. Result includes just requirements from actual Test Project.</p>

<h2>To search:</h2>

<ol>
	<li>Write searched string to an appropriate box. Leave unused fields in form blank.</li>
	<li>Choose required keyword or leave value 'Not applied'.</li>
	<li>Click the 'Find' button.</li>
	<li>All fulfilling requirements are shown. You can modify requirements via 'Title' link.</li>
</ol>

<h2>Note:</h2>

<p>- Only requirements within the current project will be searched.<br>
- The search is case-insensitive.<br>
- Empty fields are not considered.</p>";

// requirement specification search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReqSpec']	= "Requirement Specification Search Page";
$TLS_htmltext['searchReqSpec'] 		= "<h2>Purpose:</h2>

<p>Navigation according to keywords and/or searched strings. The search is not
case sensitive. Result includes just requirement specifications from actual Test Project.</p>

<h2>To search:</h2>

<ol>
	<li>Write searched string to an appropriate box. Leave unused fields in form blank.</li>
	<li>Choose required keyword or leave value 'Not applied'.</li>
	<li>Click the 'Find' button.</li>
	<li>All fulfilling requirements are shown. You can modify requirement specifications via 'Title' link.</li>
</ol>

<h2>Note:</h2>

<p>- Only requirement specifications within the current project will be searched.<br>
- The search is case-insensitive.<br>
- Empty fields are not considered.</p>";
/* end contribution */


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printTestSpec']	= "打印需求规约"; //printTC.html //已校对
$TLS_htmltext['printTestSpec'] 			= "<h2>目的:</h2>
<p>在这里你可以打印单个测试用例，测试套件中的所有测试用例或者测试项目或测试计划中的所有测试用例.</p>
<h2>开始:</h2>
<ol>
<li>
<p>选择你希望显示的测试用例,点击一个测试用例、测试套件或者测试项目.
一个可打印的页面就会显示出来.</p>
</li>
<li><p>使用导航栏下拉框中的\"Show As\" 来决定把信息显示成HTML、OpenOffice或者Word文档. 更多信息请查看:<span class=\"help\" onclick=\"javascript:open_help_window('printFilter','{$locale}');\">帮助</span> .</p>
</li>
<li><p>使用浏览器的打印功能来输出信息.<br />
 <i>注意:保证只打印右边的框架.</i></p></li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['reqSpecMgmt']	= "需求规约设计"; //printTC.html //已校对
$TLS_htmltext['reqSpecMgmt'] 			= "<p>你可以管理需求规约文档.</p>

<h2>需求规约</h2>

<p>需求是由<b>需求规约文档</b>来约定的,然后关联到测试项目.
<br /> TestLink(当前版本)暂时还不支持需求规约版本中包含了需求本身的情况. 
所以，文档的版本必须在需求规约之后创建<b>标题</b>.
用户可以添加简单描述到 <b>范围</b> 区域.</p>

<p><b><a name='total_count'>需求覆盖数目</a></b> 
是为了统计需求覆盖率而使用的,如果不把所有的需求提交到TestLink管理，<b>0</b>那么当前结果分析中需求的数量以TestLink管理起来的需求为依据.</p>
<p><i>例如: SRS项目中包括200个需求,但是只有50个需求被TestLink管理起来.那么测试覆盖率就是25%(如果所有的测试需求被测试的情况下).</i></p>

<h2><a name='req'>需求</a></h2>

<p>点击已创建的需求规约，如果项目中还不存在需求规约先创建一个.然后你就可以为这个需求规约创建具体的需求。每个需求包括标题，范围和当前状态.需求的状态可以是'有效的'或者'不可测试的'.不可测试的需求在结果分析时不被计入统计数据。这个参数可以被用来设置那些不可实施的功能特点或者错误的需求.</p>

<p>用户可以在需求界面中使用已创建的需求自动创建测试用例.这些测试用例被创建到名字定义在配置文件<i>(default is: \$tlCfg->req_cfg->default_testsuite_name ='Test suite created by Requirement - Auto';)</i>
中的测试套件中. 标题和范围被复制到测试用例中.</p>";

















// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printReqSpec'] = "Print Requirement Specification"; //printReq
$TLS_htmltext['printReqSpec'] = "<h2>Purpose:</h2>
<p>You can generate document with the requirements within a requirement specification,
or all the requirements in a test project.</p>
<h2>Get Started:</h2>
<ol>
<li>
<p>Select the parts of the requirements you want to display, and then click on a  
requirement specification, or the test project. A printable page will be displayed.</p>
</li>
<li><p>Use the \"Show As\" drop-box in the navigation pane to specify whether you want 
the information displayed as HTML, or in a Pseudo Micosoft Word document. 
See <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">help</span> for more information.</p>
</li>
<li><p>Use your browser's print functionality to actually print the information.<br />
<i>Note: Make sure to only print the right-hand frame.</i></p>
</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['keywordsAssign']	= "指派关键字"; //已校对
$TLS_htmltext['keywordsAssign'] 			= "<h2>目的:</h2>
<p>在该功能中用户可以批量地把关键字设置到现有的测试用例和测试套件中
</p>

<h2>指派关键字:</h2>
<ol>
	<li>在左边的视图中选择一个测试用例或者测试套件.</li>
	<li>页面右上角中的设置可以让你把可用的关键字指派到每一个单独的测试用例上.</li>
	<li>下面的选项可以让你更详细地对测试用例进行指派.</li>
</ol>

<h2>有关测试计划中显示的关键字的重要提示:</h2>
<p>当且仅当测试计划中包含最新版本的测试用例时，你指派的关键字才能影响到你的测试用例上.
如果你的测试计划中包含的是旧版本的测试用例，你设置的关键字将不会被看到。
</p>
<p>TestLink会使用这种要求，以至于你对最新版本的测试用例指派的关键字对测试计划中的旧版本没什么影响. 
如果你希望测试计划中的关键字及时更新，首先使用'更新修改的测试用例'来验证是否是最新版本
在指派关键字之前.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['executeTest']	= "测试用例执行"; //已校对
$TLS_htmltext['executeTest'] 		= "<h2>目的:</h2>

<p>允许用户执行测试用例.用户为了构建的需要可以把测试结果和相关测试用例关联起来.
 查看关于过滤器和设置的更多帮助 " .
		"(点击?按钮).</p>

<h2>开始:</h2>

<ol>
	<li>用户必须为测试计划定义一个构建.</l>
	<li>从下拉框中选择一个构建，然后点击导航栏中的应用按钮.</li>
	<li>点击菜单树中的测试用例.</li>
	<li>完善测试用例的结果和任何合适的记录或问题报告.</li>
	<li>保存结果.</li>
</ol>
<p><i>注意:如果你打算直接创建/跟踪问题，必须先配置TestLink关联到相关的bug跟踪工具.</i></p>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['showMetrics']	= "测试报告和统计数据"; //已校对
$TLS_htmltext['showMetrics'] 		= "<p>关于测试计划的报告" .
		"(在导航条里定义了). 这个测试计划可能与当前执行的测试计划不同. 可以选择的格式有:</p>
<ul>
<li><b>HTML</b> - 报告显示为网页格式</li>
<li><b>MS Excel</b> - 报告输出为 Microsoft Excel</li>
<li><b>OpenOffice Writer</b> - 报告输出为OpenOffice Writer</li>
<li><b>OpenOffice Calc</b> - 报告输出为OpenOffice Calc</li>
<li><b>HTML Email</b> - 报告以邮件形式发送到用户的信箱 </li>
<li><b>Charts</b> - 报告以图表形式显示(flash 技术)</li>
</ul>

<p>打印键将激活当前报告的打印功能.</p>
<p>报表有多种形式. 其格式, 目的和功能如下.</p>

<h3>通用测试统计报告</h3>
<p>该页只显示当前测试计划中的测试套件,所有者, 关键字的最新状态.
'当前状态' 是指最新构建测试版本的执行状态.例如. 一个测试用例在多个构建版本上执行过. 这里只显示最新版本的结果.</p>

<p>'最终测试结果'是许多报告里用的一个概念. 它是这样定义的:</p>
<ul>
<li>构建版本加入到测试计划里的先后顺序决定了哪个构建版本是最新的. 最新构建版本中的测试结果比旧版本的测试结果优先. 例如, 如果你在版本1里记录了一个测试用例的测试结果是’失败’. 你在版本2里记录同一个测试用例的测试结果是'通过',则最终的测试结果是 '通过'.</li>
<li>如果同一个测试用例在同一个构建版本上执行了多次. 那么最后一次执行的结果优先. 例如. 如果版本 3 发布了. 你的团队里的 tester 1 在 2:00pm 报告结果为'通过',而 tester 2 在 3:00pm 报告结果为'失败'- 则最终结果显示为'失败'.</li>
<li>在某个版本里显示为'未执行'的测试用例不会覆盖上一次的测试结果. 例如, 如果你在版本 1 中测试结束后记录为'通过', 在版本2里还没有执行, 则显示的最终结果是'通过'.
</li>
</ul>
<p>显示的列表:</p>
<ul>
	<li><b>按顶级测试套件</b>
	表中列出顶级的测试套件. 总的测试用例数目, 通过数目, 失败数目, 受阻数目, 未执行数目, 顶级测试套件的子集及下级子集的百分比.</li>
	<li><b>按关键字</b>
	表中列出当前测试计划中所有测试用例里的关键字, 以及对应的测试结果.</li>
	<li><b>按测试者</b>
	列出当前测试计划里分派给各用户的测试用例. 未分配给用户的测试用例归类到 '未分派' 栏里.</li>
</ul>

<h3>总体构建状态</h3>
<p>列出各个构建的执行结果. 对于每一个构建, 有总测试用例数, 总通过数, 通过的比例, 总失败数, 失败的比例, 总受阻数, 受阻的比例, 未执行的总数, 未执行的比例. 如果一个测试用例在同一构建版本上执行了多次, 则最近一次的结果才计入统计结果.</p>

<h3>查询统计</h3>
<p>该报表包括一个查询输入表单, 一个查询结果表单. 查询输入表单有四个按钮. 每个按钮的缺省值设置为查询可以包括的最大范围. 用户可以更改按钮以缩小查询范围. 可以按执行人, 关键字, 子类, 构建等组合过滤.</p>

<ul>
<li><b>关键字</b>可以选择 0->1 个关键字. 系统缺省的设置是不选. 如果该关键字不被选中, 则不管测试规范里和关键字管理页中有没有分配该关键字,所有测试用例都被认为忽略了关键字的关联. 一个关键字指派到一个测试用例后, 将传播到该测试用例所属于的所有的测试计划, 以及该测试用例的所有版本. 如果你只关心包含特定的关键字的测试结果, 你需要修改控制按钮的值. </li>
<li><b>所有者</b> 可以选择 0->1 个所有者. 系统缺省的设置是不选. 如果不选. 则所有测试用例都将被选择,不管测试任务分派给谁. 目前还没有搜索'未指派'执行人的测试用例的功能. 所有权的问题是通过 '指派测试任务'页来实现的, 而且是每个测试计划都要单独做的. 如果你关心工作是谁做的,你要修改这个按钮的值.</li>
<li><b>顶级子集</b>可以选择 0->n 级测试子集. 缺省状态是所有子集.
    只有被选取的子集才出现在查询结果中.如果你只关心某个子集,你可以修改这项控制.</li>
<li><b>构建</b> 可以选择 1->n 个构建. 缺省状态是选择所有构建.统计报告只显示你选取的构建中已执行过的测试结果. 例如, 如果你只想看到在过去的三个构建上做过多少次测试, 你可以修改这个按钮. 关键字, 所有者, 顶级子集的这三项的选择决定了计入统计数据中的测试用例数目. 例如, 如果你选择了执行者= '张三', 关键字 = '优先 1', 以及所有的子集- 那么只有分派给张三的优先级为 1 的测试用例被计算在内. 报表中看到的测试用例的总数目会随着这三个过滤按钮给出的条件的不同而不同. 构建过滤只对'通过', '失败', '受阻', 或者'未执行' 的测试用例有作用. 参见上面关于最后测试结果的说明.</li>
</ul>
<p>点击\"提交\"按钮启动查询和输出显示的文件</p>

<p>查询报告页将显示: </p>
<ol>
<li>用于创建报告的查询参数</li>
<li>测试计划的全部参数</li>
<li>显示了一个测试套件里所有执行的结果和按(总和/通过/失败/受阻/未执行)的分类结果. 如果一个测试用例在多个版本上执行过多次, 各次执行的结果都会显示出来. 然而在该测试套件的总结里, 只有选定版本的测试结果才会被显示出来.</li>
</ol>

<h3>受阻, 失败, 未执行的测试用例报告</h3>
<p>这些报告显示当前受阻, 失败或者未执行的测试用例. 使用的数据是'最终测试结果' (见前面通用测试统计段落). 如果系统整和了错误跟踪系统, 那些受阻和失败的测试案例报告还将显示出错误编号.</p>

<h3>测试报告</h3>
<p>阅读各个构建上的每个测试用例的状态. 如果一个测试用例在某一构建上被执行过多次, 只显示最近一次的结果. 如果数据很多, 建议输出到excel表格中来阅读.
</p>

<h3>图表 - 通用测试计划图表</h3>
<p>所有四个图表都使用'最终测试结果'. 图表有图片显示, 方便查看当前测试计划的统计结果.四个报表提供了:
</p>
<ul><li>通过/失败/受阻/未执行的测试用例的分布饼图</li>
<li>按关键词显示的图表</li>
<li>按所有者显示的图表</li>
<li>按顶级子集显示的图表</li>
</ul>
<p>图表中的方块都有颜色标记, 方便用户识别出通过,失败, 受阻, 未执行的测使用例的大概数目.
</p>

<h3>每个测试用例报告的错误总数</h3>
<p>该报表显示了每个测试用例所发现的所有错误. 包括全部项目中的所有错误. 该报表只有在和错误跟踪系统整合了以后才可见.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTC']	= "添加/删除测试用例到测试计划"; // testSetAdd //已校对
$TLS_htmltext['planAddTC'] 			= "<h2>目的:</h2>
<p>用户可以从测试计划中添加或者删除测试用例(用户的级别至少为项目经理).</p>

<h2>添加／删除测试用例的步骤:</h2>
<ol>
	<li>点击测试套件查看它的所有的子测试套件以及所有的测试用例.</li>
	<li>当你点击\"添加/删除测试用例\"来添加或者删除测试用例时
    	注意: 不可能多次添加相同的测试用例.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['tc_exec_assignment']	= "给测试员分配测试任务"; //已校对
$TLS_htmltext['tc_exec_assignment'] 		= "<h2>目的</h2>
<p>管理者通过该页面来对测试人员分配具体测试任务.</p>

<h2>开始</h2>
<ol>
	<li>选择要测试的测试用例或者测试套件.</li>
	<li>选择该项目的测试员.</li>
	<li>点击'保存'按钮提交.</li>
	<li>打开测试员的执行页面验证关联的情况.可以为使用者设置过滤器.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planUpdateTC']	= "更新测试计划中的测试用例"; //已校对
$TLS_htmltext['planUpdateTC'] 		= "<h2>目的</h2>
<p>如果需求规约版本改变了，可以通过该页面对测试用例进行更新.
        在测试过程中经常发生添加新的需求的情况." .
		" 例如:用户更改了测试规约，但是这个改变需要传达到测试计划中. " .
		" 否则测试计划继续使用着旧版本的需求规约,测试结果还在关联测试用例中的字段.</p>

<h2>开始</h2>
<ol>
	<li>选择要测试的测试用例或者测试套件.</li>
	<li>从复选框中为指定的测试用例选择新版本.</li>
	<li>点击'更新测试计划'来提交改变.</li>
	<li>验证方法:查看执行页面中的测试用例(集).</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['test_urgency']	= "设置测试的紧急程度";
$TLS_htmltext['test_urgency'] 		= "<h2>目的</h2>
<p>TestLink允许设置测试套件的紧急程度来影响测试用例执行的优先级. 
	测试的优先级取决于测试用例的重要程度和定义在测试计划中的紧急程度两个方面.
    项目领导者可以设置哪一套测试用例先被执行. 使用这个功能来确保在时间允许的情
    况下覆盖最重要的测试用例.</p>

<h2>开始</h2>
<ol>
	<li>在左上角的窗口中选择一个测试套件来设置产品/组件的严重程度.</li>
	<li>设置测试用例的紧急程度(包括高,中和低).默认是中. 你可以改变一个尚未
    执行的产品的严重程度.</li>
	<li>点击'保存'来提交改变.</li>
</ol>
<p><i>例如:一个'高'紧急程度的测试套件中的'低'紧急程度的测试用例在执行时是'中'级别 " ."</i>";


// ------------------------------------------------------------------------------------------

?>
