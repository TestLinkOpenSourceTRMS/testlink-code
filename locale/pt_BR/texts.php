<?php
/**
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: texts.php,v $
 * @version $Revision: 1.5 $
 * @modified $Date: 2010/07/11 17:16:17 $ by $Author: franciscom $
 * @author Martin Havlat and reviewers from TestLink Community
 *
 * --------------------------------------------------------------------------------------
 * IMPORTANT NOTICE REGARDING UTF-8
 * An special/weird character has been added to this file in order to be recognized as UTF8
 * by editors	
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

$TLS_htmltext_title['assignReqs']	= "Atribuir Requisitos aos Casos de Teste";
$TLS_htmltext['assignReqs'] 		= "<h2>Objetivo:</h2>
<p>Usuários podem criar relacionamentos entre requisitos e casos de teste. Um arquiteto pode definir relacionamentos 0..n para 0..n. I.e. Um caso de teste pode ser atribuido para nenhum, um ou mais requisitos e vice versa. Assim a matriz de rastreabilidade ajuda a investigar a cobertura dos requisitos de teste e finalmente quais falharam durante os testes. Esta análise serve como entrada para o próximo planejamento de testes.</p>

<h2>Iniciar:</h2>
<ol>
	<li>Escolha um Caso de Teste na árvore à esquerda. O combo box com a lista de Especificações de Requisitos é exibido no topo da área de trabalho.</li>
	<li>Escolha um documento de Especificação se mais de um estiver definido. O Testlink recarregará a página automaticamente.
	Um bloco ao centro da área de trabalho lista todos os requisitos (para a Especificação selecionada), que está conectada ao caso de teste. O bloco abaixo 'Requisitos Disponíveis' lista todos os requisitos que não estão relacionados ao caso de teste selecionado. Um arquiteto pode selecionar requisitos que são cobertos por este caso de teste e então clicar em 'Atribuir'. Este novo caso de teste atribuido será exibido no bloco central 'Requisitos Atribuidos'.</li>
</ol>";


// --------------------------------------------------------------------------------------
$TLS_htmltext_title['editTc']	= "Especificação de Testes";
$TLS_htmltext['editTc'] 		= "<h2>Objetivo:</h2>
<p>A <i>Especificação de Testes</i> permite aos usuários visualizarem e editar todas as <i>Suites de Teste</i> e <i>casos de teste</i> existentes. " . "Os Casos de Teste são versionados e todas as versões anteriores estão disponíveis e podem ser visualizadas e gerenciadas aqui.</p>

<h2>Iniciar:</h2>
<ol>
	<li>Selecione seu Projeto de Testes na árvore de navegação (o nó principal). <i>Observe: " .
	"Você sempre poderá trocar o Projeto de Testes ativo selecionando um diferente da " .
	"lista drop-down do canto superior esquerdo.</i></li>
	<li>Crie uma nova Suite de Testes clicando em <b>Nova Suite de Testes</b>. As Suites de Testes podem " .
	"trazer a estrutura da sua documentação de testes conforme suas convenções (testes funcionais/não-funcionais " .
	", produtos, componentes or características, solicitãções de mudança, etc.). A descrição da " .
	"Suite de Testes poderia conter o escopo dos casos de testes incluidos, configuração padrão, " .
	"links para documentos relevantes, limitações e outras informações usuais. Em geral, " .
	"todas anotações que são comuns às Suites de Testes. As Suites de Testes seguem " .
	"a metáfora do &quot;diretório&quot;, assim os usuários podem mover e copiar Suites de Testes dentro " .
	"do Projeto de Testes. Além disso, eles podem ser importados ou exportados (incluindo os Casos de Teste nele contidos).</li>
	<li>Suites de Testes são pastas escaláveis. Os usuários podem mover ou copiar Suites de Testes dentre " .
	"o Projeto de Testes. Suites de Testes podem ser importadas ou exportadas (incluindo os Casos de Teste).
	<li>Selecione sua mais nova Suite de Testes criada na árvore de navegação e crie " .
	"um novo Caso de Teste clicando em <b>Criar Caso(s) de Teste</b>. Um Caso de Teste especifica " .
	"um cenário de testes particular, resultados esperados e campos personalizados definidos " .
	"no Projeto de Testes (consulte o manual do usuário para maiores informações). Também é possivel " .
	"atribuir <b>palavras-chave</b> para melhorar a rastreabilidade.</li>
	<li>Navegue pela the tree view do lado esquerdo e edite os dados. Os Casos de Teste armazenam histórico próprio.</li>
	<li>Atribua suas Especificações de Testes criadas ao <span class=\"help\" onclick=\"javascript:open_help_window('glosary','$locale');\">Test Plan</span> quando seus Casos de Testes estiverem prontos.</li>
</ol>

<p>Com o TestLink você organiza os Casos de Testes em Suites de Teste." .
" Suites de Testes podem ser aninhadas em outras Suites de Testes, permitindo a você criar hierarquias de Suites de Testes.
 Então você pode imprimir esta informação juntamente com o Caso de Teste.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchTc']	= "Página de Busca de Casos de Teste";
$TLS_htmltext['searchTc'] 		= "<h2>Objetivo:</h2>

<p>Navegue de acordo com palavras-chave e/ou strings buscadas. A busca não é case sensitive. Os resultados incluem apenas Casos de Teste do Projeto de Testes atual.</p>

<h2>Para Buscar:</h2>

<ol>
	<li>Escreva a string buscada no campo apropriado. Deixe em branco campos sem uso no formulário.</li>
	<li>Escolha palavras-chave exigidas ou deixe o valor do campo 'Not applied'.</li>
	<li>Clique no botão Buscar.</li>
	<li>Todos os Casos de Teste cobertos são exibidos. Você pode modificar os Casos de Teste via link 'Título'.</li>
</ol>";


/* contribution by asimon for 2976 */
// requirements search
// ------------------------------------------------------------------------------------------

