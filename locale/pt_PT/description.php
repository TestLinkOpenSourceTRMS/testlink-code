<?php
/** 
 * ♔ TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Localization: Portuguese (pt_PT) texts - default development localization (World-wide English)
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
 * No revision is stored for the the file - see GIT history
 * 
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2003-2017 TestLink community 
 * @version    	GIT: $Id: description.php,v 1.9.17 2017/02/10 23:52:02 HelioGuilherme66 Exp $
 * @link 		http://www.testlink.org/
 *
 * @internal Revisions:
 * 20170209 - HelioGuilherme66 - Adaptation to Portuguese (pt_PT) version 1.9.17
 * 20130221 - mazin - Translation for Portuguese (pt_BR) version 1.9.5
 * 20111117 - pravato - Translation for Portuguese (pt_BR)
 * 20100409 - eloff - BUGID 3050 - Update execution help text
 **/
// printFilter.html
$TLS_hlp_generateDocOptions = "<h2>Opções para a produção do documento</h2>

<p>Esta tabela permite ao utilizador filtrar os Casos de Teste antes de serem visualizados. 
Se selecionado (marcado) os dados serão mostrados. Para alterar os dados 
apresentados, marque ou desmarque clicando no Filtro, e selecione o nível 
desejado na árvore de dados.</p>

<p><b>Cabeçalho do Documento:</b> Os utilizadores podem filtrar informações no cabeçalho do documento. 
As informações do cabeçalho do documento incluem: Introdução, Âmbito, 
Referências, Metodologia de Teste, e Limitações de Teste.</p>

<p><b>Corpo do Caso de Teste:</b> Os utilizadores podem filtrar informações do corpo do Caso de Teste. As informações do corpo do Caso de Teste 
incluem: Resumo, Passos, Resultados Esperados, e Palavras Chave</p>

<p><b>Resumo do Caso de Teste:</b> Os utilizadores podem filtrar informações do Resumo do Caso de Teste através do Título do Caso de Teste, 
no entanto, eles não podem filtrar informações do Resumo do Caso de Teste através do Corpo de um Caso de Teste. 
O resumo do Caso de Teste foi apenas parcialmente separado do corpo do Caso de Teste a fim de apoiar a visualização 
do Título com um breve resumo e a ausência de Passos, Resultados Esperados, 
e Palavras Chave. Se um utilizador decidir ver o corpo do Caso de Teste, o Resumo do Caso de Teste 
será sempre incluído.</p>

<p><b>Tabela de Conteúdo:</b> O TestLink insere uma lista com todos os títulos com seus links internos marcados.</p>

<p><b>Formatos de Saída:</b> Existem várias possibilidades: HTML, OpenOffice Writer, OpenOffice Calc, Excel, 
Word ou por E-mail (HTML).</p>";

// testPlan.html
$TLS_hlp_testPlan = "<h2>Plano de Teste</h2>

<h3>Geral</h3>
<p>O Plano de Teste é uma abordagem sistemática ao teste de um sistema de software. Você pode organizar a atividade de teste com 
 versões particulares do produto em tempo e resultados rastreáveis.</p>

<h3>Execução do Teste</h3>
<p>Esta é a secção onde os utilizadores podem executar os Casos de Teste (escrever os resultados dos testes) 
e imprimir a Suite de Casos de Teste do Plano de Teste. Nesta secção os utilizadores podem 
acompanhar os resultados da sua execução dos Casos de Teste.</p> 

<h2>Gestão do Plano de Teste</h2>
<p>Esta secção, apenas acessível aos líderes, permite que os utilizadores possam administrar os Planos de Testes. 
A administração de Planos de Testes envolve a criação/edição/eliminação de Planos, acréscimo/edição 
/eliminação/atualização dos Casos de Teste dos Planos, criando versões, bem como definindo quem pode 
ver qual Plano.<br />
Utilizadores com permissão de líder poderão também definir a prioridade/risco e a propriedade das 
Suites de Caso de Teste (categorias) e criar marcos de teste.</p> 

