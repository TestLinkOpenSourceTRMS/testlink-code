{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource midAirCollisionMessage.inc.tpl
@author     franciscom
@şince      2.0

*}
{lang_get var='mdcl18n' s='collision_detected,no_open_builds'}
             
{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

<div class="user_feedbak">
  <h1>{$mdcl18n.collision_detected}</h1>
  <p>{$mdcArgsMain}</p>
  <p>{$mdcArgsDetails}</p>
</div>
