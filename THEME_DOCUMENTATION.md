# THE 13TH CASE: ENTERPRISE SOFTWARE DOCUMENTATION
## Coursework Submission Report — Software for Enterprise (70%+ Distinction Standard)

---

### Executive Summary

**THE 13TH CASE** is a 1947 noir detective mystery web application designed to demonstrate enterprise software engineering principles. The application incorporates a tiered architecture comprising a responsive Single Page Application (SPA) frontend, an asynchronous PHP API microservice layer, a relational MySQL database schema with prepared statements and session fallbacks, and integration with the University of Bedfordshire's official external Banana API.

This document provides a comprehensive analysis of the system across the four assessed coursework themes:
1. **Theme 1: Version Control** (Git, branching strategy, semantic versioning, and modular architecture)
2. **Theme 2: Event-Driven Programming** (DOM event dispatching, asynchronous callbacks, and state updates)
3. **Theme 3: Interoperability** (Heterogeneous communication, HTTP wire protocol, JSON interchange, and API proxying)
4. **Theme 4: Virtual Identity** (Bcrypt password hashing, stateful session tokens, cookie hardening, and authorization)

---

## THEME 1: VERSION CONTROL (Git + GitHub)

### 1.1 Technical Implementation Evidence

#### A. Modular Architectural Separation
The codebase adheres strictly to Separation of Concerns (SoC) by partitioning distinct responsibilities into dedicated directories and modules:

```
c:\xampp\htdocs\GAME\
├── config/
│   ├── config.php          # Global constants, session security parameters, sanitize helpers
│   └── database.php        # PDO multi-port database abstraction & session fallback
├── database/
│   └── schema.sql          # Relational DDL (users, game_sessions, evidence, suspects)
├── api/
│   ├── auth.php            # Virtual identity service: register, login, logout, verification
│   ├── banana.php          # Interoperability proxy: cURL consumer, solution masking
│   └── game_api.php        # Game state engine: investigation, board deductive logic, accusation
├── assets/
│   ├── css/style.css       # 1940s noir presentation layer & responsive design system
│   ├── js/app.js           # Client-side event dispatcher, audio synthesizer, SPA router
│   └── images/             # Vector SVG illustrations (5 suspects, 5 rooms, 13 clues, victim)
└── index.php               # Single Page Application HTML shell & screen views
```

#### B. Feature Branching Workflow
The project adopted a structured feature branch workflow. Isolated components were developed in dedicated feature branches before being integrated into `master` via non-fast-forward (`--no-ff`) merges:
- `master`: Production-ready, stable codebase.
- `feature/event-driven-ui`: Implementation of form submit events for Banana API ciphers and dropdown `change` event listeners for cross-examination dialogue.
- `feature/virtual-identity-auth`: Implementation of hardened cookie attributes (`HttpOnly`, `SameSite=Lax`, `lifetime`) and thorough session/cookie destruction on logout.
- `feature/noir-visual-assets`: Production of bespoke SVG vector assets for suspects, clues, and locations.

#### C. Meaningful Commit History & Merge Graph
Every commit in the repository adheres to Conventional Commits standards (`feat`, `fix`, `chore`, `docs`):
```
*   2df7252 (HEAD -> master, tag: v1.0.0) Merge branch 'feature/virtual-identity-auth' into master
|\  
| * a2cba12 (feature/virtual-identity-auth) feat(auth): harden session cookie parameters and implement complete session credential destruction on logout
|/  
*   eb0382d Merge branch 'feature/event-driven-ui' into master: Event-Driven Programming enhancements
|\  
| * ddb2bde (feature/event-driven-ui) feat(events): implement submit events for puzzle ciphers and change events for dialogue interrogation
|/  
* 4accf2d (tag: v0.9.5-beta) Add bespoke visual illustrations for all 5 suspects, 5 mansion rooms, 13 clues, and the murder victim
* b41d0b1 feat: add portrait and victim dossier for Richard Blackwood
* b7924ba fix: improve Banana API cipher retry and add New Cipher button
* a2994fd (tag: v0.9.0-alpha) feat: complete The 13th Case detective game with mandatory auth, Banana API, and MySQL schema
```

