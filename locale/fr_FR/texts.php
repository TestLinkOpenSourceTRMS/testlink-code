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


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['error']	= "Erreur applicative";
$TLS_htmltext['error'] 		= "<p>Une erreur inattendue est survenue. Merci de consulter le moniteur d'événement ou " .
		"les fichiers de logs pour plus de détails.</p><p>Nous vous incitons à signaler le problème. Merci d'utiliser notre " .
		"<a href='http://mantis.testlink.org'>gestionnaire d'anomalies</a>.</p>";



$TLS_htmltext_title['assignReqs']	= "Lier les exigences aux fiches de test";
$TLS_htmltext['assignReqs'] 		= "<h2>Objectif :</h2>
<p>Les utilisateurs peuvent créer des relations entre exigences et fiches de test. Un concepteur de test peut
définir des relations 0..n vers 0..n. Par exemple, une fiche de test peut être affectée à une ou plusieurs 
exigences, ou aucune, et inversement. Tout comme la matrice de traçabilité aide à rechercher la couverture des tests
d'une exigence et trouver lesquelles ont successivement échoué pendant les tests, l'analyse sert à confirmer que toutes les attentes définies ont été rencontrées.</p>

<h2>Pour commencer :</h2>
<ol>
	<li>Choisissez une fiche de test dans l'arborescence à gauche. La combo box avec la liste des dossiers
	d'exigences est affichée en haut de l'espace de travail.</li>
	<li>Choisissez un dossier d'exigence si plus d'un est défini. 
	TestLink recharge la page automatiquement.</li>
	<li>Un bloc au milieu de l'espace de travail liste toutes les exigences (des spécifications choisies), qui
	sont liées à la fiche de test. Le bloc du dessous 'Exigences disponibles' liste toutes 	les exigences qui 
	n'ont pas de relation avec la fiche de test sélectionnée. Un concepteur peut marquer les exigences qui sont 
	couvertes par cette fiche de test et alors cliquer sur le bouton 'Affecter'. Cette nouvelle fiche de test 
	affectée est affichée dans 	le bloc du milieu 'Exigences affectées'.</li>
</ol>
<h2>Attention :</h2>
Une exigence verrouillée ne peut pas voir sa couverture modifiée. En conséquence, les exigences verrouillées sont listées mais les cases à cocher correspondantes sont désactivées.";


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['editTc']	= "Cahier de test";
$TLS_htmltext['editTc'] 		= "<h2>Objectif :</h2>
<p>La <i>Cahier de Test</i> autorise les utilisateurs à voir et éditer tous les " .
		"<i>Dossiers de Test</i> et <i>Fiches de Tests</i> existants. Les fiches de test ont une version " .
		" et toutes les versions précédentes sont disponibles et peuvent être vues et gérées ici.</p>

<h2>Pour commencer :</h2>
<ol>
	<li>Sélectionner votre projet dans l'arborescence (le noeud racine). <i>Veuillez noter : " .
	"Vous pouvez toujours changer le projet actif en sélectionnant un projet différent dans la " .
	"liste déroulante dans le coin en haut à droite.</i></li> " .
	"<li>Créer un dossier de test en cliquant sur <b>Créer</b> dans <b>Opérations sur les dossiers de tests</b>. " .
	"Les dossiers de test peuvent apporter une structure à vos documents de test conformément à vos normes " .
	"(tests fonctionnels/non-fonctionnels, composants du produit ou fonctionnalités, requêtes de modifications, etc.). " .
	"La description d'un dossier de test peut contenir le contexte des fiches de tests inclus, configuration par défaut," .
	"des liens vers les documents utiles, les limitations et autres informations utiles. En général, " .
	"toutes les annotations sont communes aux fiches de tests enfants. Les dossiers de test suivent " .
	"le 'dossier' métaphore, ses utilisateurs peuvent déplacer ou copier les dossiers de test à l'intérieur " .
	"du projet. De plus, ils peuvent les importer ou les exporter (incluant le contenu des fiches de tests).</li>
	<li>Les dossiers de tests sont des dossiers divisibles. L'utilisateur peut déplacer ou copier les dossiers de tests à l'intérieur " .
	"du projet. Les dossiers de tests peuvent être importés ou exportés (incluant les fiches de tests).
	<li>Sélectionnez votre nouveau dossier de test dans l'arborescence et créer une nouvelle fiche de test en " .
	"cliquant sur <b>Créer</b> dans <b>Opérations sur les fiches de tests</b>.. Une fiche de test spécifie " .
	" une fiche de test particuliere, les résultats attendus et la définition des champs personnalisés " .
	"dans le projet (se référer au manuel utilisateur pour plus d'information). Il est également possible " .
	"d'affecter des <b>mots clés</b> pour améliorer la traçabilité.</li>
	<li>Naviguez via l'arborescence sur le côté gauche et éditer les données. Les fiches de tests stockent leur propre historique.</li>
	<li>Affectez votre fiche de test créée à la <span class=\"help\" onclick=
	\"javascript:open_help_window('glosary','$locale');\">Campagne de test</span> lorsque votre fiche de test est prête.</li>
