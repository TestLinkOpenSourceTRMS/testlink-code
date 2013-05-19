<?php
/**
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * French(fr_FR) texts for help/instruction pages. Strings for dynamic pages
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
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2003-2009, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 * 20110427 - Julian - BUGID 4446 - Updated french localization according to TL 1.9.2 en_GB files
 *                                  contributed by Jean-Yves Boo 2011 - boojeanyves@gmail.com
 **/


$TLS_htmltext_title['assignReqs']	= "Affecter les exigences au cas de test";
$TLS_htmltext['assignReqs'] 		= "<h2>Objectif:</h2>
<p>Les utilisateurs peuvent créer des relations entre exigences et cas de test. Un concepteur de test peut
définir des relations 0..n vers 0..n. Par exemple, un cas de test peut être affecté à une ou plusieurs 
exigences, ou aucune, et inversement. Tout comme la matrice de traçabilité aide à rechercher la couverture des tests
d'une exigence et trouver lesquelles ont successivement échoué pendant les tests, l'analyse sert à confirmer que toutes les attentes définies ont été rencontrées.</p>

<h2>Pour commencer:</h2>
<ol>
	<li>Choisissez un cas de test dans l'arborescence à gauche. La combo box avec la liste des dossiers
	d'exigences est affichée en haut de l'espace de travail.</li>
	<li>Choisissez un dossier d'exigence si plus d'un est défini. 
	TestLink recharge la page automatiquement.</li>
	<li>Un bloc au milieu de l'espace de travail liste toutes les exigences (des spécifications choisies), qui
	sont liées au cas de test. Le bloc du dessous 'Exigences disponibles' liste toutes 
	les exigences qui n'ont aucune relation
	avec le cas de test courant. Un concepteur peut marquer les exigences qui sont couvertes par ce
	cas de test et alors cliquer sur le bouton 'Affecter'. Ce nouveau cas de test affecté est affiché dans
	le bloc du milieu 'Exigences affectées'.</li>
</ol>";


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['editTc']	= "Cahier de test";
$TLS_htmltext['editTc'] 		= "<h2>Objectif:</h2>
<p>La <i>Cahier de Test</i> autorise les utilisateurs à voir et éditer toutes " .
		"<i>Séquences de Test</i> et <i>Cas de Tests</i> existants. Les cas de test ont une version " .
		" et toutes les versions précédentes sont disponibles et peuvent être vues et gérées ici.</p>

<h2>Pour commencer:</h2>
<ol>
	<li>Sélectionner votre projet dans l'arborescence (le noeud racine). <i>Veuillez noter: " .
	"Vous pouvez toujours changer le projet actif en sélectionnant un projet différent dans la " .
	"liste déroulante dans le coin en haut à droite.</i></li>
	<li>Créer une nouvelle séquence de test en cliquant sur <b>Nouvelle séquence de test enfant</b>. Les séquences de test peuvent " .
	"apporter une structure à vos documents de test conformément à vos normes (tests fonctionnels/non-fonctionnels, " .
	"composants du produit ou fonctionnalités, requêtes de modifications, etc.). La description d'une " .
	"séquence de test peut contenir le contexte des cas de tests inclus, configuration par défaut, " .
	"des liens vers les documents utiles, les limitations et autres informations utiles. En général, " .
	"toutes les annotations sont communes aux cas de tests enfants. Les séquences de test suivent " .
	"le 'dossier' métaphore, ses utilisateurs peuvent déplacer ou copier les séquences de test à l'intérieur " .
	"du projet. De plus, ils peuvent les importer ou les exporter (incluant le contenu des cas de tests).</li>
	<li>Les séquences de tests sont des dossiers divisibles. L'utilisateur peut déplacer ou copier les séquences de tests à l'intérieur " .
	"du projet. Les séquences de tests peuvent être importées ou exportées (incluant les cas de tests).
	<li>Sélectionnez votre nouvelle séquence de test dans l'arborescence et créer " .
	"un nouveau cas de test en cliquant sur <b>Créer Cas de Test</b>. Un cas de test spécifie " .
	" un cas de test particulier, les résultats attendus et la définition des champs personnalisés " .
	"dans le projet (se référer au manuel utilisateur pour plus d'information). Il est également possible " .
	"d'affecter des <b>mots clés</b> pour améliorer la traçabilité.</li>
	<li>Naviguez via l'arborescence sur le côté gauche et éditer les données. Les cas de tests stockent leur propre historique.</li>
	<li>Affectez votre spécification de test créée au <span class=\"help\" onclick=
	\"javascript:open_help_window('glosary','$locale');\">Campagne de test</span> lorsque votre cas de test est prêt.</li>