<p>Nota: É possível que os utilizadores não possam ver uma lista suspensa que contenha os Planos de Testes. 
Nesta situação, todos os links (exceto para os líderes ativos) estarão desativados. Se você 
estiver nesta situação, contacte a administração do TestLink para lhe conceder os 
direitos de Projecto adequado ou criar um Plano de Teste para você.</p>"; 

// custom_fields.html
$TLS_hlp_customFields = "<h2>Campos Personalizados</h2>
<p>Seguem alguns factos sobre a implementação de Campos Personalizados:</p>
<ul>
<li>Campos Personalizados são definidos para todo o sistema.</li>
<li>Campos Personalizados são associados ao tipo do elemento (Suite de Teste, Caso de Teste).</li>
<li>Campos Personalizados podem ser associados a múltiplos Projectos de Testes.</li>
<li>A sequência em que os Campos Personalizados serão mostrados pode ser diferente para cada Projecto de Testes.</li>
<li>Campos Personalizados podem ser inativados para um Projecto de Testes específico.</li>
<li>O número de Campos Personalizados não é restrito.</li>
</ul>

<p>A definição de um Campo Personalizado inclui os seguintes
atributos:</p>
<ul>
<li>Nome do Campo Personalizado.</li>
<li>Capturar o nome da variável (ex: Este é o valor que é fornecido para a API lang_get(), 
ou mostrado como se não for encontrado no ficheiro de linguagem).</li>
<li>Tipo do Campo Personalizado (texto, numérico, decimal, enumeração, email).</li>
<li>Possibilidade de enumerar os valores (ex: RED|YELLOW|BLUE), aplicável a uma lista, lista de multiseleção 
e tipos de combo.<br />
<i>Utilize o caractere pipe ('|') para
separar os possíveis valores para uma enumeração. Um dos possíveis valores pode ser 
um texto vazio.</i>
</li>
<li>Valor por omissão: NÃO IMPLEMENTADO AINDA.</li>
<li>Tamanho Mínimo/Máximo para o valor do Campo Personalizado (utilize 0 para desativar). (NÃO IMPLEMENTADO AINDA).</li>
<li>Utilizar uma expressão regular para validar a entrada do utilizador
(use <a href=\"http://au.php.net/manual/en/function.ereg.php\">ereg()</a>
syntax). <b>(NÃO IMPLEMENTADO AINDA)</b></li>
<li>Todos os Campos Personalizados são salvos como VARCHAR(255) na base de dados.</li>
<li>Mostrado na Especificação do Teste.</li>
<li>Ativado na Especificação do Teste. O utilizador pode alterar o valor durante a Especificação do Caso de Teste.</li>
<li>Mostrado na Execução do Teste.</li>
<li>Ativado na Execução do Teste. O utilizador pode alterar o valor durante a Execução do Caso de Teste.</li>
<li>Mostrado no Planeamento do Plano de Teste.</li>
<li>Ativado no Planeamento do Plano de Teste. O utilizador pode alterar o valor durante o planeamento do Plano de Teste (adicionar Casos de Teste ao Plano de Teste).</li>
<li>Disponível para o utilizador escolher o tipo de campo.</li>
</ul>
";

// execMain.html
$TLS_hlp_executeMain = "<h2>Executar Casos de Teste</h2>
<p>Permite aos utilizadores 'Executar' os Casos de Teste. Uma Execução propriamente 
dita é apenas a atribuição do resultado de um Caso de Teste (Passou, 
Falhado ou Bloqueado) de uma compilação selecionada.</p>
<p>O acesso a um Gestor de Ocorrências (Bugtracker) pode ser configurado. O utilizador pode adicionar diretamente novas Ocorrências e navegar pelas existentes. Consulte o manual de instalação para mais detalhes.</p>";

