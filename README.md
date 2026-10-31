# THE 13TH CASE — 1947 Noir Detective Mystery
### Software for Enterprise Coursework Submission (70%+ Distinction Standard)

> *"The rain never stops in this city, and neither does the truth."*

---

## 🕵️‍♂️ Project Overview
**THE 13TH CASE** is a full-stack, browser-based 1947 noir detective investigation game. Players assume the identity of Detective Alex Morgan investigating the locked-room murder of shipping tycoon Richard Blackwood. The application integrates real-world enterprise software patterns:
- **Banana API Interoperability**: Live mathematical cryptograms fetched from the University of Bedfordshire (`https://marcconrad.com/uob/banana/api.php`) via a secure server-side PHP proxy.
- **Deductive Evidence Board**: Interactive pinboard evaluating logical connections between 13 bespoke clues.
- **5 Suspect Cross-Examinations**: Dynamic dialogue trees with behavioral cues and alibi verification.
- **Accusation & 3 Dynamic Endings**: High-stakes Grand Jury indictment yielding *Perfect Detective*, *Case Solved*, or *Case Failed*.
- **Atmospheric 1940s Noir Audio**: Synthesized in real-time using the HTML5 Web Audio API (typewriter clatter, vinyl crackle, chime of realization, dramatic stings).

---

## 🎓 Academic Themes Mapping (70%+ Distinction Target)

| Theme | Key Implementation | Source Files |
| :--- | :--- | :--- |
| **Theme 1: Version Control** | Modular MVC/SPA separation, feature branches (`feature/event-driven-ui`, `feature/virtual-identity-auth`), semantic tags (`v0.9.0-alpha`, `v0.9.5-beta`, `v1.0.0`), commit history. | `.git/`, `config/`, `api/`, `database/`, `assets/` |
| **Theme 2: Event-Driven Programming** | Click events (investigate, board), Submit events (`#puzzle-form`), Change events (`#interrogate-dialogue-select`, `#accuse-suspect-select`), asynchronous `fetch()` pipelines. | `assets/js/app.js`, `index.php`, `api/game_api.php` |
| **Theme 3: Interoperability** | Multi-tier heterogeneous communication: JS (Browser) ↔ PHP (Proxy) ↔ Remote University Banana API (Cloud) using HTTP/JSON with error handling and solution masking. | `api/banana.php`, `assets/js/app.js` |
| **Theme 4: Virtual Identity** | Detective registration/login, Bcrypt password hashing, session tokens (`PHPSESSID`), cookie hardening (`HttpOnly`, `SameSite=Lax`), session destruction on logout, user-keyed persistence. | `api/auth.php`, `config/config.php`, `database/schema.sql` |

*For complete academic justifications and comparative analyses, refer to [`THEME_DOCUMENTATION.md`](THEME_DOCUMENTATION.md).*

---

## 🏛 System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    CLIENT BROWSER (UI)                      │
│   Single Page Application (SPA) HTML5 / CSS3 / Vanilla JS   │
│   Event Dispatcher • Web Audio Synthesizer • SPA Router     │
└──────────────────────────────┬──────────────────────────────┘
                               │ Asynchronous HTTP / JSON
                               ▼
┌─────────────────────────────────────────────────────────────┐
│                ENTERPRISE PHP SERVICE LAYER                 │
│  ├── config/config.php      - Session & Security Config     │
│  ├── config/environment.php - Diagnostics & Audit Log       │
│  ├── config/database.php    - PDO Multi-Port Connector      │
│  ├── api/auth.php           - Virtual Identity & Bcrypt     │
│  ├── api/banana.php         - Remote API Gateway & Proxy    │
│  └── api/game_api.php       - Deductive Game State Machine  │
└──────────────┬──────────────────────────────┬───────────────┘
               │ PDO Prepared Statements       │ cURL / HTTP GET
               ▼                               ▼
┌─────────────────────────────┐ ┌─────────────────────────────┐
│    LOCAL MYSQL DATABASE     │ │     REMOTE WEB SERVICE      │
│  Tables:                    │ │  Marc Conrad's Banana API   │
│  • users                    │ │  uob/banana/api.php         │
│  • game_sessions            │ │                             │
│  • evidence (13 seed clues) │ │  Returns dynamic visual     │
│  • suspects (5 seed dossiers│ │  cryptograms & solutions    │
└─────────────────────────────┘ └─────────────────────────────┘
```

---

## 🚀 Quick Start Guide (XAMPP Environment)

### 1. Prerequisites
- **XAMPP** (Apache + PHP 7.4+ or 8.x + MySQL).
- Project files located at `c:\xampp\htdocs\GAME\`.

### 2. Database Initialization
1. Open XAMPP Control Panel and start **Apache** and **MySQL**.
2. Open your browser to `http://localhost/phpmyadmin/`.
3. Create a database named `detective_game` (or `game_db`).
4. Import `database/schema.sql`.

> **Note on Database Resiliency**: The application automatically probes ports `3306` and `3307`. If MySQL is stopped or unavailable, the game operates seamlessly in **Session Mode**, allowing full immediate evaluation without configuration blockers.

### 3. Launch the Application
Navigate in any modern web browser to:
```
http://localhost/GAME/index.php
```

### 4. Health Check Endpoint
To inspect database connectivity and schema row counts at any time, visit:
```
http://localhost/GAME/database/healthcheck.php
```

---

## 🏷 Version Control Summary

```bash
# View semantic releases
git tag -l -n3

# View commit tree and merge graph
git log --graph --oneline --decorate -n 15
```

- **`v0.9.0-alpha`**: Initial core game architecture, MySQL relational schema, and session management.
- **`v0.9.5-beta`**: University Banana API cipher proxying, deductive evidence board, and complete SVG vector illustrations.
- **`v1.0.0`**: Final Enterprise release featuring event-driven form submits, dialogue change triggers, hardened cookies, and academic documentation.

---

## 🔒 Security Summary
- **Zero Plaintext Passwords**: Uses `PASSWORD_BCRYPT` with cost factor 10.
- **SQL Injection Defense**: 100% parameter binding with PDO prepared statements.
- **XSS & CSRF Defense**: Input sanitization via `htmlspecialchars()`, cookies set to `HttpOnly` and `SameSite=Lax`.
- **Anti-Cheat Cipher Protection**: Remote cipher solutions are stripped by the PHP proxy and stored in `$_SESSION['banana_solution']`; client inspect tools cannot expose answers.