</ol>

<p>Avec TestLink vous pouvez organiser les cas de tests dans des séquences de tests." .
"Les séquences de tests peuvent être imbriquées dans d'autres séquences de tests, habituez-vous à créer des hiérarchies de séquences de tests.
 Vous pouvez alors imprimer cette information avec les cas de tests.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchTc']	= "Page de recherche de cas de test";
$TLS_htmltext['searchTc'] 		= "<h2>Objectif:</h2>

<p>Navigation selon des mots clés et/ou des phrases. La recherche n'est pas 
sensible à la casse. Le résultat inclut seulement les cas de tests du projet actuel.</p>

<h2>Pour rechercher:</h2>

<ol>
	<li>Ecrire une phrase dans le champ approprié. Laissez les champs non utilisés du formulaire vide.</li>
	<li>Choisir le mot clé requit ou laisser la valeur 'Non appliqué'.</li>
	<li>Cliquer sur le bouton Rechercher.</li>
	<li>Tous les cas de tests remplissant les conditions sont affichés. Vous pouvez modifier les cas de test via le lien 'Titre'.</li>
</ol>";

/* contribution by asimon for 2976 */
// requirements search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReq']	= "Page de recherche d'exigences";
$TLS_htmltext['searchReq'] 		= "<h2>Objectif:</h2>

<p>Navigation conformément aux mots-clés et/ou chaînes recherchées. La recherche n'est pas sensible à la casse. Le résultat inclut juste les exigences du projet de test actuel.</p>

<h2>Pour rechercher:</h2>

<ol>
	<li>Ecrire une phrase dans le champ approprié. Laissez les champs non utilisés du formulaire vide.</li>
	<li>Choisir le mot clé requis ou laisser la valeur 'Non appliqué'.</li>
	<li>Cliquer sur le bouton Rechercher.</li>
	<li>Toutes les exigences remplissant les conditions sont affichées. Vous pouvez modifier les exigences via le lien 'Titre'.</li>
</ol>";

// requirement specification search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReqSpec']	= "Pas de recherche de dossier d'exigence";
$TLS_htmltext['searchReqSpec'] 		= "<h2>Objectif:</h2>

<p>Navigation conformément aux mots-clés et/ou chaînes recherchées. La recherche n'est pas sensible à la casse. Le résultat inclut juste les dossiers d'exigences du projet de test actuel.</p>

<h2>Pour rechercher:</h2>

<ol>
<ol>
	<li>Ecrire une phrase dans le champ approprié. Laissez les champs non utilisés du formulaire vide.</li>
	<li>Choisir le mot clé requis ou laisser la valeur 'Non appliqué'.</li>
	<li>Cliquer sur le bouton Rechercher.</li>
	<li>Tous les dossiers d'exigences remplissant les conditions sont affichées. Vous pouvez modifier les dossiers d'exigences via le lien 'Titre'.</li>
</ol>";
/* end contribution */


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printTestSpec']	= "Imprimer un cahier de test"; //printTC.html
$TLS_htmltext['printTestSpec'] 			= "<h2>Objectif:</h2>
<p>D'ici vous pouvez imprimer un cas de test seul, tous les cas de tests d'une séquence de tests,
ou tous les cas de test du projet ou de la campagne de test.</p>
<h2>Commencement:</h2>
<ol>
<li>
<p>Sélectionner la partie du cas de test que vous voulez afficher, et cliquer sur un cas de test, 
une séquence de tests, ou un projet. Une page imprimable sera affichée.</p>
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
$TLS_htmltext_title['reqSpecMgmt']	= "Conception du cahier d'exigences"; //printTC.html
$TLS_htmltext['reqSpecMgmt'] 			= "<p>Vous pouvez gérer le cahier d'exigences.</p>

