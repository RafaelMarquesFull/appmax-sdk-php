<?php

namespace Appmax;

// Autoloader para as classes do SDK
spl_autoload_register(function ($class) {
    // Verifica se a classe pertence ao namespace Appmax
    if (strpos($class, 'Appmax\\') !== 0) {
        return;
    }
    
    // Remove o namespace base
    $relativeClass = substr($class, strlen('Appmax\\'));
    
    // Converte namespace para caminho de arquivo
    $file = __DIR__ . '/' . str_replace('\\', '/', $relativeClass) . '.php';
    
    // Carrega o arquivo se existir
    if (file_exists($file)) {
        require $file;
    }
});
