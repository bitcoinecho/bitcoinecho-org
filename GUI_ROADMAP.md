# Bitcoin Echo GUI — Implementation Roadmap

**A Living Document for GUI Development**

*Last Updated: December 13, 2025*

---

## Quick Orientation

**What is Bitcoin Echo GUI?**
A beautiful, standalone browser-based interface to a running Bitcoin Echo node. It communicates exclusively via JSON-RPC, making it completely decoupled from the frozen C implementation.

**Key distinction from bitcoin-echo:**
The GUI is *not* ossified. It may evolve, receive updates, and improve independently of the frozen core. This is intentional—the GUI is a window into the artifact, not part of the artifact itself.

**Repository structure:**
```
bitcoinecho-org/          ← Docs, roadmap, whitepaper
bitcoin-echo/             ← Frozen C implementation (ossified)
bitcoinecho-gui/          ← This project (can evolve)
```

---

## The Sequencing Problem

### Current State of bitcoin-echo (Phase 9 Complete)

The node has these components implemented:
- Full consensus engine (block/tx validation, chain selection)
- Storage layer (SQLite UTXO/index databases, block files)
- Protocol layer (P2P messages, peer management, mempool)
- Application layer (node lifecycle, event loop, RPC server, logging)

**What the RPC can do today:**
- `getblockchaininfo` — Returns chain state (but no blocks synced yet)
- `getblock` — Fetch block by hash (once blocks exist)
- `getblockhash` — Fetch hash by height (once blocks exist)
- `getrawtransaction` — Fetch transaction (mempool or confirmed)
- `sendrawtransaction` — Submit transaction to mempool
- `getblocktemplate` — Mining work (Phase 10 stub)
- `submitblock` — Submit mined block (Phase 10 stub)

