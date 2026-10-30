# THE 13TH CASE — A 1947 Noir Detective Mystery

> **University Coursework Project:** Software for Enterprise  
> **Coursework Focus:** API Interoperability, Event-Driven Programming, Modular Architecture, Git Version Control & Database Integration.

---

## 1. Project Overview

**THE 13TH CASE** is a complete, playable browser-based detective mystery game set in 1947 Blackwood City. 

The player steps into the shoes of **Detective Alex Morgan**. At midnight on October 13, 1947, a phone call summons the detective to Blackwood Manor. Richard Blackwood, a wealthy shipping magnate and industrialist, has been found dead inside his private study. The room was locked from the inside, crucial company audit records are missing, and the grandfather clock is frozen at 11:13 PM. 

Five suspects were present in the mansion that night:
1. **Eleanor Blackwood** — The victim's wife (Motive: inheritance and marital estrangement)
2. **Thomas Reed** — Business partner (Motive: financial disputes and debt pressure)
3. **Henry Collins** — Head butler (Motive: blackmail and knowledge of secret passages)
4. **Margaret Hayes** — Investigative journalist (Motive: whistleblower scoop and corporate fraud)
5. **Victor Graves** — Personal lawyer (**The True Murderer**: concealed systematic embezzlement and forged Blackwood's will)

---

## 2. Technology Stack

- **Frontend:** HTML5, CSS3 (Custom Noir Theme matching reference visual), Vanilla JavaScript (Modular ES6+).
- **Backend:** PHP 8.x (RESTful endpoints, secure session management, input sanitization).
- **Database:** MySQL (InnoDB engine, utf8mb4 charset, prepared statements via PHP PDO).
- **External API:** University of Bedfordshire Banana API (`https://marcconrad.com/uob/banana/api.php`).
- **Audio:** Web Audio API (Synthesized rain ambiance, typewriter effects, clue chimes, and cipher buzzers).
- **Version Control:** Git & GitHub.

---

## 3. Visual Design & Theme

Styled after the 1940s detective noir aesthetic matching the project reference specification:
- **Charcoal & Ebony Backgrounds** (`#0a0a0a`, `#121212`) with radial vignettes and subtle scanline textures.
- **Vintage Gold Highlights** (`#C9A84C`, `#DFBF67`) with luminous drop shadows and glowing borders.
- **Crimson Accents** (`#8B3A3A`) highlighting danger, murder clues, and final indictments.
- **Classic Typography:** *Playfair Display* (Serif headings), *Crimson Text* (Narrative prose), and *Special Elite* (1940s Typewriter text).
- **Gold Circular Magnifying Glass Badge** with dynamic pulse lighting.

---

## 4. University Banana API Integration

The puzzle system integrates genuinely with the official University Banana API:

1. **Security & Session Secrecy:**  
   The frontend never communicates with the Banana API directly. The PHP service (`api/banana.php`) contacts `https://marcconrad.com/uob/banana/api.php` via cURL, decodes the response, and stores the numerical `solution` in `$_SESSION['banana_solution']`. Only the puzzle image URL (`question`) is sent to the client. This prevents inspection of client memory or DOM to cheat.
2. **Client Interaction:**  
   The player inputs their deduction (0–9) and submits it via POST.
3. **Validation & Rewards:**  
   `api/banana.php` verifies the solution against the session. Correct answers award **+150 investigation points** and unlock corresponding crime scene clues (such as the Stopped Clock mechanism, Library Secret Compartment, and Basement Strongbox).
4. **Fault Tolerance & Retries:**  
   Network timeouts and API errors are handled gracefully with clear retry prompts—no silent fallbacks or hardcoded fake images.

---

## 5. Detective Authentication System (Mandatory Registration & Login)

To access Blackwood Manor and investigate Case #13, all players must register and sign in with an official Detective Badge:
- **Registration ([api/auth.php](file:///c:/xampp/htdocs/GAME/api/auth.php)):** Players create a unique detective username, optional police email, and an access code password securely encrypted with `password_hash(..., PASSWORD_BCRYPT)`.
- **Login & Save File Restoration:** Authenticates using `password_verify()` and automatically restores the detective's saved investigation progress from the `game_sessions` MySQL database table.
- **Access Control:** Clicking `BEGIN INVESTIGATION` or `CONTINUE INVESTIGATION` prompts unauthenticated users with the Blackwood Police Precinct badge authorization window before granting entry to the crime scene.
- **Top Navigation Badge:** Displays the active detective's clearance (e.g. `DETECTIVE: MORGAN`) with sign-off and account management options.

---

## 6. Game Features & 14 Dedicated Screens

1. **Main Menu:** Noir landing screen with glowing badge, quote, and navigation buttons.
2. **Case Introduction:** Interactive typewriter narrative dossier introducing Detective Alex Morgan and the murder facts.
3. **Mansion Map:** Five distinct exploration sectors with dynamic status badges.
4. **Room Investigation:** Interactive hotspot scenes with examine interactions for clues and puzzles.
5. **Banana API Puzzle Screen:** Cryptogram interface with live API image, number dial, and score feedback.
6. **Evidence Inventory:** Comprehensive case notebook listing all 13 pieces of physical evidence with importance tiers (*Critical, High, Medium, Low*).
7. **Evidence Board:** Interactive pinboard allowing players to cross-examine two clues and uncover deductive breakthroughs.
8. **Suspect Profiles:** Dossiers detailing motives, occupations, and psychological backgrounds of all 5 suspects.
9. **Interrogation Room:** Two-column interrogation interface featuring dynamic typewriter testimonies and 4 distinct questions per suspect.
10. **Final Accusation:** Indictment interface requiring the player to select the killer, the true motive, and at least 3 critical supporting clues.
11. **Three Distinct Endings:**
    - **PERFECT DETECTIVE:** Successfully accuses Victor Graves, identifies Embezzlement/Fraud motive, and provides sufficient proof.
    - **CASE SOLVED:** Accuses Victor Graves with correct motive but missing some supporting evidence.
    - **CASE FAILED:** Accuses an innocent suspect or exceeds the 13-mistake limit.
12. **Settings:** Noir audio synthesizer toggle, typography scaling, case saving, and case reset.
13. **How to Play:** Complete detective field manual.
14. **Database Diagnostics:** Dedicated setup verification screen (`pages/setup.php`).

---

## 6. Project Directory Structure

```
c:\xampp\htdocs\GAME\
│
├── index.php                 # Main SPA application containing all game screens
├── README.md                 # Full coursework documentation
├── .gitignore                # Git exclusions
│
├── config/
│   ├── config.php            # Core constants, session initialization, helper functions
│   └── database.php          # PDO connection factory with session fallback
│
├── api/
│   ├── banana.php            # University Banana API proxy, solution caching & validation
│   └── game_api.php          # Backend REST endpoints for state, evidence board, dialogue & accusation
│
├── pages/
│   └── setup.php             # Database diagnostics and table inspector
│
├── database/
│   └── schema.sql            # Full MySQL schema with seed data for MySQL Workbench
│
└── assets/
    ├── css/
    │   └── style.css         # Complete noir stylesheet matching reference aesthetic
    ├── js/
    │   └── app.js            # Modular game engine, audio synthesizer, state & animations
    └── images/               # Image asset directory
```

---

## 7. How to Run Locally with XAMPP

1. **Place Project in XAMPP:**  
   Ensure the project files reside in `C:\xampp\htdocs\GAME\`.
2. **Start Services in XAMPP Control Panel:**  
   - Start **Apache**.
   - Start **MySQL**.
3. **Setup Database (Using MySQL Workbench or phpMyAdmin):**  
   - Connect to `127.0.0.1:3306` as `root` (no password by default).
   - Open and execute `database/schema.sql`.
   - Verify connection at `http://localhost/GAME/pages/setup.php`.
4. **Launch the Game:**  
   Open your browser and navigate to:  
   `http://localhost/GAME/`

---

## 8. Git & GitHub Setup

To initialize and push this project to your GitHub repository:

```bash
cd C:\xampp\htdocs\GAME
git init
git add .
git commit -m "feat: complete playable detective game The 13th Case with Banana API integration"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/the-13th-case.git
git push -u origin main
```
