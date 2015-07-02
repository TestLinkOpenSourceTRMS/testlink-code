<?php
/**
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Spanish (es_ES) texts for help/instruction pages. Strings for dynamic pages
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
 * @version    	CVS: $Id: texts.php,v 1.29 2010/07/22 14:14:44 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * ------------------------------------------------------------------------------------- 
 * Spanish (es_ES) translation
 * -------------------------------------------------------------------------------------
 * Translated by: Jesus Hernandez
 * Date: 2014/11/04
 * -------------------------------------------------------------------------------------
 **/


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['error']	= "Error de la Aplicación";
$TLS_htmltext['error'] 		= "<p>Ha ocurrido un error inesperado. Por favor, consulta el visor de eventos o " .
		"los mensajes de registro para más detalles.</p><p>Agradeceríamos que informaras del error. Por favor, visita nuestra " .
		"<a href='http://www.teamst.org'>web</a>.</p>";



$TLS_htmltext_title['assignReqs']	= "Asignar Requisitos a un Caso de Prueba";
$TLS_htmltext['assignReqs'] 		= "<h2>Propósito:</h2>
<p>Esta funcionalidad permite establecer relaciones entre los Requisitos y los Casos de Prueba.
Un diseñador podría definir relaciones 0..n a 0..n. Por ejemplo, un Caso de Prueba podría estar
asignado a ninguno, a uno o a muchos Requisitos y viceversa. Esta matriz de trazabilidad ayuda
a investigar la cobertura de requisitos y a descubrir cual de ellos falla durante
las pruebas. Este análisis sirve para confirmar que se cumplen todas las expectativas.</p>

<h2>Primeros pasos:</h2>
<ol>
	<li>Selecciona un Caso de Prueba en el árbol de la izquierda. El desplegable con la lista de las
	Especificaciones de Requisitos se muestra en la parte superior del área de trabajo.</li>
	<li>Selecciona una Especificación de Requisitos si hay más de una definida.
	TestLink recarga la página automáticamente.</li>
    <li>Aparecen dos bloques: 'Requisitos Asignados' que es la lista de todos los Requisitos de la Especificación
	seleccionada que están asignados al Caso de Prueba y 'Requisitos Disponibles' que es la lista de todos los 
    Requisitos que no están asignados al Caso de Prueba actual.
	Un diseñador podría marcar Requisitos que están cubiertos
	por este Caso de Prueba y hacer click en el botón 'Asignar'. Estos nuevos Requisitos asignados al Caso de Prueba se
	mostrarán en el bloque de 'Requisitos Asignados'.</li>
</ol>";


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['editTc']	= "Especificación de Pruebas";
$TLS_htmltext['editTc'] 		= "<h2>Propósito:</h2>
<p>La <i>Especificación de Pruebas</i> permite a los usuarios ver y editar todo el contenido existente para " .
		"<i>Suites de Pruebas</i> y <i>Casos de Prueba</i>. Los Casos de Prueba son versionados y todas " .
		"las versiones anteriores están disponibles y pueden ser vistas y gestionadas desde aquí.</p>

<h2>Primeros pasos:</h2>
<ol>
	<li>Selecciona tu Proyecto de Pruebas en el árbol de navegación (el nodo raíz). <i>Por favor, ten en cuenta que: " .
	"Siempre puedes cambiar el Proyecto de Pruebas activo seleccionando uno diferente en la " .
	"lista desplegable de la esquina superior derecha.</i></li>
	<li>Crea una nueva Suite de Pruebas pulsando en <b>Nueva Suite de Pruebas</b>. Las Suites de Pruebas pueden " .
	"ser utilizadas para estructurar tus documentos de prueba de acuerdo a tus necesidades (tets funcionales/no funcionales, " .
	"componentes del producto o características, peticiones de cambio, etc.). La descripción de " .
	"una Suite de Pruebas puede contener el alcance de los casos de prueba incluidos, la configuración por defecto, " .
	"enlaces a documentos importantes, limitaciones y otra información de utilidad. En general, " .
	"todas las anotaciones que son comunes a los Casos de Prueba incluidos. Las Suites de Pruebas se comportan " .
	"como un directorio escalable, por lo que los usuarios pueden mover y copiar las Suites de Pruebas dentro " .
	"del Proyecto de Pruebas. Además, las Suites de Pruebas pueden ser importadas o exportadas (incluyendo los casos de prueba que contienen).</li>
	<li>Las Suites de Pruebas son directorios escalables. Los usuarios pueden mover y copiar las Suites de Pruebas dentro " .
	"del Proyecto de Pruebas. Las Suites de Pruebas podrían ser importadas o exportadas (incluidos los Casos de Prueba).
	<li>Seleccionar tu recien creada Suite de Pruebas en el árbol de navegación y crea " .
	"un nuevo Caso de Prueba pulsando en <b>Crear Caso de Prueba</b>. Un Caso de Prueba especifica " .
	"un escenario de pruebas en particular, resultados esperados y campos personalizados definidos " .
	"en el Proyecto de Pruebas (consulta el manual de usuario para más información). Además es posible " .
	"asignar <b>keywords</b> para mejorar la trazabilidad.</li>
	<li>Navega por la vista en árbol del lado izquierdo y edite la información. Los Casos de Prueba almacenan su propio historial.</li>
	<li>Asigna tu Especificación de Pruebas al <span class=\"help\" onclick=
	\"javascript:open_help_window('glosary','$locale');\">Plan de Pruebas</span> cuando tus Casos de Prueba estén preparados.</li>
