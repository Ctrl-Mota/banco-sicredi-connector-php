<?php

namespace crtlmota\BancoSicrediConnector\Cobranca;

use crtlmota\BancoSicrediConnector\Serializable;
use crtlmota\BancoSicrediConnector\Utils;

class Boleto extends Serializable 
{
    use ValidationTrait;

    protected ?Beneficiario $beneficiario = null;
    protected ?string $codigoBeneficiario = null;
    protected ?string $dataVencimento = null;
    protected ?Pagador $pagador = null;
    protected string $especieDocumento = "DUPLICATA_MERCANTIL_INDICACAO";
    protected $tipoCobranca = "NORMAL";
    protected ?int $nossoNumero = null;
    protected ?string $seuNumero = null;

    protected float $valor = 0.0;

    protected array $mensagens = [];

    protected float $multa = 0.0;
    protected float $juros = 0.0;
 
    protected ?string $tipoJuros = "PERCENTUAL";

    protected $codigoBarras = null;
    protected $linhaDigitavel = null;

    protected int $validadeAposVencimento = 60;

    protected ?string $tipoDesconto = "VALOR";

    protected ?float $valorDesconto1 = null;
    protected ?string $dataDesconto1 = null;

    protected ?float $valorDesconto2 = null;
    protected ?string $dataDesconto2 = null;

    protected ?float $valorDesconto3 = null;
    protected ?string $dataDesconto3 = null;

    protected ?Split $splitBoleto = null;