</ol>

<p>Avec TestLink vous pouvez organiser les fiches de tests dans des dossiers de tests." .
"Les dossiers de tests peuvent être imbriqués dans d'autres dossiers de tests. Habituez-vous à créer des hiérarchies de dossiers de tests.
 Vous pouvez alors imprimer cette information avec les fiches de tests.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchTc']	= "Page de recherche de fiches de test";
$TLS_htmltext['searchTc'] 		= "<h2>Objectif :</h2>

<p>Navigation selon des mots clés et/ou des phrases. La recherche n'est pas 
sensible à la casse. Le résultat inclut seulement les fiches de tests du projet actuel.</p>

<h2>Pour rechercher :</h2>

<ol>
	<li>Ecrire une phrase dans le champ approprié. Laissez les champs non utilisés du formulaire vide.</li>
	<li>Choisir le mot clé requit ou laisser la valeur 'Non appliqué'.</li>
	<li>Cliquer sur le bouton Rechercher.</li>
	<li>Toutes les fiches de tests remplissant les conditions sont affichées. Vous pouvez modifier les fiches de test via le lien 'Titre'.</li>
</ol>";

/* contribution by asimon for 2976 */
// requirements search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReq']	= "Page de recherche d'exigences";
$TLS_htmltext['searchReq'] 		= "<h2>Objectif :</h2>

<p>Navigation conformément aux mots-clés et/ou chaînes recherchées. La recherche n'est pas
sensible à la casse. Le résultat inclut juste les exigences du projet de test actuel.</p>

<h2>Pour rechercher :</h2>

<ol>
	<li>Ecrire une phrase dans le champ approprié. Laissez les champs non utilisés du formulaire vide.</li>
	<li>Choisir le mot clé requis ou laisser la valeur 'Non appliqué'.</li>
	<li>Cliquer sur le bouton Rechercher.</li>
	<li>Toutes les exigences remplissant les conditions sont affichées. Vous pouvez modifier les exigences via le lien 'Titre'.</li>
</ol>

<h2>Note :</h2>

<ol>
	<li>Seules les exigences dans le projet courant seront recherchées.</li>
	<li>La recherche n'est pas sensible à la casse.</li>
	<li>Les champs vides sont ignorés.</li>
</ol>
";

// requirement specification search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReqSpec']	= "Page de recherche de dossier d'exigence";
$TLS_htmltext['searchReqSpec'] 		= "<h2>Objectif :</h2>

<p>Navigation conformément aux mots-clés et/ou chaînes recherchées. La recherche n'est pas
sensible à la casse. Le résultat inclut juste les dossiers d'exigences du projet de test actuel.</p>

<h2>Pour rechercher :</h2>

<ol>
	<li>Ecrire une phrase dans le champ approprié. Laissez les champs non utilisés du formulaire vide.</li>
	<li>Choisir le mot clé requis ou laisser la valeur 'Non appliqué'.</li>
	<li>Cliquer sur le bouton Rechercher.</li>
	<li>Tous les dossiers d'exigences remplissant les conditions sont affichées. Vous pouvez modifier les dossiers d'exigences via le lien 'Titre'.</li>
</ol>

<h2>Note :</h2>

<ol>
	<li>Seules les dossiers d'exigences dans le projet courant seront recherchées.</li>>
	<li>La recherche n'est pas sensible à la casse.</li>
	<li>Les champs vides sont ignorés.</li>
