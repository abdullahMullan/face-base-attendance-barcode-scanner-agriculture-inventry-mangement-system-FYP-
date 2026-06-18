# Manual to Digital Agriculture Fertilizer & Spray Inventory Management System
*With Integrated Barcode Scanning and Facial Attendance System*

**Authors:**
- Abdullah Mehboob (222030) — Reg: 2022-GCUF-02608
- Muzammal Ali (222025) — Reg: 2022-GCUF-02603

**Degree:** Bachelor of Science in Software Engineering
**Supervisor:** Dr. Awais (Department of Software Engineering, Government College University, Faisalabad)
**Session:** 2022–2026

---

## Abstract
This document presents the design, implementation, and evaluation of an integrated Agriculture Inventory Management System (Agriculture Tracker) featuring Barcode Scanning and an optional Face Recognition Attendance module. The system supports structured category and product cataloging (fertilizers, sprays), automated purchase and sales recording, real-time stock adjustment, supplier and customer management, credit tracking, reporting, and automated attendance logging. 

The system architecture uses Laravel 12 for robust backend routing, business rules, and report streaming, with a Vite/Tailwind CSS and Alpine.js frontend. Barcode generation utilizes a server-side PHP library to print Code128 labels, and the scanner decodes camera frames in-browser for swift product lookups. Face attendance extracts 128-dimensional biometric embeddings in-browser via face-api.js, performing matching server-side using Euclidean distance metrics to ensure transaction integrity and biometric privacy. The product provides small-to-medium agricultural depots and demonstration labs with a cheap, hardware-independent digital management utility.

---