</ol>

<p>Con TestLink organizas los casos de prueba en suites de pruebas." .
"Las Suites de Pruebas pueden ser anidadas dentro de otras suites de pruebas, permitiendote crear jerarquías de suites de pruebas.
 Entonces puedes imprimir esta información junto con los casos de prueba.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchTc']	= "Pantalla de Búsqueda de Caso de Prueba";
$TLS_htmltext['searchTc'] 		= "<h2>Propósito:</h2>

<p>Navegación según las keywords y/o las palabras buscadas. La búsqueda
no distingue minúsculas de mayúsculas. Los resultados incluyen sólo los casos de prueba del Proyecto de Pruebas actual.</p>

<h2>Para buscar:</h2>

<ol>
	<li>Escribe cadenas de búsqueda en el campo apropiado. Deja en blanco los campos del formulario que no quieras utilizar.</li>
	<li>Elige la keyword requerida o escribe el valor 'No aplica'.</li>
	<li>Pulsa el botón 'Buscar'.</li>
	<li>Todos los casos de prueba que cumplen los criterios son mostrados. Puedes modificar los casos de prueba mediante el enlace 'Título'.</li>
</ol>";

// requirements search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReq']	= "Pantalla de Búsqueda de Requisitos";
$TLS_htmltext['searchReq'] 		= "<h2>Propósito:</h2>

<p>Navegación según las keywords y/o las palabras buscadas. La búsqueda no distingue
minúsculas de mayúsculas. Los resultados incluyen sólo los requisitos del Proyecto de Pruebas actual.</p>

<h2>Para buscar:</h2>

<ol>
	<li>Escribe cadenas de búsqueda en el campo apropiado. Deja en blanco los campos del formulario que no quieras utilizar.</li>
	<li>Elige la keyword requerida o escribe el valor 'No aplica'.</li>
	<li>Pulsa el botón 'Buscar'.</li>
	<li>Todos los requisitos que cumplen los criterios son mostrados. Puedes modificar los requisitos mediante el enlace 'Título'.</li>
</ol>

<h2>Nota:</h2>

<p>- Sólo se buscarán requisitos del proyecto de pruebas actual.<br>
- La búsqueda no distingue minúsculas de mayúsculas.<br>
- Los campos vacíos no se tienen en cuenta.</p>";

// requirement specification search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReqSpec']	= "Pantalla de Búsqueda de Especificación de Requisitos";
$TLS_htmltext['searchReqSpec'] 		= "<h2>Propósito:</h2>

<p>Navegación según las keywords y/o las palabras buscadas. La búsqueda no distingue
minúsculas de mayúsculas. Los resultados incluyen sólo las especificaciones de requisitos del Proyecto de Pruebas actual.</p>

<h2>Para buscar:</h2>

<ol>
	<li>Escribe cadenas de búsqueda en el campo apropiado. Deja en blanco los campos del formulario que no quieras utilizar.</li>
	<li>Elige la keyword requerida o escribe el valor 'No aplica'.</li>
	<li>Pulsa el botón 'Buscar'.</li>
	<li>Todos los requisitos que cumplen los criterios son mostrados. Puedes modificar las especificaciones de requisitos mediante el enlace 'Título'.</li>
</ol>

<h2>Nota:</h2>

