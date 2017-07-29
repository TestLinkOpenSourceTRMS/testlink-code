<?php
/**
 * ♔ TestLink Open Source Project - http://testlink.org/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * French (fr_FR) strings (en_GB is default development language)
 *
 * This list of labels is defined as GLOBAL string variables. The first sections are general
 * for strings used over all GUI. These definition should not be redefined. Next sections are
 * related to particular pages. Comment with page filename indicate a begin of section. There
 * must be defined all other strings.
 *
 * ********************************************************************************************
 * Warning - Warning - Warning - Warning - Warning - Warning - Warning - Warning - Warning
 * ********************************************************************************************
 *
 * 1. Be careful about format - the file is parsed by script -> comment only with "//" except header
 * 2. for JS string you must use \\n to get \n for end of line
 *
 * ********************************************************************************************
 *
 **/


// printFilter.html
$TLS_hlp_generateDocOptions = "<h2>Options pour un document généré</h2>

<p>Cette table permet à l’utilisateur de filtrer les fiches de test avant leur visualisation. Si elles sont sélectionnées, leurs données sont affichées. Pour modifier les données présentées, cocher ou décocher, cliquer sur Filtre et sélectionner le niveau des données voulues depuis l’arborescence.</p>";

// testPlan.html
$TLS_hlp_testPlan = "<h2>Campagne de test</h2>

<h3>Général</h3>
<p>Une campagne de test est une approche systématique pour tester un système ou un logiciel. Il est possible d’organiser l’activité de test avec des versions du produit, des plateformes pour tracer les résultats.</p>

<h3>Campagne de test</h3>
<p>Cette section permet l'administration des campagnes de test. Administrer les campagnes de test implique la création/modification/suppression de campagnes, l’ajout/modification/suppression de version du produit pour chaque campagne et l’ajout/modification/suppression d'indicateurs d'avancement.</p> 

<h3>Contenu de la campagne de test</h3>
<p>Cette section permet la définition du contenu d'une campagne de test. Gérer le contenu d'une campagne de test implique la définition des platesformes utilisées dans la campagne, la définition des fiches de test utilisées dans la campagne, l'assignation éventuelle des fiches de test à des utilisateurs liés à la campagne et la définition de l'urgence des tests. Au cours de la réalisation, les versions de fiches de tests peuvent également être mises à jour si de nouvelles versions de fiches de tests ont été créées.</p> 

<h3>Exécution des fiches de test</h3>
<p>Cette section est celle où les utilisateurs peuvent exécuter les fiches de test (écrire des résultats de test) et imprimer les suites de test de la campagne de test. Cette section est où les utilisateurs peuvent tracer les résultats de leur exécution de fiches de test.</p> 

<p>Remarque: Il est possible que les utilisateurs puissent ne pas voir de liste déroulante avec les campagnes de test. Dans ce cas, tous les liens (sauf ceux actifs pour le test leader) seront indisponibles. Si tel est le cas, veuillez contacter le test leader ou l’administrateur pour vous donner les droits du projet qui convienne ou pour vous créer une campagne de test.</p>"; 

// custom_fields.html
$TLS_hlp_customFields = "<h2>Champs personnalisés</h2>
<p>Les points suivants sont des éléments sur l’implémentation des champs personnalisés:</p>
<ul>
<li>Les champs personnalisés sont transverses aux projets;</li>
<li>Les champs personnalisés sont liés à un type d’élément (séquence de test, cas de test);</li>
<li>Les champs personnalisés peuvent être liés à plusieurs projets de test;</li>
<li>L’ordre d’affichage des champs personnalisés peuvent être différent par projet de test;</li>
<li>Les champs personnalisés peuvent être désactivés sur des projets de test particuliers;</li>
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
<li>Tous les champs personnalisés sont actuellement enregistrés dans un champ de type VARCHAR(255) dans la base de données;</li>
<li>Affichage sur la conception de fiches de test;</li>
<li>Activer sur le dossier de test: l’utilisateur peut changer la valeur pendant la conception de fiches de test</li>
<li>Affichage sur l’exécution de test;</li>
<li>Activer sur l’exécution de test; l’utilisateur peut modifier la valeur pendant l’exécution de test;</li>
<li>Affichage sur la conception de campagne de test;</li>
<li>Activer sur la conception de campagne de test; l’utilisateur peut modifier la valeur pendant la conception de la campagne de test (ajouter des fiches de test à la campagne);</li>
<li>Disponible pour; l’utilisateur choisit quelle sorte d’objet est sous le champ.</li>
</ul>
";

