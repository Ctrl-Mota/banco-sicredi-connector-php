<?php
namespace crtlmota\BancoSicrediConnector\Tests;

use PHPUnit\Framework\TestCase;
use crtlmota\BancoSicrediConnector\Cobranca\Boleto;
use crtlmota\BancoSicrediConnector\Cobranca\Pagador;

final class BoletoTest extends TestCase
{
    public function testBoleto() {
        $boleto = new Boleto();
        $this->assertInstanceOf(Boleto::class, $boleto);
        
        $boleto->setPagador(new Pagador());        
    }

}