    /**
     * @return ?Beneficiario
     */
    public function getBeneficiario()
    {
        return $this->beneficiario;
    }
    /**
     *
     * @param Beneficiario $beneficiario
     */
    public function setBeneficiario(Beneficiario $beneficiario)
    {
        $this->beneficiario = $beneficiario;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getCodigoBeneficiario(): ?string
    {
        return $this->codigoBeneficiario;
    }
    /**
     * 
     * @param string $codigoBeneficiario
     */
    public function setCodigoBeneficiario($codigoBeneficiario)
    {
        $this->codigoBeneficiario = $codigoBeneficiario;
        return $this;
    }

    /**
     *
     * @return ?Pagador
     */
    public function getPagador(): ?Pagador
    {
        return $this->pagador;
    }

    /**
     *
     * @param Pagador $pagador
     */
    public function setPagador(Pagador $pagador)
    {
        $this->pagador = $pagador;
        return $this;
    }

    /**
     * @return string
     */
    public function getEspecieDocumento()
    {
        return $this->especieDocumento;
    }

    /**
     * @param string $especieDocumento
     * @required
     * @options DUPLICATA_MERCANTIL_INDICACAO | DUPLICATA_RURAL | NOTA_PROMISSORIA | NOTA_PROMISSORIA_RURAL | NOTA_SEGUROS | RECIBO | LETRA_CAMBIO | NOTA_DEBITO | DUPLICATA_SERVICO_INDICACAO | OUTROS | BOLETO_PROPOSTA | CARTAO_CREDITO | BOLETO_DEPOSITO
     */
    public function setEspecieDocumento(string $especieDocumento)
    {
        $this->especieDocumento = $especieDocumento;
        return $this;
    }

    /**
     * @return string
     */
    public function getTipoCobranca()
    {
        return $this->tipoCobranca;
    }

    /**
     * Indica o tipo de cobrança do boleto, podendo ser: NORMAL (Tradicional) ou Híbrido (Com QRCODE PIX)
     * @param string $especieDocumento
     * @required
     * @options NORMAL | HIBRIDO
     */
    public function setTipoCobranca(string $tipoCobranca)
    {
        $this->tipoCobranca = $tipoCobranca;
        return $this;
    }


    /**
     * @return int
     */
    public function getNossoNumero(): ?int
    {
        return $this->nossoNumero;
    }
    /**
     * Caso o beneficiário não informe, o Sicredi gera automaticamente.
     * 
     * @param int $nossoNumero
     */
    public function setNossoNumero(int $nossoNumero)
    {
        $this->nossoNumero = $nossoNumero;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSeuNumero()
    {
        return $this->seuNumero;
    }

    /**
     * Número de controle interno do beneficiário que faz referência ao pagador.
     * @param string $seuNumero
     */
    public function setSeuNumero(string $seuNumero)
    {
        $this->seuNumero = $seuNumero;
        return $this;
    }


    /**
     * @return float
     */
    public function getValor()
    {
        return $this->valor;
    }
    /**
     * precision 14 e scale 2
     * @param float $valor
     * 
     */
    public function setValor(float $valor)
    {
        $this->valor = $valor;
        return $this;
    }
    /**
     * @return string format: Y-m-d
     */
    public function getDataVencimento()
    {
        return $this->dataVencimento;
    }

    /**
     * @param mixed $dataVencimento
     */
    public function setDataVencimento($dataVencimento)
    {
        $this->dataVencimento = $dataVencimento;
        return $this;
    }

    /**
     * @return int
     */
    public function getValidadeAposVencimento(): int
    {
        return $this->validadeAposVencimento;
    }

    /**
     * @param int $validadeAposVencimento
     */
    public function setValidadeAposVencimento(int $validadeAposVencimento)
    {
        $this->validadeAposVencimento = $validadeAposVencimento;
        return $this;
    }

    /**
     * @return string
     */
    public function getTipoDesconto(): ?string
    {
        return $this->tipoDesconto;
    }

    /**
     * Tipo de desconto podendo ser: VALOR ou PERCENTUAL
     * @param string $tipoDesconto
     * @required
     * @options VALOR | PERCENTUAL
     */
    public function setTipoDesconto(string $tipoDesconto)
    {
        $this->tipoDesconto = $tipoDesconto;
        return $this;
    }

    /**
     *
     * @return Mensagem[]
     */
    public function getMensagens(): array
    {
        return $this->mensagens;
    }

    /**
     *
     * @param string $mensagem
     */
    public function addMensagem(string $mensagem)
    {
        $this->mensagens[] = $mensagem;
        return $this;
    }

    /**
     * @return float
     */
    public function getValorDesconto1(): ?float
    {
        return $this->valorDesconto1;
    }

    public function getValorDesconto2(): ?float
    {
        return $this->valorDesconto2;
    }

    public function getValorDesconto3(): ?float
    {
        return $this->valorDesconto3;
    }
    public function getDataDesconto1(): ?string
    {
        return $this->dataDesconto1;
    }

    public function getDataDesconto2(): ?string
    {
        return $this->dataDesconto2;
    }

    public function getDataDesconto3(): ?string
    {
        return $this->dataDesconto3;
    }

    /**
     * Desconto 1 podendo ser: VALOR ou PERCENTUAL
     * @param string $data YYYY-MM-DD
     * @param string $valor precision 14 scale 2
     */
    public function setDesconto1(string $data, float $valor)
    {
        $this->dataDesconto1 = $data;
        $this->valorDesconto1 = $valor;
        return $this;
    }
    /**
     * Desconto 2 podendo ser: VALOR ou PERCENTUAL
     * @param string $data YYYY-MM-DD
     * @param string $valor precision 14 scale 2
     */
    public function setDesconto2(string $data, float $valor)
    {
        $this->dataDesconto2 = $data;
        $this->valorDesconto2 = $valor;
        return $this;
    }

    /**
     * Desconto 3 podendo ser: VALOR ou PERCENTUAL
     * @param string $data YYYY-MM-DD
     * @param string $valor precision 14 scale 2
     */
    public function setDesconto3(string $data, float $valor)
    {
        $this->dataDesconto3 = $data;
        $this->valorDesconto3 = $valor;
        return $this;
    }

    /**
     * @return float
     */
    public function getMulta(): float
    {
        return $this->multa;
    }

    /**
     * Percentual de multa a cobrar
     * @param float $multa
     */
    public function setMulta(float $multa)
    {
        $this->multa = $multa;
        return $this;
    }


    public function getJuros(): ?float
    {
        return $this->juros;
    }

    /**
     * Valor de juros a cobrar por dia
     * @param float $juros
     */
    public function setJuros(float $juros)
    {
        $this->juros = $juros;
        return $this;
    }

    /**
     * @return string
     */
    public function getTipoJuros(): ?string
    {
        return $this->tipoJuros;
    }
    /**
     * Tipo de Juros, podendo ser: VALOR ou PERCENTUAL
     * @param string $tipoJuros
     * @required
     * @options VALOR | PERCENTUAL
     */
    public function setTipoJuros(string $tipoJuros)
    {
        $this->tipoJuros = $tipoJuros;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodigoBarras(): ?string
    {
        return $this->codigoBarras;
    }

    /**
     * Código preenchido após a geração do boleto.
     * @param mixed $codigoBarras
     */
    public function setCodigoBarras($codigoBarras)
    {
        $this->codigoBarras = $codigoBarras;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getLinhaDigitavel(): ?string
    {
        return $this->linhaDigitavel;
    }

    /**
     * Preenchido após a geração do boleto.
     * @param mixed $linhaDigitavel
     */
    public function setLinhaDigitavel($linhaDigitavel)
    {
        $this->linhaDigitavel = $linhaDigitavel;
        return $this;
    }
    /**
     * @param Split $splitBoleto
     */
    public function setSplitBoleto(Split $splitBoleto)
    {
        $this->splitBoleto = $splitBoleto;
        return $this;
    }

    /**
     * @param Split $splitBoleto
     */
    public function getSplitBoleto(): ?Split
    {
        return $this->splitBoleto;
    }
    
}