$TLS_htmltext_title['searchReq']        = "Página de Busca de Requisitos";
$TLS_htmltext['searchReq']              = "<h2>Objetivo:</h2>

<p>Navegue de acordo com as palavras-chave e/ou strings procuradas. A busca não é case sensitive. Os resultados incluem somente requisitos do projeto de teste atual.</p>

<h2>Para buscar:</h2>

<ol>
        <li>Escreva a string de busca no campo apropriado. Deixe os campos não utilizados em branco.</li>
	<li>Escolha a palavra-chave requerida ou deixe o valor em branco.</li>
	<li>Clique no botão  'Buscar'.</li>
	<li>Todos os requisitos cobertos pelos critérios de busca serão exibidos. Você pode modificar requisitos através do link 'Título'.</li>
</ol>";

// requirement specification search
// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['searchReqSpec']    = "Página de Busca de Especificação de Requisitos";
$TLS_htmltext['searchReqSpec']          = "<h2>Objetivo:</h2>

<p>Navegue de acordo com as palavras-chave e/ou strings procuradas. A busca não é case sensitive. Os resultados incluem somente requisitos do projeto de teste atual.</p>

<h2>Para buscar:</h2>
<ol>
	<li>Escreva a string de busca no campo apropriado. Deixe os campos não utilizados em branco.</li>
	<li>Escolha a palavra-chave requerida ou deixe o valor em branco.</li>
	<li>Clique no botão  'Buscar'.</li>
	<li>Todos os requisitos cobertos pelos critérios de busca serão exibidos. Você pode modificar requisitos através do link 'Título'.</li>