<h2>Dossier d'exigences</h2>

<p>Les exigences sont regroupées en <b>dossier d'exigences</b>, qui est relié au
projet.<br /> TestLink ne supporte pas (encore) des versions pour les dossiers d'exigences et
les exigences elles-mêmes. Donc, la version d'une exigence doit être ajoutée après
le <b>Titre</b> d'une exigence.
Un utilisateur peut ajouter une simple description ou notes au champ <b>Contexte</b>.</p>

<p><b><a name='total_count'>Surcharger le compteur d'exigences</a></b> sert pour
évaluer la couverture des exigences dans le cas où pas toutes les exigences sont ajoutées dans TestLink.
La valeur <b>0</b> signifie que le compte des exigences courant est utilisé
pour les métriques.</p>
<p><i>Ex: Le cahier d'exigences inclut 200 exigences mais seulement 50 sont ajoutées dans TestLink. La couverture
de test est de 25% (en considérant que les 50 exigences ajoutées seront actuellement testées).</i></p>

<h2><a name='req'>Exigences</a></h2>

<p>Cliquer sur le titre d'une exigence existante. Si aucune n'existe, cliquez sur le noeud du projet pour en créer une. Vous pouvez créer, éditer, supprimer
ou importer des exigences pour le cahier. Chaque exigence a un titre, un contexte et un statut.
Un statut peut être soit 'Normal' ou 'Non testable'. Une exigence non testable n'est pas comptée
pour les métriques. Ce paramètre peut être utilisé pour les fonctionnalités non implémentées et
les exigences mal conçues.</p>

<p>Vous pouvez créer un nouveau cas de test pour les exigences en utilisant l'action multiple en sélectionnant
les exigences dans l'écran des spécifications. Ces cas de test sont créés dans la séquence de test
avec un nom configurer de la sorte <i>(default is: \$tlCfg->req_cfg->default_testsuite_name =
'Séquence de test créée par exigence - Auto';)</i>. Le titre et le contexte sont copiés dans ce cas de test.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printReqSpec'] = "Imprimer le cahier d'exigences"; //printReq
$TLS_htmltext['printReqSpec'] = "<h2>Objectif:</h2>
<p>Il est possible d'imprimer une seule exigence, toutes les exigences d'un dossier d'exigence, ou toutes les exigences d'un projet de test.</p>
<h2>Pour commencer:</h2>
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
<i>Note: Faîtes attention à n'imprimer que le cadre à droite.</i></p>
</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['keywordsAssign']	= "Affectation des mots-clés";
$TLS_htmltext['keywordsAssign'] 			= "<h2>Objectif:</h2>
<p>La page d'affectation des mots-clés est l'endroit où les utilisateurs peuvent affecter
par lot les mots clés à une séquence de test ou un cas de test existant.</p>

<h2>Pour affecter les mots clés:</h2>
<ol>
	<li>Sélectionnez une séquence de test, ou un cas de test dans l'arborescence
		sur la gauche.</li>
	<li>La box la plus en haute qui se trouve sur le côté droit vous
		autorise à affecter les mots-clés à chaque cas de test
		seul.</li>
	<li>La sélection plus bas vous autorise à affecter les cas à un niveau
		plus granulaire.</li>
</ol>

<h2>Information importante concernant l'affectation des mots-clés dans une campagne de tests:</h2>
<p>L'affectation des mots-clés faite à une campagne de test sera effective seulement sur les cas de test
dans votre campagne de test si et seulement si la campagne de test contient la dernière version du cas de test.
Sinon si une campagne de test contient une ancienne version du cas de test, l'affection que vous avez faite
n'apparaît pas dans la campagne de test.
</p>
<p>TestLink utilise cette approche afin que les anciennes versions des cas de test dans les campagnes de test ne soient pas impactées
par l'affectation des mots-clés faite sur la version la plus récente du cas de test. Si vous voulez que vos
cas de tests dans votre campagne de test soient mis à jour, vérifier d'abord que les cas de tests ont été mis à jour en utilisant la fonctionnalité
'Mettre à jour les cas de test' AVANT de faire l'affectation des mots clés.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['executeTest']	= "Exécution des cas de test";
$TLS_htmltext['executeTest'] 		= "<h2>Objectif:</h2>

