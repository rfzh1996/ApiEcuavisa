## Instalacion de la Application

Requerimientos

PHP 7.4.29
Composer

Herramientas
VSCode
SlimFramework
PHP

Bajar dependencias de la aplicacion para poder ejecutar 

	composer install

Ejecutar el siguiente comando para ejecutar la App dentro del directorio

	composer start
	

EndPoint

DELETE http://localhost/v1/eliminar/{id} Elimina item por ID 
GET http://localhost/v1/obtener/{id} Obtiene Item por ID
PUT http://localhost/v1/editar/{id} Edita un Item por ID 
POST http://localhost/v1/crear Crea Item Parametros en Boddy (Titulo, Descripcion, Link)
GET http://localhost/v1/base Descarga Datos de URL de Ultimas Noticias y Muestra solo titulos
GET http://localhost/v1/stored Muestra datos desde Json Local y muestra Titulo y ID