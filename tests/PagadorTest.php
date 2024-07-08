<?php
namespace crtlmota\BancoSicrediConnector\Tests;

use PHPUnit\Framework\TestCase;
use crtlmota\BancoSicrediConnector\Cobranca\Pagador;

final class PagadorTest extends TestCase
{
    public function testPagador() {
        $pagador = new Pagador();
        $this->assertInstanceOf(Pagador::class, $pagador);
    }
}
