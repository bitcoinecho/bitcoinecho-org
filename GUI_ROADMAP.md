# Bitcoin Echo GUI — Implementation Roadmap

**A Living Document for GUI Development**

*Last Updated: December 18, 2025*

---

## Quick Orientation

**What is Bitcoin Echo GUI?**
A beautiful, standalone browser-based interface to a running Bitcoin Echo node. It communicates via JSON-RPC with the local node and uses mempool.space API for live network context. The GUI transforms node operation from a technical exercise into an engaging experience.

**Key distinction from bitcoin-echo:**
The GUI is *not* ossified. It may evolve, receive updates, and improve independently of the frozen core. This is intentional—the GUI is a window into the artifact, not part of the artifact itself.

**Repository structure:**
```
bitcoinecho-org/          ← Docs, roadmap, whitepaper
bitcoin-echo/             ← Frozen C implementation (ossified)
bitcoinecho-gui/          ← This project (can evolve)
```

---

## Design Philosophy

### Core Principle: Progressive Disclosure

The GUI serves everyone from child novices to seasoned veterans, from ultra-minimalists to show-me-everything enthusiasts. This is achieved through **progressive disclosure**:

```
┌─────────────────────────────────────────────────────────────────┐
│                    PROGRESSIVE DISCLOSURE                        │
│                                                                  │
│  Surface: Clean, essential, beautiful                           │
│      ↓                                                          │
│  Depth 1: Expanded detail on demand                             │
│      ↓                                                          │
│  Depth 2: Full technical exposure for power users               │
│      ↓                                                          │
│  Depth 3: Raw data, developer tools, kitchen sink               │
└─────────────────────────────────────────────────────────────────┘
```

### Guiding Principles

1. **Window, Not Wall**
   The GUI is a view into the node, not a barrier. Show raw data alongside formatted data. Let users see the underlying JSON-RPC. Don't hide complexity; make it navigable.

2. **Offline-First Thinking**
   The GUI should gracefully handle:
   - Node not running
   - Node still syncing
   - Node disconnected mid-session
   Always show what state we're in. Never leave users guessing.

3. **The Sync is the Journey**
   Initial Block Download is not "waiting for software to work"—it's validating the entire history of Bitcoin. Make this feel remarkable through education, milestones, and engagement.

4. **Developer-Friendly**
   The RPC console is not a power-user afterthought—it's a first-class feature. Many users of Bitcoin Echo will be developers studying the protocol.

5. **Timeless Aesthetics**
   Avoid trendy design. Choose clean, minimal, functional. The GUI should look as appropriate in 2035 as it does today.

6. **Performance Budget**
   - Initial load: < 500KB JS
   - Time to interactive: < 2 seconds
   - No runtime CSS-in-JS (Tailwind compiles away)

---

## Data Architecture

The GUI combines two data sources for a complete picture:

```
┌─────────────────────────────────────────────────────────────────┐
│                      DATA ARCHITECTURE                          │
│                                                                 │
│   mempool.space API                    Bitcoin Echo Node RPC    │
│   ─────────────────                    ─────────────────────    │
│   • Current block height               • Validated block height │
│   • Network hashrate                   • Our chain work         │
│   • Live mempool                       • Our mempool (if synced)│
│   • Fee estimates                      • UTXO set size          │
│   • Recent blocks (announced)          • Blocks we've validated │
│   • Difficulty adjustment              • Sync progress          │
│                                                                 │
│                         ↓                                       │
│                   ┌─────────────┐                               │
│                   │     GUI     │                               │
│                   │   merges    │                               │
│                   │    both     │                               │
│                   └─────────────┘                               │
│                         ↓                                       │
│              "Network is at 874,571                             │
│               You've validated to 367,500                       │
│               507,071 blocks to go"                             │
└─────────────────────────────────────────────────────────────────┘
```

### mempool.space API Endpoints