<p>- Sólo se buscarán requisitos del proyecto de pruebas actual.<br>
- La búsqueda no distingue minúsculas de mayúsculas.<br>
- Los campos vacíos no se tienen en cuenta.</p>";
/* end contribution */


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printTestSpec']	= "Imprimir Especificación de Pruebas"; //printTC.html
$TLS_htmltext['printTestSpec'] 			= "<h2>Propósito:</h2>
<p>Desde aquí puedes imprimir un único caso de prueba, todos los casos de prueba de una suite de pruebas,
o todos los casos de prueba de un proyecto de pruebas o plan.</p>
<h2>Primeros pasos:</h2>
<ol>
<li>
<p>Selecciona las partes de los casos de prueba que quieres mostrar, y entonces pulsa en un caso de prueba,
suite de pruebas, o proyecto de pruebas. Se mostrará una página imprimible.</p>
</li>
<li><p>Usa la lista desplegable \"Mostrar como\" del panel de navegación para especificar si quieres
que la información se muestre como HTML o en un documento Microsoft Word. Consulta la
<span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">ayuda</span> para más información.</p>
</li>
<li><p>Usa la funcionalidad de imprimir de tu navegador para imprimir la información.<br />
<i>Nota: Asegúrate de imprimir únicamente el marco derecho de la pantalla.</i></p>
</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['reqSpecMgmt']	= "Diseño de Especificación de Requisitos"; //printTC.html
$TLS_htmltext['reqSpecMgmt'] 			= "<p>Puedes gestionar documentos de Especificación de Requisitos.</p>

<h2>Especificación de Requisitos</h2>

<p>Los requisitos están agrupados por documento de <b>Especificación de Requisitos</b> que están relacionados al 
Proyecto de Pruebas.</p>

<p>Los documentos de Especificación de Requisitos pueden estar ordenados jerárquicamente. 
Crear el nivel superior de los documentos de Especificación de Requisitos ulsando en el nodo del proyecto. </p>

<p>TestLink no soporta (aún) versiones para la Especificación de Requisitos
y los Requisitos en sí mismos. Por tanto, la versión del documento debe ser añadida después de
un <b>Título</b> de Especificación.
Un usuario puede añadir una simple descripción o notas al campo <b>Descripción</b>.</p>

<p><b><a name='total_count'>Sobreescribir el contador de REQs</a></b> sirve para  
evaluar la cobertura de Req. en caso de que no todos los requisitos estén añadidos a TestLink. 
El valor <b>0</b> significa que el valor actual de requisitos es el que se usará
para las métricas.</p>
<p><i>Ejemplo. El campo muestra un valor de 200 requisitos pero sólo 50 son añadidos a TestLink. La cobertura 
de pruebas es del 25% (si todos los requisitos añadidos son probados).</i></p>

<h2><a name='req'>Requisitos</a></h2>

<p>Pulsa en el título de una Especificación de Requisitos. Puedes crrar, editar, borrar
o importar requisitos en el documento. Cada requisito tiene título, descripción y estado.
El estado puede ser 'Normal' o 'No testable'. Los requisitos No testables no son tenidos en cuenta en
las métricas. Este parámetro debería ser usado tanto para funcionalidades no implementadas como para 
requisitos mal diseñados.</p>

<p>Puedes crear nuevos casos de prueba desde los requsititos usando la acción múltiple con los requisitos 
seleccionados en la pantalla de especificación. Estos Casos de Prueba son creados dentro de la Suite de Pruebas
con el nombre definido en la configuración <i>(por defecto es: \$tlCfg->req_cfg->default_testsuite_name =
'Título del Documento de Especificación de Requisitos + (generado automáticamente desde espec. req.)';)</i>.
Título y Descripción son copiados a estos Casos de Prueba.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printReqSpec'] = "Imprimir Especificación de Requisitos"; //printReq
$TLS_htmltext['printReqSpec'] = "<h2>Propósito:</h2>
<p>Desde aquí puedes imprimir un requisito, todos los requisitos de la especificación de requisitos,
o todos los requisitos del proyecto de pruebas.</p>
<h2>Primeros pasos:</h2>
<ol>
<li>
<p>Selecciona las partes de los requisitos que quieres mostrar y luego pulsa en un requisito, 
especificación de requisito o proyecto de pruebas. Se mostrará una página imprimible.</p>
</li>
<li><p>Usa la lista desplegable \"Mostrar como\" del panel de navegación para especificar si quieres
que la información se muestre como HTML o en un documento Microsoft Word. Consulta la
<span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">ayuda</span> para más información.</p>
</li>
<li><p>Usa la funcionalidad de imprimir de tu navegador para imprimir la información.<br />
<i>Nota: Asegúrate de imprimir únicamente el marco derecho de la pantalla.</i></p>
</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['keywordsAssign']	= "Asignación de Keyword";
$TLS_htmltext['keywordsAssign'] 			= "<h2>Propósito:</h2>
<p>La pantalla de asignación de Keywords es el lugar en el que los usuarios pueden
asignar keywords a la Suite de Pruebas existente o a un Caso de Prueba</p>

