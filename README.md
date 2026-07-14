# Laravel AI & Analytics Platform

A high-performance Laravel 12 workspace showcasing real-time AI integrations, secure biometric authentication, interactive analytics dashboards, and rich Vue 3/Livewire single-page interfaces.

---

## 🚀 Tech Stack

### Backend
- **Framework**: Laravel 12.x (PHP 8.2+)
- **Real-time Engine**: Laravel Reverb (high-speed WebSockets)
- **AI Integration**: `laravel/ai` SDK for model integrations and agent workflows
- **Multi-Factor Auth**: Google 2FA (`pragmarx/google2fa-laravel`) & Passkeys/WebAuthn (`laragear/webauthn`)

### Frontend
- **Reactivity**: Livewire 3 & Volt (declarative inline components)
- **SPA Views**: Vue 3 with PrimeVue v4, PrimeIcons, and `@tanstack/vue-table`
- **Styling**: TailwindCSS, Tailwind Forms, and Custom Glassmorphic CSS variables
- **Charts**: Apache ECharts (for vector visualizations)
- **Calendar**: `vanilla-calendar-pro` (fluid event scheduler)

---

## 🛠 Feature Modules

### 1. Analytics & Operations Dashboard (`/dashboard`)
An interactive glassmorphic dashboard built using the custom local package `khemraj/laravel-dashboard`.
- **ECharts Widgets**: Dynamic line, bar, pie, and scatter charts bound to real-time database queries.
- **Workflow Flowchart**: Interactive drag-and-drop node graph canvas for visual workflow automation.
- **Embedded AI Chat**: Conversational AI panel sidebar capable of querying data models and generating analytics summaries.
- **Unified Navigation**: Integrated with the host application's responsive main navigation bar.

### 2. Multi-Factor & Biometric Authentication Suite
Advanced security workflows beyond conventional password logins:
- **Email Identification**: Step 1 authentication utilizing device fingerprinting.
- **Passkeys (WebAuthn)**: Hardware-backed biometrics registration and login.
- **Custom PIN Login**: Secure 4-to-6 digit PIN registration and recovery tools.
- **Two-Factor Authentication**: Standard TOTP integration.

### 3. Real-Time Chat (`/chat`)
A collaborative chat interface built with Livewire 3 and powered by Laravel Reverb.
- **Websocket Broadcasts**: Instant message updates and typing indicators.
- **Interactive Modals**: Group creation, chat settings, and attachment browser.
- **Clean UI**: Responsive chat window styled with premium dark slate palettes.

### 4. AI Agent Playground (`/ai-playground`)
An AI interaction console for testing custom agent behaviors and models.
- **Live Conversation Threads**: Create, name, and delete conversation instances.
- **Tool-Call Diagnostics**: Visual feedback for tool calls, attachments, and model responses.

### 5. Leads CRUD SPA (`/leads`)
A reactive single-page app view built using Vue 3 and PrimeVue.
- **PrimeVue Components**: Premium table UI with pagination, filtering, and inline edits.
- **TanStack Table**: High-performance grid data management.
- **Bulk Operations**: Perform batch status updates and multi-row deletes in a single request.

### 6. Dynamic Calendar (`/calendar`)
A fluid schedule management interface.
- **Vanilla Calendar Pro**: Lightweight, dependency-free calendar displaying events with sub-millisecond load times.
- **Interactive Forms**: Create, edit, and delete calendar events dynamically via an API-driven frontend.

---

## ⚙️ Getting Started & Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js (v18+) & NPM

### Setup Instructions

1. **Clone the Repository** and navigate to the project directory.
2. **Install Composer & NPM Dependencies**:
   ```bash
   composer install
   npm install
   ```
3. **Configure Environment Variables**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Make sure to configure your database connection and Reverb variables in `.env`.*
4. **Run Migrations & Seeders**:
   ```bash
   php artisan migrate --seed
   ```
5. **Run the Development Environment**:
   Concurrently start the Laravel application, queue listener, log tailing, and Vite server:
   ```bash
   npm run dev
   ```
