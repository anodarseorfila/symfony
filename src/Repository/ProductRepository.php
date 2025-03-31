<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @return Product[] Returns an array of Product objects
     */

     public function getAllProducts(): array
     {
         return $this->createQueryBuilder('p')
             ->orderBy('p.id', 'ASC')
             ->getQuery()
             ->getResult();
     }

     //Metodo para buscar un producto y devolverlo como arreglo
     public function findProductAsArray(int $id): ?array
     {
        
        $product = $this->find($id);

        if(!$product) {
            return null;
        }

        return [
            'id' => $product->getId(),
            'nombre' => $product->getNombre(),
            'descripcion' => $product->getDescripcion(),
            'precio' => $product->getPrecio(),
            'stock' => $product->getStock(),
            'fecha_creacion' => $product->getFechaCreacion()
        ];
     }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
