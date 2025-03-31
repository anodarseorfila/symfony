<?php
// src/Controller/Api/ProductController.php
namespace App\Controller\Api;

use App\Dto\CreateProductDTO;
use App\Dto\UpdateProductDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface; //importo el validador
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductRepository;


class ProductoController extends AbstractController
{
    //metodo para listar todos los productos
    #[Route('/api/products', name: 'api_products', methods: ['GET'])]
    public function getProducts(EntityManagerInterface $entityManager): JsonResponse
    {

        ///busco los productos de mi tabla
        $products = $entityManager->getRepository(Product::class)->findAll();
    
        //Doy formato a la respuesta
        $responseData = [];
        foreach ($products as $product)
        {
            $responseData[] = [
                'id' => $product->getId(),
                'nombre' => $product->getNombre(),
                'descripcion' => $product->getDescripcion(),
                'precio' => $product->getPrecio(),
                'stock' => $product->getStock(),
                'fecha_creacion' => $product->getFechaCreacion(),
            ];
        }
 
        return $this->json($responseData);
    }

    //Metodo para obtener un producto dado su id
    #[Route('/api/products/{id}', name: 'api_product', methods: ['GET'])]
    public function getProduct(int $id, ProductRepository $productRepository): JsonResponse
    {
        $productData = $productRepository->findProductAsArray($id);

        if (!$productData) {
            return $this->json(['error' => 'Producto no encontrado'], 404);
        }
    
        return $this->json($productData);
    }

    //Metodo para crear un producto
    #[Route('/api/products', name: 'api_product_create', methods: ['POST'])]
    public function createProduct(
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ): JsonResponse {
        try {
            // Deserializar el JSON directamente al DTO
            $createProductDTO = $serializer->deserialize(
                $request->getContent(),
                CreateProductDTO::class,
                'json'
            );
            
            // Validar el DTO
            $errors = $validator->validate($createProductDTO);
            if (count($errors) > 0) {
                return $this->json($errors, 400);
            }
            
            // Mapear DTO a Entidad
            $product = new Product();
            $product->setNombre($createProductDTO->nombre);
            $product->setDescripcion($createProductDTO->descripcion);
            $product->setPrecio($createProductDTO->precio ?? 0);
            $product->setStock($createProductDTO->stock ?? 0);
            
            $fechaCreacion = $createProductDTO->fecha_creacion 
                ? new \DateTime($createProductDTO->fecha_creacion)
                : new \DateTime();
            $product->setFechaCreacion($fechaCreacion);
            
            $entityManager->persist($product);
            $entityManager->flush();
            
            return $this->json([
                'id' => $product->getId(),
                'nombre' => $product->getNombre(),
                'status' => 'Producto creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    //Metodo para actualizar un producto
    #[Route('/api/products/{id}', name: 'api_product_update', methods: ['PUT'])]
    public function updateProduct(
        int $id,
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ProductRepository $productRepository
    ): JsonResponse {
        try {
            // Buscar el producto por ID
            $product = $productRepository->find($id);

            if (!$product) {
                return $this->json(['error' => 'Producto no encontrado'], 404);
            }

            // Deserializar el JSON directamente al DTO
            $updateProductDTO = $serializer->deserialize(
                $request->getContent(),
                UpdateProductDTO::class, // DTO específico para actualización
                'json'
            );

            // Validar el DTO
            $errors = $validator->validate($updateProductDTO);
            if (count($errors) > 0) {
                return $this->json($errors, 400);
            }

            // Actualizar los campos del producto
            if (isset($updateProductDTO->nombre)) {
                $product->setNombre($updateProductDTO->nombre);
            }
            if (isset($updateProductDTO->descripcion)) {
                $product->setDescripcion($updateProductDTO->descripcion);
            }
            if (isset($updateProductDTO->precio)) {
                $product->setPrecio($updateProductDTO->precio);
            }
            if (isset($updateProductDTO->stock)) {
                $product->setStock($updateProductDTO->stock);
            }
            if (isset($updateProductDTO->fecha_creacion)) {
                $product->setFechaCreacion(new \DateTime($updateProductDTO->fecha_creacion));
            }

            // Guardar los cambios en la base de datos
            $entityManager->flush();

            return $this->json([
                'id' => $product->getId(),
                'nombre' => $product->getNombre(),
                'status' => 'Producto actualizado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    //Metodo para eliminar un produto
    #[Route('/api/products/{id}', name: 'api_product_delete', methods: ['DELETE'])]
    public function deleteProduct(
        int $id,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository
    ): JsonResponse {
        try {
            // Buscar el producto por ID
            $product = $productRepository->find($id);

            if (!$product) {
                return $this->json(['error' => 'Producto no encontrado'], 404);
            }

            // Eliminar el producto
            $entityManager->remove($product);
            $entityManager->flush();

            return $this->json([
                'status' => 'success',
                'message' => 'Producto eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}