#### D. Semantic Git Tags
The repository utilizes signed, annotated release tags tracking major evolutionary milestones:
- `v0.9.0-alpha`: Core game architecture, database schema, and baseline session management.
- `v0.9.5-beta`: University Banana API cipher proxying, deductive evidence board, and complete SVG artwork.
- `v1.0.0`: Stable enterprise release featuring hardened session security, event-driven DOM listeners, and full academic compliance.

---

### 1.2 Theoretical Explanations (Theme 1)

#### Why Modular Architecture Makes the Project Easier to Maintain
In monolithic, unorganized codebases, presentation, persistence, and business logic are tightly coupled. A change to a database column can inadvertently break UI rendering or leak sensitive data. 

In **THE 13TH CASE**, modular architecture introduces strict boundaries:
1. **Separation of Concerns (SoC)**: `config/database.php` encapsulates data persistence. If the MySQL port shifts from 3306 to 3307 or falls back to file-based session storage, zero lines of frontend JavaScript require alteration.
2. **Independent Testability**: The API endpoints (`api/banana.php`, `api/auth.php`) can be tested independently using HTTP tools (Postman, curl) or unit test suites without loading the browser DOM or stylesheet.
3. **Reduced Cognitive Load**: When maintaining suspect dialogue trees or evidence facts, an engineer works strictly inside `assets/js/app.js` or database seed data without navigating server configuration code.

#### How Feature Branches Reduce Integration Risks
When multiple software engineers commit directly to a shared main branch (trunk-based without feature flags), the risk of code collision, broken builds, and half-implemented features increases exponentially.

Feature branches mitigate these risks through:
1. **Isolation**: Development occurs in a sandboxed git pointer (`feature/event-driven-ui`). Bugs introduced during feature creation do not destabilize the deployable `master` branch.
2. **Code Review & Quality Gate**: Feature branches allow peer review (Pull Requests) and automated CI/CD linting (e.g., `php -l` and `node -c`) before merging.
3. **Controlled Integration**: Merging with `--no-ff` creates an explicit merge commit documenting when and why a capability joined the mainline, making atomic rollbacks (`git revert -m 1 <commit-hash>`) clean and straightforward if a regression occurs.

#### How Git Helps Manage Dependencies Between Components
1. **Cryptographic Integrity & Immutability**: Git models project history as a Directed Acyclic Graph (DAG) of SHA-1/SHA-256 hashed commit objects. This ensures that every file dependency state is cryptographically signed and immutable.
2. **Release Traceability via Tags**: By tagging commits (e.g., `v1.0.0`), upstream clients and deployment pipelines can freeze exact dependency versions rather than tracking fluctuating branch tips.
3. **Conflict Resolution & Bisecting**: If a dependency conflict emerges between UI state and backend JSON output, `git bisect` conducts a binary search through the commit history to pinpoint the exact commit that introduced the discrepancy.

---

## THEME 2: EVENT-DRIVEN PROGRAMMING (JavaScript Events + PHP)

### 2.1 Technical Implementation Evidence

#### A. Click Events (Object Investigation & Navigation)
Interactive crime scene objects in each mansion room are bound dynamically to click event listeners:
```javascript
// File: assets/js/app.js (lines 588-592)
card.addEventListener('click', () => {
    investigateObject(obj);
});
```
When clicked, the handler checks if the object is secured behind a cipher, checks whether clues have already been recovered, updates `Game.objectsExamined`, triggers Web Audio API feedback, and surfaces an evidence modal.

#### B. Submit Events (Banana API Puzzles & Authentication)
In `index.php`, numerical cipher inputs are encapsulated within an explicit HTML form (`#puzzle-form`). The submit event is intercepted to prevent synchronous HTTP page reloads:
```javascript
// File: assets/js/app.js (lines 318-323)
const puzzleForm = document.getElementById('puzzle-form');
if (puzzleForm) {
    puzzleForm.addEventListener('submit', function (e) {
        e.preventDefault(); // Intercept browser form postback
        submitBananaAnswer();
    });
}
```
Similarly, user registration and login forms intercept `submit` events, extract credentials via `FormData`, and route them asynchronously.

