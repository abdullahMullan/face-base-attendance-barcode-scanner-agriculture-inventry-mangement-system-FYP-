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

This document presents the design, development, and evaluation of an integrated **Agriculture Inventory Management System** (referred to as "Agriculture Tracker"), which includes a Barcode Scanning module and a Face Recognition Attendance module.

In simple terms, this system replaces paper registers and manual Excel sheets used in fertilizer and spray shops. Instead, shopkeepers can now:
- **Add products** with barcodes and track how many are in stock in real time.
- **Record purchases** from suppliers and **sell** to customers using barcode scanning.
- **Manage credit sales** — for customers who buy now and pay later.
- **Mark employee or student attendance** automatically by scanning their face on a webcam — no fingerprint machine needed.
- **Generate PDF invoices** for every sale and purchase, and **export reports** to Excel or CSV format.

The system is built using **Laravel 12** (a PHP web framework) for the backend, and **Tailwind CSS + Alpine.js** for a modern, responsive front-end interface. Barcode generation uses the Picqer PHP Barcode library (Code128 format), and face recognition uses `face-api.js` — a browser-based AI library that extracts unique facial features without storing any raw photographs or video.

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

### Module Summary
> **What this chapter is about:** This chapter explains *why* the project was created, *what problem* it solves, and *how* the two special features — Barcode Scanning and Face Recognition Attendance — work at a high level. Even a reader with no software background should be able to understand this chapter fully.

---

### 1.1 Project Background

Small and medium-sized agricultural depots, fertilizer shops, and university agriculture demonstration labs still rely heavily on **manual methods** — paper ledgers, handwritten receipts, and basic spreadsheets — to track their daily inventory. These traditional methods cause several real-world problems:

- Stock levels go out of sync because human errors are common when writing quantities by hand.
- There is no easy way to know which products are running low without physically counting shelves.
- Attendance is recorded manually on paper, which can be forged, delayed, or simply lost.
- Generating an invoice requires manually calculating totals, which is slow and error-prone.

To solve these problems, this project introduces the **Agriculture Tracker** — a web-based management system that digitalizes all of these operations. The system provides real-time stock updates, tracks supplier purchases and customer sales, manages credit accounts, and handles attendance automatically through face recognition.

The system is specifically designed to work on **any standard computer or laptop equipped with a webcam** — no expensive or specialized hardware devices are required.

### 1.2 Problem Statement

Manual operations in fertilizer and spray retail create three major technical and operational challenges:

1. **Manual Typing Errors:** When a shop clerk manually types a product's name or code during checkout, mistakes are frequent. A wrongly typed product name leads to incorrect stock deductions and inaccurate invoices. This slows down the checkout process and creates data quality issues over time.

2. **Administrative Overhead:** Recording attendance manually for training sessions or lab classes takes time away from actual productive work. Paper registers can also be manipulated — for example, one person signing in on behalf of another (proxy attendance). This defeats the entire purpose of attendance tracking.

3. **Concurrency Issues:** If two clerks are processing sales on the same product at the same time on two different computers, the stock count can become incorrect. For example, if only 5 bags of fertilizer are left in stock and both clerks sell 5 bags simultaneously, the system might incorrectly allow both transactions — resulting in a stock count of -5, which is logically impossible.

This software solution addresses all three challenges while running entirely on standard consumer devices without requiring specialized hardware terminals.

### 1.3 Barcode Integration (Purpose & Core Mechanism)

**Why it is useful:**
In a traditional shop, the clerk manually searches for a product by name and types the quantity for every sale. With barcode scanning, the clerk simply points the device webcam at the printed barcode on a product's label, and that product is **automatically added to the current invoice** — no typing or searching needed. This eliminates spelling mistakes, speeds up checkout, and ensures the correct product is always selected.

**How it works — Step by Step:**

1. **Barcode Generation (performed by the Admin):**
   When a new product is added to the system, the admin enters a unique barcode number. The system uses a PHP library called `Picqer Barcode` to automatically create a **Code128 barcode image** — a widely-used standard barcode format found in retail stores worldwide. This image is stored on the server and can be printed on sticker labels to be attached to product packaging.

2. **Barcode Scanning (performed at the point of sale):**
   When a store clerk is creating a new sale, they click the "Scan" button in the browser. The system activates the device camera using the browser's built-in HTML5 camera API. A JavaScript library called `HTML5-QRCode` continuously reads the live video feed from the camera and recognizes barcode stripe patterns in real time — frame by frame.

3. **Automatic Product Lookup:**
   Once a barcode is successfully decoded, the browser immediately sends a silent background request to the server route `/product-by-barcode/{code}`. The server's `BarcodeController` searches the products database for a matching barcode and returns the product's name, selling price, and current stock level as a JSON response. The product is then instantly added to the active sale invoice — the clerk does not need to do anything further.

### 1.4 Face Recognition Attendance (Purpose & Core Mechanism)

