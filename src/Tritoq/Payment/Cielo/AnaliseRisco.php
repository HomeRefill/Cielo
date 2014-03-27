<?php
/**
 * Created by PhpStorm.
 * User: arturmagalhaes
 * Date: 21/03/14
 * Time: 18:01
 */

namespace Tritoq\Payment\Cielo;

use Tritoq\Payment\Cielo\AnaliseRisco\ClienteAnaliseRiscoInterface;
use Tritoq\Payment\Cielo\AnaliseRisco\PedidoAnaliseRisco;
use Tritoq\Payment\Exception\InvalidArgumentException;

class AnaliseRisco
{

    const ACAO_DESFAZER = 'desfazer';

    const ACAO_MANUAL_POSTERIOR = 'amp';

    const ACAO_CAPTURAR = 'capturar';

    /**
     * @var ClienteAnaliseRiscoInterface
     */
    private $cliente;

    /**
     * @var PedidoAnaliseRisco
     */
    private $pedido;


    // configurações da análise de risco

    /**
     * @var string
     */
    private $altoRisco;

    /**
     * @var string
     */
    private $medioRisco;

    /**
     * @var string
     */
    private $baixoRisco;

    /**
     * @var string
     */
    private $erroDados;

    /**
     * @var string
     */
    private $erroIndisponibilidade;
    /**
     * @var string
     */
    private $afsServiceRun;

    /**
     * @var array
     */
    private $tagsAdicionais;

    /**
     * @var array
     */
    private $tagsOpcionais;

    /**
     * @var string
     */
    private $deviceFingerPrintID;

    /**
     * @param null $options
     * @throws \Tritoq\Payment\Exception\InvalidArgumentException
     */
    public function __construct($options = null)
    {
        if (isset($options)) {
            foreach ($options as $key => $item) {
                if (isset($this->$key)) {
                    $method = 'set' . ucfirst($key);
                    $this->$method($item);
                } else {
                    throw new InvalidArgumentException('A opção ' . $key . ' não existe na classe');
                }
            }
        }

        $this->prencherTagsElementosVaziosRequeridos();
        $this->preencherOpcionaisElementosVaziosRequeridos();
    }

    /**
     *
     */
    private function prencherTagsElementosVaziosRequeridos()
    {
        $dataProvider = array();

        foreach ($dataProvider as $item) {
            $this->addTagAdicional($item, 'NULL');
        }
    }

    private function preencherOpcionaisElementosVaziosRequeridos()
    {
        $dataProvider = array(
            'merchantDefinedData_mddField_13',
            'merchantDefinedData_mddField_14',
            'merchantDefinedData_mddField_26',
        );

        foreach ($dataProvider as $item) {
            $this->addTagOpcional($item, 'NULL');
        }
    }


    private function addXmlOpcionais(\SimpleXMLElement $xml)
    {
        if (sizeof($this->tagsOpcionais) > 0) {
            foreach ($this->tagsOpcionais as $key => $item) {
                $xml->addChild($key, $item);
            }
        }

    }

    private function addXmlAdicionais(\SimpleXMLElement $xml)
    {
        if (sizeof($this->tagsAdicionais) > 0) {
            foreach ($this->tagsAdicionais as $key => $item) {
                $xml->addChild($key, $item);
            }
        }

    }

    /**
     * @param array $tagsOpcionais
     */
    public function setTagsOpcionais($tagsOpcionais)
    {
        $this->tagsOpcionais = $tagsOpcionais;
    }

    /**
     * @return array
     */
    public function getTagsOpcionais()
    {
        return $this->tagsOpcionais;
    }

    /**
     * @param $tag
     * @param $valor
     * @return $this
     */
    public function addTagOpcional($tag, $valor)
    {
        $this->tagsOpcionais[$tag] = $valor;
        return $this;
    }


    /**
     * @param $tag
     * @param $valor
     * @return $this
     */
    public function addTagAdicional($tag, $valor)
    {
        $this->tagsAdicionais[$tag] = $valor;
        return $this;
    }

    /**
     * @return array
     */
    public function getTagsAdicionais()
    {
        return $this->tagsAdicionais;
    }

