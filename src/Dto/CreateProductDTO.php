<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateProductDTO
{
    #[Assert\NotBlank(message: 'El nombre es obligatorio')]
    public ?string $nombre = null;
    
    public ?string $descripcion = null;
    
    #[Assert\Type(type: 'numeric', message: 'El precio debe ser un número')]
    #[Assert\PositiveOrZero(message: 'El precio no puede ser negativo')]
    public ?float $precio = null;
    
    #[Assert\Type(type: 'integer', message: 'El stock debe ser un número entero')]
    #[Assert\PositiveOrZero(message: 'El stock no puede ser negativo')]
    public ?int $stock = null;
    
    #[Assert\DateTime(message: 'La fecha debe tener un formato válido')]
    public ?string $fecha_creacion = null;
}