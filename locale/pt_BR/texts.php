<?php
/**
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
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
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2003-2009, TestLink community 
 * @version    	CVS: $Id: texts.php,v 1.29 2010/07/22 14:14:44 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 **/


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['error']	= "Erro na Aplicação";
$TLS_htmltext['error'] 		= "<p>Um erro inesperado ocorreu. Por favor, verifique o event viewer ou " .
		"logs para detalhes.</p><p>Você está convidado para relatar o problema. Por favor, visite nosso " .
		"<a href='http://www.teamst.org'>website</a>.</p>";



$TLS_htmltext_title['assignReqs']	= "Atribuir Requisitos aos Casos de Teste";
$TLS_htmltext['assignReqs'] 		= "<h2>Purpose:</h2>
<p>Usuários podem criar relacionamentos entre requisitos e casos de teste. Um arquiteto pode 
definir relacionamentos 0..n para 0..n. I.e. Um caso de teste pode ser atribuído para nenhum, um ou mais 
requisitos e vice versa. Assim a matriz de rastreabilidade ajuda a investigar a cobertura 
dos requisitos de teste e finalmente quais falharam durante os testes. Esta 
análise serve como entrada para o próximo planejamento de teste.</p>

<h2>Iniciar:</h2>
<ol>
	<li>Escolha um Caso de Teste na árvore à esquerda. O combo box com a lista de 
	Especificações de Requisitos é exibido no topo da área de trabalho.</li>
	<li>Escolha um documento de Especificação se mais de um estiver definido. 
	 O TestLink recarregará a página automaticamente.</li>
	<li>Um bloco ao centro da área de trabalho lista todos os requisitos (para a Especificação selecionada), que 
	está conectada ao caso de teste. O bloco abaixo 'Requisitos Disponíveis' lista todos 
	os requisitos que não estão relacionados 
	ao caso de teste selecionado. Um arquiteto pode selecionar requisitos que são cobertos por este 
	caso de teste e então clicar em 'Atribuir'. Este novo caso de teste atribuído será exibido no 
	bloco central 'Requisitos atribuídos'.</li>
</ol>";


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['editTc']	= "Especificação de Teste";
$TLS_htmltext['editTc'] 		= "<p>A <i>Especificação de Teste</i> permite aos usuários visualizar " .
		"e editar todas as <i>Suítes de Teste</i> e <i>Casos de Teste</i> existentes. " .
		"Os Casos de Teste são versionados e todas as versões anteriores estão disponíveis e podem ser " .
		"visualizadas e gerenciadas aqui.</p>
		
<h2>Iniciar:</h2>
<ol>
	<li>Selecione seu Projeto de Teste na árvore de navegação (o nó principal). <i>Observe: " .
	"Você sempre poderá trocar o Projeto de Teste ativo selecionando um diferente da " .
	"lista drop-down do canto superior esquerdo.</i></li>
	<li>Crie uma nova Suíte de Teste clicando em <b>Nova Suíte de Teste</b>. As Suítes de Teste podem " .
	"trazer a estrutura da sua documentação de teste conforme suas convenções (testes funcionais/não-funcionais " .
	", produtos, componentes ou características, solicitações de mudança, etc.). A descrição da " .
	"Suíte de Teste poderia conter o escopo dos Casos de Teste incluídos, configuração padrão, " .
	"links para documentos relevantes, limitações e outras informações usuais. Em geral, " .
	"todas anotações que são comuns às Suítes de Teste. As Suítes de Teste seguem " .
	"a metáfora do &quot;diretório&quot;, assim os usuários podem mover e copiar Suítes de Teste dentro " .
	"do Projeto de Teste. Além disso, eles podem ser importados ou exportados (incluindo os Casos de Teste nele contidos).</li>
	<li>Suítes de Teste são pastas escaláveis. Os usuários podem mover ou copiar Suítes de Teste dentro " .
	"do Projeto de Teste. Suítes de Teste podem ser importadas ou exportadas (incluindo os Casos de Teste).
	<li>Selecione sua mais nova Suíte de Teste criada na árvore de navegação e crie " .
	"um novo Caso de Teste clicando em <b>Criar Caso(s) de Teste</b>. Um Caso de Teste especifica " .
	"um cenário de testes particular, resultados esperados e campos personalizados definidos " .
	"no Projeto de Teste (consulte o manual do usuário para maiores informações). Também é possível " .
	"atribuir <b>palavras-chave</b> para melhorar a rastreabilidade.</li>
	<li>Navegue pela árvore de navegação do lado esquerdo e edite os dados. Os Casos de Teste armazenam histórico próprio.</li>
	<li>Atribua suas Especificações de Teste criadas ao <span class=\"help\" onclick=
	\"javascript:open_help_window('glosary','$locale');\">Test Plan</span> quando seus Casos de Teste estiverem prontos.</li>