**What's NOT functional yet:**
- No actual network sync (can't connect to mainnet peers and IBD)
- Mining interface is stub (Phase 10 not started)
- No chain sync test verified (Phase 11)

### The Horse and Cart

The GUI development can proceed intelligently by:

1. **Building against the RPC interface** (stable API contract)
2. **Using mock data** during early phases
3. **Testing against regtest** once the node can mine locally
4. **Graduating to testnet/mainnet** as node capabilities mature

This means GUI work can happen *in parallel* with Phases 10-11 of the node.

---

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    bitcoinecho-gui                          │
│            (SvelteKit SPA, can evolve)                      │
│                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐ │
│  │  Dashboard  │  │  Explorer   │  │   RPC Console       │ │
│  │  (sync,     │  │  (blocks,   │  │   (raw JSON-RPC)    │ │
│  │   peers)    │  │   txs)      │  │                     │ │
│  └─────────────┘  └─────────────┘  └─────────────────────┘ │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐  │
│  │              RPC Client Layer                         │  │
│  │    (typed fetch wrapper, error handling, retries)    │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                           │
                           │ HTTP POST (JSON-RPC 1.0)
                           │ localhost:8332
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                      bitcoin-echo                           │
│                   (Frozen C daemon)                         │
└─────────────────────────────────────────────────────────────┘
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
| Charts | Lightweight charting lib (TBD) | Sync progress, difficulty, etc. |
| State | **Svelte stores** | Built-in reactivity, no extra dependencies |
| Testing | **Vitest + Testing Library** | Aligned with Vite ecosystem |

**Why Svelte over React/Vue:**
- **Smallest bundle** — Framework compiles away, ~2KB vs 33-40KB
- **Simplest code** — No `.value`, no `useState`, just `let x = 5`
- **Philosophical alignment** — "Compile once, disappear" mirrors "build once, stop"
- **Sufficient ecosystem** — We need very little for this focused app

**Explicitly avoiding:**
- Heavy frameworks (Next.js, Nuxt) — we're a simple SPA
- Complex state management (Redux, Pinia) — overkill for this scope
- Server-side rendering — pure client-side is sufficient

---

## Implementation Phases

### Phase 0: Project Foundation ✅
*Establish the development environment*

### Phase 1: Live Observer (The "Wow Moment") 🎯
*Connect to a real Bitcoin node and watch live network traffic*

### Phase 2: Full RPC Client & Mock Mode
*Complete RPC coverage and development tooling*

### Phase 3: Dashboard View
*Chain status for full node mode*

### Phase 4: Block Explorer
*Navigate the chain*

### Phase 5: Transaction View
*Decode and display transactions*

### Phase 6: RPC Console
*Power user interface*

### Phase 7: Polish & Integration Testing
*Ready for community testing*

---

## Session Work Units

Each session is designed to be completable in a single focused chat session.

---

### Phase 0: Project Foundation

#### Session 0.1: Repository Setup
**Objective:** Initialize the GUI project with proper structure

**Tasks:**
- Initialize SvelteKit + TypeScript project in `bitcoinecho-gui/`
- Configure for SPA mode (static adapter, no SSR)
- Configure ESLint + Prettier
- Set up Tailwind CSS
- Create directory structure:
  ```
  bitcoinecho-gui/
  ├── src/
  │   ├── lib/
  │   │   ├── components/  # Reusable UI components
  │   │   ├── rpc/         # RPC client
  │   │   └── stores/      # Svelte stores
  │   ├── routes/          # SvelteKit file-based routing
  │   │   ├── +layout.svelte
  │   │   └── +page.svelte
  │   └── app.html
  ├── static/
  ├── package.json
  ├── svelte.config.js
  ├── tsconfig.json
  ├── vite.config.ts
  ├── tailwind.config.js
  └── README.md
  ```
- Verify `npm run dev` works

**Deliverables:** Working development environment

---

#### Session 0.2: Design System Foundation
**Objective:** Establish visual identity and base components

**Tasks:**
- Define color palette (inspired by bitcoinecho.org landing page)
- Create base components:
  - `Button` (primary, secondary, ghost variants)
  - `Card` (container for content blocks)
  - `Badge` (status indicators)
  - `Spinner` (loading state)
  - `Hash` (monospace, truncatable hash display)
- Set up dark mode support
- Create app shell (header, sidebar, main content area)

**Deliverables:** Design system foundation

---

### Phase 1: Live Observer (The "Wow Moment")

**Philosophy:** The first real feature should be the most impressive. Before mock data, before block explorers, before anything else—we show live Bitcoin network traffic. This proves the entire stack works and creates immediate engagement.

**Prerequisites:** Node Session 9.5 (Observer Mode) must be complete first.

#### Session 1.1: Minimal RPC Client for Observer
**Objective:** Build just enough RPC client to support observer mode

**Tasks:**
- Create `src/lib/rpc/client.ts`:
  - JSON-RPC 1.0 request builder
  - Fetch wrapper with timeout
  - Basic error handling
  - Configurable endpoint (default localhost:8332)
- Create `src/lib/rpc/types.ts` (observer types only initially):
  ```typescript
  interface ObserverStats {
    peers: number;
    messages_received: number;
    blocks_announced: number;
    txs_announced: number;
    uptime_seconds: number;
    mode: 'observer' | 'full';
  }

  interface ObservedBlock {
    hash: string;
    time_received: number;
    from_peer: string;
  }

  interface ObservedTx {
    txid: string;
    time_received: number;
  }
  ```
- Create typed method wrappers:
  - `getObserverStats(): Promise<ObserverStats>`
  - `getObservedBlocks(limit?: number): Promise<ObservedBlock[]>`
  - `getObservedTxs(limit?: number): Promise<ObservedTx[]>`
- Create `src/lib/stores/connection.ts`:
  - Simple connection state store
  - Auto-reconnect on failure
  - Health check via `getObserverStats`

**Deliverables:** RPC client sufficient for observer mode

---

#### Session 1.2: Live Network Observer View
**Objective:** Real-time display of Bitcoin network activity

**Tasks:**
- Create `src/routes/observer/+page.svelte` (make this the default home)
- Create `ObserverStats` component:
  - Connected peer count (big number, prominent)
  - Messages received (by type)
  - Uptime
  - Mode indicator ("Observer Mode - Watching the Network")
- Create `LiveBlockFeed` component:
  - Real-time list of announced blocks (last 20)
  - Block hash (truncated, copyable)
  - Time received (relative: "3s ago")
  - Auto-scroll with pause on hover
  - Visual pulse animation on new block
- Create `LiveTxFeed` component:
  - Real-time transaction stream
  - Txid (truncated)
  - Rate indicator (tx/sec rolling average)
  - Toggle to pause/resume (can be overwhelming)
- Create `NetworkPulse` component:
  - Simple CSS animation showing activity
  - Pulses/glows on each received message
  - Visual heartbeat of Bitcoin
- Implement polling:
  - `getObserverStats` every 2 seconds
  - `getObservedBlocks` every 3 seconds
  - `getObservedTxs` every 2 seconds
- Update Sidebar:
  - "Observer" as primary nav item (default home)
  - Live indicator dot (green when connected, pulsing when receiving)
- Handle states gracefully:
  - "Connecting..." (initial)
  - "Waiting for peers..." (0 peers)
  - "Observing Bitcoin Network" (1+ peers, receiving data)
  - "Node not running" (connection failed)
  - "Node not in observer mode" (connected but wrong mode)

**Deliverables:** The wow moment—live Bitcoin traffic in the GUI

---

#### Session 1.3: Connection Settings & Polish
**Objective:** Make observer view production-ready

**Tasks:**
- Create connection settings modal:
  - RPC endpoint URL input
  - Test connection button
  - Save to localStorage
- Add `ConnectionStatus` component to Header:
  - Show connected/disconnected state
  - Click to open settings
- Add CORS handling documentation/guidance
- Improve error messages (user-friendly)
- Add "What am I seeing?" help tooltip/modal explaining observer mode
- Responsive design verification

**Deliverables:** Polished, user-friendly observer experience

---

### Phase 2: Full RPC Client & Mock Mode

#### Session 2.1: Complete RPC Client
**Objective:** Extend RPC client for all node methods

**Tasks:**
- Extend `src/lib/rpc/types.ts` with full type definitions:
  ```typescript
  interface BlockchainInfo {
    chain: string;
    blocks: number;
    headers: number;
    bestblockhash: string;
    difficulty: number;
    mediantime: number;
    verificationprogress: number;
    chainwork: string;
  }
  // ... Block, Transaction, etc.
  ```
- Add typed method wrappers:
  - `getBlockchainInfo(): Promise<BlockchainInfo>`
  - `getBlock(hash: string, verbosity?: number): Promise<Block>`
  - `getBlockHash(height: number): Promise<string>`
  - `getRawTransaction(txid: string, verbose?: boolean): Promise<Transaction | string>`
  - `sendRawTransaction(hex: string): Promise<string>`
  - `getBlockTemplate(): Promise<BlockTemplate>`
  - `submitBlock(hex: string): Promise<string | null>`

**Deliverables:** Complete typed RPC client

---

#### Session 2.2: Mock Data System
**Objective:** Enable GUI development without a running node

**Tasks:**
- Create `src/lib/rpc/mock.ts`:
  - Mock implementation of all RPC methods
  - Simulated blockchain state (genesis + 100 blocks)
  - Simulated observer mode data (fake live traffic)
  - Realistic mock transactions
- Create mock mode toggle:
  - Environment variable: `VITE_MOCK_MODE=true`
  - Runtime toggle in settings (dev only)
- Add visual indicator when in mock mode

**Deliverables:** Full mock mode for development

---

### Phase 3: Dashboard View

#### Session 3.1: Chain Status Panel
**Objective:** Display current blockchain state (for full node mode)

**Tasks:**
- Create/update `src/routes/+page.svelte` (Dashboard for full node mode)
- Create `ChainStatusCard` component:
  - Current height
  - Best block hash (linked to explorer)
  - Current difficulty
  - Chain work (formatted)
  - Median time past
- Auto-refresh every 10 seconds
- Loading skeleton while fetching
- Update Sidebar: Show Dashboard when in full node mode, Observer when in observer mode

**Deliverables:** Basic chain status display

---

#### Session 3.2: Sync Progress
**Objective:** Visualize synchronization state

**Tasks:**
- Create `SyncProgressCard` component:
  - Progress bar (headers vs blocks)
  - Estimated time remaining (if syncing)
  - Blocks/second rate
  - "Synced" badge when complete
- Create simple sync progress chart:
  - X-axis: time
  - Y-axis: block height
  - Show sync velocity over last hour

**Deliverables:** Sync visualization

---

#### Session 3.3: Peer Information
**Objective:** Display network connectivity

**Note:** Requires `getpeerinfo` RPC (not yet in bitcoin-echo).

**Tasks:**
- Create `PeersCard` component:
  - "Coming soon" placeholder initially
  - Design ready for when RPC is available
- Document needed RPC addition in bitcoin-echo backlog

**Deliverables:** Peer panel placeholder

---

### Phase 4: Block Explorer

#### Session 4.1: Block List View
**Objective:** Browse recent blocks

**Tasks:**
- Create `src/routes/blocks/+page.svelte`
- Create `BlockList` component:
  - Recent 20 blocks
  - Height, hash (truncated), time, tx count
  - Pagination (load more)
- Create `BlockListItem` component:
  - Click to view details
  - Time ago (relative)

**Deliverables:** Block list view

---

#### Session 4.2: Block Detail View
**Objective:** Display single block details

**Tasks:**
- Create `src/routes/block/[hash]/+page.svelte`
- Display:
  - Header info (version, prev hash, merkle root, time, bits, nonce)
  - Transaction list (txids, linked to tx view)
  - Size/weight
  - Navigation: prev/next block
- Handle invalid hash gracefully

**Deliverables:** Block detail view

---

### Phase 5: Transaction View

#### Session 5.1: Transaction Detail View
**Objective:** Display transaction details

**Tasks:**
- Create `src/routes/tx/[txid]/+page.svelte`
- Display:
  - Txid, wtxid
  - Version, locktime
  - Inputs list (prev txid:vout, scriptSig hex)
  - Outputs list (value, scriptPubKey hex, address if decodable)
  - Size, vsize, weight
  - Raw hex (collapsible)
- Handle mempool vs confirmed transactions

**Deliverables:** Transaction detail view

---

#### Session 5.2: Transaction Broadcast
**Objective:** Allow submitting raw transactions

**Tasks:**
- Create `src/routes/broadcast/+page.svelte`
- Form to paste raw transaction hex
- Validation (basic hex check)
- Submit via `sendrawtransaction`
- Display result (txid on success, error message on failure)
- Link to transaction view on success

**Deliverables:** Transaction broadcast functionality

---

### Phase 6: RPC Console

#### Session 6.1: Interactive Console
**Objective:** Power user JSON-RPC interface

**Tasks:**
- Create `src/routes/console/+page.svelte`
- Features:
  - Method dropdown (all 7 methods)
  - Parameter input (JSON or form-based)
  - Execute button
  - Response display (formatted JSON)
  - Request history (session)
- Syntax highlighting for JSON
- Copy response button

**Deliverables:** RPC console

---

### Phase 7: Polish & Integration Testing

#### Session 7.1: Integration Testing
**Objective:** Test against real node

**Tasks:**
- Test all views against bitcoin-echo on regtest
- Document any RPC response format issues
- Fix any discrepancies
- Add error boundaries throughout

**Deliverables:** Integration-tested GUI

---

#### Session 7.2: Visual Polish
**Objective:** Production-ready aesthetics

**Tasks:**
- Review all views for consistency
- Add animations/transitions
- Improve loading states
- Responsive design verification
- Favicon and app title
- About/credits modal

**Deliverables:** Polished GUI

---

#### Session 7.3: Documentation & Release
**Objective:** Prepare for community use

**Tasks:**
- Write README:
  - Installation
  - Configuration (RPC endpoint)
  - Development setup
  - Building for production
- Add screenshot(s)
- Create production build
- Tag v0.1.0 release

**Deliverables:** First release

---

## Progress Tracking

### Phase 0: Foundation ✅
| Session | Status | Notes |
|---------|--------|-------|
| 0.1 Repository Setup | Complete | Dec 2025 — SvelteKit + TS + Tailwind + ESLint/Prettier, Node 20 LTS |
| 0.2 Design System | Complete | Dec 2025 — Brand colors, Button/Card/Badge/Spinner/Hash components, Header/Sidebar shell |

### Phase 1: Live Observer 🎯
| Session | Status | Notes |
|---------|--------|-------|
| 1.1 Minimal RPC Client | Not Started | Blocked on Node Session 9.5 |
| 1.2 Live Network Observer View | Not Started | THE WOW MOMENT — live Bitcoin traffic |
| 1.3 Connection Settings & Polish | Not Started | |

### Phase 2: Full RPC & Mock Mode
| Session | Status | Notes |
|---------|--------|-------|
| 2.1 Complete RPC Client | Not Started | |
| 2.2 Mock Data System | Not Started | |

### Phase 3: Dashboard
| Session | Status | Notes |
|---------|--------|-------|
| 3.1 Chain Status | Not Started | |
| 3.2 Sync Progress | Not Started | |
| 3.3 Peer Info | Not Started | Requires `getpeerinfo` RPC |

### Phase 4: Block Explorer
| Session | Status | Notes |
|---------|--------|-------|
| 4.1 Block List | Not Started | |
| 4.2 Block Detail | Not Started | |

### Phase 5: Transaction View
| Session | Status | Notes |
|---------|--------|-------|
| 5.1 Transaction Detail | Not Started | |
| 5.2 Transaction Broadcast | Not Started | |

### Phase 6: RPC Console
| Session | Status | Notes |
|---------|--------|-------|
| 6.1 Interactive Console | Not Started | |

### Phase 7: Polish
| Session | Status | Notes |
|---------|--------|-------|
| 7.1 Integration Testing | Not Started | Needs node on regtest |
| 7.2 Visual Polish | Not Started | |
| 7.3 Documentation | Not Started | |

---

## Coordination with bitcoin-echo

### Parallel Development Strategy

```
bitcoin-echo                  bitcoinecho-gui
─────────────                 ───────────────
                              Phase 0: Foundation ✅ DONE

Session 9.5: Observer Mode ─► Phase 1: Live Observer
                              🎉 THE WOW MOMENT 🎉

Session 9.6: Full Integration Phase 2: Full RPC & Mock Mode
Phase 10: Mining Interface    Phase 3-5: Dashboard, Explorer, Tx Views

Phase 11: Testing & Hardening Phase 6-7: Console, Polish & Release
Phase 12: Completion          (Full integration testing)
```

**Critical Path:** Node Session 9.5 → GUI Phase 1 (Observer)

### Dependencies & Blockers

| GUI Feature | Node Requirement | Status |
|-------------|------------------|--------|
| **Live Network Observer** | Session 9.5 (`--observe` mode, observer RPCs) | **NEXT UP** 🎯 |
| Dashboard chain status | `getblockchaininfo` | Ready (after 9.6) |
| Block explorer | `getblock`, `getblockhash` | Ready (after 9.6) |
| Transaction view | `getrawtransaction` | Ready (after 9.6) |
| Transaction broadcast | `sendrawtransaction` | Ready (after 9.6) |
| Peer information | `getpeerinfo` (not implemented) | Blocked |
| Mining view | `getblocktemplate` (stub) | Partial |
| Real chain sync | Full IBD capability | Phase 11 |

### Recommended RPC Additions (backlog for bitcoin-echo)

These are NOT required for MVP but would enhance the GUI:

1. **`getpeerinfo`** — List connected peers
2. **`getmempoolinfo`** — Mempool statistics
3. **`getnetworkinfo`** — Network state
4. **`uptime`** — Node uptime

These could be added post-Phase 11 without breaking ossification (they're read-only informational endpoints, not consensus-critical).

---

## Design Philosophy for GUI

### Guiding Principles

1. **Window, Not Wall**
   The GUI is a view into the node, not a barrier. Show raw data alongside formatted data. Let users see the underlying JSON-RPC. Don't hide complexity; make it navigable.

2. **Offline-First Thinking**
   The GUI should gracefully handle:
   - Node not running
   - Node still syncing
   - Node disconnected mid-session

   Always show what state we're in. Never leave users guessing.

3. **Developer-Friendly**
   The RPC console is not a power-user afterthought—it's a first-class feature. Many users of Bitcoin Echo will be developers studying the protocol. Make exploration easy.

4. **Timeless Aesthetics**
   Avoid trendy design. Choose clean, minimal, functional. The GUI should look as appropriate in 2035 as it does today.

5. **Performance Budget**
   - Initial load: < 500KB JS
   - Time to interactive: < 2 seconds
   - No runtime CSS-in-JS (Tailwind compiles away)

---

## Open Questions

### CORS Handling

The bitcoin-echo RPC server currently doesn't send CORS headers. Options:
1. Add `Access-Control-Allow-Origin: *` to RPC responses (simplest)
2. Run GUI through a local proxy
3. Use browser extension to inject headers (development only)

**Recommendation:** Add CORS headers to bitcoin-echo. This is a one-line addition to the RPC HTTP response and doesn't affect security for a localhost-bound server.

### Authentication

The bitcoin-echo RPC has no authentication. This is fine for localhost-only binding but should be documented clearly.

**Recommendation:** Document that RPC should never be exposed to the network without authentication, which is out of scope for the ossified core.

---

## Timeline Alignment

| Milestone | bitcoin-echo | bitcoinecho-gui |
|-----------|--------------|-----------------|
| **Proof of Concept** | Phase 11 in progress | Phase 5 complete |
| **Community Testing** | Phase 11 complete | Phase 7 complete |
| **MVP Release** | Phase 12 complete | v1.0 tagged |

---

*This document guides GUI development. For core node implementation, see [ROADMAP.md](ROADMAP.md). For technical specification, see the [Whitepaper](bitcoin-echo-whitepaper.md).*

**Bitcoin Echo: The Last Implementation. Now Visible.**
