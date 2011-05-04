<?php
/** 
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Localization: Frech (fr_FR) descriptions
 *
 * 
 * The file contains global variables with html text. These variables are used as 
 * HELP or DESCRIPTION. To avoid override of other globals we are using "Test Link String" 
 * prefix '$TLS_hlp_' or '$TLS_txt_'. This must be a reserved prefix.
 * 
 * Contributors howto:
 * Add your localization to TestLink tracker as attachment to update the next release
 * for your language.
 * 
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2003-2009, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 * 20110427 - Julian - BUGID 4446 - Updated french localization according to TL 1.9.2 en_GB files
 *                                  contributed by Jean-Yves Boo 2011 - boojeanyves@gmail.com
 **/


// printFilter.html
$TLS_hlp_generateDocOptions = "<h2>Options pour un document généré</h2>

<p>Cette table permet à l’utilisateur de filtrer les cas de test avant leur visualisation. S’ils sont sélectionnés, leurs données sont affichées. Pour modifier les données présentées, cocher ou décocher, cliquer sur Filtre et sélectionner le niveau des données voulues depuis l’arborescence.</p>

<p><b>Entête de document:</b> Les utilisateurs peuvent filtrer les informations de l’entête du document. Les informations de l’entête de document comprennent: l’introduction, le contexte, les références, la méthodologie de test, et les limitations de test.</p>

<p><b>Corps de cas de test:</b> Les utilisateurs peuvent filtrer les informations du corps du cas de test. Les informations du corps de cas de test comprennent: le résumé, les pas de test, les résultats attendus, et les mots-clés.</p>

<p><b>Résumé de cas de test:</b> Les utilisateurs peuvent filtrer les informations du résumé de cas de test depuis le titre du cas de test, mais ils ne peuvent pas filtrer les informations de résumé de cas de test du corps de cas de test. Le résumé du cas de test a été partiellement séparé du corps du cas de test afin de permettre la visualisation des tires avec un bref résumé et sans les pas de test, les résultats attendus, et les mots-clés. Si un utilisateur décide de visualiser le corps de cas de test, le résumé de cas de test est inclus.</p>

<p><b>Table des matières:</b> TestLink insère la liste des tous les titres avec un lien hypertexte interne si coché.</p>

<p><b>Format de sortie:</b> Il y a deux possibilités: HTML et MS Word. Le navigateur appelle le composant MS word dans le second cas.</p>";

// testPlan.html
$TLS_hlp_testPlan = "<h2>Campagne de test</h2>

<h3>Général</h3>
<p>Une campagne de test est une approche systèmatique pour test un système comme un logiciel. Il est possible d’organiser l’activité de test avec des Builds particuliers du produit à temps et tracer les résultats.</p>

<h3>Exécution de test</h3>
<p>Cette section est celle où les utilisateurs peuvent exécuter les cas de test (écrire des résultats de test) et imprime la séquence de test de la campagne de test. Cette section est où les utilisateurs peuvent tracer les résultats de leur exécution de cas de test.</p> 

<h2>Gestion de campagne de test</h2>
<p>Cette section, accessible uniquement par les test leaders, permet les utilisateurs d’administrer les campagnes de test. Administrer les campagnes de test implique la création/modification/suppression de campagnes, l’ajout/modification/suppression/mise à jour des cas de test dans les campagnes, la création de Builds aussi bien que la définition des droits de lecture des campagnes.<br />
Les utilisateurs avec les permissions de leader peuvent aussi définir la priorité/risques et la propriété des séquences de cas de test (catégories) et créer des jalons de test.</p> 

<p>Remarque: Il est possible que les utilisateurs puissent ne pas voir de liste déroulante avec les campagnes de test. Dans ce cas, tous les liens (sauf ceux actifs pour le test leader) seront indisponibles. Si tel est le cas, veuillez contacter le test leader ou l’administrateur pour vous donner les droits du projet qui conviennt ou pour vous créer une campagne de test.</p>"; 

// custom_fields.html
$TLS_hlp_customFields = "<h2>Champs personnalisés</h2>
<p>Les points suivants sont des faits sur l’implémentation des champs personnalisés:</p>
<ul>
<li>Les champs personnalisés sont transverses aux projets;</li>
<li>Les champs personnalisés sont liés à un type d’élément (séquence de test, cas de test);</li>
<li>Les champs personnalisés peuvent être liés à plusieurs projet de test;</li>
<li>L’ordre d’affichage des champs personnalisés peuvent être différent par projet de test;</li>
<li>Les champs personnalisés peuvnet être désactivés sur des projets de test particuliers;</li>
<li>Le nombre de champs personnalisés n’est pas limité.</li>
</ul>

