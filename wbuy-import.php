<?php

/*
    Plugin Name: Plugin de importação do Wbuy
    Plugin URI: https://aireset.com.br/wbuy-import
    Description: Um plugin para sincronizar categorias e produtos com uma API externa
    Version: 1.0
    Author: Felipe Almeman
    Author URI: https://aireset.com.br
    License: GPL2
*/

//Função para sincronizar categorias e produtos com a API externa
include_once('includes/sync-api.php');

//Função para registrar o evento cron do WordPress
// include_once('includes/cron.php');

//Função para criar a tela de opções do plugin
include_once('includes/front.php');

//Hook para registrar a função que vai adicionar o link para a página de opções do plugin na lista de ações do plugin
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'adicionar_link_opcoes');

//Função que vai adicionar o link para a página de opções do plugin na lista de ações do plugin
function adicionar_link_opcoes($links) {
  //Gerar o link para a página de opções do plugin usando a função admin_url e passando o slug da página como parâmetro
  $link_opcoes = admin_url('options-general.php?page=wbuy-import');
  
  //Adicionar um novo elemento no array de links com o texto Configurações e o link gerado anteriormente
  $links[] = '<a href="' . $link_opcoes . '">Configurações</a>';
  
  //Retornar o array modificado com o novo link adicionado
  return $links;
}