</ol>

<p>Com o TestLink você organiza os Casos de Teste em Suítes de Teste." .
" Suítes de Teste podem ser aninhadas em outras Suítes de Teste, permitindo a você criar hierarquias de Suítes de Teste.
 Então você pode imprimir esta informação juntamente com o Caso de Teste.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchTc']	= "Página de Busca de Casos de Teste";
$TLS_htmltext['searchTc'] 		= "<h2>Objetivo:</h2>

<p>Navegue de acordo com palavras-chave e/ou strings procuradas. A busca não é
case sensitive. Os resultados incluem apenas Casos de Teste do Projeto de Teste atual.</p>

<h2>Para Pesquisar:</h2>

<ol>
	<li>Escreva a string procurada no campo apropriado. Deixe em branco campos sem uso no formulário.</li>
	<li>Escolha palavras-chave exigidas ou deixe o valor do campo 'Not applied'.</li>
	<li>Clique no botão Pesquisar.</li>
	<li>Todos os Casos de Teste cobertos são exibidos. Você pode modificar os Casos de Teste via link 'Título'.</li>
</ol>";

/* contribution by asimon for 2976 */
// requirements search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReq']        = "Página de Busca de Requisitos";
$TLS_htmltext['searchReq']              = "<h2>Objetivo:</h2>

<p>Navegue de acordo com as palavras-chave e/ou strings procuradas. A busca não é 
case sensitive. Os resultados incluem somente requisitos do projeto de teste atual.</p>

<h2>Para Pesquisar:</h2>

<ol>
        <li>Escreva a string de busca no campo apropriado. Deixe os campos não utilizados em branco.</li>
	<li>Escolha a palavra-chave requerida ou deixe o valor em branco.</li>
	<li>Clique no botão 'Pesquisar'.</li>
	<li>Todos os requisitos cobertos pelos critérios de busca serão exibidos. Você pode modificar requisitos através do link 'Título'.</li>
</ol>

<h2>Nota:</h2>

<p>- Somente Requisitos dentro do projeto atual seão pesquisados.<br>
- A pesquisa é case sensitive.<br>
- Campos vazios não são considerados.</p>";

// requirement specification search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReqSpec']    = "Página de Busca de Especificação de Requisitos";
$TLS_htmltext['searchReqSpec']          = "<h2>Objetivo:</h2>

<p>Navegue de acordo com as palavras-chave e/ou strings procuradas. A busca não é 
case sensitive. Os resultados incluem somente requisitos do projeto de teste atual.</p>

<h2>Para Pesquisar:</h2>

<ol>
	<li>Escreva a string de busca no campo apropriado. Deixe os campos não utilizados em branco.</li>
	<li>Escolha a palavra-chave requerida ou deixe o valor em branco.</li>
	<li>Clique no botão 'Pesquisar'.</li>
	<li>Todos os requisitos cobertos pelos critérios de busca serão exibidos. Você pode modificar requisitos através do link 'Título'.</li>
</ol>

<h2>Nota:</h2>

<p>- Somente Especificações de Requisito dentro do projeto atual seão pesquisados.<br>
- A pesquisa é case sensitive.<br>
- Campos vazios não são considerados.</p>";
/* end contribution */


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printTestSpec']	= "Imprimir Especificação de Testes"; //printTC.html
$TLS_htmltext['printTestSpec'] 			= "<h2>Objetivo:</h2>
<p>A partir daqui você pode imprimir um único caso de teste, todos os casos de teste dentro de uma suite 
ou todos os casos de teste de um Projeto de Teste ou Plano de Teste.</p>
<h2>Iniciar:</h2>
<ol>
<li>
<p>Selecione os campos dos casos de teste que você deseja exibir, e então clique em um Caso de Teste, 
Suíte de Teste, ou Projeto de Teste. Uma página pronta para impressão será exibida.</p>
</li>
<li><p>Use a drop-box \"Mostrar Como\" no painel de navegação para especificar se você quer 
a informação exibida como HTML, como documento do Open Office Writer ou num documento do Microsoft Word. 
Veja <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">Ajuda</span> para maiores informações.</p>
</li>
<li><p>Use a funcionalidade de impressão do seu browser para imprimir a informação presente.<br />
 <i>Nota: Certifique-se de imprimir somente o frame direito.</i></p>
