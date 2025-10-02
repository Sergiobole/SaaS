<?php
echo "<pre>"; // Para formatar a saída e facilitar a leitura

echo "--- Diagnóstico do Autoloader ---<br><br>";

echo "Passo 1: Verificando os caminhos...<br>";
$vendas_dir = __DIR__;
echo "Diretório atual (__DIR__): " . $vendas_dir . "<br>";

$autoloader_path = $vendas_dir . '/../vendor/autoload.php';
echo "Caminho construído para o autoloader: " . $autoloader_path . "<br><br>";

echo "Passo 2: Verificando o arquivo...<br>";
if (file_exists($autoloader_path)) {
    echo "RESULTADO: SUCESSO! O arquivo autoload.php FOI ENCONTRADO.<br>";

    if (is_readable($autoloader_path)) {
        echo "RESULTADO: SUCESSO! O arquivo autoload.php tem permissão de leitura.<br><br>";

        echo "Passo 3: Tentando incluir o arquivo...<br>";
        require_once $autoloader_path;
        echo "RESULTADO: SUCESSO! O comando require_once foi executado sem erros.<br><br>";

        echo "Passo 4: Verificando a classe do Mercado Pago...<br>";
        if (class_exists('MercadoPago\SDK')) {
            echo "RESULTADO FINAL: SUCESSO TOTAL! A classe 'MercadoPago\SDK' foi encontrada!<br>";
        } else {
            echo "RESULTADO FINAL: FALHA! A classe 'MercadoPago\SDK' AINDA NÃO foi encontrada, mesmo após incluir o autoloader.<br>";
            echo "Isso sugere que a pasta 'vendor' pode estar corrompida ou incompleta. Tente deletá-la e rodar 'composer install' novamente.<br>";
        }
    } else {
        echo "RESULTADO: FALHA! O arquivo autoload.php foi encontrado, mas NÃO PODE SER LIDO. Verifique as permissões do arquivo/pasta.<br>";
    }
} else {
    echo "RESULTADO: FALHA! O arquivo autoload.php NÃO FOI ENCONTRADO no caminho especificado.<br>";
}

echo "</pre>";