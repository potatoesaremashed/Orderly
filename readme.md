# 🍽️ Orderly — Restaurant Order Management System

**Orderly** is a web-based restaurant management platform built with PHP, MySQL, Bootstrap 5, and vanilla JavaScript. It connects three user roles — **Manager**, **Kitchen Staff (Cucina)**, and **Table (Tavolo)** — into a seamless real-time ordering workflow.

---

## 📋 Table of Contents

- [Features](#-features)
- [Architecture Overview](#-architecture-overview)
- [User Roles & Dashboards](#-user-roles--dashboards)
- [Database Schema](#-database-schema)
- [API Endpoints](#-api-endpoints)
- [Project Structure](#-project-structure)
- [Installation & Setup](#-installation--setup)
- [Default Test Credentials](#-default-test-credentials)
- [Tech Stack](#-tech-stack)

---

## ✨ Features

### General
- **Role-based authentication** — Manager, Kitchen, and Table users each get a dedicated dashboard
- **Dark / Light theme toggle** — persisted via `localStorage`
- **Responsive design** — works on desktop, tablet, and mobile devices
- **Real-time updates** — Kitchen dashboard auto-refreshes every 3 seconds

### Table (Tavolo) Dashboard
- **Browse menu** with dish images, descriptions, prices, and allergen badges
- **Category filtering** via sticky sidebar (Antipasti, Primi, Secondi, Dolci)
- **Allergen filtering** — exclude dishes containing specific allergens
- **Search bar** — instant text-based dish filtering
- **Dish detail modal** — full-screen view with allergen info and kitchen notes
- **Quick quantity controls** (+/−) directly on cards without opening detail modal
- **Shopping cart** — review, modify quantities, and submit orders
- **Order history (Ordini)** — view all previously sent orders with status badges:
  - 🟡 **In Attesa** (Waiting)
  - 🔵 **In Preparazione** (Being Prepared)
  - 🟢 **Pronto** (Ready)
- **Order submission** with confirmation dialog and success animation

### Kitchen (Cucina) Dashboard
- **Kanban board** with two columns: *In Arrivo* and *In Preparazione*
- **Real-time polling** — new orders appear automatically every 3 seconds
- **Audio notification** when a new order arrives
- **Order cards** showing table name, elapsed time, items with notes
- **State progression** — move orders from "In Attesa" → "In Preparazione" → "Pronto"
- **Timer per order** — shows how long each order has been waiting

### Manager Dashboard
- **Full menu management** — add, edit, and delete dishes (name, price, description, allergens, image, category)
- **Category management** — create and delete food categories
- **Complete menu table** — overview of all dishes with inline edit/delete actions
- **Image upload** — attach images to dishes

---

## 🏗️ Architecture Overview

```
┌──────────────┐       ┌──────────────┐       ┌──────────────┐
│    TAVOLO    │       │    CUCINA    │       │   MANAGER    │
│  (Customer)  │       │  (Kitchen)   │       │   (Admin)    │
│              │       │              │       │              │
│ Browse Menu  │       │ Kanban Board │       │ CRUD Dishes  │
│ Place Orders │──────▶│ View Orders  │       │ Categories   │
│ View History │       │ Update State │       │ Menu Config  │
└──────┬───────┘       └──────┬───────┘       └──────┬───────┘
       │                      │                      │
       └──────────┬───────────┴──────────────────────┘
                  │
          ┌───────▼───────┐
          │   PHP API     │
          │  (12 endpoints)│
          └───────┬───────┘
                  │
          ┌───────▼───────┐
          │    MySQL      │
          │ ristorante_db │
          └───────────────┘
```

### Order Flow

1. **Table** browses the menu and adds items to the cart
2. **Table** submits the order → `api/invia_ordine.php` creates a record with status `in_attesa`
3. **Kitchen** sees the new order appear in the "In Arrivo" column (auto-refreshed)
4. **Kitchen** clicks "Prepara" → status moves to `in_preparazione`
5. **Kitchen** clicks "Pronto" → status moves to `pronto`
6. **Table** can track order status via the "Ordini" button at any time

---

## 👥 User Roles & Dashboards

| Role | Dashboard | URL | Key Actions |
|------|-----------|-----|-------------|
| **Manager** | `dashboards/manager.php` | `/Orderly/index.php` → login | Add/edit/delete dishes and categories |
| **Cucina** | `dashboards/cucina.php` | `/Orderly/index.php` → login | View and manage incoming orders |
| **Tavolo** | `dashboards/tavolo.php` | `/Orderly/index.php` → login | Browse menu, order food, track status |

---

## 🗃️ Database Schema

The application uses a MySQL database called `ristorante_db`. Schema is defined in `templatedb.sql`.

### Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `manager` | Admin accounts | `id_manager`, `username`, `password` |
| `cuochi` | Kitchen staff accounts | `id_cuoco`, `username`, `password` |
| `tavoli` | Table accounts | `id_tavolo`, `nome_tavolo`, `password`, `id_menu` |
| `menu` | Menu groups (one per manager) | `id_menu`, `nome_menu`, `id_manager` |
| `categorie` | Food categories | `id_categoria`, `nome_categoria`, `id_menu` |
| `alimenti` | Menu items (dishes) | `id_alimento`, `nome_piatto`, `prezzo`, `descrizione`, `lista_allergeni`, `immagine`, `id_categoria` |
| `ordini` | Order headers | `id_ordine`, `stato` (enum: `in_attesa`, `in_preparazione`, `pronto`), `data_ora`, `id_tavolo` |
| `dettaglio_ordini` | Order line items | `id_ordine`, `id_alimento`, `quantita`, `note` |

### Entity Relationships

```
manager ──1:N──▶ menu ──1:N──▶ categorie ──1:N──▶ alimenti
                   │
                   └──1:N──▶ tavoli ──1:N──▶ ordini ──1:N──▶ dettaglio_ordini
                                                                    │
                                                        alimenti ◀──┘
```

---

## 🔌 API Endpoints

All APIs are in the `api/` directory and return JSON responses.

### Order Management
| Endpoint | Method | Description |
|----------|--------|-------------|
| `invia_ordine.php` | POST | Submit a new order (table → kitchen) |
| `leggi_ordini_cucina.php` | GET | Fetch active orders for kitchen display |
| `leggi_ordini_tavolo.php` | GET | Fetch order history for the current table |
| `cambia_stato_ordine.php` | POST | Update order status (in_attesa → in_preparazione → pronto) |

### Cart Management
| Endpoint | Method | Description |
|----------|--------|-------------|
| `aggiungi_al_carrello.php` | POST | Add item to session-based cart |
| `get_carrello.php` | GET | Get current cart contents |
| `rimuovi_dal_carrello.php` | POST | Remove item from cart |

### Menu Management (Manager)
| Endpoint | Method | Description |
|----------|--------|-------------|
| `aggiungi_piatto.php` | POST | Add a new dish to the menu |
| `modifica_piatto.php` | POST | Update an existing dish |
| `elimina_piatto.php` | POST | Delete a dish from the menu |
| `aggiungi_categoria.php` | POST | Create a new food category |
| `elimina_categoria.php` | POST | Delete a food category |

---

## 📁 Project Structure

```
Orderly/
├── index.php                  # Login page (routes to dashboards by role)
├── logout.php                 # Session destroy + redirect
├── templatedb.sql             # Database schema + test data
├── readme.md                  # This file
│
├── dashboards/
│   ├── tavolo.php             # Table customer interface
│   ├── cucina.php             # Kitchen kanban board
│   └── manager.php            # Admin menu management
│
├── api/
│   ├── invia_ordine.php       # Submit order
│   ├── leggi_ordini_cucina.php# Kitchen: fetch active orders
│   ├── leggi_ordini_tavolo.php# Table: fetch order history
│   ├── cambia_stato_ordine.php# Update order status
│   ├── aggiungi_al_carrello.php
│   ├── get_carrello.php
│   ├── rimuovi_dal_carrello.php
│   ├── aggiungi_piatto.php
│   ├── modifica_piatto.php
│   ├── elimina_piatto.php
│   ├── aggiungi_categoria.php
│   └── elimina_categoria.php
│
├── css/
│   ├── common.css             # Shared variables, theme, sticky header
│   ├── tavolo.css             # Table dashboard styles
│   ├── cucina.css             # Kitchen dashboard styles
│   └── manager.css            # Manager dashboard styles
│
├── js/
│   ├── tavolo.js              # Table: cart, filtering, zoom, order history
│   ├── gestioneCarrello.js    # Cart management helpers
│   └── manager.js             # Manager: modal form handling
│
├── include/
│   ├── conn.php               # Database connection (mysqli)
│   ├── header.php             # Shared HTML head (Bootstrap, meta)
│   └── footer.php             # Shared footer (Bootstrap JS)
│
└── imgs/                      # Uploaded dish images
```

---

## 🚀 Installation & Setup

### Prerequisites
- **XAMPP** (or any Apache + PHP + MySQL stack)
- PHP 7.4+
- MySQL 5.7+

### Steps

1. **Clone the repository** into your web server's document root:
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs/
   git clone <repository-url> Orderly
   ```

2. **Create the database** by importing the template:
   ```bash
   mysql -u root < Orderly/templatedb.sql
   ```
   Or use phpMyAdmin:
   - Open `http://localhost/phpmyadmin`
   - Go to **Import** tab
   - Select `templatedb.sql` and click **Go**

3. **Configure the database connection** (if needed):
   - Edit `include/conn.php`
   - Default: `localhost`, user `root`, no password, database `ristorante_db`

4. **Start Apache and MySQL** in XAMPP Control Panel

5. **Access the application**:
   ```
   http://localhost/Orderly/
   ```

---

## 🔑 Default Test Credentials

| Role | Username | Password |
|------|----------|----------|
| Manager | `admin` | `admin` |
| Kitchen | `cheftest` | `test` |
| Table | `tavolotest` | `test` |

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| **Frontend** | HTML5, CSS3, JavaScript (ES6+) |
| **UI Framework** | Bootstrap 5.3.3 |
| **Icons** | Font Awesome 6.4 |
| **Fonts** | Google Fonts (Poppins) |
| **Backend** | PHP 7.4+ |
| **Database** | MySQL 5.7+ (MySQLi) |
| **Server** | Apache (XAMPP) |

---

## 📝 License

This project was created as an educational project.
