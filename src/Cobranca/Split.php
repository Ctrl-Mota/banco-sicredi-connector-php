<?php

namespace crtlmota\BancoSicrediConnector\Cobranca;

use crtlmota\BancoSicrediConnector\Serializable;

/**
 * Informações da distribuição de crédito por boleto.
 * @package crtlmota\BancoSicrediConnector\Cobranca
 * @rule Obrigatório caso seja um boleto com Distribuição de Crédito.
 */
class Split extends Serializable {
    protected ?string $repasseAutomaticoSplit = "SIM";
    protected ?string $tipoValorRateio = "PERCENTUAL";
    protected ?string $regraRateio = "VALOR_COBRADO";
    protected ?array $destinatarios = [];


 /**
     * @return string|null
     */
    public function getRepasseAutomaticoSplit(): ?string
    {
        return $this->repasseAutomaticoSplit;
    }

    /**
     * Define o repasse automático do Split.
     *
     * @param string|null $repasseAutomaticoSplit Repasse automático do Split, podendo ser "SIM" ou "NAO".
     * @return Split
     */
    public function setRepasseAutomaticoSplit(?string $repasseAutomaticoSplit): Split
    {
        $this->repasseAutomaticoSplit = $repasseAutomaticoSplit;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTipoValorRateio(): ?string
    {
        return $this->tipoValorRateio;
    }

    /**
     * Define o tipo de valor do rateio do Split.
     *
     * @param string|null $tipoValorRateio Tipo de valor do rateio do Split, podendo ser "PERCENTUAL" ou "VALOR".
     * @return Split
     */
    public function setTipoValorRateio(?string $tipoValorRateio): Split
    {
        $this->tipoValorRateio = $tipoValorRateio;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegraRateio(): ?string
    {
        return $this->regraRateio;
    }

    /**
     * Define a regra do rateio do Split.
     *
     * @param string|null $regraRateio Regra do rateio do Split, podendo ser "MENOR_VALOR", "VALOR_COBRADO" ou "VALOR_REGISTRO".
     * @return Split
     */
    public function setRegraRateio(?string $regraRateio): Split
    {
        $this->regraRateio = $regraRateio;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getDestinatarios(): ?array
    {
        return $this->destinatarios;
    }

    /**
     * Define os destinatários do Split.
     * Contas destinatárias do Split do boleto. Poderá ser informado de 1 a 30 contas.
     *
     * @param Destinatario[]|null $destinatarios Array de objetos Destinatario.
     * @return Split
     */
    public function setDestinatarios(Destinatario $destinatario): Split
    {
        
        $this->destinatarios[] = $destinatario;
        return $this;
    }
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
