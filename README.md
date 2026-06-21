# 📚 Bookavy - Glassmorphism Library Information System

**Bookavy** is a library information system web application built using **Native PHP** and a **MySQL** database. It is designed with a premium, modern user interface utilizing the **Glassmorphism** concept (blur background effects), inspired by contemporary Laravel and Tailwind CSS design guidelines.

This application is ideal for study projects, vocational competency tests (UKK), or as a starting template for school library management software.

---

## ✨ Key Features

### 🧑‍💻 Administrator Features (Admin Panel)
*   **Real-time Dashboard**: Live summary cards showing total books, total members, active transactions, and currently borrowed books count.
*   **Book Management (CRUD)**: Create, read, update, and delete books, including a secure **book cover upload** feature.
*   **Member Management (CRUD)**: Manage student/member accounts.
*   **Loan & Return Verification**:
    *   Approve or reject book loan requests from members (automatically manages book stock levels).
    *   Verify book returns and **automatically calculate late return fines** (configured at Rp 1,000 / day).
*   **Manual Recording**: Ability to manually register a book loan directly from the admin panel.

### 👥 Member Features (Student Portal)
*   **Interactive Catalog**: Search for books by title or author, and filter results by category.
*   **Self-Loan Request**:
    *   Students can request to borrow books directly from the online catalog.
    *   Maximum limit of **3 active borrowed books** per user.
    *   Maximum borrowing duration is **14 days**.
    *   Book stock is instantly reserved upon request to prevent double-booking.
*   **Real-time Status Tracking**: Monitor loan status stages (`Pending`, `Borrowed`, `Return Pending`, `Returned`).
*   **Self-Return Request**: Submit a request to return borrowed books for admin verification.
*   **Request Cancellation**: Cancel any pending borrow requests (instantly restoring the book stock to the catalog).

---

## 🎨 Aesthetics & Design Elements
*   **Glassmorphic UI**: Transparent glass-like panels with backdrop blur (`backdrop-filter`) for a premium design layout.
*   **Harmonious Color Palette**: Built with lavender shades and soft radial gradients (`#e8e4fb`, `#fedcb2`, `#c6bfff`).
*   **Premium Typography**: Uses the modern **Outfit** typeface imported from Google Fonts.
*   **Smooth Animations**: Fluid hover effects and transitions on cards, buttons, inputs, and the sidebar navigation.

---

## 🛠️ System Requirements
Ensure your local environment meets the following requirements:
*   **PHP** >= 8.0
*   **MySQL** or **MariaDB**
*   **Apache** or **Nginx** (or PHP built-in CLI server)
*   PHP Extensions: `PDO_MySQL`, `GD` (optional, for image processing)