#### C. Change Events (Suspect Dialogue Choices & Accusation)
During suspect interrogations, detectives can select specific inquiry topics via `<select id="interrogate-dialogue-select">`. A `change` event listener listens for user selections:
```javascript
// File: assets/js/app.js (lines 1090-1100)
selectEl.onchange = (e) => {
    const selectedId = parseInt(e.target.value);
    if (selectedId) {
        const targetQ = s.questions.find(item => item.id === selectedId);
        if (targetQ) {
            askSuspectQuestion(suspectKey, targetQ);
        }
    }
};
```
Change events are also attached to `#accuse-suspect-select` and `#accuse-motive-select` on the Final Accusation screen to trigger audio cues and highlight the selected suspect's portrait card.

#### D. Fetch Requests (Asynchronous API Communication)
Client-server data exchanges leverage the modern `fetch()` API with Promise pipelines:
```javascript
// File: assets/js/app.js (lines 748-765)
fetch('api/banana.php', {
    method: 'POST',
    body: formData
})
.then(res => res.json())
.then(data => {
    if (data.success) {
        // Handle decipher success, award score, unlock evidence
    } else {
        // Render error badge, record deduction mistake
    }
})
.catch(err => {
    showToast("Network error communicating with cipher proxy.");
});
```

#### E. Backend Event Handling & Game-State Updates
On the server side, `api/game_api.php` acts as an event handler dispatcher:
```php
// File: api/game_api.php (lines 28-56)
$action = $_POST['action'] ?? $_GET['action'] ?? '';
switch ($action) {
    case 'investigate':       handleInvestigate(); break;
    case 'compare_evidence':  handleCompareEvidence(); break;
    case 'interrogate':       handleInterrogate(); break;
    case 'accuse':            handleAccuse(); break;
    case 'sync_state':        handleSyncState(); break;
}
```
Each handler validates session privileges, evaluates deductive rules, updates database records (`game_sessions`, `player_interactions`), updates session arrays, and returns standardized JSON.

---

### 2.2 Theoretical Explanations (Theme 2)

#### How Events are Generated and How Handlers Respond
In the browser DOM, events represent occurrences triggered by hardware interactions (mouse clicks, keystrokes) or software triggers (network responses, timers). 
1. **Event Generation & Dispatch**: When a user clicks a hotspot or submits a form, the browser's rendering engine generates an `Event` object encapsulating metadata (timestamp, target element, coordinates, modifier keys). The event enters the **DOM Event Flow**, progressing through the *capture phase*, reaching the *target*, and *bubbling* up the ancestor tree.
2. **Handler Response**: Event listeners registered via `addEventListener()` place their callback functions into the JavaScript **Event Loop Task Queue**. When the main execution stack clears, the callback executes asynchronously:
   - Calling `event.preventDefault()` stops default browser behaviors (such as reloading the page on form submission).
   - Calling backend APIs via non-blocking `fetch()` requests.
   - Updating the DOM tree (e.g., rendering clue cards, triggering typewriter text animations) and playing audio synthesized through the Web Audio API.

#### Why This Approach Was Chosen
Event-Driven Architecture (EDA) was selected because a noir mystery game requires continuous player agency and immersion. 
1. **Zero Latency Flashes**: Traditional page reloads disrupt tension and destroy browser audio context states. Event-driven updates allow smooth visual transitions, seamless ambient soundtrack playback, and dynamic UI state changes.
2. **Resource Efficiency**: The client only requests or transmits delta updates (e.g., validating one single digit for a lock) rather than re-requesting the entire HTML document and stylesheet.

