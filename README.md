# desafioFastpay

# La siguiente aplicación formaparte del desafio propuesto por elequipo de desarrollo de software de fastpay

# ** Pasos necesarios para iniciar el proyecto. **
#
# 1. Clonar el repositorio en su ordenador local. (git clone https://github.com/fgutierrezg/desafioFastpay.git)
# 2. Descargar e instalar composer desde su web oficial https://getcomposer.org/download/
# 3. Acceder al directorio del proyecto (cd fastpay-app)
# 4. Ejecutar en linea de comandos "Composer Install" para obtener todas las dependencias
# 5. Renombrar archivo ".env.example" por ".env". (Las credenciales dentro de .env se dejaron a proposito para la demostración)
# 6. Asegurate de crear la base de datos que coincida con las configuraciones del archivo .env
# 6. Ejecutar "php artisan jwt:secret" para regenerar el JWT secret en Laravel
# 7. Ejecutar "php artisan l5-swagger:generate" para regenerar interfaz de swager
# 8. Ejecutar "php artisan migrate" para ejecutar migraciones de la base de datos interfaz de swager
# 9. Ejecutar "php artisan serve" para correr el servidor local"
# 10. Una vez corriendo el server, dirigirse a la url http://localhost/fastpay-app/public/api/documentation#/  para revisar documentación de Swagger
# Nota 1: Debe incluir el puerto que esté utilizando en la URL de ser necesario.
# Nota 2: La API requiere un token JWT Bearer para acceder a las rutas protegidas. Asegúrate de generar e incluir un token válido en la cabecera Authorization.


# *** Pasos para revisar y correr las pruebas unitarias y de integración. ***
# 1. Ejecutar comando: "php artisan test"

# *** Rendimiento ***
# Dentro del fichero en la ruta app\Performance pueden encontrarse tests y pruebas de performance, realizados con PostmanRunner

