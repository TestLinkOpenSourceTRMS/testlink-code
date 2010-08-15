<?php
/**
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: texts.php,v $
 * @version $Revision: 1.5 $
 * @modified $Date: 2010/08/15 10:45:21 $ by $Author: franciscom $
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

$TLS_htmltext_title['assignReqs']	= "Asignar requerimientos a los Casos de prueba";
$TLS_htmltext['assignReqs'] 		= "<h2>Propósito:</h2>
<p>Los usuarios pueden establecer relaciones entre los Requerimientos y los Casos de prueba.
Un diseñador podria definir relaciones 0..n a 0..n I.e. un Caso de prueba podria ser asignado a ninguno, uno o más
Requerimientos y viceversa.La trazabilidad ayuda a investigar la cobertura de los requerimientos de los Casos de prueba
y a encontrar cuales fallan durante el testeo.Este análisis sirve como insumo para la planificación de la próxima.</p>

<h2>Primeros pasos:</h2>
<ol>
	<li>Seleccione un Caso de prueba en el árbol de la izquierda. El cuadro combinado 
        con la lista de Especificaciones de Requerimientos se muestra en la parte superior 
        del área de trabajo .</li>
	<li>Seleccione un documento de especificaciones de requerimientos si se encuentra definido 
        una vez más.Testlink automáticamente recarga la página.</li>
	<li>El bloque intermedio del área de trabajo registra todos los requerimientos
        (de la especificación seleccionada), los cuales están unidos al Caso de prueba. 
        El bloque de fondo 'Requerimientos disponibles' lista todos los requerimientos que no poseen 
        relación  al Caso de prueba actual. Un diseñador podría marcar requerimientos, los cuales están 
        cubiertos por el Caso de prueba,  luego haga clic en la botón ‘Asignar’. Estos nuevos casos de 
        prueba asignados se muestran en el bloque intermedio de “Requerimientos Asignados”.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReqSpec'] = "Buscar Especificación de Requerimientos"; //printReq
$TLS_htmltext['searchReqSpec'] = "<h2>Propósito:</h2>
<p>Navegar acorde a keywords y/o palabras buscadas. La busqueda no es un caso sensible. El resultado incluye solo
las especificaciones de requerimientos del proyecto actual.</p>

<h2>Comenzar:</h2>

<ol>
	<li>Escribe la palabra a buscar en el cuadro apropiado. Deje campos sin usar en blanco.</li>
	<li>Elige la keyword requerida o deje el valor en 'no aplicado'.</li>
	<li>Haga click en el botón 'Encontrar'.</li>
	<li>Se muestran todos los requisitos de cumplimiento.Puede modificar las especificaciones de requerimientos por medio del vínculo 'Titulo'.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReq']	= "Buscar Requerimientos";
$TLS_htmltext['searchReq'] 		= "<h2>Próposito:</h2>


<p>Navegar acorde a keywords y/o palabras buscadas. La busqueda no es un caso sensible. El resultado incluye solo
los requerimientos del proyecto actual..</p>

<h2>Para buscar:</h2>

<ol>
	<li>Escribe la palabra a buscar en el cuadro apropiado. Deje campos sin usar en blanco.</li>
	<li>Elige la keyword requerida o deje el valor en 'no aplicado'.</li>
	<li>Haga click en el botón 'Encontrar'.</li>
	<li>Se muestran todos los requerimientos de cumplimiento.Puede modificar los requerimientos 
        por medio del vínculo 'Titulo'.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printReqSpec'] = "Imprimir Especificación de Requerimientos"; //printReq
$TLS_htmltext['printReqSpec'] = "<h2>Propósito:</h2>
<p>Desde aquí usted puede imprimir un requerimiento en particular, todos los requerimientos 
dentro de la especificación de requerimientos,o todos los requerimientos del Proyecto.</p>
<h2>Comenzar:</h2>
<ol>
<li>
<p>Seleccione las partes de los requerimientos que desea mostrar y, a continuación, haga clic en un requerimiento, 
en el requerimiento de especificación, o en el proyecto. Una página para imprimir en la pantalla.</p>
</li>
<li><p>Use el cuadro \"Mostrar como\" en el panel de navegación para especificar si quiere que la información 
se muestre como HTML, documento de word o de OpenOffice.</p>
</li>
<li><p>Use la función de imprimir de su explorer para imprimir la información actual.<br />
 <i>Nota: Aseguresé de solo imprimir el marco de la mano derecha.</i></p>
</li>
</ol>";

// --------------------------------------------------------------------------------------
$TLS_htmltext_title['editTc']	= "Especificaciones de pruebas";
$TLS_htmltext['editTc'] 		= "<h2>Propósito:</h2>
<p>La <i>Especificación de pruebas</i> permite a los usuarios ver y editar todos las " .
		"<i>Suites de pruebas</i> y los <i>Casos de prueba</i> existentes. Los Casos de prueba están versionados y todas " .
		"las versiones anteriores estan disponibles y pueden ser vistas y gestionadas desde aquí.</p>

<h2>Primeros pasos:</h2>
<ol>
	<li>Seleccione un Proyecto en el árbol de navegación. <i>Tenga en cuenta que " .
	"Siempre puede cambiar el Proyecto activo seleccionando uno diferente en la " .
	"lista desplegable que está en la esquina superior-derecha de la página.</i></li>
	<li>Cree una nueva Suite de prueba haciendo click en <b>(Crear nueva Suite de prueba)</b>.La Suite de prueba puede " .
	"brindarle estructura a sus Documentos, de acuerdo a sus convenciones. La descripción de " .
	"una Suite de prueba podría tener el alcance de los Casos de prueba incluídos, configuración por defecto, " .
	"enlaces a documentos relevantes, limitaciones y otra información util. En general, " .
	"todas las anotaciones son comunes a los Casos de prueba.</li>
	<li>Las Suites de pruebas son carpetas escalables. Por lo cual un usuario puede mover y copiar Suites de pruebas dentro del " .
	"Proyecto. Además, pueden ser importadas o exportadas (incluyendo los Casos de prueba).
	<li>Seleccione su Suite de pruebas recien creada en el árbol de navegación y cree " .
	"un nuevo Caso de prueba haciendo click en <b>Crear Casos de prueba</b>. Un Caso de prueba precisa " .
	"un escenario de testing particular, resultados esperados y campos personalizados definidos " .
	"en el Proyecto. También es posible " .
	"asignar <b>Palabras Clave</b> para una mejor trazabilidad.</li>
	<li>Navegue a través de la vista de árbol en el lado izquierdo y edite datos. Los Casos de prueba 
         almacenan su propio historial.</li>
</ol>

<p>con Testlink organize los Casos de prueba dentro de las Suites de pruebas." .
"Las Suites de pruebas se pueden anidar dentro de otras Suites, esto permite crear jerarquias de Casos de prueba.
 Entonces usted puede imprimir esta información junto con los Casos de prueba.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchTc']	= "Busar Casos de prueba";
$TLS_htmltext['searchTc'] 		= "<h2>Próposito:</h2>

<p>Navegar acorde a Palabras Clave y/o palabras buscadas.El resultado incluye solo los Casos de prueba del Proyecto actual.</p>

<h2>Para buscar:</h2>

<ol>
	<li>Escribe el dato buscado en el cuadro apropiado. En los campos blancos en el formulario a la izquierda.</li>
	<li>Elige las Palabras Clave requeridas o el valor 'No aplicado'.</li>
	<li>Haga Click en el botón <b>'Buscar'</b>.</li>
	<li>Todos los Casos de prueba cumplidos se muestran. Usted puede modificar los Casos de prueba a través del <b>'Titulo'</b>.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printTestSpec']	= "Imprimir Especificación de pruebas"; //printTC.html
$TLS_htmltext['printTestSpec'] 			= "<h2>Propósito:</h2>
<p>Desde aquí usted puede imprimir un Caso de prueba en particular, todos los Casos de prueba dentro de la Suite de pruebas,
o todos los Casos de prueba del Proyecto o del Plan de pruebas.</p>
<h2>Comenzar:</h2>
<ol>
<li>
<p>Seleccione los datos del Caso de prueba que quiere imprimir, y luego haga click en el Caso de prueba, Suite de pruebas, o en el Proyecto.
Una página imprimible se va a mostrar.</p>
</li>
<li><p>Use el cuadro \"Mostrar como\" en el panel de navegación para especificar si quiere que la información se muestre como HTML o en un
documento de Word.</p>
</li>
<li><p>Use la función de imprimir de su explorer para imprimir la información actual.<br />
 <i>Nota: Aseguresé de solo imprimir el marco de la mano derecha.</i></p></li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['reqSpecMgmt']	= "Diseño de Especificación de Requerimientos"; //printTC.html
$TLS_htmltext['reqSpecMgmt'] 			= "<p>Usted puede administrar los documentos de especificación de requerimientos.</p>

<h2>Especificación de requerimientos</h2>

<p>Los requerimientos estan agrupados por <b>documento de especificación de requerimientos</b>, el cual esta relacionado con
el Proyecto.<br /> Testlink no soporta (aún) versiones tanto para la especificación de requerimientos como para los requerimientos en sí mismos.
Así, la versión del documento debe añadirse después de la Especificación de un <b>Titulo</b>.
Un usuario puede añadir una descripción simple o una nota en el campo <b>Alcance</b>.</p>

<p><b>Sobreescribir el contador de Reqs</b> sirve para
evaluar la cobertura de Reqs.en caso de que no todos los requerimientos se añadan a Testlink.
El valor <b>0</b> significa que el contador actual de Reqs. se utiliza para las métricas.
<p><i>Por ejemplo SRS incluye 200 requerimientos pero solo 50 son añadidos a Testlink. La cobertura 
de las pruebas es del 25% (si todos estos requerimientos añadidos se testearan).</i></p>

<h2>Requerimientos</h2>

<p>Haga click en el titulo de la Especificación de requerimientos creada,si no hay nada existente haga 
click en el proyecto para crear uno. Puede crear, editar, eliminar o importar requerimientos 
de un documento. Cada requerimiento tiene un titulo, un alcance y un estado.
El estado debe ser 'Normal' o 'No testeable'. Los requerimientos no testeables no tienen contador 
para las métricas. Este parámetro debe ser usado tanto para características que no se han implementado como para
requerimientos mal diseñados.</p>

<p>Puede crear nuevos casos de prueba para requerimientos mediante el uso de acciones multiples con requerimientos
seleccionados dentro de la pantalla de especificación. Estos casos de prueba son creados dentro de la Suite de pruebas
con un nombre definido en la configuración. El Titulo y alcance son copiados a estos casos de prueba.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['keywordsAssign']	= "Asignación de Keywords";
$TLS_htmltext['keywordsAssign'] 			= "<h2>Propósito:</h2>
<p>La página de asignación de Keywords es donde los usuarios pueden asignar Keywords a la Suite 
de pruebas existente o a un Caso de prueba</p>

<h2>Para asignar Keywords:</h2>
<ol>
	<li>Seleccione una Suite de pruebas, un Caso de pruebas en la vista de árbol
		de la izquierda.</li>
	<li>El cuadro de nivel superior que aparece en el lado derecho le permitirá asignar 
         palabras clave a disposición de todos los casos de prueba en particular.</li>
	<li>Las selecciones siguientes le permiten asignar los casos de prueba a un nivel más específico</li>
</ol>

<h2>Información importante respecto a las asignaciones de Keywords en los planes de pruebas:</h2>
<p>Las asignaciones de keywords que realice en el pliego de condiciones sólo tienen efecto en casos de 
prueba en su plan de pruebas si y sólo si el plan de pruebas contiene la versión más reciente del caso de prueba.
De otra manera si el Plan de pruebas contiene versiones antiguas del Caso de prueba, las asignaciones que haga
ahora no apareceran en el Plan de pruebas.</p>
<p>Testlink utiliza este enfoque para que las versiones anteriores de casos de prueba en los planes de 
prueba no sean afectadas por las asignaciones de Keywords que realice en la versión más reciente 
del caso de prueba. Si usted quiere que se actualizen sus casos de prueba dentro del plan de pruebas, PRIMERO verifique 
que ellos esten al día usando la funcionalidad de 'Actualizar Casos de prueba modificados' ANTES de realizar asignaciones de Keywords.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['executeTest']	= "Ejecución de casos de prueba";
$TLS_htmltext['executeTest'] 		= "<h2>Propósito:</h2>

<p>Permite a los usuarios ejecutar casos de prueba. Los usuarios pueden asignarle un resultado por build a los casos de prueba.</p>

<h2>Primeros pasos:</h2>

<ol>
	<li>El usuario debe tener definida una build para el Plan de pruebas.</li>
	<li>Seleccione una build del cuadro desplegable y haga click en el botón \"Aplicar\" en el panel de navegación.</li>
	<li>Haga Click en un Caso de prueba en el menú de árbol.</li>
	<li>Rellen el resultado del caso de prueba y el de notas aplicables o bugs.</li>
	<li>Guarde los resultados.</li>
</ol>
<p><i>Nota: Testlink debe estar configurado para colaborar con su seguidor de bug 
si usted desea crear / trazar un problema directamente reportado desde la GUI.</i></p>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['showMetrics']	= "Descripción del reporte y de las métricas";
$TLS_htmltext['showMetrics'] 		= "<p>Los reportes están relacionados con el plan de pruebas" .
		"(definido  en la parte superior del navegador). Este plan de pruebas podría diferir del
plan de pruebas actual para ejecutar. También puede seleccionar un formato de reporte:</p>
<ul>
<li><b>Normal</b> - el reporte se muetras en la página web</li>
<li><b>MS Excel</b> - el reporte es exportado en un archivo de microsoft excel</li>
<li><b>HTML Email</b> - el reporte es enviado por mail a la dirección del usuario</li>
<li><b>Gráficos</b> - el reporte incluye gráficos (tecnología flahs)</li>
</ul>

<p>El botón te imprimir activa solamente la impresión del reporte (sin la navegación).</p>
<p>Hay varios informes por separado para elegir, su propósito y función se explican a continuación.</p>

<h3>Métricas generales del plan de pruebas</h3>
<p>Esta página solo le muestra el estado más actual del plan de pruebas por suite de pruebas , dueño, y keywords.
El 'estado más actual' es determinado por la más reciente build de caso de prueba ejecutada.  Por
ejemplo, si un caso de prueba fue ejecutado a través de multiples builds, sólo el último resultado de prueba se tendrá en cuenta.</p>

<p>'Ultimo resultado de prueba' es un concepto usado en varios reportes, y es determinado de la siguiente manera:</p>
<ul>
<li>El orden en que se agregan builds al plan de pruebas determina que build es la más reciente. Los resultados de la más 
reciente build tendrá precedencia sobre las antiguas builds. Por ejemplo, si marca una prueba como 'fail'
en la build 1, y marca ésta como 'pass' en la build 2, el resultado final de la prueba será 'pass'.</li>
<li>Si un caso de prueba es ejecutado en tiempos multiples en la misma build, la ejecución más reciente tendrá precedencia.</li>
<li>Los casos de pruebas listados como 'not run' en contra de una build no se tienen en cuenta. Por ejemplo, si marcas un 
caso como 'pass' en la build 1, y no lo ejecutas en la build 2, el resultado final del caso sera considerado como 'pass'</li>
</ul>
<p>En las siguientes tablas se muestran:</p>
<ul>
	<li><b>Resultados por nivel superior de Suite de pruebas</b>
	Muestra los resultados de cada suite de nivel superior. Son mostrados: Casos totales, pasados, fallados, bloqueados, no ejecutados,
        y porcentaje de completados. Un caso de prueba 'completado' es uno que esté marcado como pass, fail, o bloqueado.
	Los resultados de las suite de nivel superior incluye todas las suites internas.</li>
	<li><b>Resultados por keyword</b>
	Muetras todas las keywords que estan asignadas a los casos en el plan actual, y los resultados asociados.</li>
	<li><b>Resultados por dueño</b>
	Muestra cada dueño que tenga casos de prueba asignados en el plan actual. Los casos de prueba que no están asignados
	son listados en el cuadro de 'no asignados'.</li>
</ul>

<h3>El estado general de la build</h3>
<p>Muestra los resultados de la ejecución de todas las builds. Para cada build, el total de casos de prueba,
el total de los pass, % pass, el total de los fail, % fail, blocked, % blocked, not run, %not run.  Si un caso 
de prueba ha sido ejecutado dos veces en la misma build, la más reciente ejecución se tomará en cuenta.</p>

<h3>Consulta de métricas</h3>
<p>Este reporte consiste en una página de consulta, y una página con los resultados de la consulta.
La página de consulta contiene 4 controles. Cada control esta fijado en su defecto. Alterar los controles
le permite a los usuarios filtrar los resultados y generar reportes especificos para dueños especificos, keyword, suite,
y combinaciones de builds.</p>

<ul>
<li><b>keyword</b> 0->1 keywords pueden ser seleccionadas. Por defecto - no hay keywords seleccionadas. Si una keyword
no está seleccionada, entonces todos los casos de prueba seran considerados independietemente de las asignaciones de keywords. Keywords are assigned
Las keywords están asignadas en la especificación de pruebas o en la administración de keywords. Las keywords asignadas a los 
casos de prueba abarcan a todos los planes de pruebas, y abarcan a lo largo de todas las verisones de los casos de prueba.  
Si está interesado en los resultados para una keyword específica debe alterar este control.</li>
<li><b>owner</b> 0->1 dueños pueden ser seleccionado. Por defecto - no hay dueño seleccionado. Si no hay dueños seleccionados,
entonces todos los casos de prueba seran considerados independietemente de las asignaciones de dueños. Actualmente no hay  
una funcionalidad para buscar un caso de prueba 'no asignado'. La propiedad es asignada a través de la página 'Asignar ejecución de caso de prueba',
y se realiza en base a un plan por prueba. Si está interesado en el trabajo hecho por un tester en específico débe modificar
este control.</li>
<li><b>Suite de nivel superior</b> 0->n suites de nivel superior se pueden elegir. Por defecto - todas están seleccionadas.
Solamente las suites que son seleccionadas serán consultadas para los resultados y métricas. Si está interesado solamente 
en los resultados para una suite específica usted debe alterar este control.</li>
<li><b>Builds</b> 1->n builds pueden ser seleccionadas.  Por defecto - todas las builds están seleccionadas. 
Sólo las ejecuciones realizadas en las builds seleccionadas seran tomadas en cuenta en producción de las métricas.
Por ejemplo - si desea ver cuando casos de prueba fueron ejecutados en las ultimas 3 builds débe alterar este control.</li>
</ul>
<p>Presione el botón 'entregar' para proceder con la consulta y mostrar la página de salida.</p>

<p>La página de informe de consultas mostrará: </p>
<ol>
<li>Los parámetros de consulta usados para crear el reporte.</li>
<li>Totales para el plan de pruebas completo.</li>
<li>Un total por suite de (sum / pass / fail / bloqueado / not run) y todas las ejecuciones realizadas
en esa suite.  Si un caso de prueba ha sido ejecutado mas de una vez en multiples builds - todas las ejecuciones que
se registraron contra las builds seleccionadas serán mostradas. Sin embargo, el resumen para esta suite
solamente incluirá el 'Ultimo resultado de prueba' para las builds seleccionadas.</li>
</ol>

<h3>Reportes de casos de pruebas 'Bloqueado', 'Falló', y 'Not Run'</h3>
<p>Estos reportes muestran todos los casos de pruebas 'bloqueado', 'falló' y 'no ejecutado' actualmente.El 'ultimo resultado de prueba'
logicamente es el usado para determinar si un caso de prueba se considera 'bloqueado', 'falló', o 'no ejecutado'.</p>

<h3>Reporte</h3>
<p>Ver el estado de cada caso de prueba en cada build. Si un caso de pruebas fue ejecutado varias veces en 
la misma build, solamente el resultado de ejecución más reciente será tenido en cuenta.Se recomienda
para exportar este reporte el formato Excel para una facil navegación o si se está utilizando 
un conjunto de datos grande.</p>

<h3>Listas - Métricas generales del plan de pruebas</h3>
<p>'Ultimo resultado de prueba' la lógica se utiliza para las cuatro cartas que se pueden ver.Los gráficos 
se han animado para ayudar al usuario a visualizar los parámetros del plan de pruebas en curso. 
Las cuatro gráficas proporcionan son :</p>
<ul><li>Pie de tabla general 'paso / no / bloqueado / y no ejecutado' de los casos de prueba</li>
<li>Gráfico de barras de los resultados por keyword</li>
<li>Gráfico de barras de los resultados por dueño</li>
<li>Gráfico de barras de los resultados por suite de nivel superior</li>
</ul>
<p>Las barras de los gráficos de barras son de color de manera que el usuario puede identificar el 
número aproximado de 'paso, no, bloqueado, y no ejecutado' de los casos de prueba.</p>
<p><i>Esta página de reporte requiere un plugin flash es su explorador web (by http://www.maani.us) para mostrar
los resultados en un formato gráfico.</i></p>

<h3>Total de bugs para cada Caso de prueba</h3>
<p>Este reporte muestra cada caso de prueba con with todos los errores reportados en contra de todo el proyecto..
Este reporte esta disponible si el sistema de seguimiento de bugs esta conectado (bugzilla por ej.).</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTC']	= "Añadir / Quitar Casos de prueba del plan de pruebas"; // testSetAdd
$TLS_htmltext['planAddTC'] 			= "<h2>Propósito:</h2>
<p>Permite a los líderes añadir o quitar casos de prueba dentro del plan de pruebas.</p>

<h2>Comenzar:</h2>
<ol>
	<li>Haga click en una suite de pruebas para ver todas las suites de pruebas y todos los casos de prueba.</li>
	<li>Cuando termine haga click en el botón 'Añadir / quitar casos de prueba'para agregar o eliminar los casos de prueba.
		Nota: No es posible añadir el mismo caso de prueba varias veces.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['tc_exec_assignment']	= "Asignar Usuarios a la ejecuciones";
$TLS_htmltext['tc_exec_assignment'] 		= "<h2>Propósito</h2>
<p>Esta página le permite a los líderes asignar usuarios a casos particulares dentro del plan de pruebas.</p>

<h2>Comenzar:</h2>
<ol>
	<li>Elige un caso de prueba o una suite de pruebas a testear.</li>
	<li>Seleccione un usuario previsto.</li>
	<li>Presione el boton para presentar la asignación.</li>
	<li>Abra la página de ejecución para verificar la asignación. Puede establecer filtros a los usuarios.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planUpdateTC']	= "Actualizar los casos de prueba en los planes de prueba";
$TLS_htmltext['planUpdateTC'] 		= "<h2>Propósito</h2>
<p>Esta página permite actualizar los casos de prueba a una version nueva (diferente) 
                en el caso de que la especificación de pruebas halla cambiado. Sucede a menudo que algunas funciones se aclaran durante el testeo.
		Los usuarios modifican la especificación de pruebas, pero los cambios necesitan propagarse al plan de pruebas también. De otro modo.
                los planes de prueba mantienen las versiones originale para estar seguro que los resultados se refieren al texto correco del caso de prueba.</p>

<h2>Empezar</h2>
<ol>
	<li>Elige un caso de prueba o una suite de pruebas para testear.</li>
	<li>Elige una nueva versión para un caso de prueba en particular.</li>
	<li>Presione el botón 'Actualizar plan de pruebas' para realizar cambios.</li>
	<li>Para verificar: abra la página de ejecución para ver el texto del caso de prueba.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['test_urgency']	= "Especificar la urgencia de la prueba";
$TLS_htmltext['test_urgency'] 		= "<h2>Propósito</h2>
<p>Testlink permite establecer urgencias en la Suite de pruebas para afectar la prioridad de testeo de los casos de prueba. 
		La prioridad de pruebas depende de la importancia de un caso de prueba 
                y la urgencia definida en el plan de pruebas. El lider de pruebas debe especificar los casos de prueba
                que deberían ser testeados en primer lugar.
		Esto ayuda a asegurar que el testeo cubrirá las pruebas más importantes bajo la presión del tiempo.</p>

<h2>Primeros pasos:</h2>
<ol>
	<li>Elige una Suite de pruebas para establecer la urgencia de un producto/componente,
        en el navegador en el lado izquierdo de la ventana.</li>
	<li>Elige un nivel de urgencia (alta, media o baja). Por defecto es media. Puede
	bajarle prioridad a partes sin tocar del producto e incrementarle a componentes con 
        cambios significativos.</li>
	<li>Presione el boton 'Guardar' para realizar los cambios.</li>
</ol>
<p><i>Por ejemplo, un caso de prueba con alta importancia en una suite de pruebas de baja importancia 
tiene prioridad media.</i>";


// ------------------------------------------------------------------------------------------

?>