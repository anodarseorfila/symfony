<?php

namespace App\Dto;

class UpdateProductDTO
{
    public ?string $nombre = null;
    public ?string $descripcion = null;
    public ?float $precio = null;
    public ?int $stock = null;
    public ?string $fecha_creacion = null;
}