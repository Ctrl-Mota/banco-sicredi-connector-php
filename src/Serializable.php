<?php

namespace crtlmota\BancoSicrediConnector;

/**
 *
 *
 * é apenas uma serializável para JSON
 */
abstract class Serializable implements \JsonSerializable
{
    protected array $others = [];

    /**
     * Método para adicionar outros dados não previstos na classe
     * 
     * @param $key
     * @param $value
     * @return $this
     */
    public function addOthers($key, $value): self
    {
        $this->others[$key] = $value;
        return $this;
    }

    public function getOthers(): array
    {
        return $this->others;
    }

    /**
     * @return array
     */
    public function JsonSerialize(): array
    {
        $data = get_object_vars($this);
        
        unset($data['others']);
        return array_merge($data, $this->getOthers());
    }
}
