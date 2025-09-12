# Overview

This is a Slovenian beekeeping e-commerce website called "Čebelarstvo Cigoj" (Cigoj Beekeeping). The project is a honey and beekeeping products online store with both customer-facing pages and administrative functionality. It features a modern, responsive design using Tailwind CSS with an amber/brown color scheme that reflects the beekeeping theme.

The application includes a product catalog, shopping cart functionality, and an administrative backend for managing products and orders. The site is designed to sell premium natural honey and beekeeping products to customers while providing business owners with tools to manage their inventory and sales.

# User Preferences

Preferred communication style: Simple, everyday language.

# System Architecture

## Frontend Architecture
The frontend is built as a multi-page static HTML application using:
- **Tailwind CSS** for responsive styling and component design
- **Feather Icons** for consistent iconography throughout the interface
- **AOS (Animate On Scroll)** library for smooth scroll animations
- Vanilla JavaScript for client-side interactivity and cart management

The design follows a consistent amber/brown color palette that reflects the beekeeping theme, with the primary brand color being `amber-800`. The layout is fully responsive and includes:
- Main storefront page (`index.html`)
- Shopping cart page (`cart.html`) 
- Administrative dashboard (`backend.html`)

## Backend Architecture
The backend is designed to be implemented in PHP with:
- **Composer** dependency management
- **Firebase JWT** library for secure authentication and session management
- Server-side cart processing and checkout functionality
- Administrative controls for product and order management

The architecture separates customer-facing functionality from administrative features, with the admin panel requiring authentication.

## Authentication System
Authentication is handled using JWT tokens via the Firebase PHP-JWT library, providing:
- Secure login sessions for administrative access
- Token-based authentication for API endpoints
- Session management for cart persistence

## Cart and Checkout System
The shopping cart system uses:
- Client-side JavaScript for immediate cart updates
- Server-side PHP processing for secure checkout operations
- Session storage for cart persistence across page loads
- Integration with payment processing (to be implemented)

## Administrative Features
The admin backend provides:
- Product inventory management
- Order processing and tracking
- Dashboard with business analytics
- Secure authentication-protected access

The admin interface uses the same design system as the customer-facing pages but with additional functionality for business management.

# External Dependencies

## Frontend Libraries
- **Tailwind CSS CDN** - Utility-first CSS framework for responsive design
- **Feather Icons CDN** - Lightweight icon library for consistent UI elements
- **AOS (Animate On Scroll) CDN** - Animation library for scroll-triggered effects

## Backend Dependencies
- **Firebase PHP-JWT** (v6.11.1) - JWT token creation and validation for secure authentication
- **PHP 8.0+** - Server-side language requirement for JWT library compatibility

## Development Tools
- **Composer** - PHP dependency management for backend packages

The application is designed to be lightweight and fast-loading by leveraging CDN resources for frontend libraries while maintaining secure server-side processing for sensitive operations like authentication and payment processing.