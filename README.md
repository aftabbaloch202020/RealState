# RealHome — Production Full-Stack Real Estate Marketplace

RealHome is a complete, enterprise-grade, full-stack real estate marketplace built with PHP 8+, MySQL (PDO), JavaScript, HTML5, CSS3, and Bootstrap 5. It closely follows the reference UI design with an emerald green, dark navy, and clean white luxury aesthetic.

---

## 🌟 Key Features

### 1. Public Marketplace
* **Hero Section:** Luxury illuminated glass villa hero section with headline *"Find the Perfect Property for Your Future"*.
* **Floating Advanced Search:** Real-time filters for Buy vs Rent, Location/City, Property Category, Price Range presets, Bedrooms, Bathrooms, and Amenities.
* **Trust & Benefits Strip:** 5 core indicators: Verified Properties, Secure Transactions, 24/7 Support, Best Deals, Prime Locations.
* **Featured Properties & Investment Promo Card:** Exact 5-card layout matching the reference UI with real photography.
* **Popular Locations:** Rich landmark cards for Karachi, Lahore, Islamabad, Bahria Town, and DHA with live database property counts.
* **Explore Categories:** Image cards for Houses & Villas, Luxury Apartments, Penthouse Suites, Commercial & Offices, and Residential Plots.
* **Exclusive Luxury Properties:** Editorial cards for high-end villas and penthouses.
* **Recently Added & Recommended Properties:** Database-driven listings grids.
* **Properties by Lifestyle:** Family Living, Urban Living, Luxury Penthouses, Commercial Assets.
* **Trending Neighborhoods:** Enclave cards for DHA Phase 9, Sector C Bahria Town, F-11 Islamabad, Gulberg III, Clifton Block 4.
* **Featured Agents:** Certified consultant cards with listing counts, call, email, and WhatsApp buttons.
* **How RealHome Works:** 4-step buyer and tenant journey cards.
* **Why Choose RealHome:** Benefits grid + Mint CTA card matching reference design.
* **Market Insights:** Live database metrics including verified listings, cities covered, active advisors.
* **Customer Testimonials:** Real client reviews from MySQL database with 5-star ratings.
* **FAQ Accordion:** Interactive expand/collapse answers to common buyer, renter, and owner questions.
* **Real Estate Guides & Blog:** Articles with categories, reading time, and author info.
* **List Your Property CTA:** High-conversion banner inviting property owners and brokers.
* **Newsletter Subscription:** AJAX subscription storing verified emails in database with toast notifications.
* **Back to Top Control:** Floating smooth-scroll button.

### 2. Complete Property Details Page (`property-details.php`)
* Main high-resolution image viewer with interactive thumbnail switcher.
* Key specifications strip: Bedrooms, Bathrooms, Area (Sq Ft/Marla), Garages, Year Built.
* Full formatted description.
* Amenities & features checklist with icons.
* Location & neighborhood preview.
* Direct consultant card with phone, email, WhatsApp, and inquiry modal.
* **Schedule a Visit Modal:** Online viewing date/time picker with AJAX backend booking.
* **Direct Inquiry Form:** AJAX submission notifying the listing agent.
* **Customer Reviews:** Approved reviews list and authenticated review submission form.
* **Recently Viewed Properties:** Saved in `localStorage` and displayed on the homepage.
* Related properties recommendations.

### 3. Role-Based Portals & Dashboards
* **Customer Portal (`/user`):**
  - Dashboard overview with KPI cards (Saved Homes, Sent Inquiries, Booked Visits, Alerts).
  - Saved homes with AJAX bookmarking & instant navbar counter updates.
  - Inquiries tracking with agent responses.
  - Property tour visits with confirmation status.
  - Submitted reviews tracking.
  - Notifications list with read status.
  - Profile settings with avatar upload and password change.
* **Agent / Owner Portal (`/agent`):**
  - Overview of listings, approval status, inquiries, and scheduled visits.
  - **Add Property:** Complete form with multiple image uploads, specifications, pricing, and amenities checklist.
  - **Edit Property:** Update details, pricing, photos, and features.
  - **Inquiries Manager:** View leads and reply directly to clients.
  - **Visits Manager:** Confirm, complete, or cancel viewing appointments.
  - Agent branding & profile management.