| Data | Endpoint | Use in GUI |
|------|----------|------------|
| Block height | `/api/blocks/tip/height` | Show network tip vs. our tip |
| Block hash | `/api/blocks/tip/hash` | Verify we're on same chain |
| Hashrate | `/api/v1/mining/hashrate/3d` | Network security indicator |
| Mempool size | `/api/mempool` | Show what's waiting |
| Fee estimates | `/api/v1/fees/recommended` | Current fee environment |
| Recent blocks | `/api/v1/blocks` | Live block feed |
| Difficulty | `/api/v1/difficulty-adjustment` | Network difficulty info |

---

## Architecture

### Operating Modes

The GUI supports three modes, detected automatically from node RPC:

**Observer Mode** — *"Watch Bitcoin Breathe"*
- Node running with `--observe` flag
- Live network pulse visualization
- Educational focus
- No validation, no storage
- Instant gratification

**Validate Lite Mode** — *"Full Validation, Minimal Storage"*
- Node running with `--prune=<MB>` flag
- Full consensus validation of every block
- Pruned storage (~10 GB instead of ~600 GB)
- Perfect for learning/educational use
- See validation working in hours, not days

**Validate Archival Mode** — *"The Complete Record"*
- Node running normally (default)
- Full validation + full block storage
- Complete blockchain archive (~600 GB)
- Serve historical blocks to other nodes
- The gold standard

### View Structure

```
┌─────────────────────────────────────────────────────────────────┐
│  SIDEBAR                    MAIN CONTENT                        │
│  ─────────                  ────────────                        │
│                                                                  │
│  ● SYNC / STATUS ◄─────────► During IBD: Journey view           │
│    (home)                    After sync: Node health dashboard  │
│                                                                  │
│  ○ OBSERVER ◄──────────────► Live network pulse                 │
│                              (available during sync too)        │
│                                                                  │
│  ○ CHAIN ◄─────────────────► Validated blocks & transactions    │
│                              (grows as sync progresses)         │
│                                                                  │
│  ○ MEMPOOL ◄───────────────► Unconfirmed transactions           │
│                              (only after sync complete)         │
│                                                                  │
│  ○ NETWORK ◄───────────────► Peers, bandwidth, connectivity     │
│                                                                  │
│  ─────────                                                      │
│  ○ CONSOLE ◄───────────────► Raw RPC                            │
│  ○ LOGS ◄──────────────────► Log stream                         │
│  ⚙ SETTINGS                                                     │
└─────────────────────────────────────────────────────────────────┘
```

---

## Technology Stack

**Chosen for minimalism, simplicity, and alignment with Bitcoin Echo philosophy:**

| Layer | Choice | Rationale |
|-------|--------|-----------|
| Framework | **SvelteKit** (SPA mode) | Compiles away (~2KB), no virtual DOM runtime |
| Build | **Vite** (bundled with SvelteKit) | Fast, modern, minimal config |
| Language | **TypeScript** | Type safety for RPC contracts |
| Styling | **Tailwind CSS** | Utility-first, consistent design system |
| State | **Svelte stores** | Built-in reactivity, no extra dependencies |
| Testing | **Vitest + Testing Library** | Aligned with Vite ecosystem |

**Why Svelte over React/Vue:**
- **Smallest bundle** — Framework compiles away, ~2KB vs 33-40KB
- **Simplest code** — No `.value`, no `useState`, just `let x = 5`
- **Philosophical alignment** — "Compile once, disappear" mirrors "build once, stop"

---

## Implementation Phases

### Phase 0: Project Foundation ✅
*Establish the development environment*

### Phase 1: Observer Mode ✅
*Live network observation with instant gratification*

### Phase 2: Onboarding & Sync Experience
*Transform IBD from waiting into a journey*

### Phase 3: Chain Explorer
*Navigate the validated blockchain*

### Phase 4: Network & Mempool
*Peer connectivity and unconfirmed transactions*