**Why it is useful:**
Traditional attendance methods — paper registers, punch cards, or manual sign-in sheets — are slow, interruptive, and can be falsified. With face recognition, an employee or student simply stands in front of a standard webcam for a brief moment. The system automatically identifies them and logs their check-in or check-out time. This runs entirely inside a normal web browser with no special hardware, no fingerprint scanner, and no additional software installation.

**How it works — Step by Step:**

1. **Face Enrollment (the Admin registers each user's face first):**
   An admin navigates to the enrollment page for a specific user. The user sits in front of the webcam. The browser loads `face-api.js` — a pre-trained AI library — which detects the face in the video frame, locates key facial landmark points (eyes, nose tip, jaw corners), and mathematically converts these features into a **128-number floating-point vector**, commonly called a "face descriptor." Think of this as a unique mathematical fingerprint for each person's face. The system captures between 3 and 10 such samples to create a robust profile. These number arrays are saved to the database. **No photograph or video frame is ever stored** — only the mathematical numbers.

2. **Face Matching During Attendance:**
   When a registered user wants to mark attendance, they open the Attendance page in the browser. The camera activates automatically. `face-api.js` extracts a fresh 128-number descriptor from the user's live face. This array is sent securely to the server.

3. **Server-Side Recognition:**
   The server's `FaceRecognitionService` class loads all active face profiles from the database. It compares the incoming descriptor against every saved sample using **Euclidean Distance** — a mathematical formula that measures how numerically "different" two arrays of numbers are. The formula is: `distance = √(Σ(a_i − b_i)²)`. A lower distance means the faces are more similar. The system finds the profile with the lowest distance. If that distance falls below the threshold value of **0.48**, the user is identified as a match and attendance is recorded.

4. **Attendance Logging Logic:**
   - **First scan of the day:** The system creates a new attendance record with the current timestamp as the **Check-In time**.
   - **Second scan of the day (after check-in exists):** The system updates the same attendance record and fills in the **Check-Out time**.
   - **If both check-in and check-out are already recorded:** The system responds with "Attendance already completed for today" and no changes are made.

### 1.5 Document Structure

The rest of this report covers the complete engineering lifecycle of the project:
- **Chapter 2:** Identifies stakeholders, defines the system's scope, and lists all functional and non-functional requirements.
- **Chapter 3:** Explains the overall system architecture, describes the database schema, and details how concurrent stock operations are handled safely.
- **Chapter 4:** Covers the technical implementation of each module — front-end, backend controllers, and the two key custom services.
- **Chapter 5:** Documents the testing approach, provides step-by-step deployment instructions, and includes a practical user guide for all user types.
- **Chapter 6:** Summarizes the project's achievements and proposes realistic enhancements for future versions.

---

## Chapter 2 — System Scope & Requirements

### Module Summary
> **What this chapter is about:** This chapter defines *who* uses the system (stakeholders), *what actions* they can perform (use cases), and *what specific rules and standards* the system must follow (functional and non-functional requirements). A reader who finishes this chapter should understand exactly what the system does and for whom.

---

### 2.1 Stakeholders & Scope

The system is designed for four distinct types of users. Each type has different access rights and responsibilities:

| User Type | Role in the System | Access Level |
|---|---|---|
| **System Administrator** | Manages the entire system setup — adds categories, products, suppliers, customers, and users. Assigns roles and permissions. Also enrolls user face profiles. | Full access to all modules. |
| **Store Clerk / Lab Assistant** | Handles day-to-day operations — records purchases from suppliers and creates sales to customers. Uses barcode scanner during transactions. | Access to purchases, sales, and inventory. No access to user management or system settings. |
| **Manager** | Monitors overall performance — views the dashboard, checks low-stock alerts, reviews sales summaries, and exports financial reports. | Read access to reports and dashboard. Cannot create or delete records. |
| **Students / Staff** | Use the attendance module only — they look at the webcam to mark their daily check-in and check-out. | Access limited to the attendance scanner page only. |

Access control is managed by the **Spatie Laravel Permission** package — a widely used PHP library that allows assigning named roles (e.g., "Admin", "Clerk") and individual permissions (e.g., `sales.manage`, `reports.view`) to each user account.

### 2.2 Core Use Cases

The following are the four primary workflows that the system supports:

- **UC1: Product Enrollment**
  The administrator adds new fertilizer or spray products to the product catalog. Each product record includes: product name, category (e.g., "Fertilizer" or "Spray"), brand, measuring unit (e.g., kg, litre, bag), purchase price, selling price, current stock quantity, low-stock threshold, and an optional barcode and product photograph.

- **UC2: Inventory Inflow — Purchase Recording**
  A store clerk registers a new purchase from a supplier. The clerk selects the supplier from the system, enters the purchase date, and adds one or more products with their quantities and per-unit costs. Upon saving, the stock levels of all listed products increase automatically, a unique invoice number is generated, and a printable PDF purchase invoice is produced.

- **UC3: Inventory Outflow — Sale Recording**
  A clerk creates a sale for a customer. Products can be added either by scanning a barcode with the webcam or by selecting them from a searchable dropdown list. The clerk selects the payment method — Cash (full payment now), Credit (full payment later), or Partial (part payment now, rest later). Upon saving, stock quantities decrease automatically, profit per item is calculated and recorded, and a PDF sale invoice is generated.

- **UC4: Automated Biometric Attendance Logging**
  An administrator first registers each user's face profile by capturing 3–10 face descriptor samples through the enrollment interface. After enrollment, any registered user can mark their daily attendance simply by looking into the webcam on the Attendance page. The system identifies them and logs the time automatically.

### 2.3 Functional Requirements

The following specific features must be present in the system:

- **FR1: Authentication & Authorization**
  Users must log in with their registered email and password. Each user is assigned one or more roles. Permissions are enforced at the controller level to ensure users can only access the parts of the system permitted by their role. Defined permissions include: `dashboard.view`, `products.manage`, `purchases.manage`, `sales.manage`, `reports.view`, and `users.manage`.

- **FR2: Product Catalog Management (CRUD)**
  Administrators must be able to Create, Read, Update, and Delete product records. Uploading a product image is supported. When a product image is replaced during an update, the old image file is automatically deleted from storage to prevent accumulation of unused files.

- **FR3: Inventory Transaction Processing**
  The system must support recording multi-item purchases and multi-item sales in a single transaction. All stock quantity changes must be applied atomically — either all changes succeed together, or none are applied (to prevent partial updates that leave the database in an inconsistent state).

- **FR4: Barcode Generation and Scanning**
  Code128 format barcodes must be generated on the server for each product and rendered as PNG images. The in-browser scanner must be able to read printed barcodes from the camera feed in real time and instantly retrieve the corresponding product from the database.

- **FR5: Facial Biometric Attendance**
  User face profiles must be enrolled and stored as JSON arrays of 128-dimensional floating-point vectors. The server must match incoming descriptors against stored profiles using Euclidean distance. The system must correctly manage the Check-In and Check-Out logic — one per user per calendar day.

- **FR6: PDF Invoicing and Data Reporting**
  The system must generate printable PDF invoices for both purchases and sales using the `barryvdh/laravel-dompdf` package. Managers must be able to export date-filtered sales data as CSV files (streamed line by line to handle large datasets without memory issues) and as formatted Excel spreadsheets using the `maatwebsite/excel` package.

### 2.4 Non-Functional Requirements

The following quality standards must be met by the system:

- **NFR1: Performance**
  All inventory lookup operations — such as fetching a product by barcode — must respond in under 200 milliseconds. Face matching operations must complete in under 500 milliseconds. The main dashboard must use **Laravel's built-in Cache** system (with a 5-minute expiry window) to avoid running expensive database aggregate queries on every single page load.

- **NFR2: Data Integrity**
  The relational database must enforce **foreign key constraints** to prevent orphaned records (e.g., a sale item referencing a deleted product). **Cascade delete** constraints must remove child records when parent records are deleted. **Unique indexes** must be applied to critical columns such as `products.barcode` and `users.email` to prevent duplicate entries.

- **NFR3: Security**
  All data transmission — especially biometric face descriptors — must occur over HTTPS (SSL/TLS encrypted connections). Every HTML form must include a **CSRF token** (Cross-Site Request Forgery protection) to prevent malicious form submissions from external websites. User passwords must be stored using Laravel's default **bcrypt** hashing algorithm — never stored in plain text. Raw face images must never be transmitted to or stored by the server at any point.

---

## Chapter 3 — System Architecture & Database Design

### Module Summary
> **What this chapter is about:** This chapter explains *how the different parts of the system fit together* (the overall architecture), *what each database table stores and how they relate to each other*, and *how the system prevents stock count errors when two people are using it simultaneously*. Think of this chapter as the engineering blueprint of the project.

---

### 3.1 Architecture Components

The system is built following the **MVC (Model-View-Controller)** software design pattern. MVC separates an application into three distinct, independent layers so that changes in one layer do not require rewriting others:

```
User opens browser
       |
       | (sends HTTP request)
       v
  Laravel Router
  (decides which controller handles this URL)
       |
       v
  Controller
  (runs the business logic, queries the database)
       |
       v
  Model (Eloquent ORM)
  (communicates with the MySQL database)
       |
       | (data returned to controller)
       v
  Blade View (HTML Template)
  (rendered and sent back to browser as a complete web page)
```

**The Three Layers:**

- **Client Side — The View Layer (Browser):**
  All pages the user sees are built with **Laravel Blade** templates — these are PHP-enhanced HTML files that the server processes before sending to the browser. Visual styling is provided by **Tailwind CSS** (a utility-first CSS framework). Small interactive behaviors — such as showing a dropdown menu, opening a modal dialog, or toggling a form section — are powered by **Alpine.js**, a lightweight JavaScript library. Camera features for barcode scanning and face recognition use the browser's standard HTML5 `getUserMedia` API, which works natively in Chrome, Edge, and Firefox without any plugins.

- **Server Side — The Controller & Business Logic Layer (Laravel):**
  Laravel 12 handles all URL routing, form input validation, business rule enforcement (e.g., checking stock before allowing a sale), database operations, PDF generation, and file storage. Complex or reusable logic is extracted into dedicated **Service classes** (e.g., `FaceRecognitionService`) to keep controller files focused and readable.

- **Data Storage — The Model Layer (MySQL Database):**
  All structured data is stored in a **MySQL or MariaDB** relational database. Laravel's **Eloquent ORM** (Object-Relational Mapping) allows developers to interact with database tables using clean PHP classes and methods instead of writing raw SQL queries directly, making the code more readable and maintainable.

### 3.2 Database Schema & Key Models

The complete database is managed through **18 migration files**. The 9 core business tables are described below:

| Table Name | Purpose | Key Columns |
|---|---|---|
| `users` | Stores login accounts and staff information | `id`, `name`, `email`, `password`, `phone`, `is_active` |
| `categories` | Groups products into logical types (e.g., Fertilizers, Sprays) | `id`, `name` |
| `products` | The main product catalog — heart of the inventory | `id`, `category_id`, `name`, `brand`, `unit`, `stock_quantity`, `low_stock_threshold`, `purchase_price`, `selling_price`, `barcode`, `image_path` |
| `suppliers` | Vendor and supplier contact directory | `id`, `name`, `phone`, `email`, `address`, `is_active` |
| `customers` | Customer contact directory, including optional photo | `id`, `name`, `phone`, `email`, `address`, `photo_path`, `is_active` |
| `purchases` & `purchase_items` | Records inbound stock from suppliers. The parent `purchases` record holds the invoice header; `purchase_items` holds individual product lines. | `supplier_id`, `invoice_no`, `total_amount` / `product_id`, `quantity`, `unit_cost`, `subtotal` |
| `sales` & `sale_items` | Records outbound customer sales. `sales` holds invoice header and payment details; `sale_items` holds per-product lines with profit calculations. | `customer_id`, `payment_type`, `total_amount`, `paid_amount`, `due_amount` / `product_id`, `quantity`, `unit_price`, `profit_amount` |
| `credit_payments` | Tracks individual payments made against outstanding credit or partial sale balances | `sale_id`, `customer_id`, `amount`, `payment_date`, `note` |
| `face_profiles` | Stores enrolled face descriptor samples as a JSON array of number arrays | `user_id`, `descriptors` (JSON), `sample_count`, `last_enrolled_at`, `is_active` |
| `attendances` | Daily attendance log with check-in and check-out timestamps per user | `user_id`, `attendance_date`, `check_in_at`, `check_out_at`, `check_in_method`, `check_in_distance` |

**Key Relationships Between Tables:**
- Each `sale` record has many `sale_items`. Each `sale_item` belongs to exactly one `product`.
- Each `purchase` record has many `purchase_items`. Each `purchase_item` belongs to exactly one `product`.
- Each `user` can have one `face_profile` (enrollment data) and many `attendance` records (one per day).
- Each `customer` can have many `sales` and many `credit_payments` tied to those sales.
- Each `supplier` can have many `purchases`.
- Each `product` belongs to one `category`.

### 3.3 Concurrency Control & Database Locking

**The Problem — Race Condition:**
Imagine two store clerks using the system simultaneously on two different computers. Both are selling the last 5 bags of a particular fertilizer. Without any protection:
1. Clerk A reads the stock: "5 bags available" — approves the sale.
2. Clerk B reads the stock (at the same instant): "5 bags available" — also approves the sale.
3. Both sales complete. Stock is now updated twice: `5 - 5 = 0`, then `5 - 5 = 0` again.
4. The result: two sales were made for 5 bags each (10 bags total), but only 5 bags existed. Stock should be -5, which is impossible.

This problem is called a **race condition** — a well-known concurrency bug in software systems.

**The Solution — Pessimistic Database Locking:**
Every operation that modifies stock levels — both sales and purchases — is wrapped inside a **database transaction** using Laravel's `DB::transaction()`. Inside each transaction, the specific product row is locked using `Product::lockForUpdate()`.

The locking process works as follows:
1. Clerk A's transaction begins and **locks** the fertilizer product row in the database.
2. Clerk B's transaction begins and attempts to access the **same** product row — the database makes Clerk B's transaction **wait** until the lock is released.
3. Clerk A's transaction completes successfully: stock drops from 5 to 0. The lock is released.
4. Clerk B's transaction now reads the product row and sees the **updated** stock of 0.
5. Since Clerk B is trying to sell 5 bags but only 0 are available, the system throws an "Insufficient Stock" validation error and cancels Clerk B's sale.

This mechanism guarantees that stock quantities are **always mathematically correct**, regardless of how many users are operating the system simultaneously.

---

## Chapter 4 — Implementation Details

### Module Summary
> **What this chapter is about:** This chapter goes into the technical implementation of how the system was actually built — how the user interface works, what each backend module (controller) is responsible for, and the internal logic of the two most complex features: face recognition matching and memory-efficient report exporting. A software graduate reading this should be able to understand the architecture and reproduce it.

---

### 4.1 Front-End Stack & UI Design Patterns

The user interface is built with **Laravel Blade** templates — server-side HTML templates processed by PHP before being sent to the browser as complete HTML pages.

- **Tailwind CSS** provides a utility-first styling system. Spacing, colors, typography, and responsive layouts are all applied using predefined utility classes directly in the HTML, eliminating the need for separate custom CSS files for most components.
- **Alpine.js** is used for client-side interactivity. It handles behaviors such as: opening and closing modals, toggling the visibility of form sections, switching between tabs, and displaying dynamic product rows when items are added to an invoice — all without requiring a full page reload.
- **Camera Access** is handled using the browser's standard `getUserMedia()` JavaScript API. This API works in modern browsers (Chrome, Edge, Firefox) on any device with a webcam — no browser extensions or additional software is required.
- **Barcode Scanning** runs in a continuous loop: the camera feed is drawn frame by frame onto a hidden HTML canvas element. The `HTML5-QRCode` library processes each frame looking for barcode stripe patterns. When detected, the barcode value is extracted and the product lookup request is triggered immediately.
- **Face Recognition Models** — the neural network weight files required by `face-api.js` (SSD MobileNet V1 for detection, Face Landmark 68 Net for landmark detection, and Face Recognition Net for descriptor extraction) — are stored and served from the Laravel application's `public/` directory. This ensures they load locally and quickly without depending on any external CDN or third-party server.

### 4.2 Back-End Services & Core Logic

The backend is organized into **Controllers** (one per module) and a dedicated **Services** folder for complex reusable logic. Each controller is responsible for handling HTTP requests, validating input, running business logic, and returning the appropriate response (an HTML view or a redirect).

**Complete List of Controllers and Their Responsibilities:**

| Controller | Responsibility |
|---|---|
| `DashboardController` | Aggregates and displays key metrics: total sales, cash vs. credit breakdown, total profit, pending dues, out-of-stock count, low-stock product list, recent sales, and a 6-month sales trend chart. Uses Laravel Cache with a 5-minute expiry to avoid heavy aggregate queries on every visit. |
| `ProductController` | Full product CRUD: create, list (with search and pagination), edit, update, and delete products. Manages image uploads to `storage/app/public/products/`. Old images are deleted automatically when a product is updated or removed. Products cannot be deleted if they have existing sales or purchase records (data protection). |
| `CategoryController` | Creates and manages product categories. Categories cannot be deleted if products are assigned to them. |
| `SupplierController` | Creates and manages supplier records (name, phone, email, address). Suppliers cannot be deleted if purchase records exist for them. |
| `CustomerController` | Creates and manages customer records. Supports optional customer photo uploads. Customers cannot be deleted if sales records exist for them. |
| `PurchaseController` | Records multi-item stock purchases. Applies `lockForUpdate()` on each product, increases stock quantity, updates the product's purchase price to the latest unit cost, and generates a downloadable PDF purchase invoice using DomPDF. |
| `SaleController` | Records multi-item sales with full stock validation. Applies `lockForUpdate()` on each product, validates sufficient stock, calculates per-item profit, supports Cash / Partial / Credit payment types, creates an initial credit payment record for partial payments, and generates a downloadable PDF sale invoice. |
| `CreditPaymentController` | Receives payments against outstanding credit or partial sales. Caps the received payment at the current due amount (cannot overpay). Updates `paid_amount` and `due_amount` on the sale record. Automatically marks the sale's payment status as "paid" when the full balance is settled. |
| `ReportController` | Displays date-filtered sales reports with summary statistics. Exports sales data as streamed CSV (line-by-line streaming prevents PHP memory exhaustion on large datasets). Also exports combined reports as `.xlsx` Excel files using the Maatwebsite Excel package. |
| `AttendanceController` | Manages face profile enrollment (accepts 3–10 descriptor samples per user and stores them as a JSON array). Handles live face scan requests by calling `FaceRecognitionService`. Records check-in and check-out attendance atomically within a database transaction. |
| `UserController` | Full user account management: create accounts, assign roles and individual permissions using Spatie Laravel Permission, update account details, reset passwords, activate/deactivate accounts, and delete users (cannot self-delete). |
| `BarcodeController` | Two functions: (1) generates and serves a Code128 barcode PNG image for a given product on demand; (2) handles the `/product-by-barcode/{code}` lookup endpoint that returns product JSON data to the browser scanner. |

**Two Key Custom Modules Explained in Depth:**

---

**Module 1: FaceRecognitionService**
File: `app/Services/FaceRecognitionService.php`

This is the core server-side class that performs face identity matching. It is injected automatically into `AttendanceController` via Laravel's dependency injection system. When the browser sends a face descriptor (an array of 128 floating-point numbers) to the server, this service performs the following steps:

**Step 1 — Load Active Profiles:**
```
FaceProfile::where('is_active', true)
           ->whereHas('user', fn($q) => $q->where('is_active', true))
           ->get()
```
Only profiles belonging to active users are loaded. This prevents deactivated employees from being matched.

**Step 2 — Compare Descriptors:**
For every loaded profile, and for every saved sample descriptor within that profile, the service calculates the Euclidean distance between the incoming probe and the stored sample:

```
distance = sqrt( sum( (probe_i - sample_i)^2 ) )
```

A distance of 0.0 would mean a perfect mathematical match (practically impossible in real conditions). A distance below 0.48 indicates sufficient similarity to confirm identity.

**Step 3 — Select Best Match:**
The service tracks the (profile, distance) pair with the lowest distance found across all comparisons. If this minimum distance is below the threshold of **0.48**, the matched user is returned. If no match falls below the threshold, the service returns `null` and the attendance scan fails gracefully with a user-facing message.

The threshold of 0.48 was determined through manual testing across multiple users and lighting conditions. It balances security (preventing false positive matches) with usability (accepting legitimate scans under minor lighting or angle variations).

---

**Module 2: Streamed CSV Export**
File: `app/Http/Controllers/ReportController.php`

When a manager exports a large date range of sales data, the naive approach would be to load all matching records into PHP memory as an array, then write the CSV file. For datasets with thousands of rows, this can trigger PHP's memory limit, causing the export to crash with a fatal error.

The `exportSalesCsv()` and `exportCreditCustomersCsv()` methods solve this by using **Symfony's `StreamedResponse`** — a response type that writes data directly to the HTTP download stream as it is generated, rather than buffering everything in memory first:

```
return response()->stream(function() use ($sales) {
    $file = fopen('php://output', 'w');
    fputcsv($file, ['Invoice', 'Date', 'Customer', ...]);  // write header
    foreach ($sales as $sale) {
        fputcsv($file, [...]);  // write one row at a time
    }
    fclose($file);
}, 200, $headers);
```

The browser receives and saves each chunk as it arrives. PHP's memory usage remains constant regardless of dataset size because only one row is in memory at a time.

---

## Chapter 5 — Testing, Deployment & User Guide

### Module Summary
> **What this chapter is about:** This chapter covers three things: (1) how the system was tested to verify correctness, (2) exact commands needed to install and run the system on a new server, and (3) a clear step-by-step guide showing how each type of user operates the system in their daily work. Even someone who has never used this system before should be able to follow these instructions.

---

### 5.1 Verification Checklist

Testing was performed across three levels to verify correctness at different granularities:

**Unit Testing (Individual Logic):**
- Verified the Euclidean distance calculation inside `FaceRecognitionService` with known input arrays where the expected output distance was pre-calculated by hand. Confirmed that the service correctly returns `null` when no match falls below the threshold.
- Confirmed that the credit payment capping logic works correctly: if a customer owes Rs. 500 and a payment of Rs. 700 is submitted, the system records only Rs. 500 (the actual due amount) and marks the sale as fully paid — not Rs. 700.
- Verified that the sale validation correctly rejects a quantity greater than the available stock and returns a meaningful error message identifying the specific product.

**Integration Testing (Module Interactions):**
- Confirmed that saving a new sale correctly decreases each product's `stock_quantity` in the database by the sold quantity.
- Confirmed that saving a new purchase correctly increases each product's `stock_quantity` and also updates the product's `purchase_price` to the latest unit cost paid.
- Verified that deleting a sale record correctly restores the stock back to its pre-sale level (the delete operation reverses the stock deduction).
- Verified that deleting a purchase record correctly reduces the stock back down by the previously added quantity (capped at 0 — stock cannot go negative from a delete operation).

**End-to-End (E2E) Testing (Full System):**
- Tested barcode scanning on **Google Chrome**, **Microsoft Edge**, and **Mozilla Firefox**. The camera activated correctly in all three. Product lookup from scan-to-display was consistently under 200ms on a local server.
- Tested face enrollment and recognition under different lighting conditions: bright overhead lighting, dim room lighting, and side-lit face. Recognition was reliable when the room had adequate light and the user faced the camera directly. Recognition rate decreased in very dim conditions, as expected for camera-based systems.
- Tested the concurrency scenario manually: two browser tabs were used to submit a sale of the same product simultaneously. Confirmed that database row locking prevented the double-deduction — one tab received a success response and the other received an "Insufficient Stock" error.

### 5.2 Installation & Server Configuration

**System Prerequisites (must be installed before setup):**
- **PHP 8.2 or higher** — the server-side programming language
- **Composer** — PHP's package manager (downloads Laravel and all PHP libraries)
- **Node.js 18+** — needed to run Vite (the frontend asset bundler)
- **npm** — Node.js package manager (comes bundled with Node.js)
- **MySQL 5.7+ or MariaDB 10.3+** — the relational database server

**Step-by-Step Deployment Commands:**

```bash
# Step 1: Download all PHP dependencies (Laravel, DomPDF, Spatie, etc.)
composer install

# Step 2: Download all JavaScript dependencies (Alpine.js, face-api.js, etc.)
npm install

# Step 3: Create the local environment configuration file
cp .env.example .env

# Step 4: Generate a unique application encryption key
php artisan key:generate

# Step 5: Open the .env file in any text editor and configure the database:
#   DB_DATABASE=your_database_name
#   DB_USERNAME=your_mysql_username
#   DB_PASSWORD=your_mysql_password
# (Create the empty database in MySQL first before running the next step)

# Step 6: Build and optimize all frontend CSS and JavaScript assets
npm run build

# Step 7: Create all database tables and insert default seed data
php artisan migrate --seed

# Step 8: Create a symbolic link so uploaded files are publicly accessible
php artisan storage:link

# Step 9: Start the local development server
php artisan serve
```

After completing these steps, open a browser and go to `http://127.0.0.1:8000`.

**Default Login Credentials:** The database seeder creates a default administrator account. Check the file `database/seeders/DatabaseSeeder.php` for the email and password defined in the seed data. It is strongly recommended to change the default password immediately after the first login.

### 5.3 Quick-Start User Manual

The following guide provides step-by-step instructions for each user type.

---

**For Administrators — System Setup:**

**Step 1 — Create Categories:**
Navigate to **Categories** in the top menu and click **Create New Category**. Enter a descriptive name (e.g., "Fertilizers", "Pesticide Sprays", "Growth Hormones") and click Save. Categories organize products and are required before products can be added.

**Step 2 — Add Products:**
Navigate to **Products → Create**. Fill in all required fields:
- **Name:** Full product name (e.g., "DAP Fertilizer 50kg")
- **Category:** Select from the categories you created.
- **Brand:** Manufacturer name (optional).
- **Unit:** The measuring unit (e.g., "bag", "kg", "litre", "bottle").
- **Purchase Price:** The cost price the shop pays per unit.
- **Selling Price:** The retail price charged to customers (must be ≥ purchase price).
- **Stock Quantity:** Current number of units in stock.
- **Low Stock Threshold:** When stock drops to or below this number, a warning appears on the dashboard. (e.g., set to 10 to get alerted when fewer than 10 bags remain).
- **Barcode:** Enter or scan a unique barcode number (optional but recommended for scanner use).
- **Product Image:** Upload a photo of the product (optional).
Click **Save Product**. The product is now available in the catalog.

**Step 3 — Add Suppliers:**
Navigate to **Suppliers → Create**. Enter the supplier's company name, phone number, email address, and physical address. Mark as Active and click Save.

**Step 4 — Add Customers (optional, required for credit sales):**
Navigate to **Customers → Create**. Enter the customer's name, phone number, and optionally an address and photo. Click Save.

**Step 5 — Manage Users:**
Navigate to **Users**. Click **Add New User**, enter the user's name, email, and a temporary password. Assign appropriate **roles** (e.g., "Clerk") and any additional individual **permissions**. Click Save. The user can now log in.

**Step 6 — Enroll a User's Face for Attendance:**
Navigate to **Users**, find the target user in the list, and click the **Enroll Face** button next to their name. On the enrollment page, instruct the user to sit directly in front of the webcam in a well-lit area. Click the **Start Camera** button. The system will automatically capture 5–10 face samples over a few seconds and save the profile. A success message confirms enrollment.

---

**For Store Clerks — Daily Operations:**

**Step 7 — Record a Purchase from a Supplier:**
Navigate to **Purchases → Create**.
- Select the **Supplier** from the dropdown.
- Enter the **Purchase Date**.
- Click **Add Product** to add a product line. Select the product from the dropdown, enter the quantity received, and enter the unit cost (price paid per unit).
- Repeat for all products in the delivery.
- Add any optional notes.
- Click **Save Purchase**. Stock levels increase automatically. Click **Download Invoice** to get the PDF.

**Step 8 — Record a Sale to a Customer:**
Navigate to **Sales → Create**.
- **To add a product by barcode scan:** Click the camera icon. Point the webcam at the printed barcode label. The product appears in the invoice automatically.
- **To add a product manually:** Use the category tab and product dropdown to select the product. Enter the quantity.
- Select the **Customer** (required for credit/partial sales) or leave blank for a walk-in cash customer.
- Select the **Payment Type:**
  - **Cash** — customer pays the full amount now.
  - **Credit** — customer will pay the full amount later (creates an outstanding balance).
  - **Partial** — customer pays a portion now; the remainder is recorded as an outstanding balance.
- For Partial payments, enter the **amount paid now** in the designated field.
- Enter any discount (optional) and notes.
- Click **Save Sale**. Stock decreases automatically. Click **Download Invoice** for the PDF.

**Step 9 — Record a Credit Payment:**
Navigate to **Credit Payments**. This page lists all credit and partial sales with outstanding balances.
- Use the search bar to find the customer or invoice number.
- Click **Add Payment** next to the relevant sale.
- Enter the payment date and the amount the customer is paying today.
- Click **Save**. The sale's due balance updates automatically. If the full balance is now paid, the sale is marked as "Paid."

---

**For Students / Staff — Marking Attendance:**

**Step 10 — Check In or Check Out:**
Click **Attendance** from the main navigation bar. The camera activates automatically. Look directly at the webcam and hold your face steady for 1–2 seconds. The system will display one of these results:
- ✅ *"Check-in marked successfully"* — Your arrival time for today has been logged.
- ✅ *"Check-out marked successfully"* — Your departure time for today has been logged.
- ℹ️ *"Attendance already completed for today"* — Both check-in and check-out are already recorded for you today.
- ❌ *"Face not recognized"* — Ensure the room is well-lit and your face is directly facing the camera. Ask an administrator to re-enroll your face profile if the problem persists.

---

**For Managers — Reports and Monitoring:**

**Step 11 — View Sales Reports:**
Navigate to **Reports**. Use the **From** and **To** date pickers to select the reporting period. The page displays:
- Total sales revenue, broken down into cash sales and credit sales.
- Total amount collected (paid) vs. total outstanding dues.
- A summary table of recent sales with invoice details.
- A list of customers with outstanding credit balances, ordered by the highest due amount.
- A list of products currently at or below their low-stock threshold.

Click **Export CSV** to download raw sales data as a spreadsheet-compatible CSV file. Click **Export Excel** to download a formatted `.xlsx` Excel workbook containing multiple report sheets.

**Step 12 — Monitor Low Stock via Dashboard:**
The main **Dashboard** page (shown after login) always displays a "Low Stock Products" section listing every product whose current stock is at or below the configured threshold. This section is also accessible from the **Products** list, where low-stock items are marked with a visible warning badge.

---

## Chapter 6 — Conclusion & Future Work

### Module Summary
> **What this chapter is about:** This chapter provides a final summary of everything the project achieved — what was built, what problems were solved, and how successfully the goals were met. It also honestly identifies limitations of the current version and proposes concrete, technically justified improvements for future development.

---

### 6.1 Achievements Summary

The **Agriculture Tracker** successfully transitions the inventory management and attendance tracking processes of small agricultural businesses from error-prone manual methods to a reliable, digital, web-based platform. The following goals were achieved:

- **Barcode scanning** fully eliminates manual product searching and typing during sales transactions, reducing checkout time and removing a major source of data entry errors.
- **Face recognition attendance** provides a completely touchless and hardware-free biometric check-in/check-out system. It removes the possibility of proxy attendance and does not require any fingerprint scanners or attendance terminals.
- **Pessimistic database row locking** ensures that concurrent stock operations never result in negative or incorrect inventory counts, solving the race condition problem that affects all multi-user inventory systems.
- **Streamed data export** allows report downloads of any size without risk of PHP memory exhaustion, making the reporting module production-ready for high-volume use.
- **User privacy** is protected throughout — the face recognition system stores only 128-dimensional mathematical descriptors, never actual photographs, video frames, or biometric images of any kind.
- **Role-based access control** ensures that each user sees only the modules relevant to their job function, keeping the interface clean, simple, and secure.
- The system runs on **any standard computer with a webcam and a web browser** — no specialized hardware, no installed software, and no recurring hardware licensing costs.

### 6.2 Future Enhancements

The following improvements are identified as realistic and valuable additions for future versions of the system:

1. **Anti-Spoofing / Liveness Detection for Face Recognition:**
   The current implementation does not distinguish between a real, live face and a printed photograph of that face held up to the camera. A future version should implement **liveness detection** by prompting the user to perform a random action (e.g., blink, turn their head left, or smile) before accepting the face scan. This would prevent spoofing attacks using photographs.

2. **Faster Face Matching Using Vector Indexing:**
   The current face matching algorithm performs a linear scan — it compares the incoming descriptor against every stored descriptor one by one. This is acceptable for systems with tens to low hundreds of enrolled users. However, as the number of enrolled users grows into the thousands, this approach becomes slow. Integrating a dedicated vector similarity search library such as **FAISS** (by Meta AI Research) would enable approximate nearest-neighbor matching in logarithmic time, dramatically improving scan speed at scale.

3. **Offline Support with Background Sync:**
   Currently, if the internet connection is interrupted, clerks are unable to record sales or attendance until connectivity is restored. A future enhancement could use the browser's **IndexedDB** API (client-side database storage) to queue transactions locally while offline. A background sync service would then automatically push the queued records to the server when the connection is re-established.

4. **Mobile Companion Application:**
   A dedicated mobile app (built with React Native or Flutter) would allow managers to monitor the dashboard, receive push notifications for low-stock alerts, and approve large transactions remotely from their smartphones, without needing to open a browser.

5. **Automated Low-Stock Reorder Notifications:**
   The current system displays a visual warning on the dashboard when a product's stock falls below the threshold. A future enhancement could trigger automatic **email or SMS notifications** to the manager or a designated supplier whenever stock drops below the threshold — ensuring that restocking decisions are made proactively before a product runs out completely.

6. **Sales Analytics & Profit Trend Reporting:**
   The current reports module focuses on sales amounts and dues. A future version could add deeper analytics: monthly profit trend graphs, best-selling products rankings, seasonal demand patterns, and supplier pricing comparison over time — helping managers make more informed purchasing and pricing decisions.
