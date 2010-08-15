<?php
/** -------------------------------------------------------------------------------------
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/
 * 
 * Filename $RCSfile: description.php,v $
 * @version $Revision: 1.4 $
 * @modified $Date: 2010/08/15 10:45:21 $ $Author: franciscom $
 * @author Martin Havlat
 *
 * LOCALIZATION:
 * 
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
$TLS_hlp_generateDocOptions = "<h2>Opciones para generar el documento</h2>

<p>Esta tabla permite al usuario filtrar los casos de prueba antes de verlos.
Si los datos están seleccionados se mostrarán. Para cambiar los datos presentados, 
marcar o desmarcar, haga clic en el Filtro, y seleccione el nivel de datos que
desee en el árbol.</p>

<p><b>Cabecera del documento:</b> Los usuarios pueden filtrar la información de cabecera del documento. 
La información de cabecera del documento incluye: Introducción, alcance, referencias, 
Metodología de prueba y limitaciones de prueba.</p>

<p><b>Cuerpo del caso de prueba:</b> Los usuarios pueden filtrar la información del cuerpo del caso de prueba.
La información del cuerpo del caso de prueba incluye: resumen, pasos, resultados esperados y keywords.</p>

<p><b>Resumen del caso de prueba:</b> Los usuarios pueden filtrar la información del resumen
desde el titulo del caso de prueba, sin embargo, no pueden filtrar la información del resumen
desde el cuerpo del caso de prueba. El resumen ha sido solo parcialmente separado del cuerpo del caso de prueba
Body a fin de apoyar los títulos de visión con un breve resumen y la ausencia de
Pasos, resultados esperados, y las keywords. Si el usuario decide ver el cuerpo del caso de prueba, 
el resumen también se incluirá.</p>

<p><b>Tabla de contenido:</b> Testlink inserta una lista con todos los titulos con enlaces internos.</p>

<p><b>Formato de salida:</b> Hay dos posibilidades: HTML y MS Word. El explorador llama al MS Word en segundo caso.</p>";

// testPlan.html
$TLS_hlp_testPlan = "<h2>Plan de pruebas</h2>

<h3>General</h3>
<p>Un plan de pruebas es una aproximación sistemática a testear el sistema como un software.
Puede organizar las actividades de testeo para una build en particular de un producto en timpo 
y con resultados de seguimiento.</p>

<h3>Ejecución</h3>
<p>Esta sección es donde los usuarios pueden ejecutar los casos de prueba (escribir los resultados)
e imprimir la Suite de pruebas del plan de pruebas. Aquí es donde los usuarios pueden seguir
el resultado de la ejecución de sus casos de prueba.</p> 

<h2>Administración del plan de pruebas</h2>
<p>Esta sección, a la cual solo un líder puede acceder, permite administrar lo planes de pruebas. 
Administrar planes de pruebas involucra crear/editar/borrar planes, agregar/editar/borrar/actualizar 
casos de prueba en planes, crear builds así como definir quien puede ver cada plan.<br />
Los lideres (usuarios con permisos de lider) también pueden establecer la prioridad/riesgo 
y la propiedad de las suites de pruebas (categorías) y crear hitos de prueba.</p> 

<p>Nota: Es posible que los usuarios no puedan ver un desplegable con todos los planes de pruebas. 
En esta situación todos los vínculos (excepto los habilitados por un lider) serán desvinculados. Si está 
en esta situación debe contactar a un lider o administrador lead or admin para concederle 
los derechos adecuados del proyecto o crear un plan de pruebas para usted.</p>"; 

// custom_fields.html
$TLS_hlp_customFields = "<h2>Campos personalizados</h2>
<p>Los siguientes son algunos hechos acerca de la implementación de campos personalizados:</p>
<ul>
<li>Custom fields son definidos en todo el sistema.</li>
<li>Los campos personalizados se vinculan a un tipo de elemento (Suite de pruebas, Caso de prueba</li>
<li>Los campos personalizados se pueden vincular a varios proyectos.</li>
<li>La secuencia de mostrar campos personalizados pueden ser diferentes para cada proyecto.</li>
<li>Los campos personalizados se pueden volver 'Inactivo' para un proyecto en específico.</li>
<li>El número de campos personalizados no está restringido.</li>
</ul>

<p>La definición de un campo personalizado incluye los siguientes atributos lógicos:</p>
<ul>
<li>Nombre</li>
<li>Etiqueta.</li>
<li>Tipo(string, numeric, float, enum, email)</li>
<li>Valores posible de enumeración(ej: rojo|amarillo|azul), aplicables a la lista, la lista de selección múltiple
y los tipos de combo.<br />
<i>Utilice el carácter ('|') para separar los posibles valores de una enumeración. 
Uno de los posibles valores puede ser una cadena vacía.</i>
</li>
<li>Valor por defecto (NO IMPLEMENTADO AUN).</li>
<li>Mínima/maxima longitud para el valor del campo (use 0 para desactivar). (NO IMPLEMENTADO AUN)</li>
<li>Expresión regular a utilizar para validar la entrada del usuario
<b>(NO IMPLEMENTADO AUN)</b></li>
<li>Todos los campos personalizados son actualmente guardados en un campo de tipo VARCHAR(255) en la base de datos.</li>
<li>Mostrar en al especificación de pruebas.</li>
<li>Habilitar en la especificación de pruebas. El usuario puede cambiar el valor durante el diseño de 
la especificación de casos de prueba.</li>
<li>Mostrar en la ejecución.</li>
<li>Habilitar en la ejecución. El usuario puede cambiar el valor durante la ejecución.</li>
<li>Mostrar en el diseño del plan de pruebas.</li>
<li>Habilitar en el diseño del plan de pruebas. El usuario puede cambiar el valor durante el diseño del plan de pruebas
(agregar casos de prueba al plan de pruebas)</li>
</ul>
";

// execMain.html
$TLS_hlp_executeMain = "<h2>Ejecutando casos de prueba</h2>
<p>Permite a los usuario a 'ejecutar' los casos de prueba. La ejecución en sí no es más que la 
asignación a un caso de prueba de un resultado (pasa, falla, bloqueado) contra una build seleccionada.</p>
<p>El acceso a un BTS puede ser configurado.Los usuarios pueden agregar un bug nuevo directamente o navegar
por los existentes.</p>";

//bug_add.html
$TLS_hlp_btsIntegration = "<h2>Agregar bugs al caso de prueba</h2>
<p><i>(Sólo si está configurado)</i>
Testlink tiene una integración muy simple con un BTS,no es capaz de enviar ni la solicitud de creación de bug de BTS, 
ni recuperar el ID del bug.La integración se realiza mediante enlaces a las páginas de BTS, que llama a las siguientes 
características:
<ul>
	<li>Insertar bug nuevo.</li>
	<li>Mostrar información de bug existente. </li>
</ul>
</p>  

<h3>Proceso para agregar un bug</h3>
<p>
   <ul>
   <li>Paso 1: use el vínculo para abrir el BTS para insertar el bug nuevo. </li>
   <li>Paso 2: escribe abajo del BUG ID asignado por el BTS.</li>
   <li>Paso 3: escribe BUG ID en el campo de entrada.</li>
   <li>Paso 4: use el botón 'agregar bug'.</li>
   </ul>  

Luego de cerrar la página de adición de bugs, verá los datos relevantes del bug en la página de ejecución.
</p>";

// execFilter.html
$TLS_hlp_executeFilter = "<h2>Instalar filtos y builds para la ejecución</h2>

<p>El panel izquierdo consta de: un navegador por los casos de prueba asignados al plan de pruebas actual " .
"y una tabla con configuraciones y filtros.Estos filtros permiten al usuario " .
"refinar el conjunto ofrecido de casos de prueba antes de ser ejecutados." .
"establezca su filtro, presione el botón \"Aplicar\" y seleccione el caso de prueba apropiado en el árbol.</p>

<h3>Build</h3>
<p>Los usuarios pueden elegir una build que se conectará con el resultado de la prueba. " .
"Las Builds son el componente básico para el plan de pruebas actual.Cada caso de prueba " .
"puede ser corrido mas veces por build.Sin embargo, sólo el último resultado es tomado en cuenta. 
<br />Las builds pueden ser creadas por lideres usando la página de creacion de build.</p>

<h3>Filtro de ID</h3>
<p>Los usuarios pueden filtrar los casos de prueba por un identificador único. Este ID es creado automáticamente 
durante el tiempo de creación.La caja vacía significa que el filtro no se aplica.</p> 

<h3>Filtro de prioridad</h3>
<p>Los usuarios pueden filtrar los casos de prueba por la prioridad. Cada importancia del" . 
"caso de prueba es combinada con la urgencia del mismo dentro del plan de pruebas actual." .
"Por ejemplo la prioridad 'ALTA' en el caso de prueba se muetra si la importancia" . 
"o urgencia es alto y su segundo atributo es por lo menos 'MEDIA'.</p> 

<h2>Filtro de resultado</h2>
<p>Los usuarios pueden filtrar los casos de prueba por los resultados.Los resultados 
son lo que pasó con ese caso de prueba durante una build en particular.Los casos de prueba
pueden pasar, fallar, ser bloqueados o no ejecutados.Este filtro está desactivado por defecto.</p>

<h3>Filtro de usuario</h3>
<p>Los usuarios pueden filtrar los casos de prueba por su asignado.El recuadro permite incluír también " .
"casos de prueba \"sin asignar\" dentro del resultado.</p>";
/*
<h2>Resultado más reciente</h2>
<p>Por defecto o si el recuadro de 'Más reciente' está desmarcado, el árbol se ordenará 
por la build que se elija en menú desplegable. Es esta condición el árbol mostrará 
el estado de los casos de prueba.</p>
 */


