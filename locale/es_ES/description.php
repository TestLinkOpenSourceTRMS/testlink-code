<?php
/** -------------------------------------------------------------------------------------
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Localization: Spanish (es_ES) texts - en_GB is the default development localization (World-wide English)
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
 * No revision is stored for the file - see CVS history
 * 
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2003-2009, TestLink community 
 * @version    	CVS: $Id: description.php,v 1.17 2010/09/13 09:52:42 mx-julian Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 * 20100409 - eloff - BUGID 3050 - Update execution help text
 *
 * ------------------------------------------------------------------------------------- 
 * Spanish (es_ES) translation
 * -------------------------------------------------------------------------------------
 * Translated by: Jesus Hernandez
 * Date: 2014/11/04
 * -------------------------------------------------------------------------------------
 **/

// printFilter.html
$TLS_hlp_generateDocOptions = "<h2>Opciones para generar un documento</h2>

<p>Esta tabla permite al usuario filtrar los casos de prueba antes de ser visualizados. Si
está seleccionada (marcada) la información será mostrada. Para cambiar los datos
presentados, marca o desmarca, pulsa en el Filtro y selecciona el nivel de información
deseada desde el árbol.</p>

<p><b>Cabecera del Documento:</b> Los usuarios pueden filtrar la información de la cabecera. 
La información de la cabecera incluye: Introducción, Alcance, Referencias, 
Metodología de Pruebas y Limitaciones de Pruebas.</p>

<p><b>Cuerpo del Caso de Prueba:</b> Los usuarios pueden filtrar la información del cuerpo de los Casos de Prueba. La información del cuerpo de los Casos de Prueba
incluye: Resumen, Pasos, Resultados Esperados y Keywords.</p>

<p><b>Resumen del Caso de Prueba:</b> Los usuarios pueden filtrar la información del Resumen del Caso de Prueba desde el título,
dien embargo, no pueden filtrar la información del Resumen del Caso de Prueba desde el Cuerpo
del Caso de Prueba. El Resumen del Caso de Prueba sólo ha sido separado parcialmente del Cuerpo del Caso de Prueba
con el fin de soportar la vista de Título con un breve Resumen y la ausencia de
Pasos, Resultados Esperados y Keywords. Si un usuario decide ver el Cuerpo del Caso
de Prueba, el Resumen del Caso de Prueba estará siempre incluido.</p>

<p><b>Índice de Contenidos:</b> TestLink inserta una lista con todos los títulos con enlaces internos si está seleccionado.</p>

<p><b>Formato de Salida:</b> Hay dos posibilidades: HTML y MS Word. El navegador llama al componente MS Word 
en segundo caso.</p>";

// testPlan.html
$TLS_hlp_testPlan = "<h2>Plan de Pruebas</h2>

<h3>General</h3>
<p>Un plan de pruebas es una aproximación sistemática al testing de sistemas tales como el software. Puedes organizar las actividades de testing con 
builds particulares para cada producto en concreto con resultados trazables en el tiempo.</p>

<h3>Ejecución de Pruebas</h3>
<p>Esta sección es donde los usuarios pueden ejecutar Casos de Prueba (escribir los resultados de las pruebas) e 
imprimir la suite de Casos de Prueba del Plan de Pruebas. Esta sección es donde los usuarios pueden realizar un seguimiento de  
los resultados de sus ejecuciones de casos de prueba.</p> 

<h2>Gestión del Plan de Pruebas</h2>
<p>Esta sección, a la que sólo pueden acceder usuarios con determinados privilegios, permite a los usuarios administrar planes de pruebas. 
La administración de planes de pruebas incluye la crear/editar/borrar planes, 
añadir/editar/borrar/actualizar casos de prueba en planes, crear builds así como definir quién puede 
ver cada plan.<br />
Los usuarios con suficientes permisos pueden además establecer la prioridad/riesgo y la propiedad de 
las suites de Casos de Prueba (categorías) y crear hitos de prueba.</p> 

