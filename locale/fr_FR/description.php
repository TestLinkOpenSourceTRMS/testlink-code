<?php
/** -------------------------------------------------------------------------------------
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/
 * 
 * Filename $RCSfile: description.php,v $
 * @version $Revision: 1.3 $
 * @modified $Date: 2010/06/23 13:13:29 $ $Author: mx-julian $
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
 * ----------------------------------------------------------------------------------- */

// printFilter.html
$TLS_hlp_generateDocOptions = "<h2>Les options pour un document généré</h2>

<p>Cette table autorise l'utilisateur à filtrer les cas de test avant qu'ils soient affichés. Si
elle est sélectionnée (cochée) les données seront affichées. Pour changer les données
présentées, sélectionnées ou désélectionnées, cliquer sur Filtre, et sélectionner le niveau de données
désiré à partir de l'arbre.</p>

<p><b>En-tête du document:</b> Les utilisateurs peuvent éliminer par filtrage les informations de l'en-tête de document. 
Les informations de l'en-tête de document incluent: Introduction, Périmètre, Références, 
Méthodologie de Test, et Limitations des Tests.</p>

<p><b>Corps du Cas de Test:</b> Les utilisateurs peuvent éliminer par filtrage les informations du Corps du Cas de Test. Les informations du Corps du Cas de Test
incluent: Résumé, Etape, Résultats attendus, et Mots Clés.</p>

<p><b>Résumé du Cas de Test:</b> Les utilisateurs peuvent éliminer par filtrage les informations du résumé de Cas de Test du Titre du Cas de Test,
cependant, ils ne peuvent éliminer par filtrage les informations du Résumé de Cas de Test du Corps du Cas
de Test. Le Résumé du Cas de Test a été partiellement séparé du Corps de Cas
de test seulement pour améliorer la présentation des Titres avec un bref Résumé et l'absence d'étape,
Résultats attendus, et Mots Clés. Si un utilisateur décide d'afficher le corps du Cas de Test,
le Résumé du Cas de Test sera toujours inclus.</p>

<p><b>Table du Contenu:</b> TestLink insère une liste de tous les titres avec un lien hypertext interne s'il est sélectionné.</p>

<p><b>Format de sortie:</b> Il y a deux possibilités: HTML et MS Word. Le navigateur appelle les composants MS Word 
en second cas.</p>";

// testPlan.html
$TLS_hlp_testPlan = "<h2>Plan de Test</h2>

<h3>Général</h3>
<p>Un Plan de Test est une approche systématique afin de tester un système tel qu'un logiciel. Vous pouvez organiser les activités de test avec 
des livraisons particulières du produit dans le temps et les traces des résultats.</p>

<h3>Exécution des Tests</h3>
<p>Cette section est l'endroit où les utilisateurs peuvent exécuter les cas de tests (écrire les résultats de tests) et 
publier la suite des cas de tests du plan de test. Cette section est l'endroit où les utilisateurs peuvent suivre 
les résultats de l'exécution de leur cas de tests.</p> 

<h2>Gestion du Plan de Test</h2>
<p>Cette section, qui est seulement accessible pour le rôle principal, autorise les utilisateurs à administrer les plans de tests. 
Administrer les plans de tests implique créer/éditer/supprimes des plans, 
ajouter/éditer/supprimer/mettre à jour des cas de test dans les plans, créer des livraisons ainsi que définir qui peut 
voir chaque plan.<br />
Les utilisateurs avec les permissions principales peuvent aussi changer la priorité/risque et propriétés 
des suites de cas de tests (catégories) et créer des jalons de test.</p> 

<p>Note: Il est possible que les utilisateurs ne peuvent pas voir un menu déroulant contenant n'importe quel plan de test. 
Dans cette situation tous les liens (exceptés les principaux activés) seront défaits. Si vous 
êtes dans cette situation vous devez contacter un rôle principal ou un administrateur pour vous accorder les droits 
requis sur le projet ou créer un plan de test pour vous.</p>"; 

// custom_fields.html
$TLS_hlp_customFields = "<h2>Champs personnalisé</h2>
<p>Voici des informations à propos de l'implémentation des champs personnalisés:</p>
<ul>
<li>Les champs personnalisés sont définis pour une utilisation étendue.</li>
<li>Les champs personnalisés sont liés à un type d'élément (Suite de test, Cas de test)</li>
<li>Les champs personnalisés peuvent être liés à plusieurs projets de test.</li>
<li>La séquence d'affichage des champs personnalisés peut être différente pour chaque projet.</li>
<li>Les champs personnalisés peuvent être désactivés pour un projet spécifique.</li>
<li>Le nombre de champ personnalisé n'est pas restreint.</li>
</ul>