### Phase 5: Power User Tools
*RPC console, logs, developer mode*

### Phase 6: Polish & Release
*Ready for the world*

---

## Session Work Units

Each session is designed to be completable in a single focused chat session.

---

### Phase 0: Project Foundation

#### Session 0.1: Repository Setup ✅
**Objective:** Initialize the GUI project with proper structure

**Tasks:**
- Initialize SvelteKit + TypeScript project in `bitcoinecho-gui/`
- Configure for SPA mode (static adapter, no SSR)
- Configure ESLint + Prettier
- Set up Tailwind CSS
- Create directory structure
- Verify `npm run dev` works

**Deliverables:** Working development environment

---

#### Session 0.2: Design System Foundation ✅
**Objective:** Establish visual identity and base components

**Tasks:**
- Define color palette (inspired by bitcoinecho.org landing page)
- Create base components: Button, Card, Badge, Spinner, Hash
- Set up dark mode support
- Create app shell (header, sidebar, main content area)

**Deliverables:** Design system foundation

---

### Phase 1: Observer Mode

#### Session 1.1: Minimal RPC Client ✅
**Objective:** Build RPC client for observer mode

**Tasks:**
- Create `src/lib/rpc/client.ts` with JSON-RPC 1.0 support
- Create `src/lib/rpc/types.ts` with observer types
- Create typed method wrappers for observer endpoints
- Create `src/lib/stores/connection.ts` with health checks

**Deliverables:** RPC client sufficient for observer mode

---

#### Session 1.2: Live Network Observer View ✅
**Objective:** Real-time display of Bitcoin network activity

**Tasks:**
- Create observer page with live block/transaction feeds
- Implement mempool.space API integration for block height and hashrate
- Create network pulse visualization
- Implement polling for real-time updates
- Handle connection states gracefully

**Deliverables:** The wow moment—live Bitcoin traffic in the GUI

---

#### Session 1.3: Connection Settings & Polish 🎯
**Objective:** Make observer view production-ready

**Tasks:**
- Create connection settings modal:
  - RPC endpoint URL input
  - Test connection button
  - Save to localStorage
- Add `ConnectionStatus` component to Header
- Add CORS handling documentation/guidance
- Improve error messages (user-friendly)
- Add "What am I seeing?" help tooltip explaining observer mode
- Responsive design verification

**Deliverables:** Polished, user-friendly observer experience

---

### Phase 2: Onboarding & Sync Experience

#### Session 2.1: First-Run Onboarding Flow ✅
**Objective:** Welcome new users with clear choices

**Tasks:**
- Create first-run detection (localStorage flag)
- Create welcome screen with Bitcoin Echo branding:
  - "Observe" option — Watch the live network, no storage
  - "Validate" option — Run a full validating node
- If "Validate" chosen:
  - Storage requirements explanation
  - Data directory information
  - "What happens next" explanation
- Create smooth transitions between onboarding steps
- Store user choice for future sessions

**Deliverables:** Engaging first-run experience

**Note:** Session 2.1 initially implements two-option onboarding (Observe/Validate). After Node Session 9.6.2 (Pruning Support), this will be enhanced to three options: Observe / Validate Lite (~10 GB pruned) / Validate Archival (~600 GB full). This enables users to experience full validation quickly without waiting days for a complete sync.

---

#### Session 2.2: Sync View — The Journey
**Objective:** Transform IBD from waiting into time travel

**Tasks:**
- Create `src/routes/sync/+page.svelte` as default home during IBD
- Implement dual-timeline visualization:
  - Live network state (from mempool.space): current height, hashrate, fees
  - Local validation state (from node RPC): validated height, progress
  - Visual gap indicator showing how far behind
- Create progress bar spanning 2009 → present
- Display current validation stats:
  - Blocks validated (this session / total)
  - Transactions checked
  - Signatures verified
  - UTXO set size
