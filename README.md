# Driveway Manager - Vehicle Rental Management System

## 📁 Project Structure

```
driveway_manager/
│
├── config/                      # Configuration files
│   ├── db.php                  # Database connection
│   └── auth.php                # Authentication & authorization functions
│
├── includes/                    # Reusable templates
│   ├── admin_header.php        # Admin sidebar layout (Purple gradient)
│   ├── customer_header.php     # Customer sidebar layout (Green gradient)
│   └── footer.php              # Footer template
│
└── public/                      # Public accessible files
    │
    ├── index.php               # Landing page
    ├── login.php               # Login page
    ├── register.php            # Registration page
    ├── logout.php              # Logout handler
    ├── dashboard.php           # Main dashboard (Admin/Customer)
    │
    ├── admin/                  # Admin-only pages
    │   ├── bookings.php        # View all bookings with payment status
    │   ├── manage_customers.php # Customer CRUD (list, edit, delete)
    │   ├── maintenance.php     # Add/view vehicle maintenance records
    │   ├── maintenance_reminders.php # Overdue & upcoming maintenance alerts
    │   ├── vehicle_history.php # Vehicle stats, booking & maintenance history
    │   ├── vehicle_report.php  # Downloadable vehicle report (PDF)
    │   └── notifications.php   # Admin notifications (booking & payment alerts)
    │
    ├── booking/                # Customer booking pages
    │   ├── vehicles.php        # Browse/search vehicles by date (card layout)
    │   ├── book.php            # Book a vehicle (double booking prevention)
    │   ├── payment.php         # Payment page with card details
    │   ├── my_bookings.php     # View bookings, pay, cancel
    │   ├── invoice.php         # Downloadable payment invoice (PDF)
    │   └── cancel.php          # Cancel booking handler
    │
    ├── customer/               # Customer settings
    │   └── settings.php        # Profile, password, dark mode toggle
    │
    ├── vehicles/               # Vehicle management (Admin)
    │   └── manage.php          # Single file CRUD (list, create, edit, delete)
    │
    └── uploads/                # Uploaded files
        └── vehicles/           # Vehicle images

```

## 🗄️ Database Tables

### 1. users
```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'customer') DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 2. vehicles
```sql
CREATE TABLE vehicles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  brand VARCHAR(50) NOT NULL,
  model VARCHAR(50) NOT NULL,
  year INT NOT NULL,
  price_per_day DECIMAL(10,2) NOT NULL,
  status ENUM('available', 'booked', 'maintenance') DEFAULT 'available',
  image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3. bookings
```sql
CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  vehicle_id INT NOT NULL,
  pickup_date DATE NOT NULL,
  return_date DATE NOT NULL,
  total_cost DECIMAL(10,2) NOT NULL,
  status ENUM('confirmed', 'cancelled') DEFAULT 'confirmed',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);
```

### 4. payments
```sql
CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  card_number VARCHAR(20),
  card_holder VARCHAR(100),
  payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status VARCHAR(20) DEFAULT 'completed',
  FOREIGN KEY (booking_id) REFERENCES bookings(id)
);
```

### 5. notifications
```sql
CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message TEXT NOT NULL,
  type VARCHAR(50),
  is_read TINYINT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### 6. maintenance
```sql
CREATE TABLE maintenance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  service_type VARCHAR(100) NOT NULL,
  description TEXT,
  service_date DATE NOT NULL,
  cost DECIMAL(10,2),
  next_service_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);