</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['reqSpecMgmt']	= "Modelar Especificação de Requisitos"; //printTC.html
$TLS_htmltext['reqSpecMgmt'] 			= "<p>Você pode gerenciar documentos de Especificação de Requisitos.</p>

<h2>Especificação de Requisitos</h2>

<p>Requisitos estão agrupados por <b>documentos de Especificação de Requisitos </b>, os quais estão relacionados ao 
Projeto de Teste.<br /> O TestLink não suporta (ainda) versões para Especificação de Requisitos 
e também Requisitos. Logo, a versão do documento deve ser inserida após o <b>Título</b> da Especificação.
Um usuário pode inserir uma descrição simples ou notas no campo <b>Escopo</b>.</p> 

<p><b><a name='total_count'>Sobrescrever o contador de Requisitos</a></b> serve para 
avaliar a cobertura dos requisitos no caso de nem todos os requisitos estarem adicionados ao TestLink.
O valor <b>0</b> significa que o contador de requisitos atual é utilizado 
para métricas.</p>
<p><i>E.g. SRS inclui 200 requisitos, mas somente 50 são adicionados ao Plano de Teste. A cobertura de testes é de 
25% (se todos estes requisitos forem testados).</i></p>

<h2><a name='req'>Requisitos</a></h2>

<p>Clique no título da Especificação de Requisitos criada, e se nenhuma existir, " .
		"clique no nó do projeto para criar uma. Você pode criar, editar, excluir
ou importar requisitos para o documento. Cada requisito tem um título, escopo e status.
O status deve ser 'Válido' ou 'Não testável'. Requisitos Não Testáveis não são contabilizados 
para métricas. Este parâmetro deve ser utilizado para características não implementadas e 
requisitos modelados incorretamente.</p>

<p>Você pode criar novos casos de teste para os requisitos utilizando multi ações para os 
requisitos ativos na tela de especificação de requisitos. Estes Casos de Teste são criados dentro da Suíte de Teste 
com nome definido na configuração <i>(default is: \$tlCfg->req_cfg->default_testsuite_name = 
'Test suite created by Requirement - Auto';)</i>. Título e Escopo são copiados destes Casos de Teste.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printReqSpec'] = "Imprimir documento de Especificação de Requisitos"; //printReq
$TLS_htmltext['printReqSpec'] = "<h2>Objetivo:</h2>
<p>Através desta opção você pode imprimir um requisito único, todos os requisitos de uma Especificação de Requisitos, 
ou todos os requisitos de um Projeto de Teste.</p>
<h2>Iniciar:</h2>
<ol>
<li>
<p>Selecione as partes dos requisitos que você deseja exibir, e então clique em requisito, 
especificação de requisito ou projeto de teste. A visualização da impressão será exibida.</p>
</li>
<li><p>Utilize a drop-box \"Mostrar Como\" no painel de navegação para especificar se você quer 
a informação exibida como HTML, como documento do Open Office Writer ou num documento do Microsoft Word. 
Veja <span class=\"help\" onclick=\"javascript:open_help_window('printFilter',
'{$locale}');\">Ajuda</span> para mais informações.</p>
</li>
<li><p>Use a funcionalidade de impressão do seu browser para imprimir a informação presente.<br />
<i>Nota: Certifique-se de imprimir somente o frame direito.</i></p>
</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['keywordsAssign']	= "Atribuição de Palavras-chave";
$TLS_htmltext['keywordsAssign'] 			= "<h2>Objetivo:</h2>
<p>A página de Atribuição de Palavras-chave é o lugar onde os usuários podem 
atribuir em lotes Palavras-chave às Suítes de Teste ou Casos de Teste.</p>

