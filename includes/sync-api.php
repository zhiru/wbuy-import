<?php

//Função para sincronizar categorias e produtos com a API externa
function sincronizar_api() {
    //Iniciar o cURL
    $curl = curl_init();
    
    //Configurar as opções do cURL
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://sistema.sistemawbuy.com.br/api/v1/category/',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Bearer '.get_option('wbuy-import-token')
      ),
    ));
    
    
    //Executar o cURL e armazenar a resposta
    $response = curl_exec($curl);
    
    //Fechar o cURL
    curl_close($curl);
    
    //Converter o JSON em objeto PHP
    $obj = json_decode($response);
    
    //Percorrer os dados dentro do array data
    foreach ($obj->data as $categoria) {
      //Extrair o nome e o id da categoria
      $nome = $categoria->nome;
      $id = $categoria->id;
    
      //Adicionar a categoria no WooCommerce
      wp_insert_term(
        $nome, //nome da categoria
        'product_cat', //taxonomia do WooCommerce
        array(
          'description' => '', //descrição da categoria (opcional)
          'slug' => $id //slug da categoria (opcional)
        )
      );
    
      //Percorrer os dados dentro do array subs
      foreach ($categoria->subs as $subcategoria) {
        //Extrair o nome e o id da subcategoria
        $subnome = $subcategoria->nome;
        $subid = $subcategoria->id;
    
        //Adicionar a subcategoria no WooCommerce
        wp_insert_term(
          $subnome, //nome da subcategoria
          'product_cat', //taxonomia do WooCommerce
          array(
            'description' => '', //descrição da subcategoria (opcional)
            'slug' => $subid, //slug da subcategoria (opcional)
            'parent' => $id //id da categoria pai
          )
        );
    
        //Percorrer os dados dentro do array subs da subcategoria
        foreach ($subcategoria->subs as $subsubcategoria) {
          //Extrair o nome e o id da subsubcategoria
          $subsubnome = $subsubcategoria->nome;
          $subsubid = $subsubcategoria->id;
    
          //Adicionar a subsubcategoria no WooCommerce
          wp_insert_term(
            $subsubnome, //nome da subsubcategoria
            'product_cat', //taxonomia do WooCommerce
            array(
              'description' => '', //descrição da subsubcategoria (opcional)
              'slug' => $subsubid, //slug da subsubcategoria (opcional)
              'parent' => $subid //id da subcategoria pai
            )
          );
        }
      }
    }
    
    //Iniciar outro cURL para ler a URL dos produtos
    $curl = curl_init();
    
    //Configurar as opções do cURL
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://sistema.sistemawbuy.com.br/api/v1/product/',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Bearer '.get_option('wbuy-import-token')
      ),
    ));
    
    //Executar o cURL e armazenar a resposta
    $response = curl_exec($curl);
    
    //Fechar o cURL
    curl_close($curl);
    
    //Converter o JSON em objeto PHP
    $obj = json_decode($response);
    
    //Percorrer os dados dentro do array data
    foreach ($obj->data as $produto) {
      //Extrair os dados do produto
      $id = $produto->id;
      $cod = $produto->cod;
      $nome = $produto->produto;
      $descricao = $produto->descricao;
      $valor_promo = floatval($produto->valor_promo);
      $ativo = intval($produto->ativo);
      
      //Verificar se o produto já existe pelo seu SKU (código)
      $product_id = wc_get_product_id_by_sku($cod);
    
      if ($product_id) {
        //Se o produto já existe, atualizar os dados existentes
        $product = wc_get_product($product_id);
        $product->set_name($nome);
        $product->set_description($descricao);
        if ($valor_promo > 0) {
          //Se tem valor promocional, definir como preço de venda e calcular o preço regular com base na porcentagem de desconto informada na API (10%)
          $preco_regular = round($valor_promo / (1 - 0.1),2);
          $product->set_regular_price($preco_regular);
          $product->set_sale_price($valor_promo);
        } else {
          //Se não tem valor promocional, definir como preço regular e remover o preço de venda se houver
          $product->set_regular_price($produto->valores[0]->valor);
          $product->set_sale_price('');
        }
        if ($ativo == 1) {
          //Se o produto está ativo, definir como publicado
          $product->set_status('publish');
        } else {
          //Se o produto está inativo, definir como rascunho
          $product->set_status('draft');
        }
        
        //Salvar as alterações no produto existente
        $product->save();
        
      } else {
        //Se o produto não existe, criar um novo produto simples com os dados informados na API
        $product = new WC_Product_Simple();
        $product->set_sku($cod);
        $product->set_name($nome);
        $product->set_description($descricao);
        if ($valor_promo > 0) {
          //Se tem valor promocional, definir como preço de venda e calcular o preço regular com base na porcentagem de desconto informada na API (10%)
          $preco_regular = round($valor_promo / (1 - 0.1),2);
          $product->set_regular_price($preco_regular);
          $product->set_sale_price($valor_promo);
        } else {
          //Se não tem valor promocional, definir como preço regular e remover o preço de venda se houver
          $product->set_regular_price($produto->valores[0]->valor);
          $product->set_sale_price('');
        }
        if ($ativo == 1) {
          //Se o produto está ativo, definir como publicado
          $product->set_status('publish');
        } else {
          //Se o produto está inativo, definir como rascunho
          $product->set_status('draft');
        }
        
        //Criar o novo produto e obter o seu ID
        $product_id = $product->save();
      }
    
      //Atribuir a categoria do produto de acordo com o seu nível
      if ($produto->categoria_level3->id > 0) {
        //Se tem categoria de nível 3, atribuir essa categoria
        wp_set_object_terms($product_id, $produto->categoria_level3->id, 'product_cat');
      } elseif ($produto->categoria_level2->id > 0) {
        //Se tem categoria de nível 2, atribuir essa categoria
        wp_set_object_terms($product_id, $produto->categoria_level2->id, 'product_cat');
      } else {
        //Se não tem categoria de nível 2 ou 3, atribuir a categoria de nível 1
        wp_set_object_terms($product_id, $produto->categoria_level1->id, 'product_cat');
      }
    
      //Adicionar as imagens do produto
      foreach ($produto->fotos as $foto) {
        if ($foto->video == "") {
          //Se é uma foto normal, adicionar como imagem do produto
          $image_id = media_sideload_image($foto->foto, $product_id, '', 'id');
          add_post_meta($product_id, '_thumbnail_id', $image_id);
        } else {
          //Se é uma foto de vídeo, adicionar como imagem da galeria e salvar a URL do vídeo em um meta customizado
          $image_id = media_sideload_image($foto->foto, $product_id, '', 'id');
          add_post_meta($product_id, '_product_image_gallery', $image_id);
          add_post_meta($product_id, '_video_url', $foto->video);
        }
      }
    }
}

//Hook para associar a função ao identificador do evento cron
add_action('sincronizar_api', 'sincronizar_api');