<p>Nota: Es posible que los usuarios no vean ninguna lista desplegable conteniendo Planes de Pruebas. 
En ese caso, todos los enlaces (excepto aquellos habilitados) estarán desenlazados. Si estás 
en esa situación debes ponerte en contacto con el administrados para que te proporcione los 
privilegios de proyecto pertinentes o para crear un Plan de Pruebas para ti.</p>"; 

// custom_fields.html
$TLS_hlp_customFields = "<h2>Campos Personalizados</h2>
<p>Información relacionada con la implementación de campos personalizados:</p>
<ul>
<li>Los campos personalizados se definen para todo el sistema.</li>
<li>Los campos personalizados se enlazan a un tipo de elemento (Suite de Pruebas, Caso de Prueba)</li>
<li>Los campos personalizados pueden ser asignados a varios Priyectos de Pruebas.</li>
<li>El orden de visualización de los campos personalizados puede ser diferentes en cada Proyecto de Pruebas.</li>
<li>Los campos personalizados pueden desactivarse para un Proyecto de Pruebas específico.</li>
<li>El número de campos personalizados no está limitado.</li>
</ul>

<p>La definición de un campo personalizado incluye los siguientes atributos 
lógicos:</p>
<ul>
<li>Nombre del campo personalizado</li>
<li>Nombre de la variable (ej: Este valor es el que se 
proporciona a la API lang_get() , o se muestra tal y como está si no se encuentra en el fichero del idioma).</li>
<li>Tipo de campo personalizado (cadena de caracteres, numérico, float, enumeración, email)</li>
<li>Valores de la enumeración (ej: ROJO|AMARILLO|AZUL), aplicable los tipos lista, lista de selección múltiple
y combo.<br />
<i>Usa el caracter ('|') para
separar posibles valores de una enumeración. Uno de los valores puede
ser una cadena de caracteres vacía.</i>
</li>
<li>Valor por defecto: NO IMPLEMENTADO AÚN</li>
<li>Longitud mínima/máxima para el valor del campo personalizado (usa 0 para desabilitarla). (NO IMPLEMENTADO AÚN)</li>
<li>Expresión regular para validar el valor introducido por el usuario
(usa la sintaxis <a href=\"http://au.php.net/manual/en/function.ereg.php\">ereg()</a>.
<b>(NO IMPLEMENTADO AÚN)</b></li>
<li>Todos los campos personalizados se almacenan en la base de datos en un campo de tipo VARCHAR(255).</li>
<li>Mostrar en especificación de pruebas.</li>
<li>Habilitar en la especificación de pruebas. Los usuarios pueden cambiar el valor durante el diseño de la especificación de los Casos de Prueba</li>
<li>Mostrar en la ejecución de pruebas.</li>
<li>Habilitar en la ejecución de pruebas. ULos usuarios pueden cambiar el valor durante la ejecución de los Casos de Prueba</li>
<li>Mostrar en el diseño del Plan de Pruebas.</li>
<li>Habilitar en el diseño del plan de pruebas. Los usuarios pueden cambiar el valor durante el diseño del Plan de Pruebas (añadir casos de prueba al plan de pruebas)</li>
<li>Disponible para. El usuario selecciona a qué tipo de elemento sigue el campo.</li>
</ul>
";

// execMain.html
$TLS_hlp_executeMain = "<h2>Ejecutar Casos de Prueba</h2>
<p>Permite a los usuarios 'ejecutar' casos de prueba. La ejecución en sí misma es simplemente
asignar un resultado a un caso de prueba (pasado,fallado,bloqueado) asociado a una build en concreto.</p>
<p>El acceso al sistema de gestión de defectos debe ser configurado. El usuario puede añadir nuevos defectos directamente
y seleccionarlos de entre los existentes. Consulta el manual de Instalación para más detalles.</p>";

//bug_add.html
$TLS_hlp_btsIntegration = "<h2>Añadir defectos a los Caso de Prueba</h2>
<p><i>(sólo si está configurado)</i>
TestLink tiene un sistema muy simple de integración con Gestores de Defectos,
no siendo posible enviar ni solicitudes de creación de defectos al gestor de Defectos, ni obtener el ID del defecto.
La integración se realiza usando enlaces a las páginas del Gestor de Defectos que llama a las siguientes funcionalidades:
<ul>
	<li>Insertar nuevo defecto.</li>
	<li>Mostrar información de un defecto existente. </li>
</ul>
</p>  

<h3>Proceso para añadir un nuevo defecto</h3>
<p>
   <ul>
   <li>Paso 1: usa el enlace para abrir el gestor de Defectos y crear un nuevo defecto. </li>
   <li>Paso 2: apunta el ID del defecto asignado por el Gestor de Defectos.</li>
   <li>Paso 3: escribe el ID del Defecto en el campo de entrada.</li>
   <li>Paso 4: usa el botón de añadir defecto.</li>
   </ul>  

Después de cerrar la pantalla para añadir un defecto verás información importante del defecto en la pantalla de ejecución.
</p>";

// execFilter.html
$TLS_hlp_executeFilter = "<h2>Opciones de Configuración</h2>

<p>Las opciones de configuración te permiten seleccionar el plan de pruebas, la build y la plataforma (si existe) a
ser ejecutada.</p>

<h3>Plan de Pruebas</h3>
<p>Puedes elegir el plan de pruebas. Según el plan de pruebas elegido se mostrarán las
builds apropiadas. Tras elegir un plan de pruebas los filtros se resetearán.</p>

<h3>Plataforma</h3>
<p>Si utilzas plataformas, debes seleccionar la apropiada antes de la ejecución.</p>

<h3>Build a ejecutar</h3>
<p>Puedes elegir la en la que quieres ejecutar el caso de prueba.</p>

<h2>Filtros</h2>
<p>Los filtros permiten modificar el conjunto de casos de prueba mostrados antes de
la ejecución. Puedes reducir el número de casos de prueba mostrados especificando filtros
y pulsando el botón \"Aplicar\".</p>

<p> Los filtros avanzados te permiten especificar un conjunto de valores para los filtros
usando CTRL-Clic dentro de la lista desplegable de selección múltiple</p>


<h3>Filtro de Keyword</h3>
<p>Puedes filtrar casos de prueba por las keywords asignadas. Puedes elegir " .
"múltiples keywords usando CTRL-Clic. Si eliges más de una keyword puedes " .
"decidir si se muestran sólo los casos de prueba que tienen asignadas todas las keywords seleccionadas " .
"(opción \"Y\") o al memos una de las keywords seleccionadas (opción \"O\").</p>

<h3>Filtro de Prioridad</h3>
<p>Puedes filtrar los casos de prueba por prioridad. La prioridad de prueba es la \"importancia del caso de prueba\" " .
"combinada con la \"urgencia de prueba\" dentro del plan de pruebas actual.</p> 

<h3>Filtro de Usuario</h3>
<p>Puedes filtrar casos de prueba que no están asignados (\"Nadie\") o asignados a \"Alguien\". " .
"También puedes filtrar casos de prueba asignados a un tester en concreto. Si eliges un tester " .
"en concreto tienes la posibilidad de mostrar además los casos de prueba sin asignar " .
"(hay disponibles Filtros Avanzados). </p>

<h3>Filtro de Resultado</h3>
<p>Puedes filtrar casos de prueba por resultado (hay disponibles Filtros Avanzados). Puedes filtrar por " .
"resultado \"en la build seleccionada para ejecución\", \"en la última ejecución\", \"en TODAS las builds\", " .
"\"en CUALQUIER build\" y \"en una build en concreto\". Si se selecciona \"en una build en concreto\" puedes " .
"especificar la build. </p>";


// newest_tcversions.html
$TLS_hlp_planTcModified = "<h2>Últimas versiones de los Casos de Prueba asignados</h2>
<p>El total de casos de prueba asociados al Plan de Pruebas es analizado, y se muestra una lista de
los Casos de Prueba con su versión más reciente (junto con la selección actual incluida en el Plan de Pruebas).
</p>";


// requirementsCoverage.html
$TLS_hlp_requirementsCoverage = "<h3>Cobertura de Requisitos</h3>
<br />
<p>Esta funcionalidad permite relacionar requisitos de usuario o de sistema con
casos de prueba. Puedes acceder a través del enlace \"Especificación de Requisitos\" de la pantalla principal.</p>

<h3>Especificación de Requisitos</h3>
<p>Los requisitos están agrupados por documento de 'Especificación de Requisitos', que están relacionados al 
Proyecto de Pruebas.<br /> TestLink no soporta versiones para la Especificación de Requisitos  
y los Requisitos en sí mismos. Por tanto, la versión del documento debe ser añadida después de 
un <b>Título</b> de Especificación.
Un usuario puede añadir una simple descripción o notas al campo <b>Descripción</b>.</p> 

<p><b><a name='total_count'>Sobreescribir el contador de REQs</a></b> sirve para  
evaluar la cobertura de Req. en caso de que no todos los requisitos estén añadidos (importados) a TestLink. 
El valor <b>0</b> significa que el valor actual de requisitos es el que se usará para las métricas.</p> 
<p><i>Ejemplo. El campo muestra un valor de 200 requisitos pero sólo 50 son añadidos a TestLink. La cobertura 
de pruebas es del 25% (si todos los requisitos añadidos son probados).</i></p>

<h3><a name=\"req\">Requisitos</a></h3>
<p>Pulsa en el título de una Especificación de Requisitos. Puedes crear, editar, borrar
o importar requisitos en el documento. Cada requisito tiene título, descripción y estado.
El estado puede ser \"Normal\" o \"No testable\". Los requisitos No testables no son tenidos en cuenta en
las métricas. Este parámetro debería ser usado tanto para funcionalidades no implementadas como para 
requisitos mal diseñados.</p> 

<p>Puedes crear nuevos casos de prueba desde los requsititos usando la acción múltiple con los requisitos 
seleccionados en la pantalla de especificación. Estos Casos de Prueba son creados dentro de la Suite de Pruebas
con el nombre definido en la configuración <i>(por defecto es: &#36;tlCfg->req_cfg->default_testsuite_name = 
\"Título del Documento de Especificación de Requisitos + (generado automáticamente desde espec. req.)\";)</i>.
Título y Descripción son copiados a estos casos de prueba.</p>
";

$TLS_hlp_req_coverage_table = "<h3>Cobertura:</h3>
Un valor de, por ejemplo, \"40% (8/20)\" significa que se deben crear 20 Casos de Prueba para probar este 
Requisito completamente. 8 de lo cuales ya han sido creados y enlazados a este Requisito, lo cual hace 
que la cobertura sea del 40%.
";


// planAddTC_m1.tpl
$TLS_hlp_planAddTC = "<h2>En relación con 'Guardar Campos personalizados'</h2>
Si has definidos y asignado Campos Personalizados al<br /> 
Proyecto de Pruebas con las opciones:<br />
 'Mostrar en plan de pruebas' y <br />
 'Habilitar en el diseño del plan de pruebas'<br />
los verás en esta pantalla SÓLO para los Casos de Prueba asignados al Plan de Pruebas.
";


// resultsByTesterPerBuild.tpl
$TLS_hlp_results_by_tester_per_build_table = "<b>Más información sobre los testers:</b><br />
Si pulsas en el nombre de un tester en esta tabla, verás un resumen más detallado
de todos los Casos de Prueba asignados a ese usuario y su progreso de ejecución de pruebas.<br /><br />
<b>Nota:</b><br />
Este informe muestra los casos de prueba que están asignados a un usuario en concreto y que han sido ejecutados 
en la build activa. Incluso si un caso de prueba ha sido ejecutado por un usuario diferente al que tiene asignado, 
el caso de prueba will aparecerá como ejecutado por el usuario asignado.
";


// req_edit
$TLS_hlp_req_edit = "<h3>Enlaces Internos:</h3>
<p>Los Enlaces Internos sirven para crear enlaces a otros requisitos/especificaciones de requisitos 
con una sintaxis especial. El comportamiento de los Enlaces Internos puede ser modificado en el archivo de configuración.
<br /><br />
<b>Uso:</b>
<br />
Enlace a requisitos: [req]req_doc_id[/req]<br />
Enlace a especificación de requisitos: [req_spec]req_spec_doc_id[/req_spec]</p>

<p>También se puede especificar el proyecto de pruebas del requisito / especificación de requisito,
una versión y un objetivo al que dirigirse:<br />
[req tproj=&lt;tproj_prefix&gt; anchor=&lt;anchor_name&gt; version=&lt;version_number&gt;]req_doc_id[/req]<br />
Esta sintaxis también funciona para especificaciones de requisitos (el atributo versión no se tiene en cuenta).<br />
Si no especificas una versión, se mostrará el requisito completo incluyendo todas las versiones.</p>

<h3>Mensaje de registro para cambios:</h3>
<p>Siempre que se realiza un cambio, TestLink pregunta si se desea incluir un mensaje de registro. Este mensaje sirve para mantener la trazabilidad.
Si sólo ha cambiado la descripción del requisito eres libre de decidir si creas una nueva revisión o no. 
Si se modifica algo más que la descripción se obliga a crear una nueva revisión.</p>
";


// req_view
$TLS_hlp_req_view = "<h3>Enlaces Directos:</h3>
<p>Para compartir fácilmente este documento con otros, simplemente pulsa el icono del blobo terráqueo de la parte superior de
este documento para crear un enlace directo.</p>

<h3>Ver Historial:</h3>
<p>Esta funcionalidad permite comparar revisiones/versiones de requisitos si existe más de una revisión/versión del requisito.
La vista general muestra el mensaje de registro de cada revisión/versión, la fecha y la hora y el autor de la última modificación.</p>

<h3>Cobertura:</h3>
<p>Muestra todos los casos de prueba asignados a este requisito.</p>

<h3>Relaciones:</h3>
<p>Las Relaciones se usan para crear un modelo de relaciones entre requisitos. 
Las relaciones personalizadas y la posibilidad de relacionar requisitos entre 
diferentes proyectos de prueba pueden ser configuradas en el archivo de configuración.
Si estableces la relación \"El Requisito A es padre del Requisito B\", 
TestLink establecerá la relación \"El Requisito B es hijo del Requisito A\" de forma implícita.</p>
";


// req_spec_edit
$TLS_hlp_req_spec_edit = "<h3>Enlaces Internos:</h3>
<p>Los Enlaces Internos sirven para crear enlaces a otros requisitos/especificaciones de requisitos 
con una sintaxis especial. El comportamiento de los Enlaces Internos puede ser modificado en el archivo de configuración.
<br /><br />
<b>Uso:</b>
<br />
Enlace a requisitos: [req]req_doc_id[/req]<br />
Enlace a especificación de requisitos: [req_spec]req_spec_doc_id[/req_spec]</p>

<p>También se puede especificar el proyecto de pruebas del requisito / especificación de requisito,
una versión y un objetivo al que dirigirse:<br />
[req tproj=&lt;tproj_prefix&gt; anchor=&lt;anchor_name&gt; version=&lt;version_number&gt;]req_doc_id[/req]<br />
Esta sintaxis también funciona para especificaciones de requisitos (el atributo versión no se tiene en cuenta).<br />
Si no especificas una versión, se mostrará el requisito completo incluyendo todas las versiones.</p>
";


// xxx.html
//$TLS_hlp_xxx = "";

// ----- END ------------------------------------------------------------------
?>