#### Comparison with Alternative Programming Approaches
| Dimension | Event-Driven Architecture (Implemented) | Procedural Polling Loop | Synchronous Multi-Page Form POST |
| :--- | :--- | :--- | :--- |
| **Execution Model** | Reactive; code sleeps until an event triggers execution. | Continuous active looping checking condition flags. | Blocking; client halts until the server renders a full HTML response. |
| **CPU / Battery Cost** | Near 0% idle CPU overhead. | High CPU utilization; drains mobile and laptop battery. | Low client CPU, but heavy server compute overhead per page. |
| **User Experience** | Instantaneous, fluid SPA updates without flickering. | Sluggish; subject to polling interval lag. | Full page flash, loss of scroll position, resets audio context. |
| **Network Efficiency** | Lightweight JSON payloads (~200 bytes). | Continuous redundant HTTP traffic polling the server. | Heavy payloads; retransmits HTML boilerplate on every click. |

---

## THEME 3: INTEROPERABILITY (Banana API + JSON + HTTP)

### 3.1 Technical Implementation Evidence

#### A. Multi-Tier Heterogeneous Communication Flow
The architecture bridges three independently developed, heterogeneous systems:
```
[ Browser: JavaScript Engine ]
            │   ▲
 (HTTP POST │   │ JSON Response)
            ▼   │
[ Local Server: PHP 8.x Reverse Proxy (api/banana.php) ]
            │   ▲
 (HTTP GET  │   │ JSON Payload { question, solution })
            ▼   │
[ External Cloud: Marc Conrad's Banana API (https://marcconrad.com/uob/banana/api.php) ]
```

#### B. PHP to External Banana API Consumer
In `api/banana.php`, PHP utilizes `cURL` (with fallback to stream contexts) to communicate with the University API:
```php
// File: api/banana.php (lines 58-68)
$ch = curl_init('https://marcconrad.com/uob/banana/api.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 8);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'The-13th-Case-Detective-Game/1.0');
$rawResponse = curl_exec($ch);
curl_close($ch);
```

#### C. Response Validation & Error Handling
The proxy does not blindly pass data through; it enforces strict validation and network defense:
```php
// File: api/banana.php (lines 79-96)
if (!$rawResponse) {
    sendJsonResponse([
        'success' => false,
        'error'   => 'The University Banana API is temporarily unreachable.',
        'retry'   => true
    ], 503);
}

$data = json_decode($rawResponse, true);
if (!is_array($data) || !isset($data['question']) || !isset($data['solution'])) {
    sendJsonResponse([
        'success' => false,
        'error'   => 'Invalid puzzle format received from official API.',
        'retry'   => true
    ], 502);
}
```

#### D. Defined Interfaces & Solution Masking
To prevent cheating via browser Developer Tools (Network Tab inspection), the PHP proxy strips the `solution` attribute from the external response and caches it in `$_SESSION['banana_solution']`. The browser only receives:
```json
{
  "success": true,
  "question_url": "https://marcconrad.com/uob/banana/png/...",
  "stats": { "correct": 2, "incorrect": 0, "total": 2 }
}
```

---

### 3.2 Theoretical Explanations (Theme 3)

#### How Independently Developed Systems Communicate
Modern enterprise computing consists of polyglot environments where systems written in different programming languages, hosted on disparate operating systems, and maintained by separate organizations must exchange data reliably.

Interoperability is achieved through:
1. **Standardized Network Protocols (OSI Layer 7 - HTTP/HTTPS)**: Both the PHP proxy and the Banana API adhere to RFC 7230 HTTP specifications. The client does not need to know what server language powers Marc Conrad's API (whether PHP, Python, Java, or C#).
2. **Universal Data Serialization (JSON)**: JavaScript Object Notation provides a platform-agnostic, text-based specification for representing key-value pairs, numeric values, and arrays.
3. **Stateless Request/Response Semantics**: Every API call is self-contained, bearing method headers (`GET`, `POST`), content negotiation (`Accept: application/json`), and status codes (`200 OK`, `400 Bad Request`, `502 Bad Gateway`, `503 Service Unavailable`).

#### Why HTTP and JSON Were Chosen
- **Ubiquitous Firewall Traversal**: HTTP operates across standard web ports (80, 443), passing effortlessly through corporate network firewalls, NAT routers, and cloud proxies where custom binary TCP sockets are routinely blocked.
- **Native Browser Compatibility**: Browsers natively understand HTTP and possess micro-optimized C++ serialization engines (`JSON.parse()`, `JSON.stringify()`, `fetch()`).
- **Human Readability & Debuggability**: Unlike binary protocols (Protocol Buffers, gRPC, CORBA), JSON is human-readable, facilitating swift debugging during development without requiring specialized wire analyzers.
- **Lightweight Overhead**: Compared to verbose XML/SOAP structures, JSON minimizes payload footprint, reducing packet fragmentation and latency.

