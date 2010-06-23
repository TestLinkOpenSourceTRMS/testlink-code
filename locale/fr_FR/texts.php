<?php
/**
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: texts.php,v $
 * @version $Revision: 1.4 $
 * @modified $Date: 2010/06/23 13:13:29 $ by $Author: mx-julian $
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


$TLS_htmltext_title['assignReqs']	= "Assigner les exigences au cas de test";
$TLS_htmltext['assignReqs'] 		= "<h2>Objectif:</h2>
<p>Les utilisateurs peuvent créer des relations entre exigences et cas de test. Un concepteur de test peut
définir des relations 0..n vers 0..n. Par exemple, un cas de test peut être assigné à une ou plusieurs 
exigences, ou aucune, et vice versa. Tout comme la matrice de traçabilité aide à rechercher la couverture des tests
d'une exigence et trouver lesquelles ont successivement échoué pendant les tests. Cette
analyse sert à confirmer que toutes les attentes définies ont été rencontrées.</p>

<h2>Commencement:</h2>
<ol>
	<li>Choisissez un cas de test dans l'arborescence à gauche. La combo box avec la liste des spécifications
	d'exigences est affichée en haut de la zone de travail.</li>
	<li>Choisissez un document de spécification d'exigence si plus d'un est défini. 
	TestLink recharge la page automatiquement.</li>
	<li>Un bloc au milieu de la zone de travail liste toutes les exigences (des spécifications choisies), qui
	sont connectées avec le cas de test. Le bloc du dessous 'Exigences disponibles' liste toutes 
	les exigences qui n'ont aucune relation
	avec le cas de test courant. Un concepteur peut marquer les exigences qui sont couvertes par ce
	cas de test et alors cliquer sur le bouton 'Assigner'. Ce nouveau cas de test assigné est affiché dans
	le bloc du milieu 'Exigences assignées'.</li>
</ol>";


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['editTc']	= "Spécification de test";
$TLS_htmltext['editTc'] 		= "<h2>Objectif:</h2>
<p>La <i>Spécification de Test</i> autorise les utilisateurs à voir et éditer toutes " .
		"<i>Suites de Test</i> et <i>Cas de Tests</i> existants. Les cas de test ont une version " .
		" et toutes les versions précédentes sont disponibles et peuvent être vues et gérées ici.</p>

<h2>Commencement:</h2>
<ol>
	<li>Sélectionner votre projet dans l'arborescence (le noeud racine). <i>Veuillez noter: " .
	"Vous pouvez toujours changer le projet actif en sélectionnant un projet différent dans la " .
	"liste déroulante dans le coin en haut à droite.</i></li>
	<li>Créer une nouvelle suite de test en cliquant sur <b>Nouvelle suite de test enfant</b>. Les suites de test peuvent " .
	"apporter une structure à vos documents de test conformément à vos normes (tests fonctionnels/non-fonctionnels, " .
	"composants du produit ou fonctionnalités, requêtes de modifications, etc.). La description d'une " .
	"suite de test peut contenir la portée des cas de tests inclus, configuration par défaut, " .
	"des liens vers les documents utiles, les limitations et autres informations utiles. En général, " .
	"toutes les annotations sont communes aux cas de tests enfants. Les suites de test suivent " .
	"le 'dossier' métaphore, ses utilisateurs peuvent déplacer ou copier les suites de test à l'intérieur " .
	"du projet. De plus, ils peuvent les importer ou les exporter (incluant le contenu des cas de tests).</li>
	<li>Les suites de tests sont des dossiers divisibles. L'utilisateur peut déplacer ou copier les suites de tests à l'intérieur " .
	"du projet. Les suites de tests peuvent être importées ou exportées (incluant les cas de tests).
	<li>Sélectionnez votre nouvelle suite de test dans l'arborescence et créer " .
	"un nouveau cas de test en cliquant sur <b>Créer Cas de Test</b>. Un cas de test spécifie " .
	"un scénario de test particulier, les résultats attendus et la définition des champs personnalisés " .
	"dans le projet (se référer au manuel utilisateur pour plus d'information). Il est également possible " .
	"d'assigner des <b>mots clés</b> pour améliorer la traçabilité.</li>
	<li>Naviguez via l'arborescence sur le côté gauche et éditer les données. Les cas de tests stockent leur propre historique.</li>
	<li>Assignez votre spécification de test créée au <span class=\"help\" onclick=
	\"javascript:open_help_window('glosary','$locale');\">Plan de Test</span> lorsque votre cas de test est prêt.</li>
</ol>

<p>Avec TestLink vous pouvez organiser les cas de tests dans des suites de tests." .
"Les suites de tests peuvent être imbriquées dans d'autres suites de tests, habituez-vous à créer des hiérarchies de suites de tests.
 Vous pouvez alors imprimer cette information avec les cas de tests.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchTc']	= "Page de recherche de cas de test";
$TLS_htmltext['searchTc'] 		= "<h2>Objectif:</h2>

<p>Navigation selon des mots clés et/ou des phrases. La recherche n'est pas 
sensible à la casse. Le résultat inclut seulement les cas de tests du projet actuel.</p>

<h2>Recherche:</h2>

<ol>
	<li>Ecrire une phrase dans le champ approprié. Laissez les champs non utilisé du formulaire vide.</li>
	<li>Choisir le mot clé requit ou laisser la valeur 'Non appliqué'.</li>
	<li>Cliquer sur le bouton Rechercher.</li>
	<li>Tous les cas de tests remplissant les conditions sont affichés. Vous pouvez modifier les cas de test via le lien 'Titre'.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printTestSpec']	= "Imprimer une spécification de test"; //printTC.html
$TLS_htmltext['printTestSpec'] 			= "<h2>Objectif:</h2>
<p>D'ici vous pouvez imprimer un cas de test seul, tous les cas de tests d'une suite de tests,
ou tous les cas de test du projet ou du plan de test.</p>
<h2>Commencement:</h2>
<ol>
<li>
<p>Sélectionner la partie du cas de test que vous voulez afficher, et alors cliquez sur un cas de test, 
une suite de tests, ou un projet. Une page imprimable sera affichée.</p>
</li>
<li><p>Utilisez la drop-box \"Show As\" dans le cadre de navigation pour spécifier si vous voulez 
afficher les informations en HTML, document OpenOffice ou document Microsoft. 
Voir <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">aide</span> pour plus d'information.</p>
</li>
<li><p>Utiliser la fonctionnalité d'impression de votre navigateur pour imprimer les informations.<br />
<i>Note: Faîtes attention à n'imprimer que la frame à droite.</i></p>
</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['reqSpecMgmt']	= "Conception des Spécifications d'exigences"; //printTC.html
$TLS_htmltext['reqSpecMgmt'] 			= "<p>Vous pouvez gérer les documents de spécification d'exigences.</p>

<h2>Spécification d'exigences</h2>

<p>Les exigences sont regroupées par <b>document de spécification d'exigences</b>, qui est relié au
projet.<br /> TestLink ne supporte pas (encore) des versions pour les spécification d'exigences et
les exigences elle-même. Donc, la version d'un document doit être ajoutée après
le <b>Titre</b> d'une spécification.
Un utilisateur peut ajouter une simple description ou notes au champ <b>Portée</b>.</p>

<p><b><a name='total_count'>Ecraser le compte d'exigence</a></b> sert pour
évaluer la couverture des exigences dans le cas où toutes les exigences sont ajoutées dans TestLink.
La valeur <b>0</b> signifie que le compte des exigences courant est utilisé
pour les métriques.</p>
<p><i>Ex: SRS inclut 200 exigences mais seulement 50 sont ajoutées dans TestLink. La couverture
de test est de 25% (en considérant que les 50 exigences ajoutées seront actuellement testées).</i></p>

<h2><a name='req'>Exigences</a></h2>

<p>Cliquer sur le titre d'une spécification d'exigence existante. Si aucune n'existe, cliquez sur le noeud du projet pour en créer une. Vous pouvez créer, éditer, supprimer
ou importer des exigences pour le document. Chaque exigence a un titre, une portée et un statut.
Un statut peut être soit 'Normal' ou 'Non testatble'. Une exigence non testable n'est pas comptée
pour les métriques. Ce paramètre peut être utilisé pour les fonctionnalités non implémentées et
les exigences mal conçues.</p>

<p>Vous pouvez créer un nouveau cas de test pour les exigences en utilisant l'action multiple en sélectionnant
les exigences dans l'écran des spécifications. Ces cas de test sont créés dans la suite de test
avec un nom configurer de la sorte <i>(default is: \$tlCfg->req_cfg->default_testsuite_name =
'Suite de test créée par exigence - Auto';)</i>. Le titre et la portée sont copiés dans ce cas de test.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['keywordsAssign']	= "Affectation des mots clés";
$TLS_htmltext['keywordsAssign'] 			= "<h2>Objectif:</h2>
<p>La page d'affectation des mots clés est l'endroit où les utilisateurs peuvent assigner
par lot les mots clés à une suite de test ou un cas de test existant</p>

<h2>Pour assigner les mots clés:</h2>
<ol>
	<li>Sélectionnez une suite de test, ou un cas de test dans l'arborescence
		sur la gauche.</li>
	<li>La box la plus en haute qui se trouve sur le côté droit vous
		autorisera à assigner les mots clés à chaque cas de test
		seul.</li>
	<li>La sélection plus bas vous autorise à assigner les cas à un niveau
		plus granulaire.</li>
</ol>

<h2>Information importante concernant l'affectation des mots clés dans un plan de tests:</h2>
<p>L'affectation des mots clés faîtes à une spécification sera effective seulement sur les cas de test
dans votre plan de test si et seulement si le plan de test contient la dernière version du cas de test.
Sinon si un plan de test contient une ancienne version du cas de test, l'affection que vous avez faîtes
n'apparaîtra pas dans le plan de test.
</p>
<p>TestLink utilise cette approche afin que les anciennes versions des cas de test dans les plan de test ne soient pas impactées
par l'affectation des mots clés faîtes sur la version la plus récente du cas de test. Si vous voulez que vos
cas de tests dans votre plan de test soient mis à jour, vérifier d'abord que les cas de tests ont été mis à jour en utilisant la fonctionnalité
'Mettre à jour Cas de Test' AVANT de faire l'affectation des mos clés.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['executeTest']	= "Exécution des Cas de Test";
$TLS_htmltext['executeTest'] 		= "<h2>Objectif:</h2>

<p>Autorise l'utilisateur à exécuter les cas de tests. L'utilisateur peut assigner les résultats de test
à des cas de tests pour la livraison. Voir l'aide pour plus d'informations à propos des filtres et des actions " .
		"(cliquez sur l'icone point d'interrogation).</p>

<h2>Commencement:</h2>

<ol>
	<li>L'utilisateur doit avoir défini une livraison pour le plan de test.</li>
	<li>Sélectionner une livraison à partir de la box en bas et le bouton \"Appliquer\" dans le cadre de navigation.</li>
	<li>Cliquer sur un cas de test dans l'arborescence.</li>
	<li>Remplir le résultat du cas de test et n'importe quelles notes pertinantes et anomalies.</li>
	<li>Sauvegarder les résultats.</li>
</ol>
<p><i>Note: TestLink doit être configuré pour interagir avec votre gestionnaire d'anomalie 
si vous voulez créer/tracer un rapport de problème directement pour le GUI.</i></p>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['showMetrics']	= "Description des rapports et métriques de test";
$TLS_htmltext['showMetrics'] 		= "<p>Les rapports sont reliés à un plan de test " .
		"(défini en haut du navigateur). Ce plan de test peut différer du plan
de test courant pour l'exécution. Vous pouvez aussi sélectionner un format de rapport:</p>
<ul>
<li><b>Normal</b> - le rapport est affiché en une page web</li>
<li><b>OpenOffice Writer</b> - le rapport est importé dans OpenOffice Writer</li>
<li><b>OpenOffice Calc</b> - le rapport est importé dans OpenOffice Calc</li>
<li><b>MS Excel</b> - le rapport est importé dans Microsoft Excel</li>
<li><b>HTML Email</b> - le rapport est envoyé par mail à l'adresse de l'utilisateur</li>
<li><b>Charts</b> - le rapport inclut des graphiques (technologie flash)</li>
</ul>

<p>Le bouton imprimer active l'impression d'un seul rapport (sans navigation).</p>
<p>Il y a différents rapports parmi lesquels choisir, leurs objectifs et fonctions sont expliqués plus bas.</p>

<h3>Plan de test</h3>
<p>Le document 'Plan de Test' a le choix de définir un contenu et une structure de document.</p>

<h3>Rapport de Test</h3>
<p>Le document 'Rapport de Test' a le choix de définir un contenu et une structure de document.
Cela inclut les cas de tests avec les résultats de tests.</p>

<h3>Métriques générales du Plan de Test</h3>
<p>Cette page vous montre seulement le statut le plus courant d'un plan de test par suite de test, propriétaire, et mot clé.
Le statut le plus courant est déterminé par la livraison la plus courante pour l'exécution de cas de tests. Pour
l'instance, si un cas de test a été exécuté pour de multiples livraisons, seulement le dernier résultat est pris en compte.</p>

<p>Le 'Dernier Résultat de Test' est un concept utilisé dans plusieurs rapports, et qui est déterminé comme suit:</p>
<ul>
<li>L'ordre dans lequel les livraison sont ajoutées à un plan de test détermine quelle livraison est la plus récente. Les résultats
de la livraison la plus récente ont préséance sur les livraisons plus anciennes. Par exemple, si vous marquez un test comme
'échoué' dans la livraison 1, et marqué à 'réussi' dans la livraison 2, c'est le dernier résultat qui sera à 'réussi'.</li>
<li>Si un cas de test est exécuté de multiple fois sr la même livraison, l'exécution la plus récente aura
préséance.  Par exemple, si la livraison 3 est assignée à votre équipe et que le testeur 1 marque cela à 'réussi' à 2PM,
et que le testeur 2 marque cela à 'échoué' à 3PM - cela apparaîtra à 'échoué'.</li>
<li>Les cas de tests listés à 'non exécuté' dans une livraison ne sont pas pris en compte. Par exemple, si vous marquez
un cas à 'réussi' dans la livraison 1, et que vous ne l'exécutez pas dans la livraison 2, c'est le dernier résultat qui sera considéré à
'réussi'.</li>
</ul>
<p>Les tables suivantes sont affichées:</p>
<ul>
	<li><b>Les résultats par suites de tests de haut niveau</b>
  Les résultats par suites de tests de haut niveau sont listés. Cas total, réussi, échoué, bloqué, non exécuté et pourcentage
	complet sont listés. Un cas de test 'complété' est celui qui a été marqué réussi, échoué ou bloqué.
	Les résultats par suites de tests de haut niveau inclut toutes les suites enfants.</li>
	<li><b>Résultats par mots clés</b>
	Tous les mots clés qui sont assignés à des cas dans le plan de test courant sont listés, et les résultats associés
	avec eux.</li>
	<li><b>Résultats par propriétaire</b>
	Chaque propriétaire qui a des cas de tests assignés dans le plan de test courant est listé. Les cas de tests qui
	ne sont pas assignés sont notés sous l'en-tête 'non assigné'.</li>
</ul>

<h3>L'ensemble des statuts des livraisons</h3>
<p>Liste les résultats d'exécutions pour chaque livraison. Pour chaque livraison, le total des cas de tests, le total des réussis,
% réussi, le total des échoués, % échoué, bloqué, % bloqué, non exécuté, % non exécuté.  Si un cas de test a été exécuté
deux fois sur la même livraison, l'exécution la plus récente sera prise en compte.</p>

<h3>Fenêtre de requêtes des métriques</h3>
<p>Ce rapport consiste à une fenêtre de requêtes, et une fenêtre de requêtes des résultats qui contient les données des requêtes.
La fenêtre de requêtes présente une page de requêtes avec 4 contrôles. Chaque contrôle est mis à une valeur par défaut qui
maximise le nombre de cas de tests et de livraisons sur lesquels peut porter la requête. Altérer les contrôles
autorise l'utilisateur à filtrer les résultats et générer des rapports spécifiques pour un propriétaire spécifique, un mot clé, une suite,
et des combinaisons de livraisons.</p>

<ul>
<li><b>Mot clé</b> 0->1 mots clés peuvent être sélectionnés. Par défaut - aucun mot clé n'est sélectionné. Si un mot clé n'est pas
sélectionné, alors tous les cas de tests seront pris en considération sans tenir compte des affectations des mots clés. Les mots clés sont affectés
dans les spécification de test ou les pages de gestion des mots clés. Les mots de clés affectés aux cas de tests portent sur tous les plans de test,
et sur toutes les version d'un cas de test. Si vous êtes intéressés sur les résultats pour un mot clé en particulier
vous devrez changer ce contrôle.</li>
<li><b>Propriétaire</b> 0->1 propriétaires peuvent être sélectionnés. Par défaut - aucun propriétaire n'est sélectionné. Si un propriétaire n'est pas sélectionné,
alors tous les cas de tests seront pris en considération sans tenir compte des affectation des propriétaires. Actuellement il n'y a aucune fonctionnalité
pour rechercher des cas de test 'non assignés'. La propriété est assignée à travers la page 'Affectation d'exécution de cas de test',
et est effectuée sur les bases d'un plan de test. Si vous êtes intéressé pour que le travail soit effectué par un testeur spécifique vous devrez
changer ce contrôle.</li>
<li><b>Suite de haut niveau</b> 0->n suites de haut niveau peuvent être sélectionnées. Par défaut - toutes les suites sont sélectionnées.
Seules les suites sélectionnées seront utilisés pour les métriques des résultats. Si vous êtes seulement intéressé par les résultats
pour une suite spécifique vous devrez changer ce contrôle.</li>
<li><b>Livraisons</b> 1->n livraisons peuvent être sélectionnées. Par défaut - toutes les livraisons sont sélectionnées. Seules les exécutions
jouées sur les livraisons que vous avez sélectionnées seront prises en compte lors des métriques de production. Par exemple - si vous
voulez voir combien de cas de tests ont été exécutés sur les trois dernières livraisons - vous devrez changer ce contrôle.
Les sélection des mots clés, propriétaires, et suite de haut niveau dictera le nombre de cas de tests de votre plan de test
utilisés pour calculer par suite et par métriques de plan de test. Par exemple, si vous sélectionnez le propriétaire = 'Greg',
mot clé='Priorité 1', et toutes les suites de test disponibles - seulement les cas de test de priorité 1 assignés à Greg seront
pris en compte. Le '# de cas de tests' totals que vous verrez sur le rapport sera influencé par ces 3 contrôles.
Les livraisons sélectionnées influenceront si un cas st considéré 'réussi', 'échoué', 'bloqué' ou 'non exécuté'. Veuillez
vous référer aux règles de 'Dernier résultat de test' refer to 'Last Test Result' comme elles apparaissent ci-dessus.</li>
</ul>
<p>Cliquez que le bouton 'soumettre' pour procéder avec la requête et afficher sur la page de sortie.</p>

<p>La page de rapport des requête sera affichée: </p>
<ol>
<li>Les paramètres des requêtes utilisé pour créer le rapport</li>
<li>totaux pour le plan de test en entier</li>
<li>une par analyse du total des suites (somme / réussi / échoué / bloqué / non exécuté) et toutes les exécutions effectuées
sur cette suite. Si un cas de test a été exécuté plus qu'une seule fois sur différentes livraisons - toutes les exécutions seront
affichées par rapport aux livraisons sélectionnées. Pourtant, le résumé pour cette suite inclura seulement
le 'Dernier Résultat de Test' pour les livraisons sélectionnées.</li>
</ol>

<h3>Rapports des cas de test bloqués, échoués et non exécutés</h3>
<p>Ces rapports montrent tous les cas de tests actuellement bloqués, échoués ou non exécutés. Le 'dernier résultat de test'
logique (qui est décrit logique ci-dessus sous Métriques générales du plan de test) est de nouveau employé pour déterminé si
un cas de test peut être considéré bloqué, échoué ou non exécuté. Les rapports sur les cas de test bloqués et échoués 
afficheront les anomalies associées si l'utilisateur utilise un gestionnaire d'anomalies intégré.</p>

<h3>Rapport de test</h3>
<p>Afficher les statuts de chaque cas de tests pour chaque livraison. Le résultat d'exécution le plus récent sera utilisé
Si un cas de test a été exécuté plusieurs fois dans la même livraison. Il est recommandé d'exporter ce rapport
dans un format Excel pour facilité le survol si un ensemble important de données est utilisé.</p>

<h3>Graphiques - Métriques générales du plan de test</h3>
<p>'Dernier résultat du test' logique est utilisé pour les quatre graphiques que vous verrez. Les graphiques sont animés pour aider
l'utilisateur à visualiser les métriques du plan de test courant. Les quatre graphiques fournis sont :</p>
<ul><li>Camembert de l'ensemble des cas de test réussi/échoué/bloqué/ et non exécuté</li>
<li>Histogramme des résultats par mot clé</li>
<li>Histogramme des résultats par propriétaire</li>
<li>Histogramme des résultats par suite de haut niveau</li>
</ul>
<p>Les barres dans les histogrammes sont colorées afin que l'utilisateur puisse identifier le nombre approximatif de
cas réussi, échoué, bloqué et non exécuté.</p>

<h3>Anomalie totales pour chaque cas de test</h3>
<p>Ce rapport montre pour chaque cas de test toutes les anomalies engagés contre lui pour le projet en entier.
Ce rapport est disponible seulement si un système de gestion des anomalies est connecté.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTC']	= "Ajouter/Supprimer un cas de test d'un plan de test"; // testSetAdd
$TLS_htmltext['planAddTC'] 			= "<h2>Objectif:</h2>
<p>Autorise l'utilisateur (avec le niveau de permission le plus haut) à ajouter ou supprimer des cas de test dans le plan de test.</p>

<h2>Pour ajouter ou supprimer des cas de tests:</h2>
<ol>
	<li>Cliquez sur une suite de test pour voir tout ses suites de tests et tout ses cas de tests.</li>
	<li>Lorsque c'est fait, cliquez sur le bouton 'ajouter/supprimer cas de tests' pour ajouter ou supprimer les cas de tests.
		Note: Ce n'est pas possible d'ajouter le même cas de test plusieurs fois.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['tc_exec_assignment']	= "Assigner testeurs à l'exécution de test";
$TLS_htmltext['tc_exec_assignment'] 		= "<h2>Objectif</h2>
<p>Cette page autorise le chef testeur à assigner des tests en particuler à des utilisateurs dans le plan de test.</p>

<h2>Commencement</h2>
<ol>
	<li>Choisir un cas de test ou une suite de test.</li>
	<li>Sélectionner un testeur planifié.</li>
	<li>Cliquez sur le bouton 'sauvegarder' pour soumettre l'affectation.</li>
	<li>Ouvrir une page d'exécution pour vérifier l'affectation. Vous pouvez exécuter un filtre par utilisateurs.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planUpdateTC']	= "Mise à jour des cas de tests dans le plan de test";
$TLS_htmltext['planUpdateTC'] 		= "<h2>Objectif</h2>
<p>Cette page autorise la mise à jour d'un cas de test vers une nouvelle (différente) version si une spécification
de test est changée. Cela arrive souvent lorsque certains fonctionnalités sont clarifiées pendant la phase de test." .
		" L'utilisateur modifie la spécification de test, mais les changements doivent être propagés au plan de test aussi. Autrement le plan" .
		" de test plan détient la version originale pour être sûr que les résultats renvoient au bon texte d'un cas de test.</p>

<h2>Commencement</h2>
<ol>
	<li>Choisissez un cas de test ou une suite de test à tester.</li>
	<li>Choisissez une nouvelle version dans le menu à choix multiples pour un cas de test particulier.</li>
	<li>Cliquez sur le bouton 'mettre à jour plan de test' pour soumettre les changements.</li>
	<li>Pour vérifier: Ouvrez la page d'exécution pour voir le texte du cas de test.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['test_urgency']	= "Spécifier les tests avec une urgence haute ou basse";
$TLS_htmltext['test_urgency'] 		= "<h2>Objectif</h2>
<p>TestLink autorise à changer l'urgence d'une suite de tests  pour affecter la priorité de cas de tests. 
		La priorité d'un test dépend de l'importance du cas de tes et de l'urgence définie dans 
		le plan de test. Le test leader peut spécifier un ensemble de cas de tests qui peuvent être testés
		en premier. Cela aide à s'assurer que les tests couvriront les tests les plus importants
		malgré une contrainte de temps.</p>

<h2>Commencement</h2>
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