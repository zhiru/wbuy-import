<?php

//Função para registrar o evento cron do WordPress
function registrar_evento_cron() {
  //Verificar se o evento já está agendado
  if (!wp_next_scheduled('sincronizar_api')) {
    //Se não está agendado, agendar o evento para ser executado de hora em hora
    wp_schedule_event(time(), 'hourly', 'sincronizar_api');
  }
}

//Hook para registrar a função como um evento cron do WordPress
add_action('wp', 'registrar_evento_cron');