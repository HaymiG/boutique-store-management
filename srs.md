Software Requirements Specification (SRS)
Boutique Store / Inventory Management System
1. Introduction
1.1 Purpose
This document defines the requirements for a Boutique Store Management System designed to manage inventory, sales, branches, and user roles efficiently.
1.2 Scope
The system will:
• Manage boutique inventory (items, stock levels, pricing)
• Track sales transactions
• Support multiple branches
• Provide reporting (daily, weekly, monthly, custom range)
• Support role-based access for Manager, Store Keeper, and Seller
1.3 Definitions
• Inventory: List of items available in the boutique
• Stock: Quantity of each item
• Branch: Physical boutique location
• Actor: System user with defined role
2. Overall Description
2.1 Product Perspective
The system is a web-based or desktop application used internally by boutique staff.
2.2 User Classes (Actors)
1. Manager
• Full system control
• Strategic and administrative functions
2. Store Keeper
• Inventory management
• Stock tracking
3. Seller (Salesperson)
• Sales transactions
• Customer interaction
3. System Features & Functional Requirements
3.1 Manager Functional Requirements
Branch Management
• Create new branch
• Update branch details
• Delete branch
• View all branches
User Management
• Create user accounts (Store Keeper, Seller)
• Assign roles and permissions
• Activate/deactivate users
Inventory Management
• View all items across branches
• Update item price
• View remaining stock per branch
• Transfer items between branches
Sales Monitoring
• View daily sales report
• View weekly sales report
• View monthly sales report
• View sales by custom date range
• View sales by branch
• View sales by seller
Reports & Analytics
• Generate sales reports
• Generate inventory reports
• Identify fast-moving and slow-moving items
• Profit and revenue analysis
3.2 Store Keeper Functional Requirements
Inventory Operations
• Add new items to store
• Update item details (name, category, quantity)
• View all items
• View remaining stock
Stock Management
• Update stock quantity
• Receive new stock
• Record damaged/expired items
• Transfer stock (if permitted)

Sales Monitoring
• View own daily sales
• View weekly sales
• View monthly sales
Reports
• Generate inventory reports
• View stock alerts (low stock)
3.3 Seller Functional Requirements
Sales Processing
• Record new sales transaction
• Select items and quantity
• Generate receipt
• Apply discounts (if permitted)
Sales Tracking
• View own daily sales
• View weekly sales
• View monthly sales
Inventory Access
• View available items
• Check item price
• Check stock availability
3.4 General Functional Requirements (All Roles)
• User login and authentication
• Role-based access control
• Password management
• Logout functionality
4. Non-Functional Requirements
4.1 Performance
• System should respond within 2 seconds for most operations
• Support multiple users concurrently
4.2 Security
• Role-based authorization
• Secure login (username/password encryption)
• Data protection and backup
4.3 Usability
• Simple and user-friendly interface
• Easy navigation
• Minimal training required
4.4 Reliability
• System uptime of at least 99%
• Automatic backup and recovery
4.5 Scalability
• Support multiple branches
• Handle increasing number of users and items
4.6 Maintainability
• Modular system design
• Easy to update and extend
4.7 Compatibility
• Accessible via web browsers or desktop
• Cross-platform support (Windows, Linux)
5. Use Case Summary 
Actor                 Use Case Examples
Manager   Manage branches,   view  reports, update prices
Store Keeper    Add items, manage stock,view inventory
Seller.           Process sales,generate receipt
the system must be built using only html,css and js for fronend
php and mysql for backend