## Table of Contents
1. [Chapter 1 — Introduction & Motivation](#chapter-1--introduction--motivation)
2. [Chapter 2 — System Scope & Requirements](#chapter-2--system-scope--requirements)
3. [Chapter 3 — System Architecture & Database Design](#chapter-3--system-architecture--database-design)
4. [Chapter 4 — Implementation Details](#chapter-4--implementation-details)
5. [Chapter 5 — Testing, Deployment & User Guide](#chapter-5--testing-deployment--user-guide)
6. [Chapter 6 — Conclusion & Future Work](#chapter-6--conclusion--future-work)

---

## Chapter 1 — Introduction & Motivation

### 1.1 Project Background
Efficient inventory management is critical for agricultural retail outlets, fertilizer/spray depots, and university-based agriculture demonstration labs. Historically, small-to-medium enterprises rely on manual ledger books or basic spreadsheets to log inventory adjustments. These methods are susceptible to mathematical discrepancies, delayed stock tracking, and unrecorded sales. In response, this project introduces the Agriculture Tracker, a modern inventory dashboard that digitalizes operations, provides real-time stock updates, tracks supplier purchases and customer sales, and handles credit account reconciliation.

### 1.2 Problem Statement
Manual operations in fertilizer and spray retail create three major challenges:
1. **Manual Typing Errors:** Typing product SKU numbers and names manually causes slow checkouts and frequent entry errors.
2. **Administrative Overhead:** Tracking attendee check-ins manually for training sessions or demonstration lab classes results in loss of active instruction time.
3. **Concurrency Issues:** Concurrent purchase and sale operations can lead to race conditions where database records reflect incorrect quantities.

To resolve these challenges, an automated software solution is required that runs on standard consumer devices without requiring specialized hardware terminals.

### 1.3 Barcode Integration (Purpose & Core Mechanism)
* **Why it is helpful:** Manual inventory systems suffer from slow checkouts and spelling mistakes when clerks type product details during transactions. Barcode integration automates product identification, ensures 100% data entry accuracy, minimizes check-out queues, and simplifies cataloging.
* **Core Mechanism:**
  1. **Server-side Generation:** When a product is created, the system uses the `Picqer PHP Barcode` library to programmatically generate a Code128 standard barcode as a PNG image, which is linked directly to the product ID or barcode string.
  2. **Client-side Scanning:** The browser scanner uses the `HTML5-QRCode` Javascript library. It accesses the device camera to stream video frames and scan for barcode patterns in real-time.
  3. **Lookup and Invoicing:** Once decoded, the client makes an asynchronous AJAX GET request to `/product-by-barcode/{code}`. The controller queries the database and returns product data as JSON, immediately appending the item to the active invoice.

### 1.4 Face Recognition Attendance (Purpose & Core Mechanism)
* **Why it is helpful:** Traditional attendance methods (paper registers) are slow, disrupt training sessions, and can lead to proxy check-ins. By integrating face recognition, we provide a quick, touchless biometric check-in/out option that runs on existing webcams without dedicated external biometrics equipment.
* **Core Mechanism:**
  1. **Browser-side Feature Extraction:** When a user steps in front of the camera, the client-side library `face-api.js` loads a pre-trained SSD MobileNet V1 convolutional neural network. It detects the face boundaries, locates landmarks, and extracts a 128-dimensional floating-point vector (face descriptor) representing the facial features. Crucially, the raw image is never stored or sent, protecting user privacy.
  2. **Server-side Matching:** The descriptor is sent to the backend. The `FaceRecognitionService` fetches the registered user profiles from the database and computes the Euclidean distance between the probed descriptor and the user's stored templates. If the minimal distance falls below the validation threshold (configured at 0.6), the user is matched.
  3. **Attendance Logic:** The system registers a Check-In if it is the first scan of the day, or a Check-Out on the second scan, updating the database atomically.

### 1.5 Document Structure
The remainder of this report details the engineering lifecycle of the project: Chapter 2 presents system scope and detailed requirements; Chapter 3 explains the system design, database schema, and concurrency safeguards; Chapter 4 covers technology implementation details; Chapter 5 details testing, deployment protocols, and the user manual; and Chapter 6 wraps up with conclusions and future scope.

---

## Chapter 2 — System Scope & Requirements

### 2.1 Stakeholders & Scope
The system serves four groups of stakeholders:
* **System Administrators:** Configure categories, users, roles, and Spatie permissions.
* **Store Clerks / Lab Assistants:** Record purchases and sales, scan products, and verify invoices.
* **Managers:** Review real-time sales summaries, check low stock alerts, and export report files.
* **Students / Staff:** Scan their face to check-in/out on the attendance scanner.

### 2.2 Core Use Cases
* **UC1: Product Enrollment:** Admin registers new fertilizers or sprays, uploads product images, and assigns barcode attributes.
* **UC2: Inventory Inflow:** Store clerk registers purchases from suppliers. Stock increases atomically, and purchase invoice PDFs are generated.
* **UC3: Inventory Outflow:** Clerk logs customer sales. Stock decrements, payment methods (cash, partial, credit) are processed, and invoices are generated.
* **UC4: Automated Biometric Logging:** Users enroll their face profiles under admin control and mark daily attendance via standard webcams.

### 2.3 Functional Requirements
* **FR1: Authentication & Authorization:** Spatie-based permissions (`dashboard.view`, `products.manage`, `purchases.manage`, `sales.manage`, `reports.view`, `users.manage`).
* **FR2: Product Catalog CRUD:** Catalog records with image upload, resizing, and automatic storage cleanup during updates.
* **FR3: Inventory Transactions:** Supporting multi-item sales and purchases with atomic calculations.
* **FR4: Barcode Support:** Code128 barcode generation and camera-based in-browser scanning lookup.
* **FR5: Facial Biometric Attendance:** Marking Check-in and Check-out logic per user, per logical date.
* **FR6: PDF Invoicing & Reporting:** Printable PDF invoice rendering for purchases/sales and CSV/Excel exports.

### 2.4 Non-Functional Requirements
* **NFR1: Performance:** Response times under 200ms for inventory lookups and under 500ms for facial match runs.
* **NFR2: Integrity:** Relational database consistency using foreign keys, cascade constraints, and unique indexes.
* **NFR3: Security:** SSL/TLS for biometric transmission, CSRF guards, and secure password hashing.

---

## Chapter 3 — System Architecture & Database Design

### 3.1 Architecture Components
The system follows the Model-View-Controller (MVC) architectural design pattern implemented via Laravel. 
* **Client (Browser):** Styled with Tailwind CSS and Alpine.js, incorporating client-side Javascript APIs to manage camera feeds for barcode scanning and face descriptor extraction.
* **Backend (Laravel):** Implements authentication middleware, controller logic, PDF rendering services, and database transactions.
* **Database:** MySQL/MariaDB database storing structured tables.

### 3.2 Database Schema & Key Models
The schema consists of nine core tables:
1. `users`: Authentication records (`id`, `name`, `email`, `password`).
2. `categories`: Inventory groupings (`id`, `name`).
3. `products`: Catalog details (`id`, `category_id`, `name`, `unit`, `stock_quantity`, `low_stock_threshold`, `purchase_price`, `selling_price`, `barcode`, `image_path`).
4. `purchases` & `purchase_items`: Inbound inventory records linking products and quantities.
5. `sales` & `sale_items`: Outbound customer transactions containing quantities, discount levels, payment status (`paid`, `partial`, `unpaid`).
6. `suppliers` & `customers`: Reference contact directories.
7. `credit_payments`: Records payments made towards outstanding sales due balances.
8. `face_profiles`: JSON storage holding enrolled facial descriptors linked to user IDs.
9. `attendances`: Logs of daily student/staff check-in and check-out timestamps.

### 3.3 Concurrency Control & Database Locking
To ensure inventory integrity when multiple clerks perform transactions concurrently, all inventory-altering operations run inside database transactions wrapped with `DB::transaction()`. Products table rows are locked using Laravel's `lockForUpdate()` during the transaction. This forces subsequent requests to wait until the current transaction commits, ensuring accurate stock updates.

---

## Chapter 4 — Implementation Details

### 4.1 Front-End Stack & UI Design Patterns
The user interface is built using Laravel Blade templates styled with Tailwind CSS. We use Alpine.js to handle minor dynamic client interactions. Camera capture relies on HTML5 APIs, sending media data directly to the barcode and face APIs. Barcode scanning runs in a specialized canvas loop, while face attendance runs face-api.js models from public directories, preventing raw biometric data from leaving the browser during extraction.

### 4.2 Back-End Services & Core Logic
The backend uses Laravel controllers to handle business rules. Two custom modules highlight the backend logic:
1. `FaceRecognitionService`: Instantiated on attendance check-in. It loads stored profiles, parses the multi-dimensional JSON vectors, and computes the Euclidean distance for each profile sample against the input probe. It identifies the profile with the minimum distance and, if within threshold (0.6), passes authorization to the attendance model.
2. **Streamed Reporting:** To prevent PHP memory leaks during large exports, `ReportController` implements streaming. It uses Symfony's `StreamedResponse` to stream CSV data directly to the client's download buffer instead of loading the entire dataset into RAM first.

---

## Chapter 5 — Testing, Deployment & User Guide

### 5.1 Verification Checklist
* **Unit testing:** Verified Euclidean distance matching logic inside `FaceRecognitionService` under varying array sizes.
* **Integration testing:** Confirmed that adding sales decreases stock and adding purchases increases stock correctly in the database.
* **E2E testing:** Tested camera feeds on various browsers (Chrome, Edge, Firefox), verifying barcode lookup speed and face matching accuracy.

### 5.2 Installation & Server Configuration
System prerequisites: PHP 8.2+, Node.js (Vite), Composer, MySQL 5.7+ (or MariaDB).

**Deployment commands:**
```bash
# 1. Install packages
composer install && npm install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Build assets
npm run build

# 4. Setup DB
php artisan migrate --seed

# 5. Link storage
php artisan storage:link

# 6. Run server
php artisan serve
```

### 5.3 Quick-Start User Manual
* **Creating Products:** Log in as Admin. Navigate to Products -> Create. Enter name, pricing details, low stock threshold, and optional barcode. Upload product image and save.
* **Sales and Billing:** Go to Sales -> Create. Use the camera scanner to scan a product barcode (or choose from list). Select payment type (cash/credit). Submit to save and download the PDF invoice.
* **Managing Credits:** For credit sales, navigate to Credit Payments. View outstanding dues per customer and record partial payments.
* **Marking Attendance:** Click Attendance on the main screen. Start the camera and scan your face. The system displays a success toast and checks you in.

---

## Chapter 6 — Conclusion & Future Work

### 6.1 Achievements Summary
The Agriculture Tracker successfully replaces manual stock tracking with an integrated, easy-to-use digital system. By adding barcode scanning and face recognition, it addresses data entry errors and automates attendance tracking. Transactions are secured using database-level row locks, and user biometrics are protected by storing only mathematical descriptors rather than raw images.

### 6.2 Future Enhancements
1. **Anti-Spoofing:** Implement client-side liveness detection (detecting eye blinks or head movements) to prevent spoofing with photos.
2. **Vector Indexing:** Integrate a dedicated vector library like FAISS or Milvus to speed up matching if the system scales to thousands of users.
3. **Offline Operations:** Implement local storage (IndexedDB) and background sync to support sales and attendance tracking when offline.