// execMain.html
$TLS_hlp_executeMain = "<h2>Exécution de fiches de test</h2>
<p>Permet aux utilisateurs d’exécuter les fiches de test. L’exécution en elle-même n’est qu’une assignation à chaque version d'une fiche de test d’un résultat (réussi, en échec, bloqué) pour une version du produit (livraison) donnée.</p>
<p>L’accès à un système de gestion d’anomalie peut être configuré. L’utilisateur peut alors ajouter directement de nouvelles anomalies et rechercher celles existantes. Voir le manuel d’installation pour d’avantage de détails.</p>";

//bug_add.html
$TLS_hlp_btsIntegration = "<h2>Ajout d’anomalie à la fiche de test</h2>
<p><i>(Seulement si c’est configuré)</i>
TestLink a une intégration très simple avec les systèmes de gestion d’anomalies, qui n’est ni capable d’envoyer de requête de création au système, ni récupérer le bug id. L’intégration est faite par des liens aux pages du système de gestion d’anomalie, qui appelle les fonctionnalités suivantes:
<ul>
	<li>Insertion d’une nouvelle anomalie;</li>
	<li>Affichage des informations de l’anomalie. </li>
</ul>
</p>  

<h3>Processus d’ajout d’anomalie</h3>
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
<p>Il est possible de choisir la campagne de test voulue, conditionnant l’affichage des versions du produit correspondantes, et réinitialisant les filtres de campagne de test.</p>

<h3>Plateforme</h3>
<p>Si la fonctionnalité de gestion des plateformes est utilisée, il est nécessaire de sélectionner la plateforme appropriée avant l’exécution.</p>

<h3>Version du produit à évaluer</h3>
<p>Il est possible de choisir la version du produit pour en exécuter les fiches de test.</p>

<h2>Filtres</h2>
<p>Les filtres fournissent la possibilité de modifier la série de fiches de test affichées avant exécution, et d’en réduire la liste en appliquant les filtres avec le bouton \"Appliquer\".</p>

<p> Les filtres avancés permet de spécifier une liste de valeurs pour les filtres en utilisant CTRL-Clic dans la liste multi-sélection.</p>


<h3>Filtre de mots-clés</h3>
<p>Il est possible de filtrer les fiches de test par mots-clés qui leurs ont été affectés. Il est possible de choisir plusieurs mots-clés en utilisant CTRL-Clic. Si vous choisissez plus d’un mot-clé, vous pouvez choisir le mode \"And\" ou \"Or\" pour le filtre.</p>

<h3>Filtre de priorité</h3>
<p>Il est possible de filtrer les fiches de test par priorité, \"Criticité de fiches de test\" combiné à \"Urgence de test\" dans la campagne de test courante.</p> 

<h3>Filtre utilisateur</h3>
<p>Il est possible de filtrer les fiches de test affectées ou non à quelqu’un, et également à un utilisateur spécifique (avec inclusion des fiches non affectés ou non - les filtres avancés sont disponibles). </p>

<h3>Filtre de résultat</h3>
<p>Il est possible de filtrer les fiches de test par résultat(les filtres avancés sont disponibles), sur la version du produit choisi pour l’exécution, sur la dernière exécution, sur tous les versions du produit, n’importe quel version du produit ou sur une version du produit spécifique. </p>";


// newest_tcversions.html
$TLS_hlp_planTcModified = "<h2>Dernière version des fiches de test liée</h2>
<p>Toute la série des fiches de test liées à une campagne de test est analysée, et la liste des fiches de test qui ont une nouvelle version disponible est affichée (par rapport aux versions courantes liées à la campagne de test).
</p>";


// requirementsCoverage.html
$TLS_hlp_requirementsCoverage = "<h3>Récapitulatif d'exigences</h3>
<br />
<p>La fonctionnalité permet de cartographier la couverture des exigences utilisateur ou système par fiche de test.</p>

<h3>Dossier d’exigences</h3>
<p>Les exigences sont regroupées dans des dossiers d’exigences qui sont liés au projet de test.<br /> TestLink ne supporte pas les versions des dossiers d’exigences et en même temps des exigences: il faut d’abord faire une version d’exigence avec d’effectuer celle du dossier.<b>Titre</b>. Un utilisateur peut ajouter une simple description ou des notes au champ <b>Périmètre</b>.</p> 

<p><b><a name=’total_count’>Le comptage surchargé d’exigences</a></b> sert d’évaluation à la couverture d’exigences dans le cas où toutes les exigences ne sont pas ajoutées (importées). La valeur <b>0</b> signifie que le comptage courant d’exigences est utilisé pour les métriques.</p> 
<p><i>Par exemple: le dossier d’exigences compte 200 exigences mais seulement 50 sont ajoutées dans Testlink. La couverture est de 25% (si toutes les exigences ajoutées sont testées).</i></p>