// newest_tcversions.html
$TLS_hlp_planTcModified = "<h2>Nuevas versiones de Casos de prueba vinculados</h2>
<p>El conjunto de casos de prueba vinculados al plan de pruebas es analizado, y se muestra una lista de 
casos de prueba que tienen una versión más reciente (en contra de la serie 
actual del plan de pruebas).</p>";


// requirementsCoverage.html
$TLS_hlp_requirementsCoverage = "<h3>Cobertura de requerimientos</h3>
<br />
<p>Esta característica permite asignar cobertura de usuario o requerimientos de sistema por caso de prueba. 
Navegar a través de \"Especificación de requerimientos\" en la pantalla principal.</p>

<h3>Especificación de requerimientos</h3>
<p>Los requerimientos estan agrupados por el documento'Especificación de requerimientos', el cual está relacionado
al proyecto.<br />
Un usuario puede añadir una descripción simple o una nota al campo <b>'Alcance'</b>.</p> 

<p><h3>Sobreescribir el contador de Reqs</h3> sirve para
evaluar la cobertura de Reqs.en caso de que no todos los requerimientos se añadan a Testlink.
El valor <b>0</b> significa que el contador actual de Reqs. se utiliza para las métricas.
<p><i>Por ejemplo SRS incluye 200 requerimientos pero solo 50 son añadidos a Testlink. La cobertura 
de Test es del 25% (si todos estos requerimientos añadidos se testearan).</i></p>