<h2>Para Atribuir Palavras-chave:</h2>
<ol>
	<li>Selecione uma Suíte de Teste ou Caso de Teste na árvore 
		à esquerda.</li>
	<li>O box no topo da página que exibe informações do lado direito 
		permitirá a você atribuir palavras-chave para os casos de 
		teste individualmente.</li>
	<li>A seleção abaixo permite a você atribuir Casos de Teste em um 
		nivel mais granular.</li>
</ol>

<h2>Informação Importante quanto à Atribuição de Palavras-chave nos Planos de Teste:</h2>
<p>Atribuir Palavras-chave à Suíte de Teste afetará somente Casos de Teste 
no seu Plano de Teste somente se o Plano de Teste conter a última versão do Caso de Teste. 
Caso contrário, se o Plano de Teste conter versões mais antigas do Caso de Teste, as atribuições que você 
fez NÃO aparecerão no Plano de Teste.
</p>
<p>O TestLink usa esta abordagem para que versões mais antigas dos Casos de Teste nos Planos de Teste não sejam afetadas pela atribuição 
de Palavras-chave que você fez nas versões mais recentes dos Casos de Teste. Se você deseja seus 
Casos de Teste no seu Plano de Teste sejam atualizados, primeiro verifique se eles estão atualizados 
utilizando a funcionalidade 'Atualizar Versão dos Casos de Teste' antes de fazer a atribuição das Palavras-chave.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['executeTest']	= "Execução dos Casos de Teste";
$TLS_htmltext['executeTest'] 		= "<h2>Objetivo:</h2>

<p>Permite aos usuários executar os Casos de Teste. O usuário pode atribuir resultados 
aos Casos de Teste nos Ciclo de Teste. Veja a ajuda para mais informações sobre filtros e configurações " .
		"(clique no ícone interrogação).</p>

<h2>Iniciar:</h2>

<ol>
	<li>O usuário deve definir um Ciclo de Teste para o Plano de Teste.</li>
	<li>Selecionar um Ciclo de Teste no menu drop down</li>
	<li>Se você quiser ver apenas alguns poucos casos de teste, em vez de toda a árvore,
		você pode escolher quais filtros aplicar. Clique no botão \"Aplicar\" 
		depois que você alterar os filtros.</li>	
	<li>Clique no Caso de Teste no menu em árvore.</li>
	<li>Preencha o resultado do Caso de Teste, suas respectivas notas e/ou bugs.</li>
	<li>Salve os resultados.</li>
</ol>
<p><i>Nota: O TestLink deve ser configurado para colaborar com seu Bugtracker 
se você quiser criar ou rastrear problemas reportados diretamente da GUI.</i></p>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['showMetrics']	= "Descrição dos Relatórios de Teste e Métricas";
$TLS_htmltext['showMetrics'] 		= "<p>Os relatórios estão relacionados a um Plano de Teste " .
		"(definido no topo do navegador). Este Plano de Teste pode diferir do Plano de Teste 
corrente para execução. Você também pode selecionar formatos dos relatórios:</p>
<ul>
<li><b>Normal</b> - relatório é exibido em uma página web</li>
<li><b>OpenOffice Writer</b> - relatório é importado para o OpenOffice Writer</li>
<li><b>OpenOffice Calc</b> - relatório é importado para o OpenOffice Calc</li>
<li><b>MS Excel</b> - relatório é exportado para o Microsoft Excel</li>
<li><b>HTML Email</b> - relatório é encaminhado por e-mail para o endereço de e-mail do usuário</li>
<li><b>Charts</b> - relatório inclui gráficos (tecnologia flash)</li>
</ul>

<p>O botão imprimir ativa a impressão para um único relatório (sem navegação).</p>
<p>Existem vários relatórios separados a sua escolha. Seus propósitos e funções estão descritas abaixo.</p>

<h3>Plano de Teste</h3>
<p>O documento 'Plano de Teste' tem as opções para definir um conteúdo e uma estrutura de documento.</p>

<h3>Relatório de Teste</h3>
<p>O documento 'Relatório de Teste' tem as opções para definir um conteúdo e uma estrutura de documento.
ela inclui casos de teste, juntamente com os resultados dos testes.</p>

<h3>Métricas Gerais do Plano de Teste</h3>
<p>Esta página exibe somente o último status de um Plano de Teste por Suíte de Teste, Testador e palavras-chave.
O 'último status' é determinado pelo ciclo mais recente onde os Casos de Teste foram executados. Por exemplo, 
se um Caso de Teste foi executado em diversos Ciclos de Teste, somente o último resultado é considerado.</p>

