# Control_Asistencia_Empleados_Admin

Proyecto web desarrollado en **PHP** para la gestión y control de asistencia de empleados, con panel de administración.

---

## Requisitos Previos

Para ejecutar este proyecto en tu entorno local, necesitas contar con un servidor local que soporte **PHP** y MySQL. Recomendamos utilizar **XAMPP**.

- Descargar e instalar **XAMPP**.

---

## Instalación y Configuración

Sigue estos pasos para poner en marcha el proyecto:

1. **Clonar o descargar** este repositorio en la carpeta de tu servidor local (por ejemplo, `htdocs` en **XAMPP**).
2. Inicia los servicios de **Apache** y **MySQL** desde el panel de control de XAMPP.
3. Importa la base de datos:
- Abre tu navegador y accede a **phpMyAdmin** (`[http://localhost/phpmyadmin`](http://localhost/phpmyadmin`)).
- Crea una nueva base de datos con el nombre `control_asistencia` (o el correspondiente al proyecto).
- **Copia y pega lo que dice en el archivo sql** ubicado en `control_asistencia/sql/control_asistencia.sql` a la interfaz de MySQL (phpMyAdmin) de **XAMPP** para crear las tablas e insertar los datos iniciales.

4. Configura la conexión a la base de datos en el archivo `control_asistencia/config.php` si tus credenciales de MySQL son diferentes a las predeterminadas (usuario: `root`, sin contraseña).

---

## Uso

Una vez configurado, abre tu navegador web y dirígete a:
[http://localhost/control_asistencia/](http://localhost/control_asistencia/)