<p>Autorise l'utilisateur à exécuter les cas de tests. L'utilisateur peut affecter les résultats de test
à des cas de tests pour le Build. Voir l'aide pour plus d'informations à propos des filtres et des actions " .
		"(cliquer sur l'icône point d'interrogation).</p>

<h2>Pour commencer:</h2>

<ol>
	<li>L'utilisateur doit avoir défini un Build pour la campagne de test.</li>
	<li>Sélectionner un Build à partir de la box en bas et le bouton \"Appliquer\" dans le cadre de navigation.</li>
	<li>Si vous voulez voir que quelques cas de test à l'a place de toute l'arborescence, il est possible d'appliquer un filtre. Cliquer sur le bouton \"Apply\" après avoir renseigné les filtres.</li>	
	<li>Cliquer sur un cas de test dans l'arborescence.</li>
	<li>Remplir le résultat du cas de test et n'importe quelles notes pertinentes et anomalies.</li>
	<li>Sauvegarder les résultats.</li>
</ol>
<p><i>Remarque: TestLink doit être configuré pour interagir avec votre gestionnaire d'anomalie 
si vous voulez créer/tracer un rapport de problème directement pour le GUI.</i></p>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['showMetrics']	= "Description des rapports et métriques de test";
$TLS_htmltext['showMetrics'] 		= "<p>Les rapports sont reliés à une campagne de test " .
		"(définie en haut du navigateur). La campagne de test peut différer de la campagne
de test courante pour l'exécution. Vous pouvez aussi sélectionner un format de rapport:</p>
<ul>
<li><b>Normal</b> - le rapport est affiché en une page web;</li>
<li><b>OpenOffice Writer</b> - le rapport est importé dans OpenOffice Writer;</li>
<li><b>OpenOffice Calc</b> - le rapport est importé dans OpenOffice Calc;</li>
<li><b>MS Excel</b> - le rapport est importé dans Microsoft Excel;</li>
<li><b>HTML Email</b> - le rapport est envoyé par mail à l'adresse de l'utilisateur;</li>
<li><b>Charts</b> - le rapport inclut des graphiques (technologie flash).</li>
</ul>

<p>Le bouton imprimer active l'impression d'un seul rapport (sans navigation).</p>
<p>Il y a différents rapports parmi lesquels choisir, leurs objectifs et fonctions sont expliqués plus bas.</p>

<h3>Campagne de test</h3>
<p>Le document 'Campagne de Test' a le choix de définir un contenu et une structure de document.</p>

<h3>Rapport de Test</h3>
<p>Le document 'Rapport de Test' a le choix de définir un contenu et une structure de document.
Cela inclut les cas de tests avec les résultats de tests.</p>

<h3>Métriques générales de la campagne de test</h3>
<p>Cette page vous montre seulement le statut le plus courant d'une campagne de test par séquence de test, propriétaire, et mot-clé.
Le statut le plus courant est déterminé par le Build le plus courant pour l'exécution de cas de tests. Pour
l'instance, si un cas de test a été exécuté pour de multiples Builds, seulement le dernier résultat est pris en compte.</p>