<h2>Para asignar Keywords:</h2>
<ol>
	<li>Selecciona una Suite de Pruebas o Caso de Prueba en la vista de árbol
		de la izquierda.</li>
	<li>El cuadro de nivel superior que aparece en el lado derecho te
		permitirá asignar keywords disponibles a cada caso
		de prueba.</li>
	<li>Las selecciones siguientes te permiten asignar casos de prueba a un
		nivel más detallado.</li>
</ol>

<h2>Información importante respecto a las asignaciones de Keywords en los Planes de Pruebas:</h2>
<p>Las asignaciones de keyword que realizas a la especificación sólo afectará a casos de prueba
en tus Planes de Prueba si y sólo si el plan de pruebas contiene la última versión del Caso de Prueba.
En otro caso, si el plan de pruebas contiene versiones antiguas de un caso de prueba, las asignaciones que realices
NO APARECERÁN en el plan de pruebas.
</p>
<p>TestLink usa este enfoque para que las versiones antiguas de los casos de prueba en los planes de pruebas no se vean afectadas
por asignaciones de keywords que realices en las versiones más nuevas de los casos de prueba. Si quieres que
los casos de prueba de tu plan de pruebas estén actualizados, primero verifica que están al día utilizando la funcionalidad
'Actualizar Casos de Prueba Modificados' ANTES de realizar la asignación de keyword.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['executeTest']	= "Ejecución de Caso de Prueba";
$TLS_htmltext['executeTest'] 		= "<h2>Propósito:</h2>

<p>Permite al usuario ejecutar casos de prueba. El usuario puede asignar resultados de prueba
a un Caso de Prueba para cada Build. Consulta la ayuda para más información sobre filtrado y configuración " .
		"(pulsa en el icono con el signo de interrogación).</p>

<h2>Primeros pasos:</h2>

<ol>
	<li>El usuario debe definir una Build para el Plan de Pruebas.</li>
	<li>Selecciona una Build de la lista desplegable</li>
	<li>Si quieres ver sólo algunos casos de prueba en lugar de todo el árbol,
		puedes seleccionar que filtros aplicar. Pulsa el botón \"Aplicar\"
		después de modificar los filtros.</li>
	<li>Pulsa en un caso de prueba del árbol.</li>
	<li>Selecciona el resultado del caso de prueba y completa las notas y la asignación de defectos.</li>
	<li>Guarda los resultados.</li>
</ol>
<p><i>Nota: TestLink debe ser configurado para trabajar con un Gestor de Defectos 
si quieres crear/enlazar un defecto directamente desde la interfaz.</i></p>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['showMetrics']	= "Descripción de Informes de Pruebas y Métricas";
$TLS_htmltext['showMetrics'] 		= "<p>Los informes están relacionados a un Plan de Pruebas " .
		"(definido en la parte superior del Navegador). Este Plan de Pruebas puede ser diferente al
Plan de Pruebas actual para la ejecución. Además puedes seleccionar el formato del informe:</p>
<ul>
<li><b>Normal</b> - el informe es mostrado en una página web</li>
<li><b>OpenOffice Writer</b> - el informe es importado a OpenOffice Writer</li>
<li><b>OpenOffice Calc</b> - el informe es importado a OpenOffice Calc</li>
<li><b>MS Excel</b> - el informe es importado a Microsoft Excel</li>
<li><b>Email en formato HTML</b> - el informe es enviado a la dirección de correo del usuario</li>
<li><b>Gráficos</b> - el informe incluye gráficos (tecnología flash)</li>
</ul>

<p>El botón de imprimir activa solamente la impresión del informe (sin la navegación).</p>
<p>Hay varios informes para elegir, su propósito y función se explican a continuación.</p>