<p> O 'último resultado' é um conceito utilizado em vários relatórios, e é determinado como a seguir:</p>
<ul>
<li>A ordem em cada Ciclo de Teste (Baseline) é adicionada ao Plano de Teste determina qual é o Ciclo de Teste mais recente. Os resultados do Ciclo de Teste 
mais recente prevalecerão sobre os Ciclos de Teste mais antigos. Por exemplo, se você marcar um teste com 
status 'Falhou' no Ciclo de Teste 1 e no Ciclo de Teste 2 como 'Passou', o último resultado será 'Passou'.</li>
<li>Se um Caso de Teste é executado diversas vezes em um mesmo Ciclo de Teste, o resultado mais recente 
prevalecerá. Por exemplo, se o Ciclo de Teste 3 é liberado para a equipe de testes e o Testador 1 marcar 'Passou' as 14h, 
e o Testador 2 marcar 'Falhou' as 15h, isto aparecerá como 'Falhou'.</li>
<li>Casos de Teste marcados como 'Não Executado' no último Ciclo de Teste não serão considerados. Por exemplo, se você marcar 
um Caso de Teste como 'Passou' no Ciclo de Teste 1 e não executar no Ciclo de Teste 2, o último resultado será considerado como 
'Passou'.</li>
</ul>
<p>As seguintes tabelas são exibidas:</p>
<ul>
	<li><b>Resultados por Suíte de Teste de Nível Top</b>
	Lista os resultados Top de cada Suíte de Teste. Total de Casos de Teste, Passou, Falhou, Bloqueado, Não Executado e o percentual 
	completo são listados. Um Caso de Teste 'completo' é aquele que foi marcado como Passou, Falhou ou Bloqueado.
	Os resultados das suites de nível superior incluem as suites filho.</li>
	<li><b>Resultados por Palavra-chave</b>
	Lista todas as palavras-chave que estão atribuídas aos Casos de Teste no Plano de Teste corrente, e os resultados associados 
	à eles.</li>
	<li><b>Resultados por Testador</b>
	Lista cada Testador que tem Casos de Teste associados a ele no Plano de Teste corrente. Os Casos de Teste que 
	não estão atribuídos são computados abaixo com a descrição 'desatribuir'.</li>
</ul>

<h3>Status Geral dos Ciclos de Teste</h3>
<p>Lista os resultados de execução para cada Ciclo de Teste. Para cada Ciclo de Teste, o total de Casos de Teste, total com Passou, 
% Passou, total Falha, % Falha, Bloqueado, % Bloqueado, Não Executado e % Não Executado.  Se um Caso de Teste foi executado 
duas vezes no mesmo Ciclo de Teste, a execução mais recente será considerada.</p>

<h3>Métricas da Consulta</h3>
<p>Este relatório consiste em uma página com o formulário de consulta e uma página com os resultados, a qual contém os dados da consulta.
A página com o formulário de consulta apresenta 4 controles. Cada controle está definido com um valor padrão que 
maximiza o número de Casos de Teste e Ciclos de Teste que a consulta deve ser executada. Alterar os controles 
permite aos usuários filtrar os resultados e gerar relatório específicos para Testadores em específico, palavras-chave, Suítes de Teste 
e combinação de Ciclos de Teste.</p>

