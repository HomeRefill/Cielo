Tritoq\Payment\Cielo
============================

Esta é um conjunto de classes, baseado na Library do Mrprompt para integração com o Webservice da Cielo.

Cartões Suportados:

`American Express`
`Aura`
`Diners`
`Discover`
`Elo`
`JCB`
`Master Card`
`Visa`

Há suporte a integração com a Análise de Risco e AVS


**Para que as funcionalidades de análise de risco funcionem, o serviço precisa estar ativo junto a Cielo.**

## Instalação

**Via Composer**

`composer.json`

```json

{
   "require" : {
        "tritoq/cielo": "dev-master"
   }

}

```

```bash

php composer.phar update

```

## Uso


### Setup

Exemplo de configuração de informações

```php

    use Tritoq\Payment\Cielo\AnaliseRisco;
    use Tritoq\Payment\Cielo\Cartao;
    use Tritoq\Payment\Cielo\CieloService;
    use Tritoq\Payment\Cielo\Loja;
    use Tritoq\Payment\Cielo\Pedido;
    use Tritoq\Payment\Cielo\Portador;
    use Tritoq\Payment\Cielo\Transacao;
    use Tritoq\Payment\Cielo\AnaliseRisco\Modelo\ClienteAnaliseRiscoTest;
    use Tritoq\Payment\Cielo\AnaliseRisco\PedidoAnaliseRisco;

    $portador = new Portador();
    $portador
        ->setBairro('Meu Bairro')
        ->setCep('89900000')
        ->setComplemento('Sala 123')
        ->setEndereco('Rua Fulano de Tal');

    $loja = new Loja();
    $loja
        ->setNomeLoja('Nome da Loja')
        ->setAmbiente(Loja::AMBIENTE_TESTE)
        ->setUrlRetorno('http://google.com.br')
        ->setChave(Loja::LOJA_CHAVE_AMBIENTE_TESTE)
        ->setNumeroLoja(Loja::LOJA_NUMERO_AMBIENTE_TESTE);

    $cartao = new Cartao();
    $cartao
        ->setNumero(Cartao::TESTE_CARTAO_NUMERO)
        ->setCodigoSegurancaCartao(Cartao::TESTE_CARTAO_CODIGO_SEGURANCA)
        ->setBandeira(Cartao::BANDEIRA_VISA)
        ->setNomePortador('Portador 1')
        ->setValidade(Cartao::TESTE_CARTAO_VALIDADE);

    $transacao = new Transacao();
    $transacao
        ->setAutorizar(Transacao::AUTORIZAR_SEM_AUTENTICACAO)
        ->setCapturar(Transacao::CAPTURA_NAO)
        ->setParcelas(1)
        ->setProduto(Transacao::PRODUTO_CREDITO_AVISTA);

    $pedido = new Pedido();
    $pedido
        ->setDataHora(new \DateTime('2014-02-02 23:32:12'))
        ->setDescricao('Descrição do Pedido')
        ->setIdioma(Pedido::IDIOMA_PORTUGUES)
        ->setNumero(9024)
        ->setValor(1200);


    $pedidoAnalise = new PedidoAnaliseRisco();
    $pedidoAnalise
        ->setEstado('SC')
        ->setCep('89802140')
        ->setCidade('Chapeco')
        ->setComplemento('Sala 1008')
        ->setEndereco('Rua Marechal Deodoro, 400')
        ->setId('123345')
        ->setPais('BR')
        ->setPrecoTotal(400.00)
        ->setPrecoUnitario(390.00);

    /// Esta é uma classe criada de exemplo, implementando a interface Tritoq\Payment\Cielo\AnaliseRisco\ClienteAnaliseRiscoInterface

    $cliente = new ClienteAnaliseRiscoTest();
    $cliente->nome = 'Artur';
    $cliente->sobrenome = 'Magahalhaes';
    $cliente->endereco = 'Rua Marechal Deodoro, 400';
    $cliente->complemento = 'Sala 1008';
    $cliente->cep = '89802140';
    $cliente->documento = '123456789123';
    $cliente->email = 'artur@tritoq.com';
    $cliente->estado = 'SC';
    $cliente->cidade = 'Chapeco';
    $cliente->id = '9024';
    $cliente->ip = '192.168.1.254';
    $cliente->pais = 'BR';
    $cliente->senha = '12345';
    $cliente->telefone = '49912341234';


    /*
    *
    * Usando a Análise de Risco
    *
    */

    // Para qualquer ação será revista com ação manual posterior, caso seja de baixo risco, a transação será capturada automaticamente

    $this->analise = new AnaliseRisco();
    $this->analise
        ->setCliente($cliente)
        ->setPedido($pedidoAnalise)
        ->setAfsServiceRun(true)
        ->setAltoRisco(AnaliseRisco::ACAO_MANUAL_POSTERIOR)
        ->setMedioRisco(AnaliseRisco::ACAO_MANUAL_POSTERIOR)
        ->setBaixoRisco(AnaliseRisco::ACAO_CAPTURAR)
        ->setErroDados(AnaliseRisco::ACAO_MANUAL_POSTERIOR)
        ->setErroIndisponibilidade(AnaliseRisco::ACAO_MANUAL_POSTERIOR)
        ->setDeviceFingerPrintID(md5('valor'));

    $service = new CieloService(array(
        'portador' => $portador,
        'loja' => $loja,
        'cartao' => $cartao,
        'transacao' => $transacao,
        'pedido' => $pedido,
        'analise' => $this->analise
    ));

    // Desabilitando a analise de risco
    $service->setHabilitarAnaliseRisco(false);

```