<h3>Informe de Plan de Pruebas</h3>
<p>El documento 'Plan de Pruebas' tiene opciones para definir el contenido y la estructura del documento.</p>

<h3>Informe de Pruebas</h3>
<p>El documento 'Informe de Pruebas' tiene opciones para definir el contenido y la estructura del documento.
Incluye casos de prueba junto con sus resultados de ejecución.</p>

<h3>Métricas Generales del Plan de Pruebas</h3>
<p>Esta pantalla te muestra sólo el estado más actual de un Plan de Pruebas por Suite de Pruebas, propietario y keyword.
El estado más 'actual' está determinado por la build más reciente en la que los casos de prueba fueron ejecutados. Por
ejemplo, si un caso de prueba fue ejecutado en varias builds, sólo el último resultado es tenido en cuenta.</p>

<p>'Último Resultado de Prueba' es un concepto usado en muchos informes y está determinado por lo siguiente:</p>
<ul>
<li>El orden en el que las builds son añadidas al Plan de Pruebas determina que build es la más reciente. Los resultados
de la build más reciente prevalece sobre las builds más antiguas. Por ejemplo, si marcas un caso de prueba como
'fallado' en la build 1, y como 'pasado' en la build 2, el último resultado será 'pasado'.</li>
<li>Si un caso de prueba es ejecutado varias veces en la misma build, la ejecución más reciente
prevalecerá. Por ejemplo, si la build 3 es entregada a tu equipo y el tester 1 la marca como 'pasada' a las 14:00,
y el tester 2 la marca como 'fallada' a las 15:00 - aparecerá como 'fallada'.</li>
<li>Los casos de prueba mostrados como 'no ejecutados' en una build no se tienen en cuenta. Por ejemplo, si marcas
un caso como 'pasado' en la build 1, y no lo ejecutas en la build 2, su último resultado será considerado como
'pasado'.</li>
</ul>
<p>Se muestran las siguientes tablas:</p>
<ul>
	<li><b>Resultados por Suites de Pruebas de nivel superior</b>
	Lista los resultados de cada suite de nivel superior. Se listan los casos totales, pasados, fallados, bloqueados, no ejecutados
	y el porcentaje completado. Un caso de prueba 'completado' es aquel que ha sido marcada como pasado, fallado o bloqueado.
	Los resultados por suite de pruebas de nivel superior incluyen a todas las suites de pruebas hijas.</li>
	<li><b>Resultados por Keyword</b>
	Lista todas las keyword que son asignadas a casos en el plan de pruebas actual y el resultado asociado
	a ellas.</li>
	<li><b>Resultados prop propietario</b>
	Lista cada propietario con casos de prueba asignados a él en el plan de pruebas actual. Los casos de prueba que
	no están asignados aparecen bajo el epígrafe 'no asignados'.</li>
</ul>

<h3>Estado general de la Build</h3>
<p>Lista los resultados de ejecución para cada build. Para cada build se muestra, el número total de casos de prueba, el total de pasados,
% pasados, el total de fallados, % fallados, el total de bloqueados, % bloqueados, el total de no ejecutados, % no ejecutados. Si un caso de prueba
ha sido ejecutado dos veces en la misma build, la ejecución más reciente es la que se tendrá en cuenta.</p>

<h3>Consulta de Métricas</h3>
<p>Este informe consiste en una pantalla con un formulario de consulta y una pantalla de resultados con la información consultada.
El formulario de consulta muestra una pantalla con 4 controles. Cada control está establecido por defecto de forma que
se maximiza el número de casos de prueba y builds sobre los que puede actuar la consulta. Modificando estos controles se
permite al usuario filtrar los resultados y generar informes específicos para combinaciones de propietarios concretos,
keyword, suite y build.</p>

