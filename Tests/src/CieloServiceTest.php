<?php
namespace Tritoq\Payment\Tests;

use Tritoq\Payment\Cielo\AnaliseRisco;
use Tritoq\Payment\Cielo\Cartao;
use Tritoq\Payment\Cielo\CieloService;
use Tritoq\Payment\Cielo\Loja;
use Tritoq\Payment\Cielo\Pedido;
use Tritoq\Payment\Cielo\Portador;
use Tritoq\Payment\Cielo\Transacao;

class CieloServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CieloService
     */
    protected $gateway;

    /**
     * @var Transacao
     */
    protected $transacao;
    /**
     * @var AnaliseRisco
     */
    protected $analise;

    public function setUp()
    {
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


        $pedidoAnalise = new AnaliseRisco\PedidoAnaliseRisco();
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

        //
        $cliente = new AnaliseRisco\Modelo\ClienteAnaliseRiscoTest();
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

        $service->setHabilitarAnaliseRisco(false);

        if (!is_dir('xml')) {
            mkdir('xml', 0775);
        }

        $this->gateway = $service;
        $this->transacao = $transacao;
    }

    /**
     *
     */
    public function testDoTransacao()
    {
        $this->gateway->doTransacao(false, true);
        $this->assertEquals((string)Transacao::STATUS_AUTORIZADA, $this->transacao->getStatus());
        $reqs = $this->gateway->getTransacao()->getRequisicoes(Transacao::REQUISICAO_TIPO_TRANSACAO);
        $reqs[0]->getXmlRetorno()->asXML(OUTPUT . 'xml/transacao.xml');
    }

    public function testDoTransacaoAnaliseRisco()
    {
        $this->gateway->setHabilitarAnaliseRisco(true);
        $this->gateway->doTransacao(false, false);
        $reqs = $this->gateway->getTransacao()->getRequisicoes(Transacao::REQUISICAO_TIPO_TRANSACAO);
        $reqs[0]->getXmlRetorno()->asXML(OUTPUT . 'xml/transacao-com-analise.xml');
        $reqs[0]->getXmlRequisicao()->asXML(OUTPUT . 'xml/requisicao-transacao-com-analise.xml');
        $this->assertEquals((string)Transacao::STATUS_AUTORIZADA, $this->transacao->getStatus());
    }

    public function testDoCaptura()
    {
        $this->gateway->doTransacao(false, true);
        $this->gateway->doCaptura();
        $this->assertEquals((string)Transacao::STATUS_CAPTURADA, $this->transacao->getStatus());
        $reqs = $this->gateway->getTransacao()->getRequisicoes(Transacao::REQUISICAO_TIPO_CAPTURA);
        $reqs[0]->getXmlRetorno()->asXML(OUTPUT . 'xml/captura.xml');
    }

    public function testDoConsulta()
    {
        $this->gateway->doTransacao(false, true);
        $this->gateway->doConsulta();
        $this->assertEquals((string)Transacao::STATUS_AUTORIZADA, $this->transacao->getStatus());
        $reqs = $this->gateway->getTransacao()->getRequisicoes(Transacao::REQUISICAO_TIPO_CONSULTA);
        $reqs[0]->getXmlRetorno()->asXML(OUTPUT . 'xml/consulta.xml');
    }

    public function testDoCancela()
    {
        $this->gateway->doTransacao(false, true);
        $this->gateway->doCancela();
        $this->assertEquals((string)Transacao::STATUS_CANCELADA, $this->transacao->getStatus());
        $reqs = $this->gateway->getTransacao()->getRequisicoes(Transacao::REQUISICAO_TIPO_CANCELA);
        $reqs[0]->getXmlRetorno()->asXML(OUTPUT . 'xml/cancela.xml');
    }

    /**
     * @return array
     */
    public function cartoesDataProvider()
    {
        return array(
            array(
                'Portador Visa',
                Cartao::BANDEIRA_VISA,
                4012001037141112,
                '201805',
                '123'
            ),
            array(
                'Portador Mastercard',
                Cartao::BANDEIRA_MASTERCARD,
                5453010000066167,
                '201805',
                '123'
            ),
            array(
                'Portador Visa 2',
                Cartao::BANDEIRA_VISA,
                4012001038443335,
                '201805',
                '123'
            ),
            array(
                'Portador Mastercard 2',
                Cartao::BANDEIRA_MASTERCARD,
                5453010000066167,
                '201805',
                '123'
            ),
            array(
                'Portador Amex',
                Cartao::BANDEIRA_AMERICAN_EXPRESS,
                376449047333005,
                '201805',
                '1234'
            ),
            array(
                'Portador Elo',
                Cartao::BANDEIRA_ELO,
                6362970000457013,
                '201805',
                '123'
            ),
            array(
                'Portador Diners',
                Cartao::BANDEIRA_DINERS,
                36490102462661,
                '201805',
                '123'
            ),
            array(
                'Portador Discover',
                Cartao::BANDEIRA_DISCOVER,
                6011020000245045,
                '201805',
                '123'
            ),
            array(
                'Portador Jcb',
                Cartao::BANDEIRA_JCB,
                3566007770004971,
                '201805',
                '123'
            ),
            array(
                'Portador Aura',
                Cartao::BANDEIRA_AURA,
                5078601912345600019,
                '201805',
                '123'
            ),
        );
    }

    /**
     * @param $nomePortador
     * @param $bandeira
     * @param $numero
     * @param $validade
     * @param $codigoSeguranca
     * @dataProvider cartoesDataProvider
     */
    public function testCartoes($nomePortador, $bandeira, $numero, $validade, $codigoSeguranca)
    {
        $cartao = new Cartao();
        $cartao
            ->setNumero($numero)
            ->setCodigoSegurancaCartao($codigoSeguranca)
            ->setBandeira($bandeira)
            ->setNomePortador($nomePortador)
            ->setValidade($validade);

        $this->gateway
            ->setCartao($cartao)
            ->doTransacao(false, true);

        $this->assertEquals((string)Transacao::STATUS_AUTORIZADA, $this->transacao->getStatus());

        $reqs = $this->gateway->getTransacao()->getRequisicoes(Transacao::REQUISICAO_TIPO_TRANSACAO);
        $reqs[0]->getXmlRetorno()->asXML(OUTPUT . 'xml/' . $bandeira . '.xml');
    }
}
 