<ul>
<li><b>Palavras-chave</b> 0->1 palavras-chave podem ser selecionadas. Por padrão, nenhuma palavra-chave é selecionada. Se uma palavra-chave não é 
selecionada, então todos os Casos de Teste serão considerados indiferentemente de atribuição de palavras-chave. Palavras-chave são atribuídas 
na especificação de testes ou na página Gerenciar Palavra-chave. Palavras-chave atribuídas aos Casos de Teste alcançam todos os Planos de Teste, e 
também todas as versões de um Caso de Teste. Se você está interessado nos resultados para uma palavra-chave específica, 
você deverá alterar este controle.</li>
<li><b>Testador</b> 0->1 Testadores podem ser selecionados. Por padrão, nenhum Testador é selecionado. Se um Testador não é selecionado, 
então todos os Casos de Teste serão considerados indiferentes de Testador atribuído. Atualmente não há funcionalidade 
para buscar por Casos de Teste não atribuídos. O Testador é atribuído através da página 'Atribuir Casos de Teste para Execução' 
e é feito com base no Plano de Teste. Se você está interessado em trabalhar com um Testador em específico, você deve 
alterar este controle.</li>
<li><b>Suíte de Teste de Nível Top</b> 0->n Suítes de Teste de Nível Top podem ser selecionadas. Por padrão, todas as Suítes de Teste são selecionadas. 
Apenas as Suítes de Teste que são selecioadas serão consultadas para as métricas do resultado. Se você está somente interessado em resultados 
para uma Suíte de Teste específica você precisa alterar este controle.</li>
<li><b>Ciclos de Teste</b> 1->n Ciclos de Teste podem ser selecionados. Por padrão, todos os Ciclos de Teste são selecionados. Somente execuções 
realizadas no Ciclo de Teste que você selecionou serão consideradas quando produzirem métricas. Por exemplo, se você quiser 
ver quantos Casos de Teste foram executados nos últimos 3 Ciclos de Teste, você precisa alterar este controle. A seleção de Palavra-chave, 
Testador e Suíte de Teste de Nível Top ditarão o número de Casos de Teste do seu Plano de Teste e serão usados para 
calcular as métricas por Suite de Teste e Plano de Teste. Por exemplo, se você selecionar o Testador = 'José', palavra-chave = 
'Prioridade 1', e todas as Suítes de Teste disponíveis, somente Casos de Teste com Prioridade 1 atribuídos para José serão considerados. 
O '# de Casos de Teste' totais que você verá neste relatório serão influenciados por estes 3 controles.
A seleção dos Ciclos de Teste influenciarão se o Caso de Teste é considerado 'Passou', 'Falhou', 'Bloqueado', ou 'Não Executado'.  
Favor classificar com a regra 'Somente os últimos resultados' à medida em que elas aparecem acima.</li>
</ul>
<p>Pressione o botão 'Executar Pesquisa' para prosseguir com a consulta e exibir a página com os resultados.</p>

<p>A página com o relatório consultado exibirá: </p>
<ol>
<li>o parâmetro da consulta utilizado para criar o relatório</li>
<li>totais para todo o Plano de Teste</li>
<li>por um conjunto de particionamento dos totais (Soma / Passou / Falhou / Bloqueado / Não Executado) e todas execuções realizadas 
na Suíte de Teste. Se um Caso de Teste foi executado mais de uma vez em múltiplos Ciclos de Teste, todas as execuções que foram gravadas serão 
exibidas nos Ciclos de Teste selecionados. No entanto, o resumo para esta Suíte de Teste somente incluirá o último resultado para 
o Ciclo de Teste selecionado.</li>
</ol>

<h3>Relatórios de Casos de Teste Bloqueados, com Falha e Não Executados</h3>
<p>Estes relatórios exibem todos os Casos de Teste Bloqueados, com Falha e Não Executados. A lógica do último resultado dos testes 
(que está descrita nas Métricas Gerais do Plano de Teste) é novamente empregada para determinar se um Caso de Teste deve ser 
considerado Bloquedo, com Falha ou Não Executado. Casos de Teste Bloqueado e com Falha exibirão os bugs associados se o usuário 
estiver utilizando um sistema de Bugtracking.</p>

<h3>Relatório de Testes</h3>
<p>Exibe o status de cada Caso de Teste em todos os Ciclos de Teste. O resultado da execução mais recente será utilizado 
se um Caso de Teste for executado múltiplas vezes em um mesmo Ciclo de Teste. É recomendado exportar este relatório para 
o formato em Excel para um fácil manuseio se um grande conjunto de dados estiver em utilização.</p>

<h3>Gráficos - Métricas Gerais do Plano de Teste</h3>
<p>A lógica do último resultado é utilizada para todos os gráficos que você verá. Os gráficos são animados para ajudar 
o usuário à visualizar as métricas do Plano de Teste atual. Os quatro gráficos fornecidos são:</p>
<ul><li>Gráfico de pizza com todos os Casos de Teste com status Passou, com Falha, Bloqueados e Não Executados</li>
<li>Gráfico de barras com os Resultados por palavra-chave</li>
<li>Gráfico de barras com os Resultados por Testador</li>
<li>Gráfico de barras com os Resultados por Suítes Nível Top</li>
</ul>
<p>As seções e barras dos gráficos são coloridos de modo que o usuário possa identificar o número aproximado de Casos de Teste com status 
Passou, Falhou, Bloqueado e Não Executado.</p>