    /**
     * @param string $altoRisco
     * @throws \Tritoq\Payment\Exception\InvalidArgumentException
     * @return $this
     */
    public function setAltoRisco($altoRisco)
    {
        switch ($altoRisco) {
            case self::ACAO_DESFAZER:
            case self::ACAO_MANUAL_POSTERIOR:
                $this->altoRisco = $altoRisco;
                return $this;
            default:
                throw new InvalidArgumentException('Opção para Alto Risco inválida');

        }

    }

    /**
     * @return string
     */
    public function getAltoRisco()
    {
        return $this->altoRisco;
    }

    /**
     * @param string $baixoRisco
     * @throws \Tritoq\Payment\Exception\InvalidArgumentException
     * @return $this
     */
    public function setBaixoRisco($baixoRisco)
    {
        switch ($baixoRisco) {
            case self::ACAO_CAPTURAR:
            case self::ACAO_MANUAL_POSTERIOR:
                $this->baixoRisco = $baixoRisco;
                return $this;
            default:
                throw new InvalidArgumentException('Opção para Baixo Risco inválida');
        }

    }

    /**
     * @return string
     */
    public function getBaixoRisco()
    {
        return $this->baixoRisco;
    }

    /**
     * @param string $medioRisco
     * @throws \Tritoq\Payment\Exception\InvalidArgumentException
     * @return $this
     */
    public function setMedioRisco($medioRisco)
    {
        switch ($medioRisco) {
            case self::ACAO_DESFAZER:
            case self::ACAO_CAPTURAR:
            case self::ACAO_MANUAL_POSTERIOR:
                $this->medioRisco = $medioRisco;
                return $this;
            default:
                throw new InvalidArgumentException('Opção para Médio Risco inválida');

        }

    }

    /**
     * @return string
     */
    public function getMedioRisco()
    {
        return $this->medioRisco;
    }

    /**
     * @param string $erroDados
     * @throws \Tritoq\Payment\Exception\InvalidArgumentException
     * @return $this
     */
    public function setErroDados($erroDados)
    {
        switch ($erroDados) {
            case self::ACAO_DESFAZER:
            case self::ACAO_CAPTURAR:
            case self::ACAO_MANUAL_POSTERIOR:
                $this->erroDados = $erroDados;
                return $this;
            default:
                throw new InvalidArgumentException('Opção para Erro Dados inválida');

        }
    }

    /**
     * @return string
     */
    public function getErroDados()
    {
        return $this->erroDados;
    }

    /**
     * @param string $erroIndisponibilidade
     * @throws \Tritoq\Payment\Exception\InvalidArgumentException
     * @return $this
     */
    public function setErroIndisponibilidade($erroIndisponibilidade)
    {
        switch ($erroIndisponibilidade) {
            case self::ACAO_DESFAZER:
            case self::ACAO_CAPTURAR:
            case self::ACAO_MANUAL_POSTERIOR:
                $this->erroIndisponibilidade = $erroIndisponibilidade;
                return $this;
            default:
                throw new InvalidArgumentException('Opção para Médio Risco inválida');

        }
    }

    /**
     * @return string
     */
    public function getErroIndisponibilidade()
    {
        return $this->erroIndisponibilidade;
    }

    /**
     * @param string $afsServiceRun
     * @throws \Tritoq\Payment\Exception\InvalidArgumentException
     * @return $this
     */
    public function setAfsServiceRun($afsServiceRun)
    {
        switch ($afsServiceRun) {
            case 'true':
            case 'false':
            case true:
            case false:
                $this->afsServiceRun = $afsServiceRun;
                return $this;
            default:
                throw new InvalidArgumentException('Opção para AfsServiceRun inválida');
        }

    }

    /**
     * @return string
     */
    public function getAfsServiceRun()
    {
        return $this->afsServiceRun;
    }

    /**
     * @param \Tritoq\Payment\Cielo\ClienteAnaliseRiscoInterface $cliente
     * @return $this
     */
    public function setCliente($cliente)
    {
        $this->cliente = $cliente;
        return $this;
    }

    /**
     * @return \Tritoq\Payment\Cielo\ClienteAnaliseRiscoInterface
     */
    public function getCliente()
    {
        return $this->cliente;
    }

    /**
     * @param \Tritoq\Payment\Cielo\PedidoAnaliseRisco $pedido
     * @return $this
     */
    public function setPedido($pedido)
    {
        $this->pedido = $pedido;

        return $this;
    }