</ol>";
/* end contribution */


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printTestSpec']	= "Imprimer un ensemble de fiches de test"; //printTC.html
$TLS_htmltext['printTestSpec'] 			= "<h2>Objectif :</h2>
<p>D'ici vous pouvez imprimer une fiche de test seule, toutes les fiche de tests d'un dossier de tests,
ou toutes les fiches de test du projet ou de la campagne de test.</p>
<h2>Commencement :</h2>
<ol>
<li>
<p>Sélectionner la partie de la fiche de test que vous voulez afficher, et cliquer sur une fiche de test, 
une dossier de tests, ou un projet. Une page imprimable sera affichée.</p>
</li>
<li><p>Utilisez la drop-box \"Afficher comme\" dans le cadre de navigation pour spécifier si vous voulez 
afficher les informations en HTML, document OpenOffice ou document Microsoft. 
Voir <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">aide</span> pour plus d'informations.</p>
</li>
<li><p>Utiliser la fonctionnalité d'impression de votre navigateur pour imprimer les informations.<br />
<i>Note: Faîtes attention à n'imprimer que le cadre à droite.</i></p>
</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['reqSpecMgmt']	= "Conception du dossier d'exigences"; //printTC.html
$TLS_htmltext['reqSpecMgmt'] 			= "<p>Vous pouvez gérer le dossier d'exigences.</p>

<h2>Dossier d'exigences</h2>

<p>Les exigences sont regroupées en <b>dossiers d'exigences</b>, qui sont reliés au
projet.<br />
Un utilisateur peut ajouter une simple description ou notes au champ <b>Périmètre</b>.</p>

<p><b><a name='total_count'>Surcharger le compteur d'exigences</a></b> sert pour
évaluer la couverture des exigences dans le cas où pas toutes les exigences sont ajoutées dans TestLink.
La valeur <b>0</b> signifie que le compte des exigences courant est utilisé pour les métriques.</p>
<p><i>Ex : Le cahier d'exigences inclut 200 exigences mais seulement 50 sont ajoutées dans TestLink. La couverture
de test est de 25% (en considérant que les 50 exigences ajoutées seront actuellement testées).</i></p>

<h2><a name='req'>Exigences</a></h2>

<p>Cliquer sur le titre d'une exigence existante. Si aucune n'existe, cliquez sur le noeud du projet pour en créer une. Vous pouvez créer, éditer, supprimer
ou importer des exigences pour le dossier. Chaque exigence a un titre, un périmètre et un statut.
Un statut peut être soit 'Normal' ou 'Non testable'. Une exigence non testable n'est pas comptée
pour les métriques. Ce paramètre peut être utilisé pour les fonctionnalités non implémentées et
les exigences mal conçues.</p>

<p>Vous pouvez créer une nouvelle fiche de test pour les exigences en utilisant l'action multiple en sélectionnant
les exigences dans l'écran des spécifications. Ces fiches de test sont créés dans le dossier de test
avec un nom configuré de la sorte <i>(default is: \$tlCfg->req_cfg->default_testsuite_name =
'Séquence de test créée par exigence - Auto';)</i>. Le titre et le périmètre sont copiés dans cette fiche de test.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printReqSpec'] = "Imprimer le dossier d'exigences"; //printReq
$TLS_htmltext['printReqSpec'] = "<h2>Objectif :</h2>
<p>Il est possible d'imprimer une seule exigence, toutes les exigences d'un dossier d'exigences, ou
toutes les exigences d'un projet.</p>
<h2>Pour commencer :</h2>
<ol>
<li>
<p>Sélectionner la partie des exigences que vous voulez afficher, et cliquer sur une exigence, 
un dossier d'exigences, ou un projet. Une page imprimable sera affichée.</p>
</li>
<li><p>Utilisez la drop-box \"Afficher comme\" dans le cadre de navigation pour spécifier si vous voulez 
afficher les informations en HTML, document OpenOffice ou document Microsoft. 
Voir <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">aide</span> pour plus d'informations.</p>
</li>
<li><p>Utiliser la fonctionnalité d'impression de votre navigateur pour imprimer les informations.<br />
<i>Note : Faîtes attention à n'imprimer que le cadre à droite.</i></p>
</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['keywordsAssign']	= "Affectation des mots-clés";
$TLS_htmltext['keywordsAssign'] 			= "<h2>Objectif :</h2>
<p>La page d'affectation des mots-clés est l'endroit où les utilisateurs peuvent affecter
par lot les mots clés à un dossier de test ou une fiche de test existante.</p>