</ol>";
/* end contribution */


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['printTestSpec']	= "Imprimir Especificação de Testes"; //printTC.html
$TLS_htmltext['printTestSpec'] 			= "<h2>Objetivo:</h2>
<p>A partir daqui você pode imprimir um único caso de teste, todos os casos de teste dentro de uma suite ou todos os casos de teste de um Projeto de Testes ou Plano de Testes.</p>
<h2>Iniciar:</h2>
<ol>
<li>
<p>Selecione os campos dos casos de teste que você deseja exibir, e então clique em um Caso de Teste, Suite de Testes, ou Projeto de Testes. Uma página pronta para impressão será exibida.</p>
</li>
<li><p>Use a drop-box \"Show As\" no painel de navegação para especificar se você quer a informação exibida como HTML, como documento do Open Office Writer ou num documento do Microsoft Word. Veja <span class=\"help\" onclick=\"javascript:open_help_window('printFilter','{$locale}');\">help</span> para maiores informações.</p>
</li>
<li><p>Use a funcionalidade de impressão do seu browser para imprimir a informação presente.<br />
 <i>Nota: Certifique-se de imprimir somente o frame esquerdo.</i></p></li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['reqSpecMgmt']	= "Modelar Especificação Requisitos"; //printTC.html
$TLS_htmltext['reqSpecMgmt'] 			= "<p>Você pode gerenciar documentos de Especificação de Requisitos.</p>

<h2>Especificação de Requisitos</h2>

<p>Requisitos estão agrupados por <b>documentos de Especificação de Requisitos </b>, os quais estão relacionados ao Projeto de Testes.<br /> O TestLink não suporta (ainda) versões para Especificação de Requisitos e Requisitos. Logo, a versão do documento deve ser inserida após o <b>Título</b> da Especificação.
Um usuário pode inserir uma descrição simples ou notas no campo <b>Escopo</b>.</p>

<p><b><a name='total_count'>Sobrescrever o contador de Requisitos</a></b> serve para avaliar a cobertura dos requisitos no caso de nem todos os requisitos estarem adicionados ao TestLink.
O valor <b>0</b> significa que o contador de requisitos atual é utilizado para métricas.</p>
<p><i>E.g. SRS inclui 200 requisitos, mas somente 50 são adicionados ao Plano de Testes. A cobertura de testes é de 25% (se todos estes requisitos forem testados).</i></p>

<h2><a name='req'>Requisitos</a></h2>

<p>Clique no título da Especificação de Requisitos criada, e se nenhuma existir, clique no node do projeto para criar uma. Você pode criar, editar, excluir ou importar requisitos para o documento. Cada requisito tem um título, escopo e status.
O status deve ser 'Válido' or 'Não testável'. Requisitos Não Testáveis não são contabilizados para métricas. Este parâmetro deve ser utilizado para características não implementadas e requisitos modelados incorretamente.</p>

<p>Você pode criar novos casos de teste para os requisitos utilizando multi ações para os requisitos ativos na tela de especificação de requisitos. Estes casos de testes são criados dentro da Suite de Testes com nome definido na configuração <i>(default is: \$tlCfg->req_cfg->default_testsuite_name = 'Test suite created by Requirement - Auto';)</i>. Título e Escopo são copiados destes Casos de Teste.</p>";



// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['keywordsAssign']	= "Atribuição de Palavras-chave";
$TLS_htmltext['keywordsAssign'] 			= "<h2>Objetivo:</h2>
<p>A página de Atribuição de Palavras-chave é o lugar onde os usuários podem atribuir em lotes Palavras-chave às suites de teste ou Casos de Teste.</p>

<h2>Para Atribuir Palavras-chave:</h2>
<ol>
	<li>Selecione uma Suite de Testes ou Caso de Teste na tree view à esquerda.</li>
	<li>O box no topo da página que exibe informações do lado direito permitirá a você atribuir palavras-chave para os casos de teste individualmente.</li>
	<li>A seleção abaixo permite a você atribuir Casos de Teste em um nivel mais granular.</li>
</ol>