- Display performance metrics:
  - Blocks/second rate
  - Estimated time remaining
  - Session duration
- Create "Currently Validating" block indicator

**Deliverables:** Engaging sync progress visualization

---

#### Session 2.3: Historical Milestones
**Objective:** Educate users as they travel through Bitcoin history

**Tasks:**
- Create milestone data structure with historical events:
  - Block 0: Genesis — "The beginning. January 3, 2009."
  - Block 170: First transaction — Hal Finney receives 10 BTC
  - Block 210,000: First halving — Reward drops to 25 BTC
  - Block 420,000: Second halving — Reward drops to 12.5 BTC
  - Block 481,824: SegWit activates
  - Block 630,000: Third halving — Reward drops to 6.25 BTC
  - Block 709,632: Taproot activates
  - Block 840,000: Fourth halving — Reward drops to 3.125 BTC
- Create milestone notification component (appears as user passes each)
- Create "Approaching milestone" indicator
- Store milestones as static data (embedded, not fetched)

**Deliverables:** Educational journey through Bitcoin history

---

#### Session 2.4: Resume & Completion Experience
**Objective:** Handle sync interruption and celebrate completion

**Tasks:**
- Implement resume detection on startup:
  - Detect partial sync via `getblockchaininfo`
  - Show "Welcome back" screen with resume context
  - Display last session info, progress saved
- Track session history in localStorage:
  - Session start/end times
  - Blocks validated per session
  - Total sessions count
- Create sync completion celebration:
  - Summary of the journey (total time, sessions, stats)
  - "Don't trust. Verify. ✓" affirmation
  - Transition to full node dashboard

**Deliverables:** Graceful resume and meaningful completion

---

#### Session 2.5: Mode Detection & Transitions
**Objective:** Seamlessly adapt to node state

**Tasks:**
- Implement mode detection from `getblockchaininfo`:
  - Observer mode: node running with --observe
  - Syncing: initialblockdownload = true
  - Synced: initialblockdownload = false
- Update sidebar to reflect current mode
- Implement smooth transitions between modes
- Allow Observer view during sync (mempool.space data + local sync progress)
- Update header status indicator:
  - `[👁 OBSERVER]` — Observer mode
  - `[⟳ SYNCING 43.7%]` — Syncing
  - `[✓ SYNCED]` — Fully synced
  - `[⚠ OFFLINE]` — Disconnected

**Deliverables:** Context-aware UI that adapts to node state

---

### Phase 3: Chain Explorer

#### Session 3.1: Block List View
**Objective:** Browse validated blocks

**Tasks:**
- Create `src/routes/chain/+page.svelte`
- Create `BlockList` component:
  - Recent validated blocks (most recent first)
  - Height, hash (truncated), timestamp, tx count
  - "Load more" pagination
- Click block to navigate to detail view
- Show relative timestamps ("3 minutes ago")
- Link hashes to mempool.space for external reference

**Deliverables:** Block list view

---

#### Session 3.2: Block Detail View
**Objective:** Display single block details

**Tasks:**
- Create `src/routes/chain/block/[hash]/+page.svelte`
- Display block header info:
  - Height, hash, previous hash
  - Timestamp, version, bits, nonce
  - Merkle root, size, weight
- Display transaction list:
  - Txids (clickable to tx detail)
  - Coinbase transaction highlighted
- Navigation: previous/next block buttons
- Handle invalid/unknown hash gracefully
- Copy-to-clipboard for hashes

**Deliverables:** Block detail view

---

#### Session 3.3: Transaction Detail View
**Objective:** Display transaction details

**Tasks:**
- Create `src/routes/chain/tx/[txid]/+page.svelte`
- Display transaction info:
  - Txid, wtxid
  - Version, locktime
  - Size, vsize, weight
  - Fee (if calculable)
- Display inputs list:
  - Previous txid:vout (clickable)
  - scriptSig hex (expandable)
  - Witness data (if present)
