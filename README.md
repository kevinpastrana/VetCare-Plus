# 🏥 VetCare Plus

<div align="center">

**Sistema de Gestión Veterinaria Profesional**

Una solución integral y robusta para la administración completa de clínicas y hospitales veterinarios

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Active-success?style=for-the-badge)]()

[Características](#-características) •
[Tecnologías](#-stack-tecnológico) •
[Instalación](#-instalación) •
[Estructura](#-arquitectura-del-sistema) •
[Capturas](#-capturas-de-pantalla) •
[Licencia](#-licencia)

</div>

---

## 📋 Descripción

**VetCare Plus** es un sistema de gestión veterinaria empresarial desarrollado con arquitectura MVC que centraliza y optimiza todos los procesos operativos de clínicas y hospitales veterinarios. Diseñado con enfoque en escalabilidad, mantenibilidad y seguridad.

### 🎯 Objetivos del Sistema

- Digitalizar completamente los procesos clínicos y administrativos
- Centralizar la información médica y financiera en una única plataforma
- Optimizar los flujos de trabajo del personal veterinario
- Generar reportes y analíticas para toma de decisiones estratégicas
- Garantizar la trazabilidad completa de historiales médicos

---

## ✨ Características

### 🏢 Gestión Administrativa

<table>
<tr>
<td width="50%">

#### 👥 Módulo de Dueños
- Registro completo de propietarios
- Historial de mascotas asociadas
- Información de contacto y facturación
- Búsqueda y filtros avanzados

</td>
<td width="50%">

#### 🐾 Módulo de Mascotas
- Perfiles detallados de pacientes
- Historial clínico completo
- Información de raza, edad y características
- Alertas de seguimiento

</td>
</tr>
<tr>
<td width="50%">

#### 👨‍⚕️ Módulo de Empleados
- Gestión de personal médico y administrativo
- Asignación de roles y permisos
- Control de horarios y turnos
- Registro de especialidades

</td>
<td width="50%">

#### 💼 Módulo de Cargos
- Definición de roles organizacionales
- Estructura jerárquica
- Permisos por cargo
- Gestión de responsabilidades

</td>
</tr>
</table>

### 🩺 Atención Médica

<table>
<tr>
<td width="50%">

#### 📋 Consultas Veterinarias
- Registro de consultas médicas
- Diagnósticos y observaciones
- Seguimiento de tratamientos
- Historial cronológico completo

</td>
<td width="50%">

#### 🔬 Procedimientos Clínicos
- Registro de cirugías y tratamientos
- Control de procedimientos realizados
- Documentación de resultados
- Protocolos de atención

</td>
</tr>
<tr>
<td width="50%">

#### 💉 Control de Vacunación
- Calendario de vacunas
- Registro de aplicaciones
- Alertas de refuerzos
- Certificados de vacunación

</td>
<td width="50%">

</td>
</tr>
</table>

### 💰 Facturación y Finanzas

- **Sistema de Facturación Completo**: generación automática de facturas
- **Control de Pagos**: gestión de estados (Pagado/Pendiente)
- **Registro de Cargos**: servicios, medicamentos y procedimientos
- **Historial Financiero**: trazabilidad completa de transacciones
- **Reportes Contables**: análisis de ingresos y estadísticas

### 📊 Reportes y Analítica

- Dashboard con métricas clave en tiempo real
- Reportes por módulo (consultas, ingresos, procedimientos)
- Estadísticas de pacientes y servicios
- Exportación de datos
- Gráficos y visualizaciones

### 🔐 Seguridad y Control de Acceso

- Sistema de autenticación robusto
- Gestión de sesiones seguras
- Control de acceso basado en roles (RBAC)
- Registro de auditoría en logs
- Protección contra inyección SQL

---

## 🛠️ Stack Tecnológico

### Backend
```
PHP 8.0+          | Lenguaje principal del servidor
MySQL 8.0+        | Sistema de gestión de base de datos
Apache 2.4+       | Servidor web
```

### Frontend
```
HTML5             | Estructura semántica
CSS3              | Estilos y diseño responsivo
JavaScript ES6+   | Interactividad del cliente
```

### Herramientas de Desarrollo
```
XAMPP             | Entorno de desarrollo local
Git               | Control de versiones
phpMyAdmin        | Administración de base de datos
```

### Arquitectura
```
MVC Pattern       | Separación de responsabilidades
Modular Design    | Componentes reutilizables
Session Management| Control de estado de usuario
```

---

## 📐 Arquitectura del Sistema

El proyecto implementa una arquitectura modular y escalable organizada en capas:

```
┌─────────────────────────────────────────────────────┐
│                  Capa de Presentación               │
│                   (Views + Assets)                  │
├─────────────────────────────────────────────────────┤
│                  Capa de Aplicación                 │
│              (Controllers + Includes)               │
├─────────────────────────────────────────────────────┤
│                   Capa de Negocio                   │
│                  (Business Logic)                   │
├─────────────────────────────────────────────────────┤
│                   Capa de Datos                     │
│                (Config + Database)                  │
└─────────────────────────────────────────────────────┘
```

### 📂 Estructura de Directorios

```
VetCarePlus/
│
├── 📁 Assets/                    # Recursos estáticos
│   ├── 🎨 Css/                  # Hojas de estilo
│   ├── 🔤 Fonts/                # Tipografías
│   ├── 🖼️ Img/                  # Imágenes y gráficos
│   └── ⚡ Js/                   # Scripts del cliente
│
├── 📁 Config/                    # Configuración del sistema
│   ├── Conexion.php             # Conexión a base de datos
│   └── Variables.php            # Variables globales
│
├── 📁 Includes/                  # Componentes reutilizables
│   ├── Functions.php            # Funciones auxiliares
│   ├── Header.php               # Cabecera común
│   ├── Footer.php               # Pie de página
│   └── Sidebar.php              # Menú lateral
│
├── 📁 Logs/                      # Registros del sistema
│   ├── access.log               # Log de accesos
│   ├── errors.log               # Log de errores
│   └── transactions.log         # Log de transacciones
│
├── 📁 Views/                     # Vistas por módulo
│   ├── 💼 Cargos/
│   │   ├── Index.php
│   │   ├── Create.php
│   │   └── Edit.php
│   │
│   ├── 📋 Consultas/
│   │   ├── Index.php
│   │   ├── Create.php
│   │   ├── Details.php
│   │   └── History.php
│   │
│   ├── 👥 Dueños/
│   │   ├── Index.php
│   │   ├── Create.php
│   │   ├── Edit.php
│   │   └── Profile.php
│   │
│   ├── 👨‍⚕️ Empleados/
│   │   ├── Index.php
│   │   ├── Create.php
│   │   └── Manage.php
│   │
│   ├── 💳 Facturas/
│   │   ├── Index.php
│   │   ├── Create.php
│   │   ├── View.php
│   │   └── Payments.php
│   │
│   ├── 🐾 Mascotas/
│   │   ├── Index.php
│   │   ├── Register.php
│   │   ├── Profile.php
│   │   └── MedicalHistory.php
│   │
│   ├── 🔬 Procedimientos/
│   │   ├── Index.php
│   │   ├── Register.php
│   │   └── Records.php
│   │
│   ├── 📊 Reportes/
│   │   ├── Index.php
│   │   ├── Financial.php
│   │   ├── Medical.php
│   │   └── Statistics.php
│   │
│   └── 💉 Vacunas/
│       ├── Index.php
│       ├── Schedule.php
│       └── Records.php
│
├── 🏠 Dashboard.php              # Panel principal
├── 🏁 Index.php                  # Página de inicio
├── 🔐 Login.php                  # Autenticación
└── 🚪 Logout.php                 # Cierre de sesión
```
## 🤝 Contribuir

Las contribuciones son bienvenidas y apreciadas. Para contribuir:

1. 🍴 Fork el proyecto
2. 🔨 Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. 💾 Commit tus cambios (`git commit -m 'Add: nueva funcionalidad increíble'`)
4. 📤 Push a la rama (`git push origin feature/AmazingFeature`)
5. 🎉 Abre un Pull Request

### 📝 Guías de Contribución

- Sigue los estándares de código PSR-12 para PHP
- Documenta todas las funciones nuevas
- Incluye tests cuando sea posible
- Actualiza el README si es necesario

---

## 📄 Licencia

```
MIT License

Copyright (c) 2025 Kevin Felipe

Se concede permiso, de forma gratuita, a cualquier persona que obtenga una copia
de este software y archivos de documentación asociados (el "Software"), para 
utilizar el Software sin restricciones...
```

---

## 👨‍💻 Autor

<div align="center">

**Kevin Felipe**  
*Ingeniero de Software*

[![GitHub](https://img.shields.io/badge/GitHub-100000?style=for-the-badge&logo=github&logoColor=white)](https://github.com/kevinpastrana)
[![LinkedIn](https://img.shields.io/badge/LinkedIn-0077B5?style=for-the-badge&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/kevin-pastrana-056072165/)
[![Email](https://img.shields.io/badge/Email-D14836?style=for-the-badge&logo=gmail&logoColor=white)](u20232215370@usco.edu.co)

</div>

---

## 🙏 Agradecimientos

- A la comunidad de PHP por su excelente documentación
- A todos los contribuidores del proyecto
- A las clínicas veterinarias que inspiraron este sistema

---

<div align="center">

**⭐ Si este proyecto te fue útil, considera darle una estrella ⭐**

Hecho con ❤️ para la comunidad veterinaria

</div>