```

## 🎯 Features

### 👨💼 Admin Features
- ✅ **Vehicle Management** - Single file CRUD with image upload
- ✅ **Booking Management** - View all bookings with payment status (Paid/Unpaid)
- ✅ **Customer Management** - Edit user details, change role, reset password, delete users
- ✅ **Maintenance Tracking** - Add service records, track costs, set next service dates
- ✅ **Maintenance Reminders** - Overdue alerts (red) & upcoming alerts (yellow)
- ✅ **Vehicle History** - Complete stats: total bookings, revenue, maintenance cost
- ✅ **Vehicle Reports** - Downloadable PDF reports with complete vehicle details
- ✅ **Notifications** - Real-time alerts for new bookings & payments (unread badge)
- ✅ **Settings** - Update profile, change password, dark mode toggle

### 👤 Customer Features
- ✅ **Browse Vehicles** - Modern card layout with images, date-based search
- ✅ **Smart Search** - Shows only available vehicles for selected dates
- ✅ **Book Vehicles** - Double booking prevention with date overlap check
- ✅ **Payment System** - Secure card payment with validation
- ✅ **My Bookings** - View all bookings with payment status
- ✅ **Pay Now** - Pay for unpaid bookings anytime
- ✅ **Invoice Download** - Downloadable PDF invoice for paid bookings
- ✅ **Cancel Booking** - Cancel confirmed bookings
- ✅ **Settings** - Update profile, change password, dark mode toggle

## 🔒 Security Features
- ✅ Password hashing (bcrypt)
- ✅ Role-based access control (admin/customer)
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (htmlspecialchars)
- ✅ Session management
- ✅ Double booking prevention

## 🎨 Design & UI
- **Framework:** Bootstrap 5.3.3
- **Icons:** Bootstrap Icons
- **Admin Sidebar:** Purple gradient (linear-gradient(135deg, #667eea 0%, #764ba2 100%))
- **Customer Sidebar:** Green gradient (linear-gradient(135deg, #11998e 0%, #38ef7d 100%))
- **Layout:** Responsive card-based design
- **Features:** Hover effects, shadow effects, modern UI/UX

## 🚀 Installation

### Step 1: Database Setup
1. Create database in phpMyAdmin:
```sql
CREATE DATABASE driveway_manager;
```

2. Run all table creation queries (see Database Tables section above)

### Step 2: Configuration
Update `config/db.php` with your database credentials:
```php
$host = "localhost";
$dbname = "driveway_manager";
$user = "root";
$pass = ""; // Your MySQL password
```

### Step 3: Access
Navigate to: `http://localhost/driveway_manager/public/`

### Step 4: Create Admin Account
1. Register a new account
2. Manually update role in database:
```sql
UPDATE users SET role='admin' WHERE email='your@email.com';
```

## 📊 File Count
- **Config:** 2 files
- **Includes:** 3 files
- **Admin:** 7 files
- **Booking:** 6 files
- **Customer:** 1 file
- **Vehicles:** 1 file (Single CRUD file)
- **Main:** 5 files

**Total: 25 PHP files** - Clean & organized structure

## 🔄 CRUD Operations
All CRUD operations use single-file pattern:
- **Vehicles:** `vehicles/manage.php?action=list|create|edit|delete`
- **Customers:** `admin/manage_customers.php?action=list|edit|delete`

## 📱 Responsive Design
- Mobile-friendly navigation
- Responsive grid layouts
- Touch-friendly buttons
- Optimized for all screen sizes

## 🌙 Dark Mode
- Cookie-based dark mode toggle
- Available in Settings page
- Persists across sessions

## 📧 Notifications System
- Auto-notification on new booking
- Auto-notification on payment
- Unread count badge in sidebar
- Mark all as read functionality

## 🔧 Maintenance System
- Service type dropdown (Oil Change, Tire Rotation, etc.)
- Cost tracking
- Next service date reminders
- Overdue & upcoming alerts
- Complete service history

## 💳 Payment System
- Card number, holder name, expiry, CVV
- Payment status tracking (Paid/Unpaid)
- Pay Now button for unpaid bookings
- Downloadable invoice for paid bookings

## 📈 Vehicle History & Reports
- Total bookings count
- Total revenue calculation
- Maintenance records count
- Total maintenance cost
- Net profit calculation
- Tabbed interface (Bookings/Maintenance)
- Downloadable PDF reports

## 📄 Invoice System
- Professional invoice layout
- Customer & vehicle information
- Detailed breakdown of charges
- Payment information (if paid)
- Downloadable as PDF
- Print-friendly format

## 🎓 University Project Notes
- Clean code structure
- Single responsibility principle
- Separation of concerns
- Professional coding standards
- Well-documented
- Easy to understand and maintain
- Single-file CRUD pattern for simplicity

## 🛠️ Technologies Used
- **Backend:** PHP 8.x
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript
- **Framework:** Bootstrap 5.3.3
- **Icons:** Bootstrap Icons 1.11.3
- **Architecture:** MVC-inspired structure
- **Reports:** HTML to PDF (Print functionality)

## 📝 License
University Project - Educational Use Only

---
**Developed for:** University Project  
**Version:** 1.0  
**Last Updated:** 2024