### Opção de Análise de Risco

Caso opte por não utilizar o serviço de análise de risco é só remover o `$cliente`, `$analise`, `$pedidoAnalise`

E o serviço ficará assim

```php

    $service = new CieloService(array(
        'portador' => $portador,
        'loja' => $loja,
        'cartao' => $cartao,
        'transacao' => $transacao,
        'pedido' => $pedido
    ));

```

### Transação


Após realizada as configurações acima para realizar uma transação

```php

    ...

    // Caso queira enviar o AVS - Verifição de Endereço / Ver manual para maiores informações

    $service->doTransacao(false, true);

    // Sem AVS

    $service->doTransacao(false, false);


    if($transacao->getStatus() === Transacao::STATUS_AUTORIZADA) {
        echo 'Transação Autorizada!';
    } else {
        echo 'Transação Não Autorizada, contate seu banco!';
    }

```


### Transação com Análise de Risco

Enviando a transação com análise de risco


```php

    $service->setHabilitarAnaliseRisco(true);
    $service->doTransacao(false,false);

    if($transacao->getStatus() === Transacao::STATUS_AUTORIZADA) {
        echo 'Transação Autorizada!';
    } else {
        echo 'Transação Não Autorizada, contate seu banco!';
    }

```


### Captura da Transação

A captura da transação é a efetivação da transação, é nela que você confirma a Cielo que é para proceder com a Transação e a loja poder receber o valor.

Preferencialmente deixamos a captura para fazer posteriormente, devido as fraudes, é interessante que a Loja faça a verificação da transação antes de captura-la.


```php

    $transacao->setTid('numero_da_transação_já_realizada');
    $service->doCaptura();

    if($transacao->getStatus() === Transacao::STATUS_CAPTURADA) {
        echo 'Transação capturada com sucesso!';
    } else {
        echo 'Não foi possível capturar, status da transação: ' . $transacao->getStatus();
    }


```

### Consulta da Transação

A consulta da transação é um procedimento muito importante, pois é nela que a loja tira uma fotografia da transação.

```php
    ...

    $transacao->setTid('numero_da_transacao');
    $service->doConsulta();

    // Pegando a requisição e XML

    $requisicoes = $transacao->getRequisicoes(Transacao::REQUISICAO_TIPO_CONSULTA);


    echo 'Status: '  . $transacao->getStatus() . '<br/>';

    if(isset($requisicoes[0])) {
        echo 'XML:' . $requisicoes->getXmlRetorno()->asXML();
    }


```

**Consulta Direta**

```php

    ...

    // Realizando a transação e a consulta
    $service
            ->doTransacao(false,true)
            ->doConsulta();


    // Pegando a requisição e XML

    $requisicoes = $transacao->getRequisicoes(Transacao::REQUISICAO_TIPO_CONSULTA);


    echo 'Status: '  . $transacao->getStatus() . '<br/>';

    if(isset($requisicoes[0])) {
        echo 'XML:' . $requisicoes->getXmlRetorno()->asXML();
    }

```

### Cancelamento

Cancelamento da transação

```php

    $transacao->setTid('numero_da_transacao');
    $service->doCancela();

    if($transacao->getStatus() == Transacao::STATUS_CANCELADO) {
        echo 'Transação Cancelada com Sucesso';
    } else {
        echo 'Erro no cancelamento, status: ' . $transacao->getStatus();
    }

```