* **Administrator Control Center (`/admin`):**
  - Overview dashboard with Chart.js charts (Categories bar chart, Purpose doughnut chart).
  - Listing moderation: Approve, Reject, Feature/Unfeature, Delete.
  - Property inspection preview modal before approval.
  - User accounts manager: Change roles (Customer/Agent/Admin), toggle status (Active/Suspended), Delete.
  - Categories management: Add/delete property categories.
  - Locations management: Add/delete cities and toggle "Popular Locations" status.
  - Inquiries & Visits moderation.
  - Reviews moderation: Approve, reject, delete.
  - Blog post manager: Create new articles with rich text & cover photo.
  - Contact messages manager: Read and delete customer inquiries.
  - System settings manager: Site name, slogan, phone, email, address, social links.
  - Security audit activity logs.

---

## 🔒 Security Architecture
* **Password Hashing:** Passwords secured with PHP `password_hash(..., PASSWORD_BCRYPT)`.
* **Prepared SQL Statements:** 100% of queries use PDO prepared statements to prevent SQL Injection.
* **CSRF Protection:** Tokens generated and verified on all state-changing POST requests.
* **Input Sanitization & XSS Prevention:** HTML entities sanitization via `sanitize_input()` and `e()`.
* **Session Security:** Session ID regeneration upon login, `HttpOnly`, `SameSite=Lax` cookies.
* **Secure File Uploads:** Validates MIME types (`finfo`), whitelists file extensions, enforces 8MB limit, and blocks script execution in `/uploads/.htaccess`.
* **Role-Based Access Control:** Strict `require_role()` middleware on all protected pages.

---

## 🛠️ Technology Stack
* **Frontend:** HTML5, CSS3, JavaScript (Vanilla ES6+), Bootstrap 5.3, Bootstrap Icons, Chart.js.
* **Backend:** PHP 8+ (Clean structure, PDO Singleton, Helpers, REST-like JSON endpoints).
* **Database:** MySQL 10.4+ / MariaDB (`realhome_db`).
* **Environment:** XAMPP on Windows.

---

## 🚀 Installation & Setup Instructions

### Step 1: Start XAMPP Services
1. Open the **XAMPP Control Panel**.
2. Click **Start** for **Apache**.
3. Click **Start** for **MySQL**.

### Step 2: Database Setup
1. Open your browser and navigate to **phpMyAdmin**:
   ```
   http://localhost/phpmyadmin/
   ```
2. Create a new database named `realhome_db` (Collation: `utf8mb4_unicode_ci`).
3. Click **Import** and select the schema file:
   ```
   C:\xampp\htdocs\RealHome\database\schema.sql
   ```
4. Then import the seed data file:
   ```
   C:\xampp\htdocs\RealHome\database\seed.sql
   ```
*(Or via MySQL command line:)*
```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root -e "source C:/xampp/htdocs/RealHome/database/schema.sql"
& "C:\xampp\mysql\bin\mysql.exe" -u root -e "source C:/xampp/htdocs/RealHome/database/seed.sql"
```

### Step 3: Access the Website
Open your browser and navigate to:
```
http://localhost/RealHome/
```
*(Also accessible at `http://localhost/RealState/`)*

---

## 👥 Demo Login Credentials

The database comes pre-seeded with 3 roles ready for testing:

| Role | Email | Password |
|---|---|---|
| **Administrator** | `admin@realhome.com` | `admin123` |
| **Real Estate Agent** | `agent1@realhome.com` | `password123` |
| **Second Agent** | `agent2@realhome.com` | `password123` |
| **Customer / Buyer** | `customer@realhome.com` | `password123` |

*(Tip: On the Login page `login.php`, you can use the **1-Click Quick Demo Fill** buttons to instantly log in as Admin, Agent, or Customer without typing!)*

---

