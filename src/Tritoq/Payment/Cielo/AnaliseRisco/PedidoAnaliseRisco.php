<?php
/**
 * Created by PhpStorm.
 * User: arturmagalhaes
 * Date: 25/03/14
 * Time: 16:41
 */

namespace Tritoq\Payment\Cielo\AnaliseRisco;


use Tritoq\Payment\Exception\InvalidArgumentException;

class PedidoAnaliseRisco
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $moeda = 'BRL';

    /**
     * @var double
     */
    protected $precoTotal;

    /**
     * @var double
     */
    protected $precoUnitario;

    /**
     * @var string
     */
    protected $endereco;

    /**
     * @var string
     */
    protected $complemento;

    /**
     * @var string
     */
    protected $cidade;

    /**
     * @var string
     */
    protected $estado;

    /**
     * @var string
     */
    protected $cep;

    /**
     * @var string
     */
    protected $pais;

    /**
     * @param int $id
     * @return $this
     * @throws \Tritoq\Payment\Exception\InvalidArgumentException
     */
    public function setId($id)
    {
        if (strlen($id) > 50) {
            throw new InvalidArgumentException('Id do pedido maior que 50 caracteres');
        }

        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $moeda
     * @throws \Tritoq\Payment\Exception\InvalidArgumentException
     * @return $this
     */
    public function setMoeda($moeda)
    {
        if (strlen($moeda) != 3) {
            throw new InvalidArgumentException('Tipo de moeda ' . $moeda . ' inválida');
        }

        $this->moeda = strtoupper($moeda);

        return $this;
    }

    /**
     * @return string
     */
    public function getMoeda()
    {
        return $this->moeda;
    }

    /**
     * @param float $precoTotal
     * @return $this
     */
    public function setPrecoTotal($precoTotal)
    {
        $this->precoTotal = (double)$precoTotal;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrecoTotal()
    {
        return $this->precoTotal;
    }

    /**
     * @param float $precoUnitario
     * @return $this
     */
    public function setPrecoUnitario($precoUnitario)
    {
        $this->precoUnitario = (double)$precoUnitario;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrecoUnitario()
    {
        return $this->precoUnitario;
    }

    /**
     * @param string $cep
     * @throws \Tritoq\Payment\Exception\InvalidArgumentException
     * @return $this
     */
    public function setCep($cep)
    {
        if (preg_match('/([[:alpha:]]|[[:punct:]]|[[:space:]])/', $cep)) {
            throw new InvalidArgumentException('Valor de CEP inválido');
        }
        $this->cep = $cep;
        return $this;
    }

    /**
     * @return string
     */
    public function getCep()
    {
        return $this->cep;
    }

    /**
     * @param string $cidade
     * @return $this
     */
    public function setCidade($cidade)
    {
        $this->cidade = $cidade;
        return $this;
    }

    /**
     * @return string
     */
    public function getCidade()
    {
        return $this->cidade;
    }

    /**
     * @param string $complemento
     * @return $this
     */
    public function setComplemento($complemento)
    {
        $this->complemento = $complemento;
        return $this;
    }

    /**
     * @return string
     */
    public function getComplemento()
    {
        return $this->complemento;
    }

    /**
     * @param string $endereco
     * @return $this
     */
    public function setEndereco($endereco)
    {
        $this->endereco = $endereco;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndereco()
    {
        return $this->endereco;
    }

    /**
     * @param string $estado
     * @throws \Tritoq\Payment\Exception\InvalidArgumentException
     * @return $this
     */
    public function setEstado($estado)
    {
        if (strlen($estado) < 2) {
            throw new InvalidArgumentException('Sigla de estado inválido');
        }
        $this->estado = $estado;
        return $this;
    }

    /**
     * @return string
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * @param string $pais
     * @throws \Tritoq\Payment\Exception\InvalidArgumentException
     * @return $this
     */
    public function setPais($pais)
    {
        if (strlen($pais) != 2) {
            throw new InvalidArgumentException('Sigla de país inválido');
        }
        $this->pais = $pais;
        return $this;
    }

    /**
     * @return string
     */
    public function getPais()
    {
        return $this->pais;
    }


}