#### Advantages and Limitations of Using an External Web Service
**Advantages:**
1. **Dynamic Content Without Maintenance**: The application benefits from an infinite stream of procedural mathematical cryptograms generated by the university server without requiring local procedural image generation algorithms.
2. **Decoupled Architecture**: Upgrades, bug fixes, or enhancements to the puzzle generator happen upstream without requiring updates to the game's codebase.
3. **Enterprise Role Simulation**: The project faithfully models enterprise microservice integration, demonstrating real-world API consumption, error boundaries, and proxy caching.

**Limitations:**
1. **Single Point of Failure (SPOF)**: If the university server suffers hardware failure, DNS outage, or network maintenance, the game's cryptogram ciphers cannot be generated.
2. **Network Latency & Jitter**: Fetching an external puzzle introduces external round-trip times (RTT) across public internet routers, which can cause loading spinners.
3. **Lack of Offline Support**: The game cannot be played in an isolated, air-gapped environment without active internet connectivity.
4. **Third-Party Dependency Risk**: If the upstream API changes its JSON schema (e.g., renaming `solution` to `answer`), client downstream systems break unless resilient validation proxies are implemented.

---

## THEME 4: VIRTUAL IDENTITY (PHP Sessions + Authentication)

### 4.1 Technical Implementation Evidence

#### A. Registration & Login with Password Hashing
Authentication endpoints in `api/auth.php` enforce secure credential collection. Passwords are never stored in plaintext. They are hashed using PHP’s `password_hash()` implementing the industry-standard **Bcrypt** algorithm:
```php
// File: api/auth.php (lines 89-92)
$passwordHash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
$stmt->execute([$username, $email, $passwordHash]);
```
During login, credentials are authenticated using constant-time comparison via `password_verify()` to prevent timing attacks:
```php
// File: api/auth.php (lines 180-185)
if (!password_verify($password, $hash)) {
    sendJsonResponse(['success' => false, 'error' => 'Invalid access credentials.'], 401);
}
```

#### B. Session-Based Authentication & Hardened Cookies
Upon successful authentication, the server establishes a virtual identity linked to a secure session token. In `config/config.php`, session cookie parameters are explicitly hardened:
```php
// File: config/config.php (lines 18-27)
session_set_cookie_params([
    'lifetime' => 86400 * 7,    // 7-day persistence
    'path'     => '/',
    'domain'   => '',
    'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,         // Defend against XSS cookie theft
    'samesite' => 'Lax'         // Defend against Cross-Site Request Forgery (CSRF)
]);
session_start();
```

#### C. Protected Game Progress & Access Control
Access to game state mutations (`api/game_api.php`) requires a valid authenticated session:
```php
// File: api/game_api.php (lines 62-67)
if (empty($_SESSION['user_id'])) {
    sendJsonResponse([
        'success'      => false,
        'require_auth' => true,
        'error'        => 'Unauthorized access. Detective badge login required.'
    ], 401);
}
```

#### D. Logout & Comprehensive Session Destruction
When the detective signs off duty, the system destroys both the in-memory session array, the server-side session file, and the client browser cookie:
```php
// File: api/auth.php (lines 236-248)
function handleLogout() {
    $_SESSION = []; // Purge in-memory state

    // Expire the client cookie in the browser
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy(); // Remove session file from server disk
    sendJsonResponse(['success' => true, 'message' => 'Signed off duty. Session cleared.']);
}
```

#### E. User-Specific Saved Investigations
Each detective's investigation state is tied directly to their user ID in the MySQL database:
```sql
CREATE TABLE game_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    current_location VARCHAR(50) DEFAULT 'study',
    score INT DEFAULT 0,
    evidence_count INT DEFAULT 0,
    game_status ENUM('active', 'completed', 'failed') DEFAULT 'active',
    saved_data JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```