## 📁 Directory Structure
```
RealHome/
├── .htaccess                   # Root Apache rules
├── .env.example                # Sample environment configuration
├── README.md                   # Complete documentation
├── index.php                   # Homepage (23 sections matching reference UI)
├── properties.php              # Property listings & advanced filtering
├── property-details.php        # Listing details, gallery, booking modal, inquiries
├── login.php                   # Authentication sign-in with 1-click demo fill
├── register.php                # Account registration (Customer or Agent)
├── logout.php                  # Session destruction and cleanup
├── forgot-password.php         # Token generation
├── reset-password.php          # Password update verification
├── about.php                   # About us, mission, core pillars
├── services.php                # Real estate advisory services
├── blog.php                    # Blog guides & market insights
├── blog-details.php            # Article reading view
├── contact.php                 # Contact form saving to database
├── privacy.php                 # Privacy policy
├── terms.php                   # Terms of service
├── 403.php, 404.php, 500.php   # Custom error pages
├── config/
│   ├── config.php              # Base URL detection, constants, session config
│   ├── database.php            # PDO singleton database connection
│   └── helpers.php             # Security, CSRF, auth checks, pricing, uploads
├── database/
│   ├── schema.sql              # Normalized relational schema (18 tables)
│   └── seed.sql                # Realistic Pakistani real estate demo records
├── assets/
│   ├── css/
│   │   ├── style.css           # Master RealHome stylesheet matching reference UI
│   │   └── dashboard.css       # Dashboards stylesheet
│   ├── js/
│   │   └── main.js             # AJAX favorites, modals, newsletter, back-to-top
│   └── images/                 # Genuine real estate photography & assets
├── api/
│   ├── toggle-favorite.php     # AJAX toggle favorite endpoint
│   ├── submit-inquiry.php      # AJAX inquiry submission endpoint
│   ├── schedule-visit.php      # AJAX visit booking endpoint
│   ├── submit-review.php       # AJAX review submission endpoint
│   └── newsletter-subscribe.php # AJAX newsletter subscription endpoint
├── uploads/
│   ├── properties/             # Uploaded property images
│   ├── avatars/                # Uploaded profile photos
│   └── .htaccess               # Blocks script execution in uploads
├── user/                       # Customer Portal
│   ├── index.php               # Overview & quick stats
│   ├── favorites.php           # Saved properties
│   ├── my-inquiries.php        # Inquiries & agent replies
│   ├── my-visits.php           # Booked property viewings
│   ├── my-reviews.php          # Reviews submitted
│   ├── notifications.php       # In-app notifications
│   └── profile.php             # Profile settings & password change
├── agent/                      # Property Owner / Agent Portal
│   ├── index.php               # Agent dashboard & KPIs
│   ├── my-properties.php       # Listings management
│   ├── add-property.php        # Listing submission form
│   ├── edit-property.php       # Listing update form
│   ├── inquiries.php           # Leads manager & reply modal
│   ├── visits.php              # Viewing appointments manager
│   └── profile.php             # Agent branding & settings
└── admin/                      # Admin Center
    ├── index.php               # Platform overview with Chart.js
    ├── properties.php          # Listings moderation
    ├── property-view.php       # Property inspection modal
    ├── users.php               # User accounts & roles manager
    ├── categories.php          # Categories manager
    ├── locations.php           # Locations manager
    ├── inquiries.php           # Platform inquiries manager
    ├── visits.php              # All property tours manager
    ├── reviews.php             # Reviews moderation
    ├── blogs.php               # Blog posts publisher
    ├── contacts.php            # Contact messages manager
    ├── settings.php            # Platform settings
    └── activity-logs.php       # Audit logs
```

---

## 🧪 Testing Checklist
- [x] Homepage matches reference screenshot (Navbar, Hero with pills, Search bar, Trust strip, 4 Featured Properties + Invest in Your Tomorrow card, Popular Locations, Why Choose RealHome, Footer).
- [x] All 23 homepage upgrade sections render seamlessly with real MySQL data.
- [x] Property search and filtering work by Location, Category, Purpose, Price, Bedrooms, and Amenities.
- [x] Property details gallery thumbnail switcher, specifications, and related listings work.
- [x] AJAX Favorites toggle updates navbar badge and database without page reload.
- [x] Schedule Visit modal records appointments and alerts the consultant.
- [x] Customer, Agent, and Admin dashboards enforce role-based access control.
- [x] Adding and editing properties with image uploads work smoothly.
- [x] Newsletter subscription AJAX works and stores subscribers in `newsletter_subscribers`.
- [x] Back to Top floating button works on scroll.
- [x] Error handling (403, 404, 500) and CSRF verification on all forms.