<h3>Requerimientos</h3>
<p>Haga click en el titulo de la Especificación de requerimientos creada,si no hay nada existente haga 
click en el proyecto para crear uno. Puede crear, editar, eliminar o importar requerimientos 
de un documento. Cada requerimiento tiene un titulo, un alcance y un estado.
El estado debe ser 'Normal' o 'No testeable'. Los requerimientos no testeables no tienen contador 
para las métricas. Este parámetro debe ser usado tanto para características que no se han implementado como para
requerimientos mal diseñados.</p>

<p>Puede crear nuevos casos de prueba para requerimientos usando la multi acción con requerimientos 
marcados dentro de la pantalla de especificación.
Estos casos de prueba son creados dentro de la Suite de pruebas con nombre definido en la configuración.
El título y alcance son copiados de estos casos de prueba.</p>
";

$TLS_hlp_req_coverage_table = "<h3>Cobertura:</h3>
Un valor de ejemplo \"40% (8/20)\" significa que 20 casos de prueba tienen qeu ser creados para este requisito 
para probarlo completamente. 8 de ellos han sido ya creados y vinculados a este requisito, que 
hace una cobertura del 40 porciento.
";


// planAddTC_m1.tpl
$TLS_hlp_planAddTC = "<h2>Con respecto a 'Guardar campos personalizado'</h2>
Si se han definido y asignado al proyecto,<br /> 
Campos personalizados con:<br />
 'Display on test plan design=true' y <br />
 'Enable on test plan design=true'<br />
podrá ver estos solo en esta página para casos de prueba relacionados con el Plan de pruebas.
";

// xxx.html
//$TLS_hlp_xxx = "";

// ----- END ------------------------------------------------------------------
?>