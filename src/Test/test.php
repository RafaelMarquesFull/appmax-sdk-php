<?php

// Arquivo de teste para validar a funcionalidade básica do SDK

require_once __DIR__ . '/src/autoload.php';

use Appmax\AppmaxAPI;

// Verifica se as classes principais foram carregadas corretamente
echo "Verificando carregamento de classes...\n";

try {
    // Tenta instanciar a classe principal
    $api = new AppmaxAPI('test_api_key');
    echo "✓ Classe AppmaxAPI carregada com sucesso\n";
    
    // Verifica se os managers foram instanciados corretamente
    echo "✓ APIManager disponível: " . (isset($api->api) ? "Sim" : "Não") . "\n";
    echo "✓ CustomersManager disponível: " . (isset($api->customers) ? "Sim" : "Não") . "\n";
    echo "✓ OrdersManager disponível: " . (isset($api->orders) ? "Sim" : "Não") . "\n";
    echo "✓ PaymentsManager disponível: " . (isset($api->payments) ? "Sim" : "Não") . "\n";
    
    echo "\nValidação básica concluída com sucesso!\n";
    
} catch (Exception $e) {
    echo "✗ Erro ao instanciar classes: " . $e->getMessage() . "\n";
    echo "Classe: " . get_class($e) . "\n";
    echo "Código: " . $e->getCode() . "\n";
}