    /**
     * @return \Tritoq\Payment\Cielo\PedidoAnaliseRisco
     */
    public function getPedido()
    {
        return $this->pedido;
    }

    /**
     * @param string $deviceFingerPrintID
     * @return $this
     */
    public function setDeviceFingerPrintID($deviceFingerPrintID)
    {
        $this->deviceFingerPrintID = $deviceFingerPrintID;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeviceFingerPrintID()
    {
        return $this->deviceFingerPrintID;
    }


    /**
     * @param \SimpleXMLElement $analise
     * @return SimpleXMLElement
     */
    public function criarXml($analise = null)
    {
        if (!isset($analise)) {
            $root = new \SimpleXMLElement('<root></root>');
            $analise = $root->addChild('analise-fraude');
        }

        // configuracao

        $configuracao = $analise->addChild('configuracao');
        $configuracao->addChild('analisar-fraude', 'true');
        $configuracao->addChild('alto-risco', $this->altoRisco);
        $configuracao->addChild('medio-risco', $this->medioRisco);
        $configuracao->addChild('baixo-risco', $this->baixoRisco);
        $configuracao->addChild('erro-dados', $this->erroDados);
        $configuracao->addChild('erro-indisponibilidade', $this->erroIndisponibilidade);
        $analise->addChild('afsService_run', $this->getAfsServiceRun() ? 'true' : 'false');

        /*
         * Dados do Pedido
         */
        $analise->addChild('merchantReferenceCode', $this->pedido->getId());

        /*
         * Endereço de cobrança
         */
        $analise->addChild('billTo_street1', $this->pedido->getEndereco());
        $analise->addChild('billTo_street2', $this->pedido->getComplemento());
        $analise->addChild('billTo_city', $this->pedido->getCidade());
        $analise->addChild('billTo_state', $this->cliente->getEstado());
        $analise->addChild('billTo_country', $this->pedido->getPais());
        $analise->addChild('billTo_postalCode', $this->pedido->getCep());
        $analise->addChild('billTo_customerID', $this->cliente->getId());
        $analise->addChild('billTo_customerPassword', $this->cliente->getSenha());
        $analise->addChild('billTo_personalID', $this->cliente->getDocumento());
        $analise->addChild('billTo_email', $this->cliente->getEmail());
        $analise->addChild('billTo_firstName', $this->cliente->getNome());
        $analise->addChild('billTo_lastName', $this->cliente->getSobrenome());
        $analise->addChild('billTo_phoneNumber', $this->cliente->getTelefone());
        $analise->addChild('billTo_ipAddress', $this->cliente->getIp());

        $analise->addChild('shipto_street1', $this->cliente->getEndereco());
        $analise->addChild('shipto_street2', $this->cliente->getComplemento());
        $analise->addChild('shipto_city', $this->cliente->getCidade());
        $analise->addChild('shipto_state', $this->cliente->getEstado());
        $analise->addChild('shipto_country', $this->cliente->getPais());
        $analise->addChild('shipto_postalCode', $this->cliente->getCep());
        $analise->addChild('shipTo_phoneNumber', $this->cliente->getTelefone());

        $analise->addChild('deviceFingerprintID', 'null');

        $analise->addChild('decisionManager_travelData_completeRoute', 'NULL');
        $analise->addChild('decisionManager_travelData_departureDateTime', 'NULL');
        $analise->addChild('decisionManager_travelData_journeyType', 'NULL');
        $analise->addChild('decisionManager_travelData_leg_origin', '');
        $analise->addChild('decisionManager_travelData_leg_destination', '');

        $analise->addChild('purchaseTotals_currency', $this->pedido->getMoeda());
        $analise->addChild('purchaseTotals_grandTotalAmount', number_format($this->pedido->getPrecoTotal(), 2));
        $analise->addChild('item_unitPrice', number_format($this->pedido->getPrecoUnitario(), 2));

        $analise->addChild('item_passengerFirstName', 'NULL');
        $analise->addChild('item_passengerLastName', 'NULL');
        $analise->addChild('item_passengerEmail', 'NULL');
        $analise->addChild('item_passengerID', 'NULL');

        $this->addXmlAdicionais($analise);

        $mdd = $analise->addChild('mdd');

        $this->addXmlOpcionais($mdd);

        return $analise;
    }
} 