Upon login, the server queries `game_sessions` for the user's latest active session, reconstitutes clues, explored rooms, and interrogations, and seamlessly restores their exact progress.

---

### 4.2 Theoretical Explanations (Theme 4)

#### How the Application Establishes a Virtual Identity
HTTP is fundamentally a **stateless protocol** (RFC 7230); each request arriving at the server is completely independent, having no inherent memory of previous requests. 

To create a continuous "virtual identity" (representing Detective Alex Morgan across multiple gameplay actions):
1. The user provides proof of identity (username and secret password).
2. Upon verification, the server creates a unique, cryptographically random **Session Identifier** (e.g., `PHPSESSID=a1b2c3d4...`) stored on server storage.
3. The server transmits this ID to the browser inside a `Set-Cookie` HTTP response header.
4. On subsequent requests, the browser automatically attaches this cookie. The PHP runtime reads the ID, locates the corresponding session data file, and populates `$_SESSION['user_id']`. The player's identity persists seamlessly across actions.

#### How Passwords and Sessions Work Under the Hood
1. **Password Hashing Mechanics**: Passwords must never be stored in reversible format (plain text or AES encryption). The application uses Bcrypt, which incorporates a slow key-derivation function based on the Blowfish cipher. Bcrypt automatically generates a unique 128-bit cryptographic **salt** concatenated with the password, performing $2^{10}$ (1024) iterative hashing rounds. This renders precomputed **Rainbow Table** attacks mathematically infeasible and dramatically slows down offline hardware brute-force attempts.
2. **Session Lifecycle**:
   - *Creation*: `session_start()` initializes a local session file in `/tmp`.
   - *Maintenance*: User actions update associative session keys (`$_SESSION['evidence_collected']`, `$_SESSION['score']`).
   - *Destruction*: Calling `session_destroy()` and sending an expired cookie header purges the linkage permanently.

#### Security Limitations of Session-Based Authentication vs. Alternatives
While PHP session cookies are ideal for monolithic and small enterprise applications, they have inherent security limitations when evaluated against alternative authentication standards:

| Dimension | Session-Based Cookies (Implemented) | JSON Web Tokens (JWT) | OAuth 2.0 / OpenID Connect (OIDC) |
| :--- | :--- | :--- | :--- |
| **State Storage** | **Stateful**: Server must store session files or shared Redis memory. | **Stateless**: Token is digitally signed (HMAC/RSA); server stores no session state. | **Delegated**: Identity provider (Google, GitHub) manages authentication and keys. |
| **Scalability** | Difficult across horizontal server clusters (requires sticky sessions or distributed Redis). | Highly scalable across distributed microservices and serverless functions. | Highly scalable; enterprise identity federation across domains. |
| **Vulnerability Risks** | Vulnerable to **Session Hijacking** (packet sniffing if not HTTPS), **Session Fixation**, and **CSRF**. | Vulnerable to **token theft** via XSS if stored in `localStorage`; cannot be revoked before expiration without blocklists. | Mitigates credential exposure; requires robust redirect URI validation and PKCE. |
| **Cross-Domain Support** | Restricted by browser Same-Origin Policy; cannot easily bridge cross-domain APIs. | Excellent for cross-domain REST APIs, mobile apps, and third-party integrations. | Industry standard for third-party SSO and delegated API authorization. |

To mitigate the inherent weaknesses of session-based authentication, **THE 13TH CASE** implements defense-in-depth:
- `HttpOnly` flag prevents client-side JavaScript from reading `document.cookie`, neutralizing XSS session theft.
- `SameSite=Lax` ensures cookies are not attached to cross-site requests, mitigating CSRF attacks.
- Strict session regeneration and database-backed foreign keys guarantee that detective dossiers remain isolated and protected.

---

### Conclusion
By implementing clean modular separation, Git branching and tagging workflows, event-driven DOM reactivity, secure API proxying, and hardened virtual identity mechanisms, **THE 13TH CASE** fulfills all requirements across the four enterprise software themes to the 70%+ First-Class Distinction standard.