<p>Le 'Dernier Résultat de Test' est un concept utilisé dans plusieurs rapports, et qui est déterminé comme suit:</p>
<ul>
<li>L'ordre dans lequel les Builds sont ajoutés à une campagne de test détermine quel Build est le plus récent. Les résultats
du Build le plus récent ont préséance sur les Builds plus anciens. Par exemple, si vous marquez un test comme
'échoué' dans le Build 1, et marqué à 'réussi' dans le Build 2, c'est le dernier résultat qui sera à 'réussi'.</li>
<li>Si un cas de test est exécuté de multiple fois sur le même Build, l'exécution la plus récente aura
préséance. Par exemple, si le Build 3 est affecté à votre équipe et que le testeur 1 marque cela à 'réussi' à 2PM,
et que le testeur 2 marque cela à 'échoué' à 3PM - cela apparaît à 'échoué'.</li>
<li>Les cas de tests listés à 'non exécuté' dans un Build ne sont pas pris en compte. Par exemple, si vous marquez
un cas à 'réussi' dans le Build 1, et que vous ne l'exécutez pas dans le Build 2, c'est le dernier résultat qui sera considéré à
'réussi'.</li>
</ul>
<p>Les tables suivantes sont affichées:</p>
<ul>
	<li><b>Les résultats par séquence de tests de haut niveau</b>
  Les résultats par séquence de tests de haut niveau sont listés. Le nombre de cas total, réussi, échoué, bloqué, non exécuté et pourcentage
	complet sont listés. Un cas de test 'complété' est celui qui a été marqué réussi, échoué ou bloqué.
	Les résultats par séquences de tests de haut niveau inclut toutes les séquences enfants.</li>
	<li><b>Résultats par mots-clés</b>
	Tous les mots-clés qui sont affectés à des cas dans la campagne de test courante sont listés, et les résultats associés
	avec eux.</li>
	<li><b>Résultats par propriétaire</b>
	Chaque propriétaire qui a des cas de tests affectés dans la campagne de test courante est listé. Les cas de tests qui
	ne sont pas affectés sont notés sous l'en-tête 'non affecté'.</li>
</ul>

<h3>L'ensemble des statuts des Builds</h3>
<p>Liste les résultats d'exécutions pour chaque Build. Pour chaque Build, le total des cas de tests, le total des réussis,
% réussi, le total des échoués, % échoué, bloqué, % bloqué, non exécuté, % non exécuté.  Si un cas de test a été exécuté
deux fois sur le même Build, l'exécution la plus récente sera prise en compte.</p>

<h3>Fenêtre de requêtes des métriques</h3>
<p>Le rapport consiste à une fenêtre de requêtes, et une fenêtre de requêtes des résultats qui contient les données des requêtes.
La fenêtre de requêtes présente une page de requêtes avec 4 contrôles. Chaque contrôle est mis à une valeur par défaut qui
maximise le nombre de cas de tests et de Builds sur lesquels peut porter la requête. Altérer les contrôles
autorise l'utilisateur à filtrer les résultats et générer des rapports spécifiques pour un propriétaire spécifique, un mot clé, une séquence,
et des combinaisons de Builds.</p>