<h2>Informação Importante quanto à Atribuição de Palavras-chave nos Planos de Teste:</h2>
<p>Atribuir Palavras-chave à Suite de Testes afetará somente Casos de Teste no seu Plano de Testes somente se o Plano de Testes conter a última versão do Caso de Teste. Caso contrário, se o Plano de Testes conter versões mais antigas do Caso de Teste, as atribuições que você fez não aparecerão no Plano de Testes.</p>
<p>O TestLink usa esta abordagem para que versões mais antigas dos Casos de Teste nos Planos de Teste não sejam afetadas pela atribuição de Palavras-chave que você fez nas versões mais recentes dos Casos de Teste. Se você deseja seus Casos de Teste no seu Plano de Teste sejam atualizados, primeiro verifique se eles estão atualizados utilizando a funcionalidade 'Atualizar Versão dos Casos de Teste' antes de fazer a atribuição das Palavras-chave.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['executeTest']	= "Execução dos Casos de Teste";
$TLS_htmltext['executeTest'] 		= "<h2>Objetivo:</h2>

<p>Permite aos usuários executar os Casos de Teste. O usuário pode atribuir resultados aos Casos de Teste nos Ciclo de Teste. Veja a ajuda para mais informações sobre filtros e configurações " .
		"(clique no ícone interrogação).</p>

<h2>Iniciar:</h2>

<ol>
	<li>O usuário deve definir um Ciclo de Testes para o Plano de Testes.</li>
	<li>Selecionar um Ciclo de Testes no menu drop down e clicar no botão \"Aplicar Filtro\" no painel de navegação.</li>
	<li>Clique no Caso de Teste no menu em árvore.</li>
	<li>Preencha o resultado do Caso de Teste, suas respectivas notas e/ou bugs.</li>
	<li>Salve os resultados.</li>
</ol>
<p><i>Nota: O TestLink deve ser configurado para colaborar com seu Bug tracker se você quiser criar ou rastrear problemas reportados diretamente da GUI.</i></p>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['showMetrics']	= "Descrição dos Relatórios de Teste e Métricas";
$TLS_htmltext['showMetrics'] 		= "<p>Os relatórios estão relacionados a um Plano de Testes " .
		"(definido no topo do navegador). Este Plano de Testes pode diferir do Plano de Testes corrente para execução. Você também pode selecionar formatos dos relatórios:</p>
<ul>
<li><b>Normal</b> - relatório é exibido em uma página web</li>
<li><b>MS Excel</b> - relatório exportado para o Microsoft Excel</li>
<li><b>HTML Email</b> - relatório é encaminhado por e-mail para o endereço de e-mail do usuário</li>
<li><b>Charts</b> - relatório inclui gráficos (tecnologia flash)</li>
</ul>

<p>O botão imprimir ativa impressão para um único relatório (sem navegação).</p><p>Existem vários relatórios separados a sua escolha. Seus propósitos e funções estão descritas abaixo.</p>

<h3>Métricas Gerais do Plano de Testes</h3>
<p>Esta página exibe somente o último status de um Plano de Testes por Suite de Testes, Testador e palavras-chave.
O 'último status' é determinado pelo ciclo mais recente onde os Casos de Teste foram executados. Por exemplo, se um Caso de Testes foi executado em diversos Ciclos de Teste, somente o último resultado é considerado.</p>