<h3><a name=\"req\">Exigences</a></h3>
<p>Cliquer sur le titre des dossiers d’exigences créés. il est possible de créer, modifier, supprimer ou importer les exigences du cahier de test. Chaque exigence a un titre, un contexte et un statut. Le statut peut être \"Normal\" ou \"Non testable\". Les exigences non testables ne sont pas comptées dans les métriques. Ce paramètre peut être utilisé pour des fonctionnalités non implémentées et des exigences mal conçues.</p> 

<p>Il est possible de créer de nouvelles fiches de test pour les exigences en utilisant l’action multiple avec les exigences sélectionnées dans l’écran du cahier. Les fiches de test sont créés dans le dossier de test avec le nom défini en configuration <i>(par défaut: &#36;tlCfg->req_cfg->default_testsuite_name = 
\"Test suite created by Requirement - Auto\";)</i>. Le titre et le contexte sont copiés dans le cas de test.</p>
";

$TLS_hlp_req_coverage_table = "<h3>Couverture:</h3>
Une valeur de \"40% (8/20)\" signifie que 20 fiches de test doivent être créés pour l’exigence pour la tester entièrement, 8 de ces fiches sont déjà créés et liés à l’exigence, ce qui fait une couverture de 40%.
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
<p>Pour toute modification, Testlink demande un message de log servant à la traçabilité. Si le périmètre de l’exigence a été modifié, vous être libre de choisir de créer ou non une nouvelle révision. Quand toute autre modification est apportée, la création d’une nouvelle révision est obligatoire.</p>
";


// req_view
$TLS_hlp_req_view = "<h3>Liens directs:</h3>
<p>Pour facilement partager le document avec d’autres personnes, cliquer tout simplement sur l’icône globe en haut du document pour créer un lien direct.</p>

<h3>Historique:</h3>
<p>La fonctionnalité permet de comparer les révisions/versions d’exigences si plusieurs révisions/versions de l’exigence existent. Le récapitulatif fournit le message de log pour chaque révision/version, un horodatage et l’auteur de la dernière modification.</p>

<h3>Couverture:</h3>
<p>Affiche toutes les fiches de test liés à l’exigence courante.</p>

<h3>Relations:</h3>
<p>Les relations d’exigence sont utilisées pour modéliser les relations entre les exigences. Les relations personnalisées et l’option pour autoriser les relations entre exigences de différents projets de test peuvent être configurées dans le fichier de configuration. Si vous définissez une relation \"Exigence A est parent de Exigence B\", Testlink définit implicitement la relation \"Exigence B est enfant de Exigence A\".</p>
";


// req_spec_edit
$TLS_hlp_req_spec_edit = "<h3>Liens internes dans le contexte:</h3>
<p>Les liens internes permettent de créer des liens vers d’autres exigences/dossiers d’exigences avec une syntaxe spéciale. Le comportement des liens internes peut être modifié dans le fichier de configuration.
<br /><br />
<b>Utilisation:</b>
<br />
Lien vers des exigences: [req]req_doc_id[/req]<br />
Lien vers des dossiers d’exigences: [req_spec]req_spec_doc_id[/req_spec]</p>

<p>Le projet de test des exigences/dossiers d’exigences, une version et une ancre pour sauter peut aussi être spécifié:<br />
[req tproj=&lt;tproj_prefix&gt; anchor=&lt;anchor_name&gt;version=&lt;version_number&gt;]req_doc_id[/req]<br />
Cette syntaxe fonctionne également pour les dossiers d’exigences (l’attribut de version n’a aucun effet).<br />
Si vous ne voulez pas définir une version, l’exigence avec toutes ses versions est affichée.</p>
";


// planAddTC_m1.tpl
$TLS_hlp_planAddTC = "<h2>Concernant ’Enregistrer les champs personnalisés’</h2>
Si des champs personnalisés ont été définis et affectés au projet, avec:<br /> 
 ’Afficher sur la conception de campagne de test=true’ et <br />
 ’Activer sur la conception de campagne de test=true’<br />
Les champs sont visibles sur la page uniquement pour les fiches de test liées à la campagne de test.
";


// resultsByTesterPerBuild.tpl
$TLS_hlp_results_by_tester_per_build_table = "<b>Plus d’informations à propos des testeurs:</b><br />
Si vous cliquez sur le nom d’un testeur dans le tableau, un récapitulatif détaillé de toutes les fiches de test affectées à l’utilisateur et sa progression d'avancement sont affichés.<br /><br />
<b>Remarque:</b><br />
Le rapport affiche les fiches de test qui sont affectées à un utilisateur spécifique et qui ont été exécutées pour chaque version du produit active. Même si une fiche de test a été exécutée par un autre utilisateur que l’utilisateur affecté, la fiche de test est affichée comme exécutée pour l’utilisateur affecté.
";


// xxx.html
//$TLS_hlp_xxx = "";

// ----- END ------------------------------------------------------------------
?>