<p>La définition des champs personnalisés comprend les attributs logiques suivants:</p>
<ul>
<li>Nom de champ;</li>
<li>Label/Nom affiché du champ (Il s’agit de la valeur fournie à l’API lang_get() , ou affichée si non trouvé dans le fichier de langue);</li>
<li>Type de champ personnalisé (string, numeric, float, enum, email);</li>
<li>Valeurs d’énumération possibles (eg: RED|YELLOW|BLUE), applicable aux listes, les listes multi-sélection et les listes combo;<br />
<i>Utiliser le caractère pipe (’|’) pour séparer les valeurs possibles pour une énumération; une des valeurs possibles peut être une chaîne vide;</i>
</li>
<li>Valeur par défaut; pas encore implémenté;</li>
<li>longueur minimale/maximale pour la valeur du champ personnalisé (utiliser 0 pour désactiver); pas encore implémenté;</li>
<li>Expression régulière pour utiliser pour valider l’entrée utilisateur (voir la syntaxe <a href=\"http://au.php.net/manual/en/function.ereg.php\">ereg()</a>). <b>(pas encore implémenté)</b></li>
<li>Tous les champs personnalisés sont actuellement enregistrés dans un champs de type VARCHAR(255) dans la base de données;</li>
<li>Affichage sur la conception de cas de test;</li>
<li>Activer sur le cahier de test: l’utilisateur peut changer la velur pendant la conception de cas de test</li>
<li>Affichage sur l’exécution de test;</li>
<li>Activer sur l’exécution de test; l’utilisateur peut modifier la valeur pendant l’exécution de test;</li>
<li>Affichage sur la conception de campagne de test;</li>
<li>Activer sur la conception de campagne de test; l’utilisateur peut modifier la valeur pendant la conception de la campagne de test (ajouter des cas de test à la campagne);</li>
<li>Disponible pour; l’utilisateur choisit quelle sorte d’objet est sous le champ.</li>
</ul>
";

// execMain.html
$TLS_hlp_executeMain = "<h2>Exécution de cas de test</h2>
<p>Permet aux utilisateurs d’exécter les cas de test. L’exécution en elle-même n’est qu’une assignation au cas de test d’un résultat (réussi, en échec, bloqué) pour un Build (livraison) donné.</p>
<p>L’accès à un système de gestion d’anomalie peut être configuré. L’utilisateur peut alors ajouté directement de nouvelles anomalies et rechercher celles existantes. Voir le manuel d’installation pour d’avantage de détails.</p>";

//bug_add.html
$TLS_hlp_btsIntegration = "<h2>Ajout d’anomalie au cas de test</h2>
<p><i>(Seulement si c’est configuré)</i>
TestLink a une intégration très simple avec les système de gestion d’anomalies, qui n’est ni capable d’envoyer de requête de création au système, ni récupérer le bug id. L’intégration est fait par des liens aux pages du système de gestion d’anomalie, qui appelle les fonctionnalités suivantes:
<ul>
	<li>Insertion d’une nouvelle anomalie;</li>
	<li>Affichage des informations de l’anomalie. </li>
</ul>
</p>  

<h3>Processur d’ajout d’anomalie</h3>
<p>
   <ul>
   <li>Etape 1: utiliser le lien pour ouvrir le gestionnaire d’anomalies pour insérer la nouvelle anomalie;</li>
   <li>Etape 2: retenir le BUGID assigné par le système;</li>
   <li>Etape 3: renseigner le champ de Testlink avec le BUGID récupéré;</li>
   <li>Etape 4: utiliser le bouton d’ajout d’anomalie.</li>
   </ul>  

Après fermeture de la fenêtre d’ajout d’anomalie, l’anomalie apparaît dans la page d’exécution.
</p>";

// execFilter.html
$TLS_hlp_executeFilter = "<h2>Propriétés</h2>

<p>Les propriétés permettent de sélectionner la campagne de test, le Build et la plateforme (si disponible) à exécuter.</p>

<h3>Campagne de test</h3>
<p>Il est possible de choisir la campagne de test voulue, conditionnant l’affichage des Builds correspondants, et réinitialisant les filtres de campagne de test.</p>

<h3>Plateforme</h3>
<p>Si la fonctionnalité de gestion des plateformes est utilisée, il est nécessaire de sélectionner la plateforme appropriée avant l’exécution.</p>

<h3>Build à exécuter</h3>
<p>Il est possible de choisir le Build pour en exécuter les cas de test.</p>

<h2>Filtres</h2>
<p>Les filtres fournissent la possibilité de modifier la série de cas de test affichés avant exécution, et d’en réduire la liste en applicant les filtres avec le bouton \"Appliquer\".</p>

<p> Les filtres avancés permet de spécifier une liste de valeurs pour les filtres en utilisant CTRL-Clic dans la liste multi-sélection.</p>


<h3>Filtre de mots-clés</h3>
<p>Il est possible de filtrer les cas de test par mots-clés qui leurs ont été affectés. Il est possible de choisir plusieurs mots-clés en utilisant CTRL-Clic. Si vous choisissez plus d’un mot-clé, vous pouvez choisir le mode \"And\" ou \"Or\" pour le filtre.</p>

<h3>Filtre de priorité</h3>
<p>Il est possible de filtrer les cas de test par priorité, \"Criticité de cas de test\" combiné à \"Urgence de test\" dans la campagne de test courante.</p> 

<h3>Filtre utilisateur</h3>
<p>Il est possible de filtrer les cas de test affectés ou non à quelqu’un, et également à un utilisateur spécifique (avec inclusion des cas non affectés ou non - les filtres avancés sont disponibles). </p>

<h3>Filtre de résultat</h3>
<p>Il est possible de filtrer les cas de test par résultat(les filtres avancés sont disponibles), sur le build choisi pour l’exécution, sur la dernière exécution, sur tous les Builds, n’importe quel Build ou sur un Build spécifique. </p>";


// newest_tcversions.html
$TLS_hlp_planTcModified = "<h2>Dernière version des cas de test lié</h2>
<p>Toute la série des cas de test liés à une campagne de test est analysée, et la liste des cas de test qui ont une nouvelle version disponible est affichée (par rapport aux versions courantes liées à la campagne de test).
</p>";


// requirementsCoverage.html
$TLS_hlp_requirementsCoverage = "<h3>Couverture des exigences</h3>
<br />
<p>La fonctionnalité permet de cartographier la couverture des exigences utilisateur ou système par cas de test.</p>

<h3>Dossier d’exigences</h3>
<p>Les exigences sont regroupés dans un cahier d’exigences qui est liée au projet de test.<br /> TestLink ne supporte pas les versions des dossier d’exigences et en même temps des exigences: il faut d’abord faire une version d’exigence avec d’effecter celle du dossier.<b>Titre</b>. Un utilisateur peut ajouter une simple description ou des notes au champ de <b>Contexte</b>.</p> 

<p><b><a name=’total_count’>Le comptage surchargé d’exigences</a></b> sert d’évaluation à la couverture d’exigences dans le cas où toutes les exigences ne sont pas ajoutées (importées). La valeur <b>0</b> signifie que le comptage courant d’exigences est utilisé pour les métriques.</p> 
<p><i>Par exemple: le cahier d’exigences compte 200 exigences mais seulement 50 sont ajoutées dans Testlink. La couverture est de 25% (si toutes les exigences ajoutées sont testées).</i></p>

<h3><a name=\"req\">Exigences</a></h3>
<p>Cliquer sur le titre des dossiers d’exigences créés. il est possible de créer, modifier, supprimer ou importer les exigences du cahier de test.Chaque exigence a un titre, un contexte et un statut. Le statut peut être \"Normal\" ou \"Non testable\". Les exigences non testables ne sont pas comptées dans les métriques. Ce paramètre peut être utilisé pour des fonctionnalités non implémentées et des exigences mal conçues.</p> 

<p>Il est possible de créer de nouveaux cas de test pour les exigences en utilisant l’action multiple avec les exigences sélectionnées dans l’écran du cahier. Les cas de test sont créés dans la séquence de test avec le nom défini en configuration <i>(par défaut: &#36;tlCfg->req_cfg->default_testsuite_name = 
\"Test suite created by Requirement - Auto\";)</i>. Le titre et le contexte sont copiés dans le cas de test.</p>
";

$TLS_hlp_req_coverage_table = "<h3>Couverture:</h3>
Une valeur de, par exemple, \"40% (8/20)\" signifie que 20 cas de test doivent être créés pour l’exigence pour la tester entièrement, 8 de ces cas sont déjà créés et liés à l’exigences, ce qui fait une couverture de 40%.
";


// req_edit
$TLS_hlp_req_edit = "<h3>Liens internes au contexte:</h3>
<p>Les liens internes permettent de créer des liens vers d’autres exigences ou dossiers d’exigences avec une syntaxe spéciale. Le comportement des liens internes peuvent être changé dans le fichier de configuration.
<br /><br />
<b>Utilisation:</b>
<br />
Lien vers des exigences: [req]req_doc_id[/req]<br />
Lien vers des dossiers d’exigences: [req_spec]req_spec_doc_id[/req_spec]</p>

<p>Le projet de test des exigences/dossiers d’exigences et une ancre pour sauter peut aussi être spécifié:<br />
[req tproj=&lt;tproj_prefix&gt; anchor=&lt;anchor_name&gt;]req_doc_id[/req]<br />
Cette syntaxe fonctionne également pour les dossiers d’exigences.</p>

<h3>Message de log pour modifications:</h3>
<p>Pour toute modification, Testlink demande un message de logservant à la tracabilité. Si uniquement le contexte de l’exigence a été modifié, vous être libre de choisir de créer ou non une nouvelle révision. Quand toute autre modification est apportée, la création d’une nouvelle révision est obligatoire.</p>
";


// req_view
$TLS_hlp_req_view = "<h3>Liens directs:</h3>
<p>Pour facilement partager le document avec d’autres personnes, cliquer tout simplement sur l’icône globe en haut du document pour créer un lien direct.</p>

<h3>Historique:</h3>
<p>La fonctionnalité permet de comparer les révisions/versions d’exigences si plusieurs révisions/versions de l’exigence existe. Le récapitulatif forunit le message de log pour chaque révision/version, un horodatage et l’auteur de la dernière modification.</p>

<h3>Couverture:</h3>
<p>Affiche tous les cas de test liés à l’exigence courante.</p>

<h3>Relations:</h3>
<p>Les relations d’exigence sont utilisées pour modéliser les relatiosn entre les exigences. Les relations personnalisées et l’option pour autoriser les relations entre exigences de différents projets de test peuvent être configurées dans le fichier de configuration. Si vous définissez une relation \"Exigence A est parent de Exigence B\", Testlink définit implicitement la relation \"Exigence B est enfant de Exigence A\".</p>
";


// req_spec_edit
$TLS_hlp_req_spec_edit = "<h3>Liens internes dans le contexte:</h3>
<p>Les liens internes permettent de créer des liens vers d’autres exigences/dossiers d’exigences avec une syntaxe spéciale. Le comportement des liens internes peut être modifié dans le fichier de configuration.
<br /><br />
<b>Utilisation:</b>
<br />
Lien vers des exigences: [req]req_doc_id[/req]<br />
Lien vers des dossiers d’exigences: [req_spec]req_spec_doc_id[/req_spec]</p>

<p>Le projet de test des exigences/dossiers d’exigences et une ancre pour sauter peut aussi être spécifié:<br />
[req tproj=&lt;tproj_prefix&gt; anchor=&lt;anchor_name&gt;]req_doc_id[/req]<br />
Cette syntaxe fonctionne également pour les dossiers d’exigences.</p>
";


// planAddTC_m1.tpl
$TLS_hlp_planAddTC = "<h2>Concernant ’Enregistrer les champs personnalisés’</h2>
Si des champs personnalisés ont été définis et affectés au projet de test, avec:<br /> 
 ’Afficher sur la conception de campagne de test=true’ et <br />
 ’Activer sur la conception de campagne de test=true’<br />
Les champs ne sont visible sur la page uniquement pour les cas de test liés à la campagne de test.
";


// resultsByTesterPerBuild.tpl
$TLS_hlp_results_by_tester_per_build_table = "<b>Plus d’informations à propos des testeurs:</b><br />
Si vous cliquez sur le nom d’un testeur dans le tableau, un récapitulatif détaillé de tout les cas de test affectés à l’utilisateur et sa progression de test sont affichés.<br /><br />
<b>Remarque:</b><br />
Le rapport affiche les cas de test qui sont affectés à un utilisateur spécifique et qui ont été exécutés pour chaque Build actif. Même si un cas de test a été exécuté par un autre utilisateur que l’utilisateur affecté, le cas de test est affiché comme exécuté pour l’utilisateur affecté..
";


// xxx.html
//$TLS_hlp_xxx = "";

// ----- END ------------------------------------------------------------------
?>