- Display outputs list:
  - Value (in BTC and sats)
  - scriptPubKey hex (expandable)
  - Address (if standard type)
- Collapsible raw hex view
- Link to containing block

**Deliverables:** Transaction detail view

---

#### Session 3.4: Search Functionality
**Objective:** Find blocks and transactions by identifier

**Tasks:**
- Add search input to header
- Implement search logic:
  - Detect input type (block hash, txid, height)
  - Route to appropriate detail view
- Handle search failures gracefully
- Add search history (recent searches)
- Keyboard shortcut (Cmd/Ctrl+K)

**Deliverables:** Search functionality

---

### Phase 4: Network & Mempool

#### Session 4.1: Network View
**Objective:** Display peer connectivity

**Tasks:**
- Create `src/routes/network/+page.svelte`
- Display connection summary:
  - Total peers, inbound/outbound
  - Bandwidth usage (if available from RPC)
- Display peer list:
  - IP/hostname, version, services
  - Connection duration
  - Data transferred
- Note: Requires `getpeerinfo` RPC (may need node enhancement)
- Fallback: Show basic peer count until RPC available

**Deliverables:** Network connectivity view

---

#### Session 4.2: Mempool View
**Objective:** Display unconfirmed transactions

**Tasks:**
- Create `src/routes/mempool/+page.svelte`
- Display mempool summary:
  - Transaction count
  - Total size/weight
  - Fee distribution (histogram)
- Display transaction list:
  - Recent transactions
  - Fee rate (sat/vB)
  - Size
- Click to view transaction detail
- Note: Only available when fully synced
- Show "Sync to view mempool" message during IBD

**Deliverables:** Mempool view

---

### Phase 5: Power User Tools

#### Session 5.1: RPC Console
**Objective:** Direct JSON-RPC interface for developers

**Tasks:**
- Create `src/routes/console/+page.svelte`
- Implement console interface:
  - Method selector dropdown (all available methods)
  - Parameters input (JSON format)
  - Execute button
  - Response display with syntax highlighting
- Request/response history (session)
- Copy response button
- Save favorite commands
- Auto-complete for method names

**Deliverables:** Interactive RPC console

---

#### Session 5.2: Log Viewer
**Objective:** Live log stream from node

**Tasks:**
- Create `src/routes/logs/+page.svelte`
- Note: Requires log streaming capability (may need node enhancement)
- Alternative: Display recent log entries if streaming unavailable
- Implement log filtering:
  - By level (ERROR, WARN, INFO, DEBUG)
  - By component (NET, P2P, CONS, etc.)
  - Text search
- Auto-scroll with pause on hover
- Clear log display button
- Export log to file

**Deliverables:** Log viewing capability

---

#### Session 5.3: Verbose/Developer Mode
**Objective:** Kitchen sink toggle for power users

**Tasks:**
- Add "Developer Mode" toggle in settings
- When enabled, show additional data throughout:
  - Raw hex in transaction view
  - Script disassembly
  - UTXO delta in block view
  - Wire protocol messages in network view
- Add timing information for RPC calls
- Show technical IDs and internal state
- Persist preference in localStorage

**Deliverables:** Developer mode toggle

---

### Phase 6: Polish & Release

#### Session 6.1: Animations & Micro-interactions
**Objective:** Delightful visual polish

**Tasks:**
- Add smooth page transitions
- Add loading skeletons for async content
- Add subtle animations:
  - New block pulse in block feed
  - Sync progress celebration moments
  - Connection status changes
- Ensure animations respect prefers-reduced-motion
- Add optional sound for new blocks (disabled by default)

**Deliverables:** Polished interactions

---

#### Session 6.2: Responsive Design & Accessibility
**Objective:** Works everywhere, for everyone