<p> O 'último resultado' é um conceito utilizado em vários relatórios, e é determinado como a seguir:</p>
<ul>
<li>A ordem em cada Ciclo de Testes é adicionada ao Plano de Testes determina qual é o Ciclo de Testes mais recente. Os resultados do Ciclo de Testes mais recente prevalecerão sobre os Ciclos de Teste mais antigos. Por exemplo, se você marcar um teste com status 'Falhou' no Ciclo de Testes 1 e no Ciclo de Testes 2 como 'Passou', o último resultado será 'Passou'.</li>
<li>Se um Caso de Teste é executado diversas vezes em um mesmo Ciclo de Testes, o resultado mais recente prevalecerá. Por exemplo, se o Ciclo de Testes 3 é liberado para a equipe de testes e o Testador 1 marcar 'Passou' as 14h, e o Testador 2 marcar 'Falhou' as 15h, isto aparecerá como 'Falhou'.</li>
<li>Casos de Teste marcados como 'Não Executado' no último Ciclo de Testes não serão considerados. Por exemplo, se você marcar um Caso de Testes como 'Passou' no Ciclo de Testes 1 e não executar no Ciclo de Testes 2, o último resultado será considerado como 'Passou'.</li>
</ul>
<p>As seguintes tabelas são exibidas:</p>
<ul>
	<li><b>Resultados por Suite de Testes de Nível Top</b>
	Lista os resultados Top de cada Suite de Testes. Total de Casos de Teste, Passou, Falhou, Bloqueado, Não Executado e o percentual completado são listados. Um Caso de Teste 'completo' é aquele que foi marcado como Passou, Falhou ou Bloqueado.
	Resultados por Suites de Teste de Nível Top incluem todas as suites de nível inferior.</li>
	<li><b>Resultados por Palavra-chave</b>
	Lista todas as palavras-chave que estão atribuidas aos Casos de Teste no Plano de Testes corrente, e os resultados associados e eles.</li>
	<li><b>Resultados por Testador</b>
	Lista cada Testador que tem Casos de Teste associados a ele no Plano de Testes corrente. Os Casos de Teste que não estão atribuidos são computados abaixo com a descrição 'desatribuir'.</li>
</ul>

<h3>Status Geral dos Ciclos de Teste</h3>
<p>Lista os resultados de execução para cada Ciclo de Testes. Para cada Ciclo de Testes, o total de Casos de Teste, total com Passou, % Passou, total Falha, % Falha, Bloqueado, % Bloqueado, Não Executado e % Não Executado.  Se um Caso de Teste foi executado duas vezes no mesmo Ciclo de Testes, a execução mais recente será considerada.</p>

<h3>Métricas da Consulta</h3>
<p>Este relatório consiste em uma página com o formulário de consulta e uma página com os resultados, a qual contém os dados da consulta.
A página com o formulário de consulta apresenta 4 controles. Cada controle está definido com um valor padrão que maximiza o número de Casos de Teste e Ciclos de Teste que a consulta deve ser executada. Alterar os controles permite aos usuários filtrar os resultados e gerar relatório específicos para Testadores em específico, palavras-chave, Suites de Teste e combinação de Ciclos de Teste.</p>

<ul>
<li><b>Palavras-chave</b> 0->1 palavras-chave podem ser selecionadas. Por padrão, nenhuma palavra-chave é selecionada. Se uma palavra-chave não é selecionada, então todos os Casos de Teste serão considerados indiferentemente de atribuição de palavras-chave. Palavras-chave são atribuidas na especificação de testes ou na página Gerenciar Palavra-chave. Palavras-chave atribuidas aos Casos de Teste alcançam todos os Planos de Teste, e também todas as versões de um Caso de Teste. Se você está interessado nos resultados para uma palavra-chave específica, você deverá alterar este controle.</li>
<li><b>Testador</b> 0->1 Testadores podem ser selecionados. Por padrão, nenhum uTestador é selecionado. Se um Testador não é selecionado, então todos os Casos de Teste serão considerados indiferentes de Testador atribuido. Atualmente não há funcionalidade para buscar por Casos de Teste não atribuidos. O Testador é atribuido através da página 'Atribuir Casos de Teste para Execução' e é feito com base no Plano de Testes. Se você está interessado em trabalhar com um Testador em específico, você deve alterar este controle.</li>
<li><b>Suite de Testes de Nível Top</b> 0->n Suites de Teste de Nível Top podem ser selecionadas. Por padrão, todas as Suites de Teste são selecionadas. Apenas as Suites de Teste que são selecioadas serão consultadas para as métricas do resultado. Se você está somente interessado em resultados para uma Suite de Testes específica você precisa alterar este controle.</li>
<li><b>Ciclos de Teste</b> 1->n Ciclos de Teste podem ser selecionados. Por padrão, todos os Ciclos de Teste são selecinados. Somente execuções realizadas no Ciclo de Testes que vcoê selecionou serão considerados quanto produzir métricas. Por exemplo, se você quiser ver quantos Casos de Teste foram executados nos últimos 3 Ciclos de Teste, você precisa alterar este controle. A seleção de Palavra-chave, Testador e Suite de Testes de Nível Top ditarão o número de Casos de Testes do seu Plano de Testes são usados para calcular métricas por Suite de Teste e Plano de Testes. Por exemplo, se você selecionar o Testador = 'Greg', palavra-chave = 'Prioridade 1', e todas as Suites de Teste disponíveis, somente Casos de Teste com Prioridade 1 atribuidos para Greg serão considerados. O '# de Casos de Teste' totais que você verá neste relatório serão influenciados por estes 3 controles.
A seleção dos Ciclos de Testes influenciarão se o Caso de Teste é considerado 'Passou', 'Falhou', 'Bloqueado', ou 'Não Executado'.  Favor classificar com a regra 'Last Test Result' à medida em que elas aparecem acima.</li>
</ul>
<p>Pressione o botão 'Submeter Consulta' para prosseguir com a consulta e exibir a página com os resultados.</p>

