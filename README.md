# 🌾 SIGVOS — Sistema Inteligente de Gestión de Cultivos (Laravel 13)

<p align="center">
  <img src="https://raw.githubusercontent.com/campodidier07-collab/campodidier07-collab/main/assets/cards/proj_sigvos.png" width="80%" alt="SIGVOS Preview" />
</p>

<p align="center">
  <a href="https://sigvos.vercel.app"><img src="https://img.shields.io/badge/🌐_Demo_en_Vivo-sigvos.vercel.app-10b981?style=for-the-badge" /></a>
  <img src="https://img.shields.io/badge/Laravel_13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" />
</p>

---

## 🎯 Propósito del Proyecto

**SIGVOS** es una plataforma web empresarial diseñada para modernizar la administración agroindustrial, permitiendo el control trazable de lotes agrícolas, insumos, etapas fenológicas, monitoreo de producción y reportes ejecutivos para la toma de decisiones basada en datos.

## 🏗️ Arquitectura & Funcionalidades Clave

- **Arquitectura MVC & Clean Code:** Desarrollado sobre **Laravel 13** con separación clara de responsabilidades, migraciones versionadas y validación robusta.
- **Control de Roles y Permisos (RBAC):** Gestión diferenciada para administradores, agrónomos y operadores de campo.
- **Trazabilidad de Cultivos e Insumos:** Seguimiento en tiempo real de costos, actividades agrícolas, fertilización y rendimiento por hectárea.
- **Interfaz Moderna y Responsiva:** Construida con **Blade**, **Tailwind CSS** y **Vite** para una experiencia rápida tanto en escritorio como en dispositivos móviles en campo.

## 🚀 Instalación y Despliegue Local

```bash
git clone https://github.com/campodidier07-collab/Sigvos_laravel.git
cd Sigvos_laravel
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

---
*Desarrollado por **Didier Puentes** ([@campodidier07-collab](https://github.com/campodidier07-collab)) • Neiva, Colombia 🇨🇴*