<h2>Pour affecter les mots clés :</h2>
<ol>
	<li>Sélectionnez un dossier de test, ou une fiche de test dans l'arborescence
		sur la gauche.</li>
	<li>Le double chevron vous autorise à affecter les mots-clés à chaque fiche de test.</li>
	<li>Le simple chevron vous autorise à affecter les cas à un niveau
		plus granulaire.</li>
</ol>

<h2>Information importante concernant l'affectation des mots-clés dans une campagne de tests :</h2>
<p>L'affectation des mots-clés faite à une campagne de test sera effective seulement sur les fiches de test
dans votre campagne de test si et seulement si la campagne de test contient la dernière version de la fiche de test.
Sinon si une campagne de test contient une ancienne version de la fiche de test, l'affection que vous avez faite
n'apparaît pas dans la campagne de test.
</p>
<p>TestLink utilise cette approche afin que les anciennes versions des fiches de test dans les campagnes de test ne soient pas impactées
par l'affectation des mots-clés faite sur la version la plus récente de la fiche de test. Si vous voulez que vos
fiches de tests dans votre campagne de test soient mis à jour, vérifier d'abord que les fiches de tests ont été mis à jour en utilisant la fonctionnalité
'Mise à jour des versions de fiches de test à exécuter ' AVANT de faire l'affectation des mots clés.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['executeTest']	= "Gestion des exécutions";
$TLS_htmltext['executeTest'] 		= "<h2>Objectif :</h2>

<p>Autorise l'utilisateur à exécuter les fiches de tests. L'utilisateur peut affecter les résultats de test
à des versions de fiche de tests pour la version du produit. Voir l'aide pour plus d'informations à propos des filtres et des actions " .
		"(cliquer sur l'icône point d'interrogation).</p>

<h2>Pour commencer :</h2>

<ol>
	<li>L'utilisateur doit avoir défini une version du produit pour la campagne de test.</li>
	<li>Sélectionner une version du produit à évaluer dans la liste.</li>
	<li>Si vous voulez voir que quelques fiches de test à la place de toute l'arborescence,
      il est possible d'appliquer un filtre. Cliquer sur le bouton \"Appliquer\"
      après avoir renseigné les filtres.</li>	
	<li>Cliquer sur une fiche de test dans l'arborescence.</li>
	<li>Remplir le résultat de l'exécution de la fiche de test et toutes notes pertinentes.</li>
	<li>Sauvegarder les résultats.</li>
</ol>
<p><i>Remarque : TestLink doit être configuré pour interagir avec votre gestionnaire d'anomalie 
si vous voulez créer/tracer un rapport de problème directement depuis la GUI.</i></p>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['showMetrics']	= "Description des rapports et métriques de test";
$TLS_htmltext['showMetrics'] 		= "<p>Les rapports sont reliés à une campagne de test " .
		"(définie en haut du navigateur). La campagne de test peut différer de la campagne
de test courante pour l'exécution. Vous pouvez aussi sélectionner un format de rapport :</p>
<ul>
<li><b>Normal</b> - le rapport est affiché en une page web;</li>
<li><b>Pseudo MS Word</b> - le rapport est importé dans Word;</li>
<li><b>Email (HTML)</b> - le rapport est envoyé par mail à l'adresse de l'utilisateur;</li>
</ul>

<p>Le bouton 'Imprimer' active l'impression d'un seul rapport (sans navigation).</p>

