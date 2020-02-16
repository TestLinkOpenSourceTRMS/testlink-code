{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: classTester.tpl *}
{* Purpose: smarty template - display TL class APIs as well as test results of those methods*}
{* 20060702 - kevinlevy - initial creation of this file *}

{include file="inc_head.tpl" }

<body>
<p><h3>Class File:</h3> {$classFile} </p>
<p><h3>Test File:</h3>{$testFile}</p>
<p><h3>Description:</h3>{$classDescription}</p>
<p><h3>How to Use this class</h3>{$classUsage}</p>

<table class="simple" border="1" style="text-align: center; margin-left: 0px;">

<tr><th>method name</th><th>description</th><th>test parameters</th><th>return value</th><th>test result</th></tr>

{foreach key=id item=array from=$mapOfRows}
<tr><td>{$array[0]}</td>
    <td>{$array[1]}</td>
    <td>{$array[2]}</td>
    <td>{$array[3]}</td>
    <td>{$array[4]}</td>
{/foreach}
</table>
</body>