<p>A página com o relatório consultado exibirá: </p>
<ol>
<li>o parâmetro da consulta utilizado para criar o relatório</li>
<li>totais para todo o Plano de Teste</li>
<li>por um conjunto de particionamento dos totais (Soma / Passou / Falhou / Bloqueado / Não Executado) e todas execuções realizadas na Suite de Testes. Se um Caso de Teste foi executado mais de uma vez em múltiplos Ciclos de Teste, todas as execuções que foram gravadas serão exibidas nos Ciclos de Teste selecionados. No entanto, o resumo para esta Suite de Testes somente incluirá o último resultado para o Ciclo de Testes selecionado.</li>
</ol>

<h3>Relatórios de Casos de Teste Bloqueados, com Falha e Não Executados</h3>
<p>Estes relatórios exibem todos os Casos de Teste Bloqueados, com Falha e Não Executados. A lógica do último resultado dos testes (que está descrita nas Métricas Gerais do Plano de Testes) é novamente empregada para determinar se um Caso de Teste deve ser considerado Bloquedo, com Falha ou Não Executado. Casos de Teste Bloqueados e com Falha exibirão os bugs associados se o usuário estiver utilizando um sistema de bug tracking.</p>

<h3>Relatório de Testes</h3>
<p>Exibe o status de cada Caso de Teste em todos os Ciclos de Teste. O resultado da execução mais recente será utilizado se um Caso de Teste for executado múltiplas vezes em um mesmo Ciclo de Testes. É recomendado exportar este relatório para o formato do Excel para um fácil manuseio se um grande conjunto de dados estiver em utilização.</p>