<p>La 'Derniere exécution' d'une fiche de test est un concept utilisé dans plusieurs rapports, et qui est déterminé comme suit :</p>
<ul>
<li>L'ordre dans lequel les versions du produit sont ajoutées à une campagne de test détermine quel version du produit est la plus récente. Les résultats
enregistrés pour la version du produit la plus récente ont préséance sur les résultats liés à des versions du produit plus anciennes. 
Par exemple, si vous marquez un test comme 'échoué' dans une version du produit 1, et marqué à 'réussi' dans une version du produit 2, 
la 'Derniere exécution' sera considérée 'réussi'.</li>
<li>Si une fiche de test est exécutée de multiple fois sur la même version du produit, l'exécution la plus récente aura
préséance. Par exemple, si la version du produit 3 est affectée à votre équipe et que le testeur 1 enregistre une exécution 'réussi' à 2PM,
et que le testeur 2 enregistre une exécution 'échoué' à 3PM - la 'Derniere exécution' sera considéré 'échoué'.</li>
<li>Les fiches de tests listées à 'non exécuté' dans une version du produit ne sont pas pris en compte. Par exemple, si vous marquez
une fiche de test à 'réussi' dans la version du produit 1, et que vous ne l'exécutez pas dans la version du produit 2, la 'Derniere exécution' sera considéré à 'réussi'.</li>
</ul>

<p>Différents rapports sont disponibles. Leurs objectifs et fonctions sont expliqués ci-dessous.</p>

<h3>Cahier de tests de la campagne</h3>
<p>Le document 'Cahier de tests de la campagne' permet de définir le contenu et une structure de document à générer. Le rapport génère un descriptif des fiches de tests liées à la campagne de tests.</p>

<h3>Compte-rendu des exécutions des tests de la campagne</h3>
<p>Le document 'Compte-rendu des exécutions des tests de la campagne' permet de définir un contenu et une structure de document à générer.
Cela inclut les fiches de tests avec les résultats de tests.</p>

<h3>Compte-rendu des exécutions des tests de la campagne pour une version du produit</h3>
<p>Le document 'Compte-rendu des exécutions des tests de la campagne pour une version du produit' permet de définir un contenu et une structure de document à générer.
Cela inclut les fiches de tests avec les résultats de tests pour une version du produit spécifique.</p>

<h3>Métriques généraux de la Campagne</h3>
<p>Cette page vous montre seulement le statut le plus à jour d'une campagne de test par version du produit, dossier de test, priorité, mot-clé et indicateurs d'avancement.
Le statut le plus à jour est déterminé par la version du produit la plus récente pour l'exécution de fiche de tests. 
Si une fiche de test a été exécutée pour de multiples versions du produit, seulement le dernier résultat est pris en compte.</p>


<h3>Matrice d'avancement par testeur et par version du produit</h3>
<p>Liste les résultats d'exécutions pour chaque version du produit par utilisateur. Pour chaque version du produit, le total des fiches de tests, le total des réussis,
% réussi, le total des échoués, % échoué, bloqué, % bloqué, non exécuté, % non exécuté. Si une fiche de test a été exécutée
plusieurs fois sur la même version du produit, l'exécution la plus récente sera prise en compte.</p>


<h3>Rapports des cas de test bloqués, échoués et non exécutés</h3>
<p>Ces rapports montrent toutes les fiches de tests actuellement bloquées, échouées ou non exécutées. La 'Derniere exécution'
 est de nouveau employée pour déterminer si une fiche de test peut être considérée bloquée, échouée ou non exécutée. Les rapports sur les 
 fiches de test bloquées et échouées affichent les anomalies associées si l'utilisateur utilise un gestionnaire d'anomalies intégré.</p>

<h3>Matrice de résultats de test</h3>
<p>Afficher les statuts de chaque fiche de tests pour chaque version du produit. Le résultat d'exécution le plus récent sera utilisé
si une fiche de test a été exécutée plusieurs fois dans la même version du produit. Il est recommandé d'exporter ce rapport
dans un format Excel pour faciliter le survol si un ensemble important de données est utilisé.</p>

<h3>Métriques graphiques</h3>
<p>La 'Derniere exécution' est utilisée pour les différents graphiques . Les graphiques sont animés pour aider
l'utilisateur à visualiser les métriques de la campagne de test courante. Les graphiques fournis sont :</p>
<ul><li>Camembert de l'ensemble des cas de test réussi/échoué/bloqué et non exécuté;</li>
<li>Histogramme des résultats par mot-clé;</li>
<li>Histogramme des résultats par séquence de haut niveau.</li>
</ul>
<p>Les barres dans les histogrammes sont colorées afin que l'utilisateur puisse identifier le nombre approximatif de
résultat réussi, échoué, bloqué et non exécuté.</p>