**Tasks:**
- Verify mobile responsiveness for all views
- Test tablet layouts
- Add keyboard navigation throughout
- Ensure proper focus management
- Add ARIA labels where needed
- Test with screen readers
- Verify color contrast meets WCAG AA

**Deliverables:** Accessible, responsive design

---

#### Session 6.3: Documentation & Screenshots
**Objective:** Ready for community

**Tasks:**
- Write comprehensive README:
  - Installation instructions
  - Configuration (RPC endpoint, settings)
  - Development setup
  - Building for production
- Add screenshots of key views
- Document all configuration options
- Add troubleshooting guide
- Create CHANGELOG

**Deliverables:** Complete documentation

---

#### Session 6.4: v1.0 Release
**Objective:** Ship it

**Tasks:**
- Final testing on all supported browsers
- Production build verification
- Performance audit (Lighthouse)
- Security review (no sensitive data exposure)
- Tag v1.0.0 release
- Update bitcoinecho.org with GUI announcement

**Deliverables:** First stable release

---

## Progress Tracking

### Phase 0: Foundation ✅
| Session | Status | Notes |
|---------|--------|-------|
| 0.1 Repository Setup | ✅ Complete | Dec 2025 — SvelteKit + TS + Tailwind + ESLint/Prettier, Node 20 LTS |
| 0.2 Design System | ✅ Complete | Dec 2025 — Brand colors, Button/Card/Badge/Spinner/Hash components, Header/Sidebar shell |

### Phase 1: Observer Mode
| Session | Status | Notes |
|---------|--------|-------|
| 1.1 Minimal RPC Client | ✅ Complete | Dec 2025 — JSON-RPC client, typed observer interfaces, connection store with health checks |
| 1.2 Live Observer View | ✅ Complete | Dec 2025 — Live block/tx feeds, mempool.space integration for height/hashrate, network stats, stable rendering |
| 1.3 Connection Settings | ✅ Complete | Dec 2025 — ConnectionSettings modal, ConnectionStatus component, ObserverHelp tooltip, user-friendly error messages, CORS guidance |

### Phase 2: Onboarding & Sync Experience
| Session | Status | Notes |
|---------|--------|-------|
| 2.1 First-Run Onboarding | ✅ Complete | Dec 2025 — Welcome screen, Observe/Validate choice, ValidateInfo step, localStorage persistence |
| 2.1+ Three-Option Onboarding | ✅ Complete | Dec 2025 — Enhanced to Observe / Validate Lite (~10 GB) / Validate Archival (~600 GB), storage estimates, CLI command display |
| 2.2 Sync View — The Journey | Not Started | |
| 2.3 Historical Milestones | Not Started | |
| 2.4 Resume & Completion | Not Started | |
| 2.5 Mode Detection | Not Started | |

### Phase 3: Chain Explorer
| Session | Status | Notes |
|---------|--------|-------|
| 3.1 Block List | Not Started | |
| 3.2 Block Detail | Not Started | |
| 3.3 Transaction Detail | Not Started | |
| 3.4 Search | Not Started | |

### Phase 4: Network & Mempool
| Session | Status | Notes |
|---------|--------|-------|
| 4.1 Network View | Not Started | May need `getpeerinfo` RPC |
| 4.2 Mempool View | Not Started | Only when synced |

### Phase 5: Power User Tools
| Session | Status | Notes |
|---------|--------|-------|
| 5.1 RPC Console | Not Started | |
| 5.2 Log Viewer | Not Started | May need log streaming RPC |
| 5.3 Developer Mode | Not Started | |

### Phase 6: Polish & Release
| Session | Status | Notes |
|---------|--------|-------|
| 6.1 Animations | Not Started | |
| 6.2 Responsive & A11y | Not Started | |
| 6.3 Documentation | Not Started | |
| 6.4 v1.0 Release | Not Started | |

---

## Coordination with bitcoin-echo

### Parallel Development Strategy