//bug_add.html
$TLS_hlp_btsIntegration = "<h2>Adicionar Ocorrências ao Caso de Teste</h2>
<p><i>(apenas se estiver configurado)</i>
O TestLink possui uma integração muito simples com os sistemas de Gestão de Ocorrências, 
mas não é capaz de enviar um pedido de abertura de Ocorrência ao Gestor de Ocorrências ou receber de volta o ID da Ocorrência. 
A integração é feita utilizando um link para a página do Gestor de Ocorrências, com as seguintes características:
<ul>
	<li>Inserir nova Ocorrência.</li>
	<li>Exibição das informações da Ocorrência. </li>
</ul>
</p>  

<h3>Processo para adicionar uma nova Ocorrência</h3>
<p>
   <ul>
   <li>Passo 1: Utilize o link para abrir o Gestor de Ocorrências e inserir uma nova Ocorrência. </li>
   <li>Passo 2: Anote o ID da Ocorrência gerada pelo Gestor de Ocorrências.</li>
   <li>Passo 3: Escreva o ID da Ocorrência no campo de entrada.</li>
   <li>Passo 4: Clique no botão Adicionar Ocorrência</li>
   </ul>  

Depois de fechar a página de Adição de Ocorrência, os dados relevantes da Ocorrência serão mostrados na página de execução.
</p>";

// execFilter.html
$TLS_hlp_executeFilter = "<h2>Configurações</h2>

<p>Em Configurações é possível que você selecione o Plano de Teste, a Build e 
a Plataforma (se disponível) para ser executado.</p>

<h3>Plano de Teste</h3>
<p>Você pode escolher o Plano de Teste necessário. De acordo com o Plano de Teste escolhido, serão mostradas as Builds apropriadas. Depois de escolher um Plano de Teste, os filtros serão reiniciados.</p>

<h3>Plataformas</h3>
<p>Se o recurso de Plataformas é usado, você deve selecionar a Plataforma apropriada antes da execução.</p>

<h3>Execução da Build</h3>
<p>Você pode escolher a Build em que deseja executar os Casos de Teste.</p>

<h2>Filtros</h2>
<p>Os Filtros proporcionam a oportunidade de influenciar ainda mais o conjunto de Casos de Teste mostrados.
Através dos Filtros é possível diminuir o conjunto de Casos de Teste mostrados. Selecione 
os filtros desejados e clique no botão \"Aplicar\".</p>

<p>Os Filtros Avançados permitem que você especifique um conjunto de valores para filtros aplicáveis 
​​usando Ctrl + Clique dentro de cada ListBox.</p>


<h3>Filtro de Palavra Chave</h3>
<p>Você pode filtrar os Casos de Teste pelas Palavras Chave que foram atribuídas. Você pode escolher " .
"múltiplas Palavras Chave utilizando Ctrl + Clique. Se você escolher mais que uma Palavra Chave, você pode " .
"decidir se apenas serão mostrados os Casos de Teste que contêm todas as Palavras Chave selecionadas " .
"(botão \"E\") ou pelo menos uma das Palavras Chave escolhidas (botão \"OU\").</p>

<h3>Filtro de Prioridade</h3>
<p>Você pode filtrar os Casos de Teste pela prioridade do Teste. A prioridade do Teste é a \"importância do Caso de Teste\" " .
"combinado com \"a urgência do Teste\" dentro do Plano de Teste atual.</p> 

