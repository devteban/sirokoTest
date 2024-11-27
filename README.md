
# Prueba Técnica - SIROKO

¡Bienvenidos a esta breve demostración de mis habilidades técnicas! Este proyecto ha sido desarrollado utilizando **Symfony 7** como parte de una prueba técnica para acceder a la empresa **SIROKO**. Aunque el diseño de la interfaz no será evaluado, he preparado un UI agradable y funcional para acompañar el desarrollo.

---

## 🚀 Funcionalidades

- **Login simple**: Acceso a las funcionalidades principales del proyecto.
- **CRUD de Productos**:
    - Visualización, creación y eliminación de productos.
    - Carga inicial con varios productos predefinidos, con la posibilidad de añadir más a través del CRUD.
- **Carrito de compras**: Implementado de acuerdo con los requisitos establecidos en el enunciado.

---

## 🛠️ Despliegue del Proyecto en Local

Para ejecutar el proyecto localmente, sigue estos pasos:

1. **Construir la imagen de Docker**
   ```bash
   docker compose up --build
   ```

2. **Instalar las dependencias de Composer**
   ```bash
   composer install
   ```

3. **Instalar las dependencias de Node.js**
   ```bash
   npm install
   ```

4. **Instalar los assets del proyecto**
   ```bash
   php bin/console importmap:install
   ```

5. **Compilar los assets**
   ```bash
   php bin/console asset-map:compile
   ```

6. **Actualizar la base de datos**
   ```bash
   php bin/console doctrine:schema:update --force
   ```

6. **Buildear TailwindCSS**
   ```bash
   php bin/console tailwind:build
   ```

---

## 📋 Notas Adicionales

- **Encapsulación de funcionalidades del carrito**:  
  Todas las funcionalidades relacionadas con el carrito de compras están completamente encapsuladas dentro del controlador `ApiController`. Esto garantiza una separación clara de responsabilidades y facilita su mantenimiento y escalabilidad.

- **Uso de Tailwind CSS**:  
  Para proporcionar una experiencia visual más agradable, he utilizado la librería de componentes de **Tailwind CSS**. Aunque el diseño no será evaluado, creo que una interfaz bien estructurada mejora la presentación y la usabilidad del proyecto.

- **Docker🐳**:  
  Este proyecto utiliza una receta personalizada de Docker diseñada específicamente para las necesidades de un proyecto Symfony. La receta ha sido creada por mí, teniendo en cuenta las configuraciones óptimas para el desarrollo y la ejecución de la aplicación.

---

Cualquier duda o comentario, no dudéis en poneros en contacto conmigo, gracias por la oportunidad.