<ul>
<li><b>Keyword.</b> Pueden ser seleccionadas de 0->1 keywords. Por defecto - no hay keywords seleccionadas. Si no se selecciona
ninguna keyword, entonces todos los casos de prueba son considerados independientemente de la asignación de keyword. Las keywords son
asignadas en las pantallas de especificación de prueba o de gestión de Keywords. Las keywords asignadas a los casos de prueba abarcan todos los
planes de pruebas, y todas las versiones de un caso de prueba. Si estás interesado en los resultados de una keyword en concreto
podrías modificar este control.</li>
<li><b>Propietario.</b> Pueden ser seleccionados de 0->1 propiestarios. Por defecto - no hay propietario seleccionado. Si no se selecciona
ningún propiestario, entonces todos los casos de prueba son considerados independientemente de la asignación de propietario. Actualmente no es posible
buscar casos de prueba 'no asignados'. La propiedad es asignada a través de la pantalla de 'Asignar ejecución de Caso de Prueba',
y se realiza por cada plan de pruebas. Si te interesa conocer el trabajo realizado por un tester en concreto, podrías
modificar este control.</li>
<li><b>Suite de nivel superior.</b> Pueden ser seleccionadas de 0->n suites de nivel superior. Por defecto se seleccionan todas las suites de pruebas.
Sólo las suites que son seleccionadas son tenidas en cuenta para los resultados de las métricas. Sí sólo estás interesado en el resultado de
una suite en concreto, podrías modificar este control.</li>
<li><b>Builds.</b> Pueden ser seleccionadas de 1->n builds. Por defecto - todas están seleccionadas. Sólo se tienen en cuenta
las ejecuciones realizadas en las builds seleccionadas cuando se generan las métricas. Por ejemplo - si quieres
ver cuantos casos de prueba fueron ejecutados en las últimas 3 builds - podrías modificar este control.
Las selecciones de keyword, propietario y suite de nivel superior dictarán el número de casos de prueba de tu plan de pruebas
que son utilizados para calcular las métricas por suite y por plan de pruebas. Por ejemplo, si seleccionar el propiestario = 'Greg',
Keyword='Prioridad 1', y todas las suites de pruebas disponibles - sólo los casos de prueba con Prioridad 1 asignados a Greg serán
considerados. El 'Nº de Casos de Prueba' total que verás en el informe estará influenciado por estos 3 controles.
La selección de build influirá en si un caso es considerado 'pasado', 'fallado', 'bloqueado', o 'no ejecutado'.  Por favor,
consulta las reglas de 'Último Resultado de Prueba' indicadas anteriormente.</li>
</ul>
<p>Pulsa el botón 'enviar' para realizar la consulta y ver la pantalla de resultados.</p>

<p>La pantalla del Informe de Consulta mostrará: </p>
<ol>
<li>los parámetros de consulta usados para crear el informe</li>
<li>los totales para el plan de pruebas completo</li>
<li>un desglose de los totales por suite (total / pasados / fallados / bloqueados / no ejecutados) y todas las ejecuciones realizadas
en esa suite. Si un caso de prueba ha sido ejecutado más de una vez en diferentes builds - todas las ejecuciones en las builds
seleccionadas serán mostradas. Sin embargo, el resumen para cada suite sólo
incluirá el 'Último Resultado de Prueba' para las builds seleccionadas.</li>
</ol>

<h3>Informes de Casos de Prueba Bloqueados, Fallados y No Ejecutados</h3>
<p>Estos informes muestran todos los casos de prueba actualmente bloqueados, fallados o no ejecutados. La lógica del 'Último
Resultado de Prueba' (descrita anteriormente en las Métricas generales del Plan de Pruebas) se emplea de nuevo para determinar si
un caso de prueba debería ser considerado como bloqueado, fallado o no ejecutado. Los informes de casos de prueba bloqueados y fallados
mostrarán los defectos asociados si el usuario está usando un gestor de defectos integrado.</p>

<h3>Informe de Pruebas</h3>
<p>Ver el estado de cada caso de prueba en cada build. Se utilizará el resultado de la ejecución más reciente
si un caso de prueba fue ejecutado varias veces en la misma build. Se recomienda exportar este informe
a formato Excel para una navegación más sencilla si se está utilizando una gran cantidad de datos.</p>

<h3>Gráficos - Métricas Generales del Plan de Pruebas</h3>
<p>La lógica del 'Último Resultado de Prueba' se aplica en los cuatro gráficos que puedes ver. Los gráficos están animados para ayudar
al usuario a visualizar las métricas del plan de pruebas actual. Los cuatro gráficos proporcionados son:</p>
<ul><li>Gráfico de tarta del total de casos de prueba pasados / fallados / bloqueados / y no ejecutados</li>
<li>Gráfico de barras de los Resultados por Keyword</li>
<li>Gráfico de barras de los Resultados por Propietario</li>
<li>Gráfico de barras de los Resultados por Suite de Nivel Superior</li>
</ul>
<p>Las barras de los gráficos de barras están coloreados de forma que el usuario pueda identificar de forma aproximada el número de
casos pasados, fallados, bloqueados y no ejecutados.</p>