<ul>
<li><b>Mot-clé</b> 0->1 mots-clés peuvent être sélectionnés. Par défaut - aucun mot clé n'est sélectionné. Si un mot clé n'est pas
sélectionné, alors tous les cas de tests sont pris en considération sans tenir compte des affectations des mots-clés. Les mots-clés sont affectés
dans le cahier de test ou les pages de gestion des mots-clés. Les mots de clés affectés aux cas de tests portent sur toutes les campagnes de test,
et sur toutes les versions d'un cas de test. Si vous êtes intéressés sur les résultats pour un mot-clé en particulier
vous devrez changer ce contrôle.</li>
<li><b>Propriétaire</b> 0->1 propriétaires peuvent être sélectionnés. Par défaut - aucun propriétaire n'est sélectionné. Si un propriétaire n'est pas sélectionné,
alors tous les cas de tests sont pris en considération sans tenir compte des affectations des propriétaires. Actuellement il n'y a aucune fonctionnalité
pour rechercher des cas de test 'non affectés'. La propriété est affectée à travers la page 'Affectation d'exécution de cas de test',
et est effectuée sur les bases d'une campagne de test. Si vous êtes intéressé pour que le travail soit effectué par un testeur spécifique vous devrez
changer ce contrôle.</li>
<li><b>Séquence de haut niveau</b> 0->n séquences de haut niveau peuvent être sélectionnées. Par défaut - toutes les séquences sont sélectionnées.
Seules les séquences sélectionnées sont utilisées pour les métriques des résultats. Si vous êtes seulement intéressé par les résultats
pour une séquence spécifique vous devrez changer ce contrôle.</li>
<li><b>Builds</b> 1->n Builds peuvent être sélectionnés. Par défaut - tous les Builds sont sélectionnés. Seules les exécutions
jouées sur les Builds que vous avez sélectionnés sont prises en compte lors des métriques de production. Par exemple - si vous
voulez voir combien de cas de tests ont été exécutés sur les trois derniers Builds - vous devrez changer ce contrôle.
Les sélections des mots-clés, propriétaires, et séquences de haut niveau dicte le nombre de cas de tests de votre campagne de test
utilisés pour calculer par séquence et par métriques de campagne de test. Par exemple, si vous sélectionnez le propriétaire = 'Greg',
mot-clé='Priorité 1', et toutes les séquences de test disponibles - seulement les cas de test de priorité 1 affectés à Greg sont
pris en compte. Le '# de cas de tests' total que vous verrez sur le rapport sera influencé par ces 3 contrôles.
Les Builds sélectionnés influencent si un cas est considéré 'réussi', 'échoué', 'bloqué' ou 'non exécuté'. Veuillez
vous référer aux règles de 'Dernier résultat de test' comme elles apparaissent ci-dessus.</li>
</ul>
<p>Cliquez que le bouton 'soumettre' pour procéder avec la requête et afficher sur la page de sortie.</p>

<p>La page de rapport des requête sera affichée: </p>
<ol>
<li>Les paramètres des requêtes utilisé pour créer le rapport</li>
<li>totaux pour la campagne de test en entier</li>
<li>une par analyse du total des séquences (somme / réussi / échoué / bloqué / non exécuté) et toutes les exécutions effectuées
sur cette séquence. Si un cas de test a été exécuté plus qu'une seule fois sur différents Builds - toutes les exécutions sont
affichées par rapport aux Builds sélectionnés. Pourtant, le résumé pour cette séquence inclut seulement
le 'Dernier Résultat de Test' pour les Builds sélectionnés.</li>
</ol>

<h3>Rapports des cas de test bloqués, échoués et non exécutés</h3>
<p>Ces rapports montrent tous les cas de tests actuellement bloqués, échoués ou non exécutés. Le 'dernier résultat de test'
logique (qui est décrit logique ci-dessus sous Métriques générales de la campagne de test) est de nouveau employé pour déterminé si
un cas de test peut être considéré bloqué, échoué ou non exécuté. Les rapports sur les cas de test bloqués et échoués 
affichent les anomalies associées si l'utilisateur utilise un gestionnaire d'anomalies intégré.</p>

<h3>Rapport de test</h3>
<p>Afficher les statuts de chaque cas de tests pour chaque Build. Le résultat d'exécution le plus récent sera utilisé
Si un cas de test a été exécuté plusieurs fois dans le même Build. Il est recommandé d'exporter ce rapport
dans un format Excel pour faciliter le survol si un ensemble important de données est utilisé.</p>

<h3>Graphiques - Métriques générales de la campagne de test</h3>
<p>'Dernier résultat du test' logique est utilisé pour les quatre graphiques que vous verrez. Les graphiques sont animés pour aider
l'utilisateur à visualiser les métriques de la campagne de test courante. Les quatre graphiques fournis sont :</p>
<ul><li>Camembert de l'ensemble des cas de test réussi/échoué/bloqué/ et non exécuté;</li>
<li>Histogramme des résultats par mot-clé;</li>
<li>Histogramme des résultats par propriétaire;</li>
<li>Histogramme des résultats par séquence de haut niveau.</li>
</ul>
<p>Les barres dans les histogrammes sont colorées afin que l'utilisateur puisse identifier le nombre approximatif de
cas réussi, échoué, bloqué et non exécuté.</p>