<h3>Gráficos - Métricas Gerais do Plano de Testes</h3>
<p>A lógica do último resultado é utilizada para todos os gráficos que você verá. Os gráficos são animados para ajudar o usuário à visualizar as métricas do Plano de Testes atual. Os quatro gráficos fornecidos são:</p>
<ul><li>Gráfico de pizza com todos os Casos de Teste com status Passou, com Falha, Bloqueados e Não Executados</li>
<li>Gráfico de barras com os Resultados por palavra-chave</li>
<li>Gráfico de barras com os Resultados por Testador</li>
<li>Gráfico de barras com os Resultados por Suites Nível Top</li>
</ul>
<p>As seções e barras dos gráficos são coloridos de modo que o usuário possa identificar o número aproximado de Casos de Teste com status Passou, Falhou, Bloqueados e Não Executados.</p>
<p><i>Esta página de relatório requer que o seu browser tenha o plugin do flash (by http://www.maani.us) para exibir os resultados em formato gráfico.</i></p>

<h3>Bugs por Casos de Teste</h3>
<p>Este relatório exibe cada Caso de Teste com todos os bugs abertos para ele em todo o projeto. Este relatório está disponível somente se um Bug Tracking está conectado.</p>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planAddTC']	= "Adicionar / Remover Casos de Teste do Plano de Testes"; // testSetAdd
$TLS_htmltext['planAddTC'] 			= "<h2>Objetivo:</h2>
<p>Permitir aos usuários (com perfil de Lider de Testes) a adicionar ou remover Casos de Teste ao Plano de Testes.</p>

<h2>Para adicionar ou remover Casos de Teste:</h2>
<ol>
	<li>Clique na Suite de Testes para ver todas as suites de teste e todos os seus Casos de Teste.</li>
	<li>Quando tiver terminado, clique no botão 'Adicionar / Remover Casos de Teste' para adicionar ou remover os Casos de Teste selecionados.
		Nota: não é possível adicionar o mesmo Caso de Teste múltiplas vezes.</li>
</ol>";

// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['tc_exec_assignment']	= "Atribuir Testadores à Execução de Testes";
$TLS_htmltext['tc_exec_assignment'] 		= "<h2>Objetivo:</h2>
<p>Esta página permite aos Líderes de Teste atribuir usuários a testes específicos dentro do Plano de Testes.</p>

<h2>Iniciar:</h2>
<ol>
	<li>Escolha o Caso de Teste ou Suite de Testes a ser testada.</li>
	<li>Selecione o Testador conforme planejamento.</li>
	<li>Pressione o botão 'Salvar' para aplicar atribuição.</li>
	<li>Abra a página de execução para verificar atribuição. Você pode estabelecer um filtro por usuários.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['planUpdateTC']	= "Atualizar Casos de Teste no Plano de Testes";
$TLS_htmltext['planUpdateTC'] 		= "<h2>Objetivo:</h2>
<p>Esta página permite atualizar Casos de Teste para uma versão mais nova (diferente) da Especificação de Casos de Teste quando alterada. Isto frequentemente acontece quando uma funcionalidade é esclarecida durante os testes." .
		" O usuário modifica a Especificação de Testes, mas as alterações precisam se propagar ao Plano de Testes também. De qualquer forma," .
		" o Plano de Testes mantém as versões originais para garantir que os resultados se referem ao texto correto dos Casos de Teste.</p>

<h2>Iniciar:</h2>
<ol>
	<li>Escolha o Caso de Teste ou Suite de Testes a testar.</li>
	<li>Escolha uma nova versão no combo box para um Caso de Testes Específico.</li>
	<li>Pressione o botão  'Atualizar Plano de Testes' para aplicar alterações.</li>
	<li>Para comprovar: abra a página de execução para verificar o texto do(s) Caso(s) de Teste.</li>
</ol>";


// ------------------------------------------------------------------------------------------
$TLS_htmltext_title['test_urgency']	= "Especificar testes com prioridade alta ou baixa";
$TLS_htmltext['test_urgency'] 		= "<h2>Objetivo:</h2>
<p>O TestLink permite definir a urgência das Suites de Teste para afetar a prioridade dos Casos de Teste. A priorização dos testes depende da importância do Caso de Teste e da urgência definida no Plano de Testes. O Lider de Testes deve especificar um conjunto de Casos de Teste que devem ser testados primeiro. Isso ajuda a assegurar que os testes cobrirão os testes mais importantes também sob a pressão do tempo.</p>

<h2>Iniciar:</h2>
<ol>
	<li>Escolha uma Suite de Testes para definiar a urgência do produto/componente no navegador ao lado esquerdo da janela.</li>
	<li>Escolha o nível de urgência (Alta, Média ou Baixa). O nível médio é o padrão. Você pode diminuir a prioridade para partes do produtos não alteradas e aumentar para componentes com mudanças significativas.</li>
	<li>Pressione o botão 'Salvar' para aplicar as alterações.</ol>

<p><i>Por exemplo, um Caso de Teste com prioridade Alta em uma Suite de Testes com urgência Baixa será de prioridade Média.</i>";


// ------------------------------------------------------------------------------------------


?>