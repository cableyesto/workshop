# Wörkshop
This is the Wörkshop project repository.

## 🚀 Project Setup (Symfony + Vue + Vite Plus + DDEV)

This project uses:

* **Backend**: Symfony (PHP)
* **Frontend**: Vue 3 + Vite Plus
* **Environment**: DDEV (Docker-based dev environment)
* **Node management**: NVM

---

## 📦 Prerequisites

Make sure you have installed:

* [DDEV](https://ddev.readthedocs.io/)
* [Docker](https://www.docker.com/)
* [NVM](https://github.com/nvm-sh/nvm)
* [VITE+](https://viteplus.dev/)
* [pnpm](https://pnpm.io/)

---

## ⚙️ Project Setup

### 1. Clone the repository
```bash
git clone <repo-url>
cd <project-folder>
```

---

## ⚫ Backend Setup (Symfony)

### 1. Setup DDEV

```bash
cd backend/
ddev config
ddev start
```

Install PHP dependencies:

```bash
ddev composer install
```

---

### 2. Configure environment

Copy environment file if needed:

```bash
cp .env .env.local
```

Update variables if required (database, mailer, etc.).

---

### 3. Setup database

```bash
ddev exec php bin/console doctrine:database:create
ddev exec php bin/console doctrine:migrations:migrate
```

---

### 4. Access application
```
https://<project>.ddev.site
```

---

## 🟢 Frontend Setup (Vue + Vite Plus)

### 1. Install Node via NVM

```bash
nvm install
nvm use
```

---

### 2. Verify pnpm installation (via NVM)

Check Node version:

```
node -v
```
Check pnpm:
```
pnpm -v
```
If pnpm is missing, install it globally:
```
npm install -g pnpm
```
👉 Ensure pnpm uses the Node version managed by NVM:
```
which pnpm
which node
```
Both should point to your NVM directory (e.g. ~/.nvm/...)


---

### 2. Install dependencies

```bash
cd frontend
vp install
```

---

### 3. Run frontend dev server

```bash
vp dev
```
---

### 4. Access application
```
http://localhost:5173
```

---

## 🔁 Full Development Workflow

Run everything together:

```bash
cd backend
ddev start
cd frontend
vp dev
```

Backend available at:

```
https://<project>.ddev.site
```

---

## 🧪 Running Tests

### Backend (PHPUnit)

```bash
cd backend
ddev exec phpunit
```

### Frontend (Vitest)

```bash
cd frontend
vp test
```

---

## 📬 Mail (DDEV MailPit)

MailPit:

```
http://<project>.ddev.site:8025
```

Example `.env.local`:

```
MAILER_DSN=smtp://mailpit:1025
```

---

## 🗄 Database Access

```bash
ddev psql
```

---

## 🧹 Code Quality

Frontend:

```bash
vp check
vp lint
vp fmt
```

---

## 📁 Project Structure
```
.
├── backend/
│   ├── .ddev/
│   └── (Symfony app)
├── frontend/ (Vue + Vite Plus)
├── README.md
```

---

## 👨‍💻 Tips

* Use `ddev ssh` to enter the container
* Use `ddev logs` for debugging
* Restart environment if needed:

```bash
ddev restart
```

---

## 📌 Troubleshooting

* Clear Symfony cache:

```bash
ddev exec php bin/console cache:clear
```

* Reinstall dependencies:

```bash
rm -rf vendor node_modules
ddev composer install
vp install
```

