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