<h3>Filtro de Utilizador</h3>
<p>Você pode filtrar os Casos de Teste que não estão atribuídos (\"Ninguém\") ou atribuídos a \"Alguém\". " .
"Você também pode filtrar os Casos de Teste que são atribuídos a um Testador específico. Se você escolheu um Testador " .
"específico, também existe a possibilidade de mostrar os Casos de Teste que estão por serem atribuídos " .
"(Filtros avançados estão disponíveis).</p>

<h3>Filtro de Resultado</h3>
<p>Você pode filtrar os Casos de Teste pelos resultados (Filtros avançados estão disponíveis). Você pode filtrar por " .
"resultado \"na Build escolhida para a execução\", \"na última execução\", \"em TODAS as Builds\", " .
"\"em QUALQUER Build\" e \"em uma Build específica\". Se \"uma Build específica\" for escolhida, então você pode " .
"especificar a Build. </p>";


// newest_tcversions.html
$TLS_hlp_planTcModified = "<h2>Versões mais recentes do Caso de Teste</h2>
<p>Todo o conjunto de Casos de Teste ligados ao Plano de Teste é analisado, e uma lista de Casos 
de Teste que têm uma versão mais recente é mostrada (contra o conjunto atual do Plano de Teste).
</p>";


// requirementsCoverage.html
$TLS_hlp_requirementsCoverage = "<h3>Cobertura de Requisitos</h3>
<br />
<p>Este recurso permite mapear uma cobertura de utilizador ou Requisitos do sistema 
por Casos de Teste. Navegue através do link \"Especificar Requisitos\" na tela principal.</p>

<h3>Especificação de Requisitos</h3>
<p>Os Requisitos estão agrupados no documento 'Especificação de Requisitos' que está relacionado ao 
Projecto de Testes.<br /> O TestLink ainda não suporta versões para a Especificação de Requisitos e   
também para os Requisitos. Assim, a versão do documento deve ser adicionada depois do 
<b>Título</b> da Especificação.
O utilizador pode adicionar uma descrição simples ou uma nota no campo <b>Âmbito</b>.</p> 

<p>Sobrescrever o contador de Requisitos serve para avaliar a cobertura dos Requisitos no caso 
de nem todos os Requisitos estarem adicionados ao TestLink.
<p>O valor <b>0</b> significa que a contagem atual de Requisitos é usado para métricas.</p> 
<p><i>Ex: SRS inclui 200 Requisitos, mas somente 50 são adicionados ao Plano de Teste. A cobertura de testes 
é de 25% (se todos estes Requisitos forem testados).</i></p>

<h3>Requisitos</h3>
<p>Clique no título para criar uma Especificação de Requisitos. Você pode criar, editar, apagar 
ou importar Requisitos para este documento. Cada Requisito tem título, âmbito e status.
O status deve ser \"Válido\" ou \"Não testado\". Requisitos não testados não são contabilizados
para as métricas. Este parâmetro deve ser utilizado para características não implementadas 
e Requisitos modelados incorretamente.</p> 

<p>Você pode criar novos Casos de Teste para os Requisitos utilizando multi ações para os Requisitos 
ativos na tela de especificação de Requisitos. Estes Casos de Teste são criados dentro da Suite de 
Teste com nome definido na configuração <i>(padrão é: &#36;tlCfg->req_cfg->default_testsuite_name = 
\"Test suite created by Requirement - Auto\";)</i>. Título e Âmbito são copiados destes Casos de Teste.</p>
";

$TLS_hlp_req_coverage_table = "<h3>Cobertura:</h3>
Um valor por ex. de \"40% (8/20)\" significa que 20 Casos de Teste devem ser criados para testar completamente este
Requisito. 8 destes já foram criados e associados ao Requisito, com 
a cobertura de 40 %.
";


// req_edit
$TLS_hlp_req_edit = "<h3>Links internos no Âmbito:</h3>
<p>Links internos servem ao propósito da criação de links a outros Requisitos / especificações de Requisitos 
com uma sintaxe especial. O comportamento dos Links internos pode ser alterado no ficheiro de configuração.
<br /><br />
<b>Uso:</b>
<br />
Link para Requisitos: [req]req_doc_id[/req]<br />
Link para Especificação de Requisitos: [req_spec]req_spec_doc_id[/req_spec]</p>

<p>O Projecto de Testes do Requisito / Especificação de Requisitos, uma versão e uma âncora 
também podem ser especificados:<br />
[req tproj=&lt;tproj_prefix&gt; anchor=&lt;anchor_name&gt; version=&lt;version_number&gt;]req_doc_id[/req]<br />
Esta sintaxe também funciona para as especificações de Requisito (atributos de versão não tem nenhum efeito).<br />
Se você não especificar a versão do Requisito completo, todas as versões serão mostradas.</p>

<h3>Log para mudanças:</h3>
<p>Sempre que uma alteração é feita, o Testlink irá pedir uma mensagem de log. Esta mensagem de log serve como rastreabilidade.
Se apenas o âmbito do Requisito mudou, você pode decidir se deseja criar uma nova revisão ou não.
Sempre que alguma coisa além do âmbito é alterado, você é forçado a criar uma nova revisão.</p>
";


// req_view
$TLS_hlp_req_view = "<h3>Links Diretos:</h3>
<p>É fácil compartilhar este documento com outros, basta clicar no ícone do globo no topo deste documento para criar um link direto.</p>

<h3>Ver Histórico:</h3>
<p>Este recurso permite comparar revisões/versões de Requisitos, se mais de uma revisão/versão de Requisitos existir.
A visão geral fornece uma mensagem de log para cada revisão/versão, um timestamp e autor da última alteração.</p>

<h3>Cobertura:</h3>
<p>Exibir todos os Casos de Teste associados para este Requisito.</p>

<h3>Relações:</h3>
<p>Relações de Requisitos são usados ​​para relacionamentos de modelos entre os Requisitos.
Relações personalizadas e a opção de permitir relações entre os Requisitos de
diferentes Projectos de Testes podem ser configurados no ficheiro de configuração.
Se você definir a relação \"Requisito A é pai do Requisito B\", 
o Testlink irá definir a relação \"Requisito B é filho do Requisito A\" implicitamente.</p>
";


// req_spec_edit
$TLS_hlp_req_spec_edit = "<h3>Links internos no Âmbito:</h3>
<p>Links internos servem ao propósito da criação de links a outros Requisitos / especificações de Requisitos 
com uma sintaxe especial. O comportamento dos Links internos pode ser alterado no ficheiro de configuração.
<br /><br />
<b>Uso:</b>
<br />
Link para Requisitos: [req]req_doc_id[/req]<br />
Link para Especificação de Requisitos: [req_spec]req_spec_doc_id[/req_spec]</p>

<p>O Projecto de Testes do Requisito / Especificação de Requisitos, uma versão e uma âncora 
também podem ser especificados:<br />
[req tproj=&lt;tproj_prefix&gt; anchor=&lt;anchor_name&gt; version=&lt;version_number&gt;]req_doc_id[/req]<br />
Esta sintaxe também funciona para as especificações de Requisito (atributos de versão não tem nenhum efeito).<br />
Se você não especificar a versão do Requisito completo, todas as versões serão mostradas.</p>
";


// planAddTC_m1.tpl
$TLS_hlp_planAddTC = "<h2>Sobre 'Campos Personalizados salvos'</h2>
Se você tiver definido e atribuído ao Projecto de Testes,<br /> 
Campos Personalizados com:<br />
 'Mostrar no desenho do Plano de Teste=true' e <br />
 'Activar no desenho do Plano de Teste=true'<br />
você irá ver nesta página APENAS os Casos de Teste ligados ao Plano de Teste.
";


// resultsByTesterPerBuild.tpl
$TLS_hlp_results_by_tester_per_build_table = "<b>Mais informações sobre os Testadores</b><br />
Se você clicar no nome do Testador nesta tabela, você irá ter uma visão mais detalhada
sobre todos os Casos de Teste atribuídos para esse utilizador e o seu progresso de teste.<br /><br />
<b>Nota:</b><br />
Este relatório mostra os Casos de Teste, que são atribuídos a um utilizador específico e foram executados
com base em cada Build ativa. Mesmo se um Caso de Teste foi executado por outro utilizador que não o utilizador atribuído,
o Caso de Teste irá aparecer como executado pelo utilizador atribuído.
";


// xxx.html
//$TLS_hlp_xxx = "";

// ----- END ------------------------------------------------------------------
?>
