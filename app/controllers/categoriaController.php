<?php

//obtiene el modelo de categoria que contiene la logica para manejar las categorias
include_once '../models/categoria.php';

// filtrar y sanitizar los datos recibidos por post
$id = filter_input(INPUT_POST, 'id_categoria', FILTER_SANITIZE_NUMBER_INT);
$nombre_categoria = filter_input(INPUT_POST, 'nombre_categoria', FILTER_SANITIZE_STRING);
$descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
$accion = filter_input(INPUT_POST, 'accion', FILTER_SANITIZE_STRING); //accion a relizar

// se ejecuta la logia de acuerdo a la accion 
if ($accion) {
    $mensaje = ''; //variable para almacenar mensajes de estado

    //maneja las diferentes acciones
    switch ($accion) {
        case 'agregar':
            //si el nombre de la categoria no esta vacio, la categoria se agrega
            if (!empty($nombre_categoria)) {
                Categoria::agregarCategoria($nombre_categoria, $descripcion);
                $mensaje = 'Categoría registrada exitosamente.';
            } else {
                $mensaje = 'El nombre de la categoría no puede estar vacío.';
            }
            break;

        case 'editar':
            if (!empty($id) && !empty($nombre_categoria)) {
                Categoria::editarCategoria($id, $nombre_categoria, $descripcion);
                $mensaje = 'Categoría editada exitosamente.';
            } else {
                $mensaje = 'ID o nombre de la categoría no puede estar vacío.';
            }
            break;

        case 'borrar':
            if (!empty($id)) {
                // Llama al método para borrar una entrada y verifica si tiene salidas asociadas
                $resultado = Categoria::borrarCategoria($id);

                if ($resultado === true) {
                    // Mensaje de alerta cuando la entrada tiene salidas asociadas
                    $mensaje = 'Categoría Eliminado existosamente.';
                } else {

                    $mensaje = 'No se puede eliminar la categoría porque esta asociado a un inventario.';
                }
            } else {
                // Mensaje cuando no se proporciona un ID
                $mensaje = 'ID de la categoría no puede estar vacío.';
            }
            break;
    }

    // redirigir con mensaje de alerta despues de la accion
    if ($mensaje) {
        echo "<script>
            alert('$mensaje');
            window.location.href='../views/vista_categorias.php';
        </script>";
        exit();
    }
}

//necesario para la paginacion, se estable el limite y offset para el listado de categorias
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;
$offset = isset($_GET['page']) ? (int) ($_GET['page'] - 1) * $limit : 0;

//obtener el listado de categorias y el total
$listaCategorias = Categoria::listarCategorias($limit, $offset); //listar categorias con limite y offset
$totalCategorias = Categoria::contarCategorias(); //contar el total de categorias
$totalPaginas = ceil($totalCategorias / $limit); // calular el total de paginas en vista_categorias