```
NODE                                  GUI
────                                  ───
                                      Phase 0: Foundation ✅
                                      Phase 1.1-1.3: Observer ✅

9.6.0 Storage Foundation ✅  ──────►   1.3 Connection Polish ✅
9.6.1 Block Pipeline ✅      ──────►   2.1 Onboarding Flow ✅
9.6.2 Pruning Support ✅     ──────►   2.1+ Three-Option Onboarding ✅
9.6.3 Transaction Pipeline   ──────►   2.2 Sync View
9.6.4 Regtest Mining         ──────►   2.3 Milestones
9.6.5 Regtest & Pruning      ──────►   2.4 Resume & Completion
9.6.6 Headers-First Sync     ──────►   2.5 Mode Detection
9.6.7 Testnet & Mainnet      ──────►   3.1-3.4 Chain Explorer

Phase 10: Mining            ──────►   Phase 4: Network & Mempool
Phase 11: Testing           ──────►   Phase 5: Power Tools
Phase 12: Completion        ──────►   Phase 6: Polish & Release
```

**Key insight:** 9.6.2 now prioritizes pruning support to enable "Validate Lite" mode. This allows users to experience full validation with ~10 GB storage instead of waiting for ~600 GB archival sync—critical for educational mission.

### Dependencies & Blockers

| GUI Feature | Node Requirement | Status |
|-------------|------------------|--------|
| Observer Mode | Session 9.5 (`--observe`, observer RPCs) | ✅ Complete |
| Two-Option Onboarding | Session 9.6.1 (block pipeline) | ✅ Complete |
| Three-Option Onboarding | Session 9.6.2 (`--prune` flag, pruned storage) | ✅ Complete |
| Sync Progress | Session 9.6.3+ (`getblockchaininfo` with sync data) | Pending |
| Block Explorer | Session 9.6.6+ (`getblock`, validated blocks) | Pending |
| Transaction View | Session 9.6.3+ (`getrawtransaction` with validation) | Pending |
| Mempool View | Session 9.6.3+ (working mempool) | Pending |
| Network/Peers | `getpeerinfo` RPC (not yet implemented) | Blocked |
| Log Streaming | Log streaming RPC (not yet implemented) | Blocked |

### Recommended Future RPC Additions

These are NOT required for MVP but would enhance the GUI:

1. **`getpeerinfo`** — List connected peers with details
2. **`getmempoolinfo`** — Mempool statistics
3. **`getnetworkinfo`** — Network state summary
4. **`uptime`** — Node uptime
5. **Log streaming** — Real-time log access via RPC or WebSocket

