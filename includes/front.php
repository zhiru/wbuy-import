<?php

//Hook para registrar a função que vai criar a tela de opções do plugin
add_action('admin_menu', 'criar_tela_opcoes');

//Função para criar a tela de opções do plugin
function criar_tela_opcoes() {
  //Adicionar uma nova página no menu de configurações do WordPress
  add_options_page(
    'Importação Wbuy', //Título da página
    'Importação Wbuy', //Título do menu
    'manage_options', //Permissão necessária para acessar a página
    'wbuy-import', //Slug da página
    'exibir_tela_opcoes' //Função que vai exibir o conteúdo da página
  );
}

//Função que vai exibir o conteúdo da tela de opções do plugin
function exibir_tela_opcoes() {
  //Verificar se o usuário tem permissão para acessar a página
  if (!current_user_can('manage_options')) {
    wp_die('Você não tem permissão para acessar esta página.');
  }

  //Exibir o título e o formulário da tela de opções
  echo '<h1>Importação Wbuy</h1>';
  echo '<form method="post" action="options.php">';
  
  //Gerar os campos ocultos necessários para o formulário
  settings_fields('wbuy-import');
  
  //Exibir as seções e os campos registrados anteriormente
  do_settings_sections('wbuy-import');
  
  //Exibir o botão de salvar as alterações
  submit_button();
  
  echo '</form>';

  //Verificar se o token foi salvo ou alterado
  if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' && !empty(get_option('wbuy-import-token'))) {
    //Se o token foi salvo ou alterado, exibir um botão para executar a sincronização manualmente
    echo '<form method="post" action="">';
    echo '<input type="hidden" name="sincronizar" value="1">';
    echo '<button type="submit">Sincronizar agora</button>';
    echo '</form>';
    
    //Verificar se o botão foi clicado
    if (isset($_POST['sincronizar']) && $_POST['sincronizar'] == '1') {
      //Se o botão foi clicado, executar a função de sincronização manualmente
      sincronizar_api();
      
      //Exibir uma mensagem de sucesso ou erro após a sincronização
      echo '<p>Sincronização concluída com sucesso.</p>';
      //echo '<p>Ocorreu um erro na sincronização.</p>';
    }
  }
}

//Hook para registrar a função que vai criar as seções e os campos da tela de opções do plugin
add_action('admin_init', 'criar_secoes_campos');

//Função para criar as seções e os campos da tela de opções do plugin
function criar_secoes_campos() {
  //Adicionar uma nova seção na tela de opções do plugin
  add_settings_section(
    'wbuy-import-secao', //Identificador da seção
    'Configurações do Importação Wbuy', //Título da seção
    'exibir_descricao_secao', //Função que vai exibir a descrição da seção (opcional)
    'wbuy-import' //Slug da página onde a seção vai aparecer
  );
  
  //Adicionar um novo campo na seção criada anteriormente
  add_settings_field(
    'wbuy-import-token', //Identificador do campo
    'Token do Wbuy', //Título do campo
    'exibir_campo_token', //Função que vai exibir o campo na tela de opções (opcional)
    'wbuy-import', //Slug da página onde o campo vai aparecer
    'wbuy-import-secao' //Identificador da seção onde o campo vai aparecer
  );
  
  //Registrar uma opção que vai armazenar o valor do campo na tabela wp_options do banco de dados
  register_setting(
    'wbuy-import', //Grupo de opções ao qual o campo pertence (deve ser igual ao usado na função settings_fields)
    'wbuy-import-token' //Nome da opção que vai armazenar o valor do campo (deve ser igual ao usado na função add_settings_field)
  );
}

//Função que vai exibir a descrição da seção na tela de opções do plugin (opcional)
function exibir_descricao_secao() {
  echo '<p>Nesta seção você pode configurar as opções do seu plugin.</p>';
}

//Função que vai exibir o campo na tela de opções do plugin (opcional)
function exibir_campo_token() {
  //Obter o valor atual da opção que armazena o token
  $token = get_option('wbuy-import-token');
  
  //Escapar o valor antes de exibi-lo no campo
  $token = esc_attr($token);
  
  //Exibir um campo de texto para inserir ou alterar o token
  echo '<input type="text" name="wbuy-import-token" value="' . $token . '" size="40">';
}