<p>La définition d'un champ personnalisé inclut les attributs logiques
suivants:</p>
<ul>
<li>Nom du champ personnalisé</li>
<li>Nom à légende variable (ex: c'est la valeur qui est passée
à l'API lang_get(), ou affichée comme si non trouvée dans la langue du fichier).</li>
<li>Type du champ personnalisé (string, numerique, float, enum, email)</li>
<li>Valeurs possibles d'énumération (ex: RED|YELLOW|BLUE), applicable à une liste, à une liste multiselection 
et des types de combo.<br />
<i>Utiliser le caractère pipe ('|') pour
séparer les valeurs possibles dans une énumération. L'une des valeurs possibles
peut être une chaîne vide.</i>
</li>
<li>Valeur par défaut: PAS ENCORE IMPLEMENTE</li>
<li>Longueur minimum/maximum pour la valeur du champ personnalisé (utiliser 0 pour désactiver). (PAS ENCORE IMPLEMENTE)</li>
<li>L'expression régulière utilisée pour valider les données d'entrées de l'utilisateur
(utiliser <a href=\"http://au.php.net/manual/en/function.ereg.php\">ereg()</a>
comme syntaxe). <b>(PAS ENCORE IMPLEMENTE)</b></li>
<li>Tous les champs personnalisés sont actuellement sauvegardés dans un champ de type VARCHAR(255) dans la base de données.</li>
<li>Affichés dans la spécification de test.</li>
<li>Activés dans la spécification de test. L'utilisateur peut changer la valeur pendant la conception de la spécification du cas de test</li>
<li>Affichés dans l'exécution de test.</li>
<li>Activés dans l'exécution de test. L'utilisateur peut changer la valeur pendant l'exécution du cas de test</li>
<li>Affiché dans la conception du plan de test.</li>
<li>Activés dans la conception du plan de test. L'utilisateur peut changer la valeur pendant la conception du plan de test (ajouter des cas de test au plan de test)</li>
<li>Disponibles pour. L'utilisateur choisi quel genre d'objet est sous le champ.</li>
</ul>
";

// execMain.html
$TLS_hlp_executeMain = "<h2>Exécution des cas de test</h2>
<p>Autorise les utilisateurs à 'exécuter' les cas de test. L'exécution elle-même est seulement
assignée au résultat d'un cas de test (réussi, échoué, bloqué) pour une livraison sélectionnée.</p>
<p>L'accès à un système de détection des anomalies peut être configuré. L'utilisateur peut directement ajouter une nouvelle anomalie
et parcourir celles existantes.</p>";

//bug_add.html
$TLS_hlp_btsIntegration = "<h2>Ajouter une anomalie à un cas de test</h2>
<p><i>(seulement si c'est configuré)</i>
TestLink a une intégration très simple avec Bug Tracking Systems (BTS),
d'un côté il n'est pas capable d'envoyer une requête de création d'une anomalie à BTS, ni de récupérer l'id de l'anomalie.
L'intégration est faîte en utilisant les liens vers les pages de BTS, ce qui appelle les caractéristiques suivantes:
<ul>
	<li>Insérer une nouvelle anomalie.</li>
	<li>Afficher les informations des anomalie existantes. </li>
</ul>
</p>  

<h3>Processus pour ajouter une anomalie</h3>
<p>
   <ul>
   <li>Etape 1: utiliser le lien pour ouvrir BTS et insérer une nouvelle anomalie. </li>
   <li>Etape 2: consigner l'id de l'anomalie assignée par BTS.</li>
   <li>Etape 3: écrire l'id dans le champ d'entrée.</li>
   <li>Etape 4: utiliser le bouton d'ajout d'anomalie.</li>
   </ul>  

Après avoir fermé la page d'ajout d'anomalie, vous pourrez voir les données utiles de l'anomalie sur la page d'exécution.
</p>";

// execFilter.html
$TLS_hlp_executeFilter = "<h2>Paramétrage de filtre et de release pour l'exécution des tests</h2>

<p>Le panneau gauche consiste à naviguer parmi les cas de tests assignés au plan de test " .
"courant et présenté avec des cadres et des filtres. Ces filtres autorisent l'utilisateur " .
"à peaufiner l'ensemble des cas de test avant qu'ils soient exécutés." .
"Installez votre filtre, cliquez sur le bouton \"Appliquer\" et sélectionnez la cas de test approprié " .
"à partir de l'arborescence.</p>

<h3>Livraison</h3>
<p>Les utilisateurs doivent choisir une livraison qui sera liée avec un résultat de test. " .
"Les livraisons sont les composants de base pour le plan de test courant. Chaque cas de test " .
"peut être exécuté plusieurs fois par livraison. Seul le dernier résultat compte. 
<br />Les livraisons peuvent être créées par le rôle principal en utilisant la page de création d'une nouvelle livraison.</p>

<h3>Filtre par ID de cas de test</h3>
<p>Les utilisateurs peuvent filtrer les cas de test par un identifieur unique. Cette ID est créée automatiquement 
pendant la phase de création. Un cadre blanc signifie que le filtre n'a pas été appliqué.</p> 

<h3>Filtre par priorité</h3>
<p>Les utilisateurs peuvent filtrer les cas de test par leur priorité. L'importance de chaque cas de test est combinée" .
"avec l'urgence du test à l'intérieur du plan de test courant. Par exemple les cas de test de 'HAUTE' priorité " .
"sont affichés si l'importance ou l'urgence est HAUTE et le second attribut est au moins au niveau MOYEN.</p> 

<h2>Filtre par résultat</h2>
<p>Les utilisateurs peuvent filtrer les cas de test par résultats. Les résultats sont ce qui est arrivé à ce cas 
de test pendant une livraison particulière. Un cas de test peut être réussi, échoué, bloqué, ou non exécuté." .
"Ce filtre est désactivé par défaut.</p>

<h3>Filtre par utilisateur</h3>
<p>Les utilisateurs peuvent filtrer les cas de test par ceux leur étant assigné. La check-box autorise à inclure seulement " .
"les tests \"non-assigné\" à l'ensemble des résultats en plus.</p>";
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
$TLS_hlp_planTcModified = "<h2>Nouvelles versions des cas de test liés</h2>
<p>L'ensemble des cas de tests liés au plan de test est analysé, et une liste de cas de tests
qui ont une nouvelle version est affichée (contre l'ensemble du plan de test courant).
</p>";


// requirementsCoverage.html
$TLS_hlp_requirementsCoverage = "<h3>Couverture des exigences</h3>
<br />
<p>Cette fonctionnalité autorise à élaborer une couverture d'exigences de l'utilisateur ou du système par
des cas de test. La navigation se fait par le lien \"Spécification d'exigences\" sur l'écran principal.</p>

<h3>Spécification des exigences</h3>
<p>Les exigences sont regroupées par document 'Spécification d'exigences' qui est relié au 
projet.<br /> TestLink ne surpporte pas les versions pour la Spécification des exigences et les exigences 
elles-mêmes en même temps. Alors, la version du document doit être ajoutée après 
le <b>Titre</b> d'une spécification.
Un utilisateur peut ajouter une simple description ou une note au champ <b>Portée</b>.</p> 

<p><b><a name='total_count'>Ecraser le total des exigences</a></b> sert pour 
évaluer la couverture des exigences dans le cas où toutes les exigences n'ont pas été ajoutées (importées). 
La valeur <b>0</b> signifie que le total actuel d'exigences est utilisé pour les métriques.</p> 
<p><i>Ex: SRS inclut 200 exigences mais seulement 50 sont ajoutées dans TestLink. La couverture 
de test est de 25% (si toutes ces exigences ajoutées seront testées).</i></p>

<h3><a name=\"req\">Exigences</a></h3>
<p>Cliquez sur le titre pour créer une spécification d'exigences. Vous pouvez créer, éditer, supprimer
ou importer des exigences pour ce document. Chaque exigence a un titre, une portée et un statut.
Le statut peut être \"Normal\" ou \"Non testable\". Les exigences non testables ne sont pas comptées
dans les métriques. Ce paramètre peut être utilisé pour à la fois des fonctionnalités non implémentées et 
des exigences mal conçues.</p> 

<p>Vous pouvez créer de nouveaux cas de tests pour une exigence en utilisant l'action multiple avec l'exigence 
sélectionnée dans l'écran des spécifications. Ces cas de tests sont créés dans la suite de tests
avec le nom défini dans la configuration <i>(par défaut: &#36;tlCfg->req_cfg->default_testsuite_name = 
\"Suite de test créée par exigence - Auto\";)</i>. Le titre et la portée sont copiés sur ce cas de test.</p>
";


// planAddTC_m1.tpl
$TLS_hlp_planAddTC = "<h2>Concernant 'Sauvegarde des champs personnalisés'</h2>
Si vous avez défini et assigné au projet,<br /> 
des champs personnalisés avec:<br />
 'Afficher conception de plan de test = vrai' et <br />
 'Activer conception de plan de test = vrai'<br />
vous les verrez sur cette page SEULEMENT pour les cas de test liés au plan de test.
";

// xxx.html
//$TLS_hlp_xxx = "";

// ----- END ------------------------------------------------------------------
?>