<h3>Bugs por Casos de Teste</h3>
<p>Este relatório exibe cada Caso de Teste com todos os bugs abertos para ele em todo o projeto. 
Este relatório está disponível somente se um Bugtracking estiver conectado.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTC']	= "Adicionar / Remover Casos de Teste do Plano de Teste"; // testSetAdd
$TLS_htmltext['planAddTC'] 			= "<h2>Objetivo:</h2>
<p>Permite aos usuários (com perfil de Líder de Testes) a adicionar ou remover Casos de Teste do Plano de Teste.</p>

<h2>Para adicionar ou remover Casos de Teste:</h2>
<ol>
	<li>Clique na Suíte de Teste para ver todas as Suítes de Teste e todos os seus Casos de Teste.</li>
	<li>Quando tiver terminado, clique no botão 'Adicionar / Remover Casos de Teste' para adicionar ou remover os Casos de Teste selecionados.
		Nota: não é possível adicionar o mesmo Caso de Teste múltiplas vezes.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['tc_exec_assignment']	= "Atribuir Testadores à Execução de Testes";
$TLS_htmltext['tc_exec_assignment'] 		= "<h2>Objetivo:</h2>
<p>Esta página permite aos Líderes de Teste atribuir usuários a testes específicos dentro do Plano de Teste.</p>

<h2>Iniciar:</h2>
<ol>
	<li>Escolha o Caso de Teste ou Suíte de Teste a ser testada.</li>
	<li>Selecione o Testador conforme planejamento.</li>
	<li>Pressione o botão 'Salvar' para aplicar a atribuição.</li>
	<li>Abra a página de execução para verificar a atribuição. Você pode estabelecer um filtro por usuários.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planUpdateTC']	= "Atualizar Casos de Teste no Plano de Teste";
$TLS_htmltext['planUpdateTC'] 		= "<h2>Objetivo:</h2>
<p>Esta página permite atualizar Casos de Teste para uma versão mais nova (diferente) da 
Especificação de Casos de Teste quando alterada. Isto frequentemente acontece quando uma funcionalidade é alterada durante os testes." .
		" O usuário modifica a Especificação de Teste, mas as alterações precisam se propagar ao Plano de Teste também. De qualquer forma," .
		" o Plano de Teste mantém as versões originais para garantir que os resultados se referem ao texto correto dos Casos de Teste.</p>

<h2>Iniciar:</h2>
<ol>
	<li>Escolha o Caso de Teste ou Suíte de Teste a testar.</li>
	<li>Escolha uma nova versão no combo box para um Caso de Teste específico.</li>
	<li>Pressione o botão  'Atualizar Plano de Teste' para aplicar alterações.</li>
	<li>Para comprovar: abra a página de execução para verificar o texto do(s) Caso(s) de Teste.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['test_urgency']	= "Especificar testes com prioridade alta ou baixa";
$TLS_htmltext['test_urgency'] 		= "<h2>Objetivo:</h2>
<p>O TestLink permite definir a urgência das Suítes de Teste para afetar a prioridade dos Casos de Teste. 
		A priorização dos testes depende da importância do Caso de Teste e da urgência definida no Plano de Teste. 
		O Líder de Teste deve especificar um conjunto de Casos de Teste que devem ser testados primeiro. Isso 
		ajuda a assegurar que os testes cobrirão os requisitos mais importantes também sob a 
		pressão do tempo.</p>

<h2>Iniciar:</h2>
<ol>
	<li>Escolha uma Suíte de Teste para definiar a urgência do produto/componente no navegador 
	ao lado esquerdo da janela.</li>
	<li>Escolha o nível de urgência (Alta, Média ou Baixa). O nível médio é o padrão. Você pode 
	diminuir a prioridade para partes do produtos não alteradas e aumentar para componentes 
	com mudanças significativas.</li>
	<li>Pressione o botão 'Salvar' para aplicar as alterações.</li>
</ol>
<p><i>Por exemplo, um Caso de Teste com prioridade Alta em uma Suíte de Teste com urgência Baixa 
		será de prioridade Média.</i>";


// ------------------------------------------------------------------------------------------

?>
