<?php

namespace App\Dto;

class CreatePedidoDTO
{
    public string $estado;
    /** @var array<int, array{id: int, cantidad: int}> */
    public array $productos;
}