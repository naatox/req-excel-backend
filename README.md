# req-excel-backend

Backend en Laravel para recibir, validar y almacenar datos importados desde un archivo Excel, generalmente enviados desde un frontend Angular.

Este backend registra usuarios junto con sus horarios de atención en la base de datos y realiza validaciones estructurales sobre los datos recibidos.

## ⚙️ Requisitos

- PHP 8.1 o superior
- Composer
- Laravel 10+
- MySQL o compatible
- (Opcional) Laravel Sail o XAMPP para entorno local

## 🚀 Instalación

1. **Clona el repositorio**

```bash
git clone https://github.com/naatox/req-excel-backend.git
cd req-excel-backend
```

2. **Instalar dependencias**

```bash
composer install

```

3. **Copiar .env**

```bash
cp .env.example .env
```

4. **Generar clave de la aplicación**

```bash
php artisan key:generate
```

5. **Configura el.env**

```bash
DB_DATABASE=excel_db
DB_USERNAME=root
DB_PASSWORD=tu_contraseña
```

6. **Ejecutar Migraciones**

```bash
php artisan migrate
```

7. **Iniciar Servido**

```bash
php artisan serve
```



