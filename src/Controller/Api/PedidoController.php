<?php

namespace App\Controller\Api;

use App\Entity\Pedido;
use App\Entity\PedidoProducto;
use App\Entity\Product;
use App\Dto\CreatePedidoDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PedidoController extends AbstractController
{
    //Metodo para crear un pedido
    #[Route('/api/pedidos', name: 'api_pedidos_create', methods: ['POST'])]
    public function createPedido(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            // Deserializar el JSON al DTO
            $createPedidoDTO = $serializer->deserialize(
                $request->getContent(),
                CreatePedidoDTO::class,
                'json'
            );

            // Crear el pedido
            $pedido = new Pedido();
            $pedido->setFechaCreacion(new \DateTime());
            $pedido->setEstado($createPedidoDTO->estado);

            foreach ($createPedidoDTO->productos as $item) {
                $producto = $entityManager->getRepository(Product::class)->find($item['id']);
                if (!$producto) {
                    return $this->json(['error' => 'Producto no encontrado'], 404);
                }

                // Crear la relación PedidoProducto
                $pedidoProducto = new PedidoProducto();
                $pedidoProducto->setProducto($producto);
                $pedidoProducto->setCantidad($item['cantidad']);
                $pedidoProducto->setPedido($pedido);

                $pedido->addPedidoProducto($pedidoProducto);
            }

            // Guardar el pedido
            $entityManager->persist($pedido);
            $entityManager->flush();

            return $this->json(['message' => 'Pedido creado exitosamente'], 201);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    //Metodo para listar todos los pedidos
    #[Route('/api/pedidos', name: 'api_pedidos_list', methods: ['GET'])]
    public function listPedidos(EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            // Obtener todos los pedidos
            $pedidos = $entityManager->getRepository(Pedido::class)->findAll();

            // Formatear la respuesta
            $responseData = [];
            foreach ($pedidos as $pedido) {
                $productos = [];
                foreach ($pedido->getPedidoProductos() as $pedidoProducto) {
                    $productos[] = [
                        'id' => $pedidoProducto->getProducto()->getId(),
                        'nombre' => $pedidoProducto->getProducto()->getNombre(),
                        'cantidad' => $pedidoProducto->getCantidad(),
                        'precio' => $pedidoProducto->getProducto()->getPrecio(),
                    ];
                }

                $responseData[] = [
                    'id' => $pedido->getId(),
                    'fecha_creacion' => $pedido->getFechaCreacion()->format('Y-m-d H:i:s'),
                    'estado' => $pedido->getEstado(),
                    'productos' => $productos,
                ];
            }

            return $this->json($responseData, 200);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Error al listar los pedidos: ' . $e->getMessage()
            ], 500);
        }
    }

}