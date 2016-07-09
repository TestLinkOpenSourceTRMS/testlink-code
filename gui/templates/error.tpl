{* TestLink Open Source Project - http://testlink.sourceforge.net/
Purpose: smarty template - error page
--------------------------------------------------------------------------------------
*} {lang_get var='labels'
s='login_name,password,btn_login,new_user_q,lost_password_q'}
{config_load file="input_dimensions.conf" section="login"} {include
file="inc_head.tpl" title="TestLink - Login" openHead='yes'}

<script language="JavaScript"
	src="{$basehref}gui/niftycube/niftycube.js" type="text/javascript"></script>
</head>
<body>
	<div class="fullpage_head">
		<p>
			<img alt="Company logo" title="logo"
				style="width: 115px; height: 53px;"
				src="{$smarty.const.TL_THEME_IMG_DIR}{$tlCfg->logo_login}" /> <br />TestLink
			{$tlVersion|escape}
		</p>
	</div>
	<div class="warning">
		<ul>
			<li>{$gui->message}</li>
		</ul>
	</div>
</body>
</html>
