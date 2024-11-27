
# Prueba Técnica - SIROKO

¡Bienvenidos a esta breve demostración de mis habilidades técnicas! Este proyecto ha sido desarrollado utilizando **Symfony 7** como parte de una prueba técnica para acceder a la empresa **SIROKO**. Aunque el diseño de la interfaz no será evaluado, he preparado un UI agradable y funcional para acompañar el desarrollo.

---

## 🚀 Funcionalidades

- **Login simple**: Acceso a las funcionalidades principales del proyecto.
- **CRUD de Productos**:
    - Visualización, creación, edición y eliminación de productos.
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

6. **Compilar TailwindCSS**
   ```bash
   php bin/console tailwind:build
   ```

---

## 🌟 Notas

- El enfoque principal de este proyecto ha sido garantizar que las funcionalidades esenciales sean **intuitivas** y **fáciles de usar**.
- Se ha implementado un diseño limpio y funcional para mejorar la experiencia de usuario durante las pruebas.

---

Espero que disfrutes explorando esta prueba técnica tanto como yo disfruté desarrollándola.  
¡Gracias por la oportunidad! 😊
