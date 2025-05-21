# SDK Appmax para PHP

Este SDK não oficial permite a integração com a API da Appmax de forma simples e direta em projetos PHP.

## Esta não é uma versão oficial.

## Requisitos

- PHP 8.1 ou superior
- Extensão cURL habilitada

## Instalação

1. Faça o download do pacote
2. Extraia os arquivos em seu projeto
3. Inclua o autoloader:

```php
composer require appmax/appmax_api_sdk_php 
```

## Uso Básico

### Inicialização

```php
use Appmax\AppmaxAPI;

// Inicializar o SDK com sua chave de API
$appmax = new AppmaxAPI('sua_chave_api_aqui');

// Para ambiente de testes
$appmax = new AppmaxAPI('sua_chave_api_aqui', ['testMode' => true]);
```

### Criar um Cliente

```php
try {
    $cliente = $appmax->customers->create([
        'firstName' => 'Nome',
        'lastName' => 'Sobrenome',
        'email' => 'email@exemplo.com',
        'telephone' => '11999999999',
        'ip' => '127.0.0.1',
        'address' => [
            'postcode' => '12345678',
            'street' => 'Rua Exemplo',
            'number' => '123',
            'district' => 'Bairro',
            'city' => 'Cidade',
            'state' => 'SP'
        ]
    ]);
    
    // O hash do cliente é necessário para criar pedidos
    $clienteHash = $cliente['hash'];
    
} catch (\Appmax\Structures\AppmaxAPIError $e) {
    echo "Erro: " . $e->getMessage();
}
```

### Criar um Pedido

```php
try {
    $pedido = $appmax->orders->create([
        'customerHash' => $clienteHash,
        'total' => 99.90,
        'items' => [
            [
                'name' => 'Produto Exemplo',
                'price' => 99.90,
                'quantity' => 1,
                'sku' => 'SKU123'
            ]
        ]
    ]);
    
    // O hash do pedido é necessário para criar pagamentos
    $pedidoHash = $pedido['hash'];
    
} catch (\Appmax\Structures\AppmaxAPIError $e) {
    echo "Erro: " . $e->getMessage();
}
```

### Criar um Pagamento

```php
try {
    // Exemplo com cartão de crédito
    $pagamento = $appmax->payments->create([
        'orderHash' => $pedidoHash,
        'method' => 'credit_card',
        'installments' => 1,
        'card' => [
            'number' => '4111111111111111',
            'holder' => 'NOME DO TITULAR',
            'expiry' => '12/25',
            'cvv' => '123',
            'brand' => 'visa'
        ]
    ]);
    
    // Ou usando token do cartão
    $pagamento = $appmax->payments->create([
        'orderHash' => $pedidoHash,
        'method' => 'credit_card',
        'installments' => 1,
        'card' => [
            'token' => 'token_do_cartao',
            'holder' => 'NOME DO TITULAR',
            'brand' => 'visa'
        ]
    ]);
    
} catch (\Appmax\Structures\AppmaxAPIError $e) {
    echo "Erro: " . $e->getMessage();
}
```

### Tokenizar Cartão

```php
try {
    $token = $appmax->payments->tokenize([
        'number' => '4111111111111111',
        'holder' => 'NOME DO TITULAR',
        'expiry' => '12/25',
        'cvv' => '123',
        'brand' => 'visa'
    ]);
    
    // Usar o token em pagamentos futuros
    $tokenCartao = $token['token'];
    
} catch (\Appmax\Structures\AppmaxAPIError $e) {
    echo "Erro: " . $e->getMessage();
}
```

### Obter Parcelas Disponíveis

```php
try {
    $parcelas = $appmax->payments->getInstallments(99.90, 'visa');
    
    // Lista de parcelas disponíveis
    foreach ($parcelas as $parcela) {
        echo "Parcelas: " . $parcela['installments'];
        echo " - Valor: " . $parcela['installment_amount'];
        echo " - Total: " . $parcela['amount'];
        echo "\n";
    }
    
} catch (\Appmax\Structures\AppmaxAPIError $e) {
    echo "Erro: " . $e->getMessage();
}
```

### Adicionar Código de Rastreio

```php
try {
    $appmax->orders->addTrackingCode($pedidoHash, 'BR1234567890BR');
} catch (\Appmax\Structures\AppmaxAPIError $e) {
    echo "Erro: " . $e->getMessage();
}
```

### Reembolsar Pedido

```php
try {
    $appmax->orders->refund($pedidoHash, [
        'reason' => 'Motivo do reembolso',
        'amount' => 99.90 // opcional, para reembolso parcial
    ]);
} catch (\Appmax\Structures\AppmaxAPIError $e) {
    echo "Erro: " . $e->getMessage();
}
```

## Tratamento de Erros

Todas as operações podem lançar exceções do tipo `\Appmax\Structures\AppmaxAPIError` em caso de falha. Sempre envolva as chamadas em blocos try/catch para tratar os erros adequadamente.

```php
try {
    // Operação com o SDK
} catch (\Appmax\Structures\AppmaxAPIError $e) {
    echo "Código do erro: " . $e->getCode();
    echo "Mensagem: " . $e->getMessage();
    
    // Dados adicionais do erro, se disponíveis
    $dadosErro = $e->getErrorData();
}
```
