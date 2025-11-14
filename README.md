<p align="center"><a href="#" target="_blank"><img src="YOUR_PROJECT_LOGO_URL_HERE" width="200" alt="Barista Dashboard Logo"></a></p>

<p align="center">
<a href="#"><img src="https://img.shields.io/badge/status-active-success" alt="Project Status"></a>
<a href="#"><img src="https://img.shields.io/github/v/release/yourusername/barista-dashboard" alt="Latest Stable Version"></a>
<a href="#"><img src="https://img.shields.io/github/license/yourusername/barista-dashboard" alt="License"></a>
<a href="#"><img src="https://img.shields.io/github/issues/yourusername/barista-dashboard" alt="Open Issues"></a>
</p>

## ☕ About Barista Dashboard

The **Barista Dashboard** is a web application designed to help baristas efficiently manage daily café operations. It provides tools to maintain the drinks menu, highlight daily specials, and track customer orders in real-time through AJAX auto-refreshing queues.

The dashboard focuses on **speed, ease of use, and live updates** to support a busy coffee shop environment.

### Key Capabilities

- ➕ Add new drinks and food menu items  
- ✏️ Edit existing menu items  
- 📋 View all menu items in a clean UI  
- ⭐ Add **Today’s Specials** with special discount pricing  
- 🧾 View a **live orders queue** that automatically updates without refreshing the page  
- 🥤 Browse drinks by categories (e.g., Coffee, Tea, Smoothies, etc.)  

---

## 📷 Screenshots

> Screenshots will be included below once added.  
*(You can attach UI screenshots manually here)*

---

## 🚀 Technologies Used

- Laravel (Backend framework)
- Blade Templates / HTML / CSS
- JavaScript + AJAX for live updates
- MySQL Database

---

## 🛠 Installation

```bash
# Clone repository
git clone https://github.com/yourusername/barista-dashboard.git

cd barista-dashboard

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure your .env database credentials

# Run migrations
php artisan migrate

# Serve the project
php artisan serve