<h3>Defectos Totales para cada Caso de Prueba</h3>
<p>Este informe muestra cada caso de prueba con todos los defectos asociados en todo el proyecto de pruebas.
Este informe sólo está disponible si hay un Gestor de Defectos conectado.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTC']	= "Añadir / Quitar Casos de Prueba del Plan de Pruebas"; // testSetAdd
$TLS_htmltext['planAddTC'] 			= "<h2>Propósito:</h2>
<p>Los usuarios con privilegios (con un nivel de permisos determinado) pueden añadir o quitar casos de prueba de un Plan de Pruebas.</p>

<h2>Añadir o quitar Casos de Prueba:</h2>
<ol>
	<li>Pulsa en una suite de pruebas para ver todas sus suites de pruebas y todos sus casos de prueba.</li>
	<li>Posteriormente pulsa el botón 'Añadir / Quitar Casos de Prueba' para añadir o quitar los casos de prueba.
		Nota: No es posible añadir el mismo caso de prueba varias veces.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['tc_exec_assignment']	= "Asignar Testers a la ejecución de pruebas";
$TLS_htmltext['tc_exec_assignment'] 		= "<h2>Propósito</h2>
<p>Esta pantalla permite a los líderes de pruebas asignar usuarios a casos de prueba particulares del Plan de Pruebas.</p>

<h2>Primeros pasos:</h2>
<ol>
	<li>Elige un Caso de Prueba o una Suite de Pruebas para probar.</li>
	<li>Selecciona un tester.</li>
	<li>Pulsa el botón para confirmar la asignación.</li>
	<li>Abre la pantalla de ejecución para verificar la asignación. Puedes configurar un filtro para los usuarios.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planUpdateTC']	= "Actualizar Casos de Prueba del Plan de Pruebas";
$TLS_htmltext['planUpdateTC'] 		= "<h2>Propósito</h2>
<p>Esta pantalla permite actualizar el Caso de Prueba a una nueva (diferente) versión en el caso de que la Especificación
de Pruebas haya cambiado. Sucede a menudo que alguna funcionalidad se clarifica durante el testing." .
		" El usuario modifica la Especificación de Pruebas, pero es necesario que los cambios se propaguen también al Plan de Pruebas. En otro caso, el Plan" .
		" de Pruebas mantiene la versión original para estar seguros de que el resultado se refiere al text correcto del Caso de Prueba.</p>

<h2>Primeros pasos:</h2>
<ol>
	<li>Elige un Caso de Prueba o Suite de Pruebas para probar.</li>
	<li>Elige una nueva versión para un Caso de Prueba en particular.</li>
	<li>Pulsa el botón 'Actualizar Plan de Pruebas' para aplicar los cambios.</li>
	<li>Para verificar: Abre la pantalla de ejecución para ver el text del caso de prueba.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['test_urgency']	= "Especificar casos de prueba con alta o baja urgencia";
$TLS_htmltext['test_urgency'] 		= "<h2>Propósito</h2>
<p>TestLink permite establecer la urgencia de una Suite de Pruebas para afectar a la Prioridad de los casos de prueba. 
		La prioridad de los casos depende tanto de la Importancia de los casos de prueba como de la Urgencia definida en 
		el Plan de Pruebas. El lider de pruebas debe especificar el conjunto de casos de prueba que deberían ser probados
		en primer lugar. Esto ayuda a asegurar que el testing cubrirá los casos de prueba más importantes
		incluso bajo presiones de tiempo.</p>

<h2>Primeros pasos:</h2>
<ol>
	<li>Elige una Suite de Pruebas para establecer la urgencia de un producto/componente en el navegador
	del lado izquierdo de la pantalla.</li>
	<li>Elige un nivel de urgencia (alta, media o baja). Media es el valor por defecto. Puedes
	bajar la prioridad de partes de un producto que no han sido modificadas e incrementar la de componentes con
	cambios significativos.</li>
	<li>Pulsa el botón 'Guardar' para aplicar los cambios.</li>
</ol>
<p><i>Por ejemplo, un Caso de Prueba con una importancia Alta en una Suite de Pruebas con Baja urgencia " .
		"tendrá una prioridad Media.</i>";


// ------------------------------------------------------------------------------------------

?>