<h3>Matrice des anomalies par fiche de test</h3>
<p>Ce rapport montre, pour chaque fiche de test, toutes les anomalies liées, pour la totalité du projet.
Ce rapport est disponible seulement si un système de gestion des anomalies est connecté.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTC']	= "Ajouter/Retirer fiches de test"; // testSetAdd
$TLS_htmltext['planAddTC'] 			= "<h2>Objectif :</h2>
<p>Permet à l'utilisateur d'ajouter ou de supprimer des fiches de test dans la campagne de test.</p>

<h2>Pour ajouter ou supprimer des fiches de tests :</h2>
<ol>
	<li>Cliquez sur un dossier de test pour voir toutes les dossiers de tests et toutes les fiches de tests.</li>
	<li>Cocher les fiches de test à ajouter/supprimer.</li>
	<li>Lorsque c'est fait, cliquez sur le bouton 'ajouter/retirer la sélection' pour ajouter ou supprimer les fiches de tests.
		Remarque : Ce n'est pas possible d'ajouter le même cas de test plusieurs fois.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['tc_exec_assignment']	= "Assignation d'exécution de fiches de test";
$TLS_htmltext['tc_exec_assignment'] 		= "<h2>Objectif</h2>
<p>Cette page autorise le test leader à affecter l'exécution des fiches de tests à des utilisateurs dans la campagne de test.</p>

<h2>Pour commencer :</h2>
<ol>
	<li>Choisir une fiche de test ou un dossier de test.</li>
	<li>Sélectionner un ou plusieurs testeurs.</li>
	<li>Cliquez sur le bouton 'Enregistrer' pour enregistrer l'affectation.</li>
	<li>Cliquez sur 'Envoyer les assignations par email aux testeurs' pour notifier les utilisateurs des affectations réalisées.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planUpdateTC']	= "Mise à jour des versions de fiches de test à exécuter";
$TLS_htmltext['planUpdateTC'] 		= "<h2>Objectif</h2>
<p>Cette page autorise la mise à jour d'une fiche de test vers une nouvelle (différente) version si une exigence
de test est changée. Cela arrive souvent lorsque certaines fonctionnalités sont clarifiées pendant la phase de test." .
		" L'utilisateur modifie le cahier de test, mais les changements doivent être propagés à la campagne de test réalisée. Autrement la campagne" .
		" de test détient la version erronée pour être sûr que les résultats renvoient au bon texte d'une fiche de test.</p>

<h2>Pour commencer :</h2>
<ol>
	<li>Choisissez une fiche de test ou un dossier de test à tester.</li>
	<li>Choisissez une nouvelle version dans le menu à choix multiples pour chque fiche de test à mettre à jour.</li>
	<li>Cliquez sur le bouton 'mettre à jour la campagne de test' pour soumettre les changements.</li>
	<li>Pour vérifier : Ouvrez la page d'exécution pour voir le texte de la fiche de test.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['test_urgency']	= "Définition de l'urgence de test";
$TLS_htmltext['test_urgency'] 		= "<h2>Objectif</h2>
<p>TestLink autorise à changer l'urgence des tests  pour modifier la priorité de chaque fiche de tests. 
		La priorité d'un test dépend de la criticité de la fiche de test et de l'urgence définie dans 
		la campagne de test. Le test leader peut spécifier un ensemble de fiches de tests qui peuvent être testées
		prioritairement. Cela aide à s'assurer que les tests les plus importants sont réalisés
		malgré une contrainte de temps.</p>

<h2>Pour commencer :</h2>
<ol>
	<li>Choisissez un dossier de test avec l'urgence à changer pour une version du produit de la campagne
      sur la partie gauche de la fenêtre</li>
	<li>Choisissez un niveau d'urgence (haute, moyenne, basse). Moyenne est la valeur par défaut. Vous pouvez
	descendre la priorité pour des composants non modifiés du produit et l'augmenter pour des composants avec
	des changements significatifs.</li>
	<li>Cliquez sur le bouton 'Définir l'urgence pour les fiches de test' pour soumettre les changements.</li>
</ol>
<p><i>Par exemple, une fiche de test avec une haute criticité dans une suite de tests avec une urgence basse " .
		"sera de priorité moyenne.</i>";


// ------------------------------------------------------------------------------------------

?>