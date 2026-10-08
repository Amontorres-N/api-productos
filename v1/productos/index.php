<?php

header("Content-Type: application/json");

require_once "../../config/database.php";

$method = $_SERVER['REQUEST_METHOD'];

$id = isset($_GET['id']) && $_GET['id'] != ''
    ? intval($_GET['id'])
    : null;

try {

    switch($method)
    {

        /* =======================================
           GET
        ======================================= */

        case 'GET':

            if($id)
            {

                $stmt = $pdo->prepare("
                    SELECT *
                    FROM producto
                    WHERE producto_id = ?
                ");

                $stmt->execute([$id]);

                $producto = $stmt->fetch(PDO::FETCH_ASSOC);

                if(!$producto)
                {
                    http_response_code(404);

                    echo json_encode([
                        "success"=>false,
                        "message"=>"Producto no encontrado"
                    ]);

                    exit;
                }

                echo json_encode($producto);

            }
            else
            {

                $stmt = $pdo->query("
                    SELECT *
                    FROM producto
                    ORDER BY producto_id
                ");

                echo json_encode(
                    $stmt->fetchAll(PDO::FETCH_ASSOC)
                );

            }

            break;

        /* =======================================
           POST
        ======================================= */

        case 'POST':

            $data =
                json_decode(
                    file_get_contents("php://input"),
                    true
                );

            if(
                !isset($data['categoria_id']) ||
                !isset($data['nombre']) ||
                !isset($data['precio']) ||
                !isset($data['stock'])
            )
            {
                http_response_code(400);

                echo json_encode([
                    "success"=>false,
                    "message"=>"Datos incompletos"
                ]);

                exit;
            }

            $stmt = $pdo->prepare("
                INSERT INTO producto
                (
                    categoria_id,
                    nombre,
                    precio,
                    stock
                )
                VALUES
                (
                    ?,?,?,?
                )
            ");

            $stmt->execute([
                $data['categoria_id'],
                $data['nombre'],
                $data['precio'],
                $data['stock']
            ]);

            http_response_code(201);

            echo json_encode([
                "success"=>true,
                "message"=>"Producto creado",
                "id"=>$pdo->lastInsertId()
            ]);

            break;

        /* =======================================
           PUT
        ======================================= */

        case 'PUT':

            if(!$id)
            {
                http_response_code(400);

                echo json_encode([
                    "success"=>false,
                    "message"=>"ID requerido"
                ]);

                exit;
            }

            $data =
                json_decode(
                    file_get_contents("php://input"),
                    true
                );

            $stmt = $pdo->prepare("
                UPDATE producto
                SET
                    categoria_id=?,
                    nombre=?,
                    precio=?,
                    stock=?
                WHERE producto_id=?
            ");

            $stmt->execute([
                $data['categoria_id'],
                $data['nombre'],
                $data['precio'],
                $data['stock'],
                $id
            ]);

            echo json_encode([
                "success"=>true,
                "message"=>"Producto actualizado"
            ]);

            break;

        /* =======================================
           DELETE
        ======================================= */

        case 'DELETE':

            if(!$id)
            {
                http_response_code(400);

                echo json_encode([
                    "success"=>false,
                    "message"=>"ID requerido"
                ]);

                exit;
            }

            $stmt = $pdo->prepare("
                DELETE FROM producto
                WHERE producto_id=?
            ");

            $stmt->execute([$id]);

            echo json_encode([
                "success"=>true,
                "message"=>"Producto eliminado"
            ]);

            break;

        default:

            http_response_code(405);

            echo json_encode([
                "success"=>false,
                "message"=>"Método no permitido"
            ]);

    }

}
catch(Exception $e)
{

    http_response_code(500);

    echo json_encode([
        "success"=>false,
        "message"=>$e->getMessage()
    ]);

}