These could be added post-Phase 11 without breaking ossification (they're read-only informational endpoints, not consensus-critical).

---

## Appendix: Key UI Mockups

### Sync View (During IBD)

```
┌────────────────────────────────────────────────────────────────┐
│  [⟳ SYNCING 43.7%]  Block 367,500 / 874,571    ● Connected    │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  YOUR VALIDATION JOURNEY                                       │
│  ═══════════════════════════════════════════════════════════  │
│  2009 ████████████████████░░░░░░░░░░░░░░░░░░░░░░░░░░░░░ 2025  │
│       ▲                  ▲                              ▲      │
│    genesis          you: 367,500                  network     │
│                     Dec 2015                      874,571     │
│  ═══════════════════════════════════════════════════════════  │
│                                                                │
│  ┌────────────────────────────┐ ┌────────────────────────────┐│
│  │  LIVE NETWORK              │ │  YOUR PROGRESS             ││
│  │  (mempool.space)           │ │  (local validation)        ││
│  │                            │ │                            ││
│  │  Height:  874,571          │ │  Validated: 367,500        ││
│  │  Hashrate: 103.92 TH/s     │ │  Remaining: 507,071        ││
│  │  Mempool: 3,482 txs        │ │  Speed: 423 blocks/sec     ││
│  │  Fees: 12-45 sat/vB        │ │  ETA: 14 hours             ││
│  └────────────────────────────┘ └────────────────────────────┘│
│                                                                │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  ⭐ MILESTONE PASSED                                      │ │
│  │  Block 367,000 — OP_CHECKLOCKTIMEVERIFY activated        │ │
│  │  Time-locked transactions become possible                 │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                                                │
│  Currently Validating                                          │
│  ─────────────────────                                        │
│  Block #367,500 │ 982 txs │ 0.87 MB │ ████████░░ scripts...  │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

### Sync Complete Celebration

```
┌────────────────────────────────────────────────────────────────┐
│                                                                │
│                         ✓ FULLY SYNCED                         │
│                                                                │
│            Your node has validated the entire Bitcoin          │
│            blockchain from genesis to the present.             │
│                                                                │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │                                                          │ │
│  │   Final block: #874,571                                  │ │
│  │   Chain work:  0x0000...5b4c3d2e1f0a9b8c                │ │
│  │                                                          │ │
│  │   Your Journey                                           │ │
│  │   ─────────────                                          │ │
│  │   Total time:      3 days, 7 hours                       │ │
│  │   Sessions:        6                                     │ │
│  │   Blocks validated: 874,571                              │ │
│  │   Transactions:     1,024,847,293                        │ │
│  │   Signatures:       2,847,293,847                        │ │
│  │   UTXO set:         156M outputs                         │ │
│  │                                                          │ │
│  │   You've independently verified every transaction        │ │
│  │   in Bitcoin's 16-year history.                         │ │
│  │                                                          │ │
│  │   Don't trust. Verify. ✓                                │ │
│  │                                                          │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                                                │
│                        [Enter Full Node Mode →]                │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

### Resume Screen

```
┌────────────────────────────────────────────────────────────────┐
│                                                                │
│                    Welcome back to Bitcoin Echo                │
│                                                                │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │                                                          │ │
│  │   Last session: December 15, 2025 at 11:42 PM           │ │
│  │   You validated up to block 367,500 (December 2015)     │ │
│  │                                                          │ │
│  │   Progress saved: 43.7%                                  │ │
│  │   UTXO set: 47.2M outputs                               │ │
│  │                                                          │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                                                │
│         [Continue Sync]              [Start in Observer Mode] │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

### First-Run Onboarding

```
┌────────────────────────────────────────────────────────────────┐
│                                                                │
│                         ◉ BITCOIN ECHO                         │
│                                                                │
│           A faithful implementation of the Bitcoin             │
│           protocol, built for permanence.                      │
│                                                                │
│                                                                │
│           ┌─────────────────────────────────────┐             │
│           │  👁  OBSERVE                        │             │
│           │                                     │             │
│           │  Watch the live Bitcoin network     │             │
│           │  No storage needed • Instant start  │             │
│           └─────────────────────────────────────┘             │
│                                                                │
│           ┌─────────────────────────────────────┐             │
│           │  ✓  VALIDATE                        │             │
│           │                                     │             │
│           │  Run a full validating node         │             │
│           │  ~600 GB storage • Initial sync     │             │
│           └─────────────────────────────────────┘             │
│                                                                │
│                                                                │
│           "Don't trust. Verify."                               │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

---

## Open Questions (Resolved)

### CORS Handling ✅
**Resolution:** Bitcoin Echo node (Session 9.5) includes CORS preflight support. Headers are sent with RPC responses.

### Mock Mode
**Resolution:** Skipped. Observer mode provides live data via mempool.space + node RPC. Mock mode is unnecessary given we can always observe the real network.

### Authentication
**Recommendation:** Document that RPC should never be exposed to the network without authentication. Out of scope for the ossified core—localhost binding is the security model.

---

*This document guides GUI development. For core node implementation, see [ROADMAP.md](ROADMAP.md). For technical specification, see the [Whitepaper](bitcoin-echo-whitepaper.md).*

*Build once. Build right. Stop. Now Visible.*