<h3>Anomalies totales pour chaque cas de test</h3>
<p>Ce rapport montre pour chaque cas de test toutes les anomalies engagés contre lui pour le projet en entier.
Ce rapport est disponible seulement si un système de gestion des anomalies est connecté.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTC']	= "Ajouter/Supprimer un cas de test d'une campagne de test"; // testSetAdd
$TLS_htmltext['planAddTC'] 			= "<h2>Objectif:</h2>
<p>Autorise l'utilisateur (avec le niveau de permission le plus haut) à ajouter ou supprimer des cas de test dans la campagne de test.</p>

<h2>Pour ajouter ou supprimer des cas de tests:</h2>
<ol>
	<li>Cliquez sur une suite de test pour voir toutes ses suites de tests et tous ses cas de tests.</li>
	<li>Lorsque c'est fait, cliquez sur le bouton 'ajouter/supprimer cas de tests' pour ajouter ou supprimer les cas de tests.
		Remarque: Ce n'est pas possible d'ajouter le même cas de test plusieurs fois.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['tc_exec_assignment']	= "Affecter testeurs à l'exécution de test";
$TLS_htmltext['tc_exec_assignment'] 		= "<h2>Objectif</h2>
<p>Cette page autorise le test leader à affecter des tests en particulier à des utilisateurs dans la campagne de test.</p>

<h2>Pour commencer:</h2>
<ol>
	<li>Choisir un cas de test ou une suite de test.</li>
	<li>Sélectionner un testeur planifié.</li>
	<li>Cliquez sur le bouton 'sauvegarder' pour soumettre l'affectation.</li>
	<li>Ouvrir une page d'exécution pour vérifier l'affectation. Vous pouvez exécuter un filtre par utilisateurs.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planUpdateTC']	= "Mise à jour des cas de tests dans la campagne de test";
$TLS_htmltext['planUpdateTC'] 		= "<h2>Objectif</h2>
<p>Cette page autorise la mise à jour d'un cas de test vers une nouvelle (différente) version si une exigence
de test est changée. Cela arrive souvent lorsque certaines fonctionnalités sont clarifiées pendant la phase de test." .
		" L'utilisateur modifie le cahier de test, mais les changements doivent être propagés à la campagne de test aussi. Autrement la campagne" .
		" de test détient la version originale pour être sûr que les résultats renvoient au bon texte d'un cas de test.</p>

<h2>Pour commencer:</h2>
<ol>
	<li>Choisissez un cas de test ou une suite de test à tester.</li>
	<li>Choisissez une nouvelle version dans le menu à choix multiples pour un cas de test particulier.</li>
	<li>Cliquez sur le bouton 'mettre à jour la campagne de test' pour soumettre les changements.</li>
	<li>Pour vérifier: Ouvrez la page d'exécution pour voir le texte du cas de test.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['test_urgency']	= "Spécifier les tests avec une urgence haute ou basse";
$TLS_htmltext['test_urgency'] 		= "<h2>Objectif</h2>
<p>TestLink autorise à changer l'urgence d'une suite de tests  pour affecter la priorité de cas de tests. 
		La priorité d'un test dépend de l'importance du cas de tes et de l'urgence définie dans 
		la campagne de test. Le test leader peut spécifier un ensemble de cas de tests qui peuvent être testés
		en premier. Cela aide à s'assurer que les tests couvrent les tests les plus importants
		malgré une contrainte de temps.</p>

<h2>Pour commencer:</h2>
<ol>
	<li>Choisissez une suite de test avec l'urgence à changer pour un produit/composant sur la partie gauche de la fenêtre</li>
	<li>Choisissez un niveau d'urgence (haute, moyenne, basse). Moyenne est la valeur par défaut. Vous pouvez
	descendre la priorité pour une partie non touchée du produit et l'augmenter pour des composants avec
	des changements significatifs.</li>
	<li>Cliquez sur le bouton 'sauvegarder' pour soumettre les changements.</li>
</ol>
<p><i>Par exemple, un cas de test avec une haute importance dans une suite de tests avec une urgence basse " .
		"sera de priorité moyenne.</i>";


// ------------------------------------------------------------------------------------------

?>