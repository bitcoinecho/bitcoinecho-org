# Bitcoin Echo: Initial Block Download Architecture

*A Technical Deep-Dive into Headers-First Sync, Parallel Block Download, and Adaptive Peer Management*

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Architectural Overview](#2-architectural-overview)
3. [Phase 1: Node Startup](#3-phase-1-node-startup)
4. [Phase 2: Headers-First Synchronization](#4-phase-2-headers-first-synchronization)
5. [Phase 3: Block Download Engine](#5-phase-3-block-download-engine)
6. [Phase 4: The Sticky Batch Racing Strategy](#6-phase-4-the-sticky-batch-racing-strategy)
7. [Phase 5: Validation Pipeline](#7-phase-5-validation-pipeline)
8. [Phase 6: Pruning During IBD](#8-phase-6-pruning-during-ibd)
9. [Constants and Tuning Parameters](#9-constants-and-tuning-parameters)
10. [Comparison: Echo vs. Bitcoin Core vs. libbitcoin](#10-comparison-echo-vs-bitcoin-core-vs-libbitcoin)
11. [Conclusion](#11-conclusion)
- [Appendix A: Data Structures](#appendix-a-data-structures)
- [Appendix B: Message Sequences](#appendix-b-message-sequences)
- [Appendix C: References](#appendix-c-references)

---

## 1. Introduction

Initial Block Download (IBD) is the critical bootstrapping process where a new Bitcoin node downloads and validates the entire blockchain history. For Bitcoin Echo, this means processing nearly 1 million blocks totaling over 700 GB of data, validating millions of transactions, and building the complete UTXO set from genesis.

This document describes Bitcoin Echo's IBD architecture in exhaustive detail. Our design draws inspiration from libbitcoin's elegant PULL-based work distribution while introducing novel mechanisms for peer racing and stall recovery.

### 1.1 Design Goals

1. **Maximize throughput**: Download and validate blocks as fast as network and CPU allow
2. **Minimize latency sensitivity**: Don't let one slow peer block the entire chain
3. **Graceful degradation**: Handle peer failures, network issues, and malicious actors
4. **Observable progress**: Provide real-time metrics for monitoring sync status
5. **Pruning support**: Optionally discard old block data to limit disk usage

### 1.2 Key Innovations

| Innovation | Problem Solved |
|------------|----------------|
| **Single-peer header racing** | Find fastest header peer without duplicate downloads |
| **PULL-based work distribution** | Peers request work when ready, not when assigned |
| **8-block atomic batches** | Balance parallelism against head-of-line blocking |
| **Sticky batch racing** | Recover from blocking peers cooperatively via redundancy |
| **Epoch-based adaptive timeouts** | Early blocks are tiny; later blocks need more time |
| **Deferred header persistence** | Batch ~1M header writes into single transaction |

---

## 2. Architectural Overview

### 2.1 The Complete IBD Pipeline

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           INITIAL BLOCK DOWNLOAD                            │
└─────────────────────────────────────────────────────────────────────────────┘

┌──────────────┐    ┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│   STARTUP    │───▶│   HEADERS    │───▶│    BLOCKS    │───▶│    DONE      │
│              │    │    SYNC      │    │    SYNC      │    │              │
│ Parse args   │    │              │    │              │    │ Tip reached  │
│ Init storage │    │ Race peers   │    │ PULL batches │    │ UTXO frozen  │
│ Load state   │    │ Validate PoW │    │ Validate all │    │ Mempool on   │
│ Connect DNS  │    │ Flush to DB  │    │ Prune as go  │    │              │
└──────────────┘    └──────────────┘    └──────────────┘    └──────────────┘
      │                    │                   │                    │
      │                    │                   │                    │
      ▼                    ▼                   ▼                    ▼
┌──────────────────────────────────────────────────────────────────────────┐
│                              PEER LAYER                                  │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐             │
│  │ Peer A  │ │ Peer B  │ │ Peer C  │ │ Peer D  │ │  ...    │             │
│  │ 45 KB/s │ │ 120KB/s │ │ 80 KB/s │ │ STALLED │ │         │             │
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘ └─────────┘             │
└──────────────────────────────────────────────────────────────────────────┘
```

### 2.2 Core Components

| Component | Responsibility |
|-----------|----------------|
| **Sync Manager** | Orchestrates mode transitions, peer selection, work queuing |
| **Download Manager** | PULL-based batch distribution, peer performance tracking |
| **Chase System** | Event-driven validation pipeline coordination |
| **Block Storage** | Append-only block files with pruning support |
| **Block Index DB** | SQLite storage for headers, chainwork, block locations |
| **UTXO DB** | SQLite UTXO set (deferred writes during IBD) |

### 2.3 Data Flow During Block Sync

```
                    ┌─────────────────┐
                    │  HEADER CHAIN   │
                    │  (in memory)    │
                    └────────┬────────┘
                             │
                    ┌────────▼────────┐
                    │  WORK QUEUE     │
                    │  [h₁][h₂]...[hₙ]│
                    └────────┬────────┘
                             │
         ┌───────────────────┼───────────────────┐
         │                   │                   │
         ▼                   ▼                   ▼
   ┌──────────┐        ┌──────────┐        ┌──────────┐
   │  Peer A  │        │  Peer B  │        │  Peer C  │
   │ Batch 1  │        │ Batch 2  │        │ Batch 3  │
   │ [h₁-h₈]  │        │ [h₉-h₁₆] │        │[h₁₇-h₂₄] │
   └────┬─────┘        └────┬─────┘        └────┬─────┘
        │                   │                   │
        │    ┌──────────────┼──────────────┐    │
        │    │              │              │    │
        ▼    ▼              ▼              ▼    ▼
   ┌─────────────────────────────────────────────────┐
   │              VALIDATION PIPELINE                │
   │                                                 │
   │  ┌─────────┐   ┌─────────┐   ┌─────────┐        │
   │  │ CHECKED │──▶│  VALID  │──▶│ORGANIZED│        │
   │  │(scripts)│   │(chainwk)│   │ (UTXO)  │        │
   │  └─────────┘   └─────────┘   └─────────┘        │
   │                                                 │
   │  Sequential at validation tip (height H)        │
   │  Parallel download of blocks H+1 to H+100000    │
   └─────────────────────────────────────────────────┘
```

---

## 3. Phase 1: Node Startup

### 3.1 Command-Line Processing

When the user executes:

```bash
./echo --prune=1024
```

The argument parser extracts configuration:

```c
typedef struct {
    uint64_t prune_target_mb;    // 1024 MB in this case
    char data_dir[PATH_MAX];     // ~/.bitcoin-echo/
    log_level_t log_level;       // INFO by default
    // ... additional options
} node_config_t;
```

**Pruning validation**: The minimum prune target is 550 MB (per Bitcoin Core compatibility). This ensures at least one complete epoch of blocks can be stored for serving to other pruned nodes.

### 3.2 Initialization Sequence

```
┌─────────────────────────────────────────────────────────────────┐
│                    NODE INITIALIZATION                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. Platform Layer                                              │
│     └── Socket abstractions, threading, entropy                 │
│                                                                 │
│  2. Data Directory                                              │
│     └── Create ~/.bitcoin-echo/blocks/                          │
│                                                                 │
│  3. Database Initialization                                     │
│     ├── block_index_db (headers, chainwork, file positions)     │
│     └── utxo_db (unspent outputs, empty at start)               │
│                                                                 │
│  4. Block Storage Manager                                       │
│     └── Initialize blk*.dat file handler                        │
│                                                                 │
│  5. Consensus Engine                                            │
│     └── Load chainstate or initialize to genesis                │
│                                                                 │
│  6. Sync Manager                                                │
│     ├── Download manager (PULL-based work distribution)         │
│     └── Chase dispatcher (validation event coordination)        │
│                                                                 │
│  7. Peer Discovery                                              │
│     └── DNS seed resolution, begin outbound connections         │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 3.3 Block Storage Format

Block data is stored in Bitcoin Core-compatible `blk*.dat` files:

```
┌────────────────────────────────────────────────────────┐
│                    blk00000.dat                        │
├────────────────────────────────────────────────────────┤
│  ┌──────────────────────────────────────────────────┐  │
│  │ Network Magic    │ 4 bytes  │ 0xF9BEB4D9         │  │
│  │ Block Size       │ 4 bytes  │ little-endian      │  │
│  │ Block Data       │ N bytes  │ header + txs       │  │
│  └──────────────────────────────────────────────────┘  │
│  ┌──────────────────────────────────────────────────┐  │
│  │ Network Magic    │ 4 bytes  │ 0xF9BEB4D9         │  │
│  │ Block Size       │ 4 bytes  │ little-endian      │  │
│  │ Block Data       │ N bytes  │ header + txs       │  │
│  └──────────────────────────────────────────────────┘  │
│  ... (continues until file reaches 128 MB)             │
└────────────────────────────────────────────────────────┘
```

**File management parameters**:
- Maximum file size: 128 MB
- Flush interval: Every 100 blocks
- File handle: Kept open during IBD to avoid syscall overhead

### 3.4 IBD Mode Detection

When the node starts with an empty or incomplete chainstate, IBD mode is activated:

```c
bool sync_is_ibd(sync_manager_t *mgr) {
    // IBD if our validated tip is significantly behind best known header
    return (best_header_height - validated_height) > IBD_THRESHOLD;
}
```

**IBD mode optimizations**:
1. **Deferred UTXO writes**: Instead of writing each UTXO change to SQLite, accumulate in memory and flush at end
2. **Reduced mempool activity**: Don't accept unconfirmed transactions until sync complete
3. **Tolerant peer management**: Keep slow-but-working peers; only disconnect truly stalled peers

---

## 4. Phase 2: Headers-First Synchronization

### 4.1 Why Headers First?

Before downloading full blocks, we first download and validate all 80-byte block headers. This provides:

1. **Chain validation**: Verify proof-of-work and difficulty adjustments without full blocks
2. **Download planning**: Know exactly which blocks to request and in what order
3. **Fork detection**: Identify the best chain before committing to full download
4. **Efficiency**: 80 bytes per header vs. ~1-2 MB per block

**Total header data**: ~1,000,000 headers × 80 bytes = ~80 MB

### 4.2 The Peer Racing Mechanism

Rather than requesting headers from multiple peers simultaneously (which wastes bandwidth on duplicates), Echo uses a **single-peer model with periodic racing**:

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    HEADER PEER RACING STRATEGY                          │
└─────────────────────────────────────────────────────────────────────────┘

PHASE 1: Discovery (first getheaders)
─────────────────────────────────────
    Send getheaders to up to 4 peers simultaneously

    Peer A ──────────────────────────────▶ [2000 headers, 156ms] ✓ WINNER
    Peer B ──────────────────────────────────────▶ [2000 headers, 312ms]
    Peer C ────────────────────────────────────────────▶ [2000 headers, 450ms]
    Peer D ────────────────────────────────────────────────────▶ [timeout]

    Result: Peer A becomes active_header_peer

PHASE 2: Continuous Sync (single peer)
──────────────────────────────────────
    Peer A ◀──▶ getheaders/headers ◀──▶ getheaders/headers ◀──▶ ...

    Batch 1    Batch 2    Batch 3    PROBE!
    [2000]     [2000]     [2000]     ↓

PHASE 3: Periodic Probing (every 3 batches)
───────────────────────────────────────────
    At batch 3, 6, 9, ...:

    ┌─────────────────────────────────────────────────────┐
    │ Send getheaders to BOTH active peer AND challenger  │
    │                                                     │
    │   Active (Peer A) ─────────────▶ [2000, 180ms]      │
    │   Challenger (Peer B) ────────▶ [2000, 95ms] ✓      │
    │                                                     │
    │   Peer B responds faster → switch active peer       │
    └─────────────────────────────────────────────────────┘

    Both responses are processed (no wasted data)
    Faster peer becomes new active peer
```

**Slow peer detection**: If the active peer takes longer than 2 seconds for a batch, immediately probe for alternatives without waiting for the 3-batch interval.

### 4.3 Header Validation

Each header undergoes validation before acceptance:

```
┌─────────────────────────────────────────────────────────────────┐
│                    HEADER VALIDATION CHECKS                     │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. Previous Block Reference                                    │
│     └── prev_block_hash must exist in our chain                 │
│                                                                 │
│  2. Proof of Work                                               │
│     └── SHA256d(header) ≤ target derived from nBits             │
│                                                                 │
│  3. Timestamp Bounds                                            │
│     ├── Must be > median of last 11 blocks                      │
│     └── Must be < current_time + 2 hours                        │
│                                                                 │
│  4. Difficulty Adjustment (every 2016 blocks)                   │
│     └── nBits must follow retargeting rules                     │
│                                                                 │
│  5. Version Rules                                               │
│     └── BIP-34/65/66/141 activation at specified heights        │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 4.4 Deferred Header Persistence

A critical optimization: during `SYNC_MODE_HEADERS`, headers are queued in memory rather than written to SQLite individually.

```
NAIVE APPROACH (slow):
──────────────────────
    for each header (~1,000,000 times):
        BEGIN TRANSACTION
        INSERT INTO headers ...
        COMMIT

    Result: ~10 minutes of SQLite thrashing

ECHO APPROACH (fast):
─────────────────────
    Accumulate headers in memory array:
    pending_headers[] = [h₁, h₂, h₃, ... h₁₀₀₀₀₀₀]

    On transition to BLOCKS mode:
        BEGIN TRANSACTION
        for each header:
            INSERT INTO headers ...
        COMMIT  (single transaction for all ~1M headers)

    Result: ~30 seconds total
```

### 4.5 Block Locator Construction

The `getheaders` message includes a **locator** — a list of block hashes that helps peers find our fork point:

```
LOCATOR STRUCTURE (exponential stride):
───────────────────────────────────────

Position in chain:    Locator index:
────────────────────  ──────────────
tip                   [0]  ← Most recent
tip - 1               [1]
tip - 2               [2]
...                   ...
tip - 10              [10] ← Last consecutive (11 entries)
tip - 11              [11] ← Still step=1, but step doubles after this
tip - 13              [12] ← First at step=2
tip - 15              [13]
tip - 19              [14] ← Step doubles to 4
tip - 23              [15]
...                   ...
genesis               [31] ← Always included

Maximum locator size: 32 hashes

Note: Step doubles every 2 entries after the first 10 entries.
```

This logarithmic structure allows peers to find the fork point efficiently regardless of how far back it is.

### 4.6 Mode Transition: HEADERS → BLOCKS

When we receive fewer than 2,000 headers (indicating peer has no more), and our best header height exceeds our validated tip:

```c
if (headers_received < SYNC_MAX_HEADERS_PER_REQUEST &&
    best_header_height > validated_height) {

    // Flush all pending headers to database
    pending_headers_flush(mgr);

    // Transition to block download mode
    mgr->mode = SYNC_MODE_BLOCKS;
    mgr->block_sync_start_time = current_time_ms();
    mgr->block_sync_start_height = validated_height;
}
```

---

## 5. Phase 3: Block Download Engine

### 5.1 The PULL Model Philosophy

Unlike Bitcoin Core's PUSH model (coordinator assigns work to peers), Echo uses libbitcoin's PULL model:

```
PUSH MODEL (Bitcoin Core):
──────────────────────────
    Coordinator: "Peer A, download blocks 1-16"
    Coordinator: "Peer B, download blocks 17-32"
    Coordinator: "Peer C, download blocks 33-48"

    Problem: What if Peer A is slow? Coordinator must track and reassign.

PULL MODEL (Echo):
──────────────────
    Peer A: "I'm ready for work" → Gets batch [1-8]
    Peer B: "I'm ready for work" → Gets batch [9-16]
    Peer A: "Done! Ready for more" → Gets batch [17-24]
    Peer C: "I'm ready for work" → Gets batch [25-32]

    Advantage: Fast peers automatically get more work.
               Slow peers naturally get less.
               No coordinator overhead for reassignment.
```

### 5.2 The 8-Block Batch

Work is organized into **atomic batches of 8 blocks**:

```c
typedef struct work_batch {
    hash256_t hashes[8];           // Block hashes to download
    uint32_t heights[8];           // Corresponding heights
    size_t count;                  // Actual blocks in batch (≤8)
    size_t remaining;              // Blocks not yet received
    uint64_t assigned_time;        // When assigned (0 if queued)
    bool received[8];              // Bitmap: which blocks arrived
    bool sticky;                   // Sticky batch flag (racing)
    uint32_t sticky_height;        // Height that resolves sticky
} work_batch_t;
```

**Why 8 blocks?**

| Batch Size | Pros | Cons |
|------------|------|------|
| 1 block | Minimal blocking | High per-request overhead |
| 8 blocks | Balance of parallelism and efficiency | Moderate blocking window |
| 16 blocks (Core) | Lower overhead | Significant head-of-line blocking |

The choice of 8 was determined empirically during IBD optimization testing.

### 5.3 Work Queue Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                       WORK QUEUE                                │
│                   (doubly-linked list)                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  queue_head                                        queue_tail   │
│      │                                                  │       │
│      ▼                                                  ▼       │
│  ┌───────┐    ┌───────┐    ┌───────┐    ┌───────┐    ┌───────┐  │
│  │Batch 1│◀──▶│Batch 2│◀──▶│Batch 3│◀──▶│Batch 4│◀──▶│Batch 5│  │
│  │[1-8]  │    │[9-16] │    │[17-24]│    │[25-32]│    │[33-40]│  │
│  │STICKY │    │       │    │       │    │       │    │       │  │
│  └───────┘    └───────┘    └───────┘    └───────┘    └───────┘  │
│                                                                 │
│  Operations:                                                    │
│  ──────────                                                     │
│  • queue_push_back()  : New work added at tail                  │
│  • queue_pop_front()  : Assignments taken from head             │
│  • queue_push_front() : Returned work goes to head (priority)   │
│                                                                 │
│  Height tracking:                                               │
│  ────────────────                                               │
│  • lowest_pending_height  = 1   (lowest queued/assigned)        │
│  • highest_queued_height  = 40  (highest ever added)            │
│                                                                 │
│  Capacity: 200 batches maximum (1,600 blocks)                   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 5.4 Peer Performance Tracking

Each peer has associated performance metrics:

```c
typedef struct {
    peer_t *peer;                 // The peer connection
    work_batch_t *batch;          // Currently assigned batch (NULL if idle)

    // Performance window (10 seconds)
    uint64_t bytes_this_window;   // Bytes received in current window
    uint64_t window_start_time;   // When window started
    float bytes_per_second;       // Calculated throughput

    // Delivery tracking
    uint64_t last_delivery_time;  // Time of most recent block
    uint64_t first_work_time;     // When first assigned (grace period)
    bool has_reported;            // True if ever delivered bytes
} peer_perf_t;
```

**Performance calculation** (every 10 seconds):
```c
static void update_peer_window(peer_perf_t *perf, uint64_t now) {
    uint64_t elapsed = now - perf->window_start_time;
    if (elapsed >= DOWNLOAD_PERF_WINDOW_MS) {
        perf->bytes_per_second = (float)perf->bytes_this_window /
                                 ((float)elapsed / 1000.0f);

        /* Only mark as "reported" if they actually delivered bytes */
        if (perf->bytes_per_second > 0.0f) {
            perf->has_reported = true;
        }

        /* Reset window */
        perf->bytes_this_window = 0;
        perf->window_start_time = now;
    }
}
```

### 5.5 The PULL API

#### `peer_request_work()` — Peer asks for work

```
┌─────────────────────────────────────────────────────────────────┐
│                    peer_request_work() FLOW                     │
└─────────────────────────────────────────────────────────────────┘

    Peer calls: "I'm idle, give me work"
                        │
                        ▼
              ┌─────────────────┐
              │ Queue empty?    │
              └────────┬────────┘
                       │
           ┌───────────┴───────────┐
           │                       │
           ▼                       ▼
    ┌─────────────┐         ┌─────────────┐
    │    YES      │         │     NO      │
    │             │         │             │
    │ Return false│         │ Check head  │
    │ (peer idle) │         │ of queue    │
    └─────────────┘         └──────┬──────┘
                                   │
                        ┌──────────┴──────────┐
                        │                     │
                        ▼                     ▼
                 ┌─────────────┐       ┌─────────────┐
                 │   STICKY    │       │   NORMAL    │
                 │   batch     │       │   batch     │
                 │             │       │             │
                 │ Clone batch │       │ Pop batch   │
                 │ Keep in Q   │       │ from queue  │
                 └──────┬──────┘       └──────┬──────┘
                        │                     │
                        └──────────┬──────────┘
                                   │
                                   ▼
                        ┌─────────────────────┐
                        │ Assign to peer      │
                        │ Send getdata(8 hashes)
                        │ Record assigned_time│
                        └─────────────────────┘
```

#### Empty Queue Handling — Cooperative Model

When a peer requests work but the queue is empty:

```c
bool download_mgr_peer_request_work(download_mgr_t *mgr, peer_t *peer) {
    if (mgr->queue_head == NULL) {
        /* No work available - peer simply waits */
        return false;
    }

    /* Assign work to peer... */
}
```

**Philosophy**: Cooperative, not punitive. When a peer finds no available work:
- They simply wait for more work to be queued (returns `false`)
- No other peers are penalized or disconnected
- Work is only returned to the queue when a peer is explicitly removed
- The sticky batch mechanism adds redundancy for blocking blocks without punishing anyone

This differs from libbitcoin's "sacrifice" model. Our approach prioritizes peer retention over aggressive throughput optimization. Slow-but-working peers still contribute blocks, and connection establishment is not free. Only truly stalled peers (0 B/s for extended periods) are disconnected via the periodic performance check.

### 5.6 Block Receipt Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    BLOCK RECEIPT FLOW                           │
└─────────────────────────────────────────────────────────────────┘

    Block arrives from network
              │
              ▼
    ┌─────────────────────┐
    │ Find block in       │
    │ peer's batch        │
    └──────────┬──────────┘
               │
               ▼
    ┌─────────────────────┐
    │ Already received?   │───── YES ────▶ Ignore (sticky race case)
    │ (received[i] set)   │
    └──────────┬──────────┘
               │ NO
               ▼
    ┌─────────────────────┐
    │ Mark received[i]    │
    │ Decrement remaining │
    │ Update bytes_this_  │
    │   window            │
    │ Update last_delivery│
    │   _time             │
    └──────────┬──────────┘
               │
               ▼
    ┌─────────────────────┐
    │ remaining == 0?     │───── NO ────▶ Wait for more blocks
    └──────────┬──────────┘
               │ YES
               ▼
    ┌─────────────────────┐
    │ Batch complete!     │
    │ Peer becomes idle   │
    │ Ready for new work  │
    └─────────────────────┘
```

The `received[]` bitmap is critical for handling the sticky batch racing case, where multiple peers may be downloading the same blocks. Without it, we'd double-decrement `remaining` and corrupt batch state.

---

## 6. Phase 4: The Sticky Batch Racing Strategy

### 6.1 The Head-of-Line Blocking Problem

Consider this scenario:

```
Validation tip: height 499 (waiting for block 500)

Peer A: batch [500-507], assigned 5 seconds ago, no deliveries
Peer B: batch [508-515], completed, idle
Peer C: batch [516-523], completed, idle
Peer D: batch [524-531], completed, idle

Downloaded: 499 + [500-531] potential
Validated:  499

PROBLEM: Peers B, C, D are idle and willing to help,
         but validation is blocked on Peer A's block 500.
```

**Naive solutions and their problems**:

| Solution | Problem |
|----------|---------|
| Kill Peer A immediately | What if they deliver in 100ms? Wasted connection. |
| Wait indefinitely | Sync stalls. Bad user experience. |
| Reassign work | Peer A might still deliver, causing duplicate processing. |

### 6.2 The Sticky Batch Solution

Echo introduces **sticky batch racing**: create a high-priority clone of the blocking batch that multiple peers can work on simultaneously.

```
┌─────────────────────────────────────────────────────────────────┐
│                    STICKY BATCH CREATION                        │
└─────────────────────────────────────────────────────────────────┘

BEFORE (Peer A blocking):
─────────────────────────

    Queue: [empty]

    Peer A: [500-507] ← blocking validation at 499
    Peer B: idle
    Peer C: idle

AFTER (sticky batch created):
─────────────────────────────

    Queue: [500-507 STICKY] ← cloned from Peer A's batch
           ↑
           Multiple peers can take clones of this

    Peer A: [500-507] ← still has original, may still deliver
    Peer B: [500-507] ← clone of sticky batch
    Peer C: [500-507] ← clone of sticky batch

RESOLUTION:
───────────

    Whoever delivers block 500 first wins.
    When validated_height reaches 500:
    - Remove sticky batch from queue
    - Reset stall timer
    - Continue normal operation
```

### 6.3 Stall Detection Algorithm

```
┌─────────────────────────────────────────────────────────────────┐
│                    STALL DETECTION FLOW                         │
└─────────────────────────────────────────────────────────────────┘

    sync_tick() periodic call
              │
              ▼
    ┌─────────────────────┐
    │ validated_height    │
    │ increased?          │
    └──────────┬──────────┘
               │
       ┌───────┴───────┐
       │               │
       ▼               ▼
    ┌─────┐         ┌─────┐
    │ YES │         │ NO  │
    │     │         │     │
    │Reset│         │Check│
    │timer│         │stall│
    └─────┘         └──┬──┘
                       │
                       ▼
              ┌─────────────────────┐
              │ Calculate timeout   │
              │ (epoch-based)       │
              └──────────┬──────────┘
                         │
    Epoch 0 (0-209,999):      1 second base
    Epoch 1 (210,000-419,999): 2 seconds base
    Epoch 2 (420,000-629,999): 3 seconds base
    ...

    With exponential backoff on repeated stalls:
    timeout = base × 2^(backoff_count)
    Maximum: 64 seconds
                         │
                         ▼
              ┌─────────────────────┐
              │ Stalled longer than │
              │ timeout?            │
              └──────────┬──────────┘
                         │
                 ┌───────┴───────┐
                 │               │
                 ▼               ▼
              ┌─────┐         ┌─────────────────┐
              │ NO  │         │      YES        │
              │     │         │                 │
              │Wait │         │ Find blocking   │
              └─────┘         │ peer and batch  │
                              └────────┬────────┘
                                       │
                                       ▼
                              ┌─────────────────┐
                              │ Create sticky   │
                              │ batch for racing│
                              │ (cooperative)   │
                              └────────┬────────┘
                                       │
                                       ▼
                              ┌─────────────────┐
                              │ Other idle peers│
                              │ can clone sticky│
                              │ and race for    │
                              │ blocking block  │
                              └─────────────────┘

Note: Stalled peers (0 B/s) are disconnected separately
via download_mgr_check_performance(), not here.
```

### 6.4 Why Epoch-Based Timeouts?

Early blocks in the blockchain are tiny (~200-300 bytes), while later blocks can be 1-4 MB. A fixed timeout would be:
- Too aggressive for large blocks (false positives)
- Too lenient for small blocks (slow recovery)

```
BLOCK SIZE PROGRESSION:
───────────────────────

Height        Typical Size    Epoch    Base Timeout
──────────    ────────────    ─────    ────────────
0-50,000      200-500 bytes   0        1 second
100,000       500 bytes       0        1 second
200,000       500 KB          0        1 second
300,000       800 KB          1        2 seconds
500,000       1.2 MB          2        3 seconds
700,000       2.0 MB          3        4 seconds
850,000       2-4 MB          4        5 seconds
```

### 6.5 Sticky Batch Resolution

When validation progresses past the sticky height:

```c
void check_sticky_resolution(download_mgr_t *mgr, uint32_t validated_height) {
    if (mgr->queue_head != NULL &&
        mgr->queue_head->batch.sticky &&
        mgr->queue_head->batch.sticky_height <= validated_height) {

        // Sticky batch is no longer needed
        batch_node_t *resolved = queue_pop_front(mgr);
        batch_node_destroy(resolved);

        log_debug("Sticky batch resolved at height %u", validated_height);
    }
}
```

---

## 7. Phase 5: Validation Pipeline

### 7.1 The Chase Event System

Echo uses an event-driven architecture called the **Chase System** for coordinating validation stages:

```
┌─────────────────────────────────────────────────────────────────┐
│                    CHASE EVENT FLOW                             │
└─────────────────────────────────────────────────────────────────┘

    Block received from network
              │
              ▼
    ┌─────────────────────┐
    │   CHASE_CHECKED     │  Structural validation complete
    │   (scripts pending) │  (magic, size, merkle root)
    └──────────┬──────────┘
               │
               ▼
    ┌─────────────────────┐
    │   CHASE_VALID       │  Script validation complete
    │   (parallel on      │  (all inputs verified)
    │    threadpool)      │
    └──────────┬──────────┘
               │
               ▼
    ┌─────────────────────┐
    │  CHASE_CONFIRMABLE  │  Ready for chain organization
    │                     │
    └──────────┬──────────┘
               │
               ▼
    ┌─────────────────────┐
    │  CHASE_ORGANIZED    │  Block added to main chain
    │  (UTXO updated)     │  (tip advanced)
    └──────────┬──────────┘
               │
               ▼
         [Next block]


FEEDBACK EVENTS:
────────────────

    ┌─────────────────────┐
    │   CHASE_STARVED     │  Validation needs more blocks
    └──────────┬──────────┘  → Trigger peer_request_work()
                              (peer waits if queue empty)
```

### 7.2 Validation Stages

```
┌─────────────────────────────────────────────────────────────────┐
│                    BLOCK VALIDATION STAGES                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  STAGE 1: Structural Checks (immediate, single-threaded)        │
│  ─────────────────────────────────────────────────────────      │
│  • Network magic bytes correct                                  │
│  • Block size within limits                                     │
│  • Header hash matches claimed                                  │
│  • Merkle root matches transaction list                         │
│  • Timestamp within bounds                                      │
│  • Coinbase transaction present and valid format                │
│                                                                 │
│  STAGE 2: Script Validation (parallel on threadpool)            │
│  ───────────────────────────────────────────────────            │
│  • For each transaction input:                                  │
│    - Find referenced UTXO                                       │
│    - Execute scriptSig + scriptPubKey                           │
│    - Verify signatures (ECDSA or Schnorr)                       │
│  • Parallelized across CPU cores                                │
│  • ~50% of total validation time                                │
│                                                                 │
│  STAGE 3: Chain Organization (sequential)                       │
│  ────────────────────────────────────────                       │
│  • Add block to main chain                                      │
│  • Update UTXO set:                                             │
│    - Remove spent outputs                                       │
│    - Add new outputs                                            │
│  • Update chainwork                                             │
│  • Advance tip height                                           │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 7.3 The Validation Bottleneck

Even with parallel script validation, chain organization is inherently sequential. This creates a natural bottleneck:

```
DOWNLOAD vs. VALIDATION RATES:
──────────────────────────────

    Download: Can fetch blocks 1-1000 in parallel from 8 peers
              Rate: ~100 blocks/second (network limited)

    Validation: Must process blocks sequentially (1, 2, 3, ...)
                Rate: ~25 blocks/second (CPU limited)

    Result: Downloaded blocks queue up waiting for validation
            "pending_validation" metric shows the gap

    ┌────────────────────────────────────────────────────────┐
    │                                                        │
    │  Downloaded:  ████████████████████████░░░░░░░░░░░░░░   │
    │  Validated:   █████████░░░░░░░░░░░░░░░░░░░░░░░░░░░░░   │
    │                        ↑                               │
    │                        pending_validation              │
    │                                                        │
    └────────────────────────────────────────────────────────┘
```

This is why the sticky batch mechanism targets the **validation tip**, not the download tip. We optimize for unblocking validation, not maximizing download parallelism.

### 7.4 UTXO Handling During IBD

During IBD, UTXO updates are deferred to avoid SQLite thrashing:

```
NAIVE APPROACH (during IBD):
────────────────────────────
    For each block:
        For each transaction:
            For each input:
                DELETE FROM utxos WHERE outpoint = ?
            For each output:
                INSERT INTO utxos VALUES (?, ?, ?)

    Result: Millions of individual SQLite operations

ECHO APPROACH:
──────────────
    During IBD:
        Maintain UTXO set in memory (hash map)
        Don't write to SQLite

    At IBD completion:
        Single bulk write of entire UTXO set
        BEGIN TRANSACTION
        INSERT INTO utxos VALUES ... (bulk)
        COMMIT

    Result: One transaction instead of millions
```

---

## 8. Phase 6: Pruning During IBD

### 8.1 Pruning Mode Activation

When `--prune=1024` is specified:

```c
if (config->prune_target_mb > 0) {
    // Validate minimum
    if (config->prune_target_mb < PRUNE_TARGET_MIN_MB) {
        log_error("Prune target must be >= %d MB", PRUNE_TARGET_MIN_MB);
        return false;
    }

    node->pruning_enabled = true;
    node->prune_target_bytes = config->prune_target_mb * 1024 * 1024;
}
```

### 8.2 Service Advertisement

Pruned nodes advertise differently than archival nodes:

```
┌─────────────────────────────────────────────────────────────────┐
│                    SERVICE FLAGS                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ARCHIVAL NODE:                                                 │
│  ──────────────                                                 │
│  Services: NODE_NETWORK | NODE_WITNESS                          │
│  Meaning: "I have all blocks, ask me for anything"              │
│                                                                 │
│  PRUNED NODE:                                                   │
│  ────────────                                                   │
│  Services: NODE_NETWORK_LIMITED | NODE_WITNESS                  │
│  Meaning: "I have recent blocks only (last 288+)"               │
│                                                                 │
│  IMPLICATION FOR IBD:                                           │
│  ────────────────────                                           │
│  • We request blocks only from NODE_NETWORK peers               │
│  • We don't waste time asking pruned peers for old blocks       │
│  • After IBD, we can serve recent blocks to other pruned nodes  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 8.3 Pruning Mechanics

Pruning is triggered periodically during sync:

```
┌─────────────────────────────────────────────────────────────────┐
│                    PRUNING FLOW                                 │
└─────────────────────────────────────────────────────────────────┘

    Periodic check (e.g., every 1000 blocks validated)
              │
              ▼
    ┌─────────────────────┐
    │ Calculate current   │
    │ blocks/ disk usage  │
    └──────────┬──────────┘
               │
               ▼
    ┌─────────────────────┐
    │ Usage > prune_target│──── NO ────▶ Done (nothing to prune)
    │ (1024 MB)?          │
    └──────────┬──────────┘
               │ YES
               ▼
    ┌─────────────────────┐
    │ Find oldest blk*.dat│
    │ file (by index)     │
    └──────────┬──────────┘
               │
               ▼
    ┌─────────────────────────────┐
    │ File max height >=         │
    │ validated height?          │──── YES ────▶ Skip (unvalidated blocks)
    └──────────┬──────────────────┘
               │ NO (safe to delete)
               ▼
    ┌─────────────────────┐
    │ Update block index: │
    │ Mark blocks as      │
    │ BLOCK_STATUS_PRUNED │
    └──────────┬──────────┘
               │
               ▼
    ┌─────────────────────┐
    │ Delete blk*.dat file│
    │ (atomic, per-file)  │
    └──────────┬──────────┘
               │
               ▼
    ┌─────────────────────┐
    │ Repeat until usage  │
    │ < prune_target      │
    └─────────────────────┘
```

**Critical safety check**: During IBD, blocks are downloaded ahead of validation. Before deleting any block file, we verify that all blocks in the file have been validated (file's max height < validated height). This prevents deleting blocks that validation still needs, which would cause an infinite retry loop.

### 8.4 Block Status Tracking

The block index tracks pruning status:

```c
typedef enum {
    BLOCK_STATUS_VALID_HEADER  = 0x01,  // Header validated
    BLOCK_STATUS_VALID_TREE    = 0x02,  // Ancestors valid
    BLOCK_STATUS_VALID_SCRIPTS = 0x04,  // Scripts validated
    BLOCK_STATUS_VALID_CHAIN   = 0x08,  // On main chain
    BLOCK_STATUS_HAVE_DATA     = 0x10,  // Full block data stored
    BLOCK_STATUS_FAILED        = 0x20,  // Validation failed
    BLOCK_STATUS_PRUNED        = 0x40,  // Block data pruned
} block_status_flags_t;
```

After pruning, a block has:
- `BLOCK_STATUS_VALID_CHAIN` set (we validated it)
- `BLOCK_STATUS_HAVE_DATA` cleared
- `BLOCK_STATUS_PRUNED` set

This allows the node to know the block was valid without having the data.

### 8.5 Interaction with Download Window

Importantly, pruning does **not** limit the download window during IBD:

```
COMMON MISCONCEPTION:
─────────────────────
    "Pruned node should only download 1024 MB of blocks at a time"

    WRONG! This would serialize downloads and destroy parallelism.

CORRECT BEHAVIOR:
─────────────────
    Download window: 100,000 blocks ahead (same as archival)
    Storage: Write all blocks to disk
    Pruning: Delete old blocks periodically to stay under target

    Result: Full parallelism, disk space bounded by prune target
```

---

## 9. Constants and Tuning Parameters

### 9.1 Sync Manager Constants

```c
/* sync.h - Header sync */
#define SYNC_MAX_HEADERS_PER_REQUEST     2000    // Headers per getheaders
#define SYNC_MAX_LOCATOR_HASHES          32      // Block locator size
#define SYNC_HEADERS_TIMEOUT_MS          30000   // 30 seconds
#define SYNC_HEADER_RETRY_INTERVAL_MS    5000    // 5 seconds between retries
#define SYNC_HEADER_REFRESH_INTERVAL_MS  30000   // Check for new headers

/* sync.c - Header peer racing (internal to sync module) */
#define HEADER_PROBE_INTERVAL            3       // Race every 3 batches
#define HEADER_SLOW_THRESHOLD_MS         2000    // >2s = probe immediately

/* sync.h - Block sync */
#define SYNC_BLOCK_DOWNLOAD_WINDOW       100000  // Blocks to queue ahead
#define SYNC_STALE_TIP_THRESHOLD_MS      1800000 // 30 minutes (stall abort)

/* download_mgr.c - Stall detection (internal to download module) */
#define STALL_EPOCH_BLOCKS               210000  // Blocks per halving epoch
#define STALL_MS_PER_EPOCH               1000    // 1 second per epoch
#define STALL_MAX_TIMEOUT_MS             64000   // 64 second maximum
```

### 9.2 Download Manager Constants

```c
/* download_mgr.h */
#define DOWNLOAD_BATCH_SIZE              8       // Blocks per batch
#define DOWNLOAD_BATCH_SIZE_MAX          8       // Maximum batch size
#define DOWNLOAD_MAX_BATCHES             200     // Queue capacity (1600 blocks)
#define DOWNLOAD_MAX_PEERS               256     // Maximum tracked peers
#define DOWNLOAD_PERF_WINDOW_MS          10000   // 10 second performance window
#define DOWNLOAD_MIN_PEERS_TO_KEEP       3       // Never evict below this count
```

Note: We deliberately avoid speed-based peer eviction. Slow-but-working peers still contribute blocks, and more slow peers beats fewer fast peers for download parallelism. Only truly stalled peers (zero bytes for extended periods) are disconnected.

### 9.3 Block Storage Constants

```c
/* blocks_storage.h */
#define BLOCK_FILE_MAX_SIZE              (128 * 1024 * 1024)  // 128 MB per file
#define BLOCK_STORAGE_FLUSH_INTERVAL     100     // Flush every 100 blocks

/* Pruning */
#define PRUNE_TARGET_MIN_MB              550     // Minimum prune target
```

### 9.4 Timeout Summary Table

| Scenario | Initial Timeout | Backoff | Maximum |
|----------|-----------------|---------|---------|
| getheaders request | 30s | None | 30s |
| Header peer slow | 2s | None | Immediate probe |
| Validation stall (epoch 0) | 1s | 2× per retry | 64s |
| Validation stall (epoch 1) | 2s | 2× per retry | 64s |
| Validation stall (epoch 4) | 5s | 2× per retry | 64s |
| Peer zero delivery | 30s | None | Disconnect |

---

## 10. Comparison: Echo vs. Bitcoin Core vs. libbitcoin

### 10.1 Feature Matrix

| Feature | Bitcoin Core | libbitcoin | Echo |
|---------|--------------|------------|------|
| **Header Strategy** | Parallel to 4+ peers | Single peer | Single peer + racing |
| **Header Duplicates** | Accepted (wasteful) | None | None |
| **Block Batch Size** | 16 | Variable | 8 (fixed) |
| **Work Distribution** | PUSH (coordinator assigns) | PULL (peers request) | PULL + sticky racing |
| **Slow Peer Handling** | Cooldown period | Immediate disconnect | Cooperative: only disconnect truly stalled (0 B/s) |
| **Stall Timeout** | Fixed 2s | Adaptive | Epoch-based adaptive |
| **Header Persistence** | Immediate | Deferred | Deferred (batched) |
| **Event System** | Validation signals | Chase events | Chase events |

### 10.2 Architectural Philosophy Comparison

```
BITCOIN CORE:
─────────────
    Philosophy: Conservative, battle-tested, backward compatible
    Strengths: Robust against edge cases, extensive testing
    Trade-offs: Less aggressive optimization, larger codebase

    Header sync: Multiple peers, accept duplicates
    Block sync: PUSH model with 16-block batches
    Slow peers: Cooldown, then disconnect

LIBBITCOIN:
───────────
    Philosophy: High performance, modular architecture
    Strengths: Fastest IBD implementation, elegant design
    Trade-offs: Less widespread deployment, steeper learning curve

    Header sync: Single peer (simple, no duplicates)
    Block sync: PULL model with variable batches
    Slow peers: Immediate sacrifice

BITCOIN ECHO:
─────────────
    Philosophy: Combine best of both, optimize for ossification
    Strengths: Racing without waste, adaptive timeouts
    Trade-offs: New implementation, less battle-tested

    Header sync: Single peer + periodic racing (best of both)
    Block sync: PULL model with 8-block batches + sticky racing
    Slow peers: Cooperative model - tolerate slow-but-working peers;
                only disconnect truly stalled (0 B/s for 20+ seconds)
```

### 10.3 Performance Characteristics

| Metric | Bitcoin Core | libbitcoin | Echo |
|--------|--------------|------------|------|
| Header sync time | ~10 min | ~1 min | ~1-2 min |
| Blocks/second (validation) | ~15-20 | ~25-30 | ~20-25 |
| Peak peer utilization | 70-80% | 90-95% | 85-95% |
| Recovery from blocking peer | 30-60s | Immediate | Immediate (sticky racing) |
| Memory usage (IBD) | ~4 GB | ~2 GB | ~2 GB |

*Note: Performance varies significantly based on hardware, network conditions, and peer quality.*

---

## 11. Conclusion

### 11.1 The Clockwork Summary

Bitcoin Echo's IBD architecture is a finely-tuned mechanism with interlocking components:

1. **Headers-First**: Validate chain structure before downloading full blocks
2. **Peer Racing**: Find the fastest header peer without wasting bandwidth
3. **PULL Distribution**: Let fast peers work more, slow peers work less
4. **8-Block Batches**: Balance parallelism against head-of-line blocking
5. **Sticky Racing**: Recover from slow peers without premature disconnection
6. **Epoch Timeouts**: Adapt expectations to block sizes throughout history
7. **Chase Events**: Decouple download from validation through async events
8. **Pruning-as-you-go**: Bound disk usage without sacrificing parallelism

### 11.2 Design Principles

The architecture embodies several key principles:

**Prefer racing to waiting**: When uncertain if a peer will deliver, start a race rather than waiting for timeout.

**Cooperative, not punitive**: A connected peer represents handshake cost, version exchange, and established state. Slow-but-working peers still contribute blocks. Only disconnect truly stalled peers (0 B/s for extended periods). Use sticky batches to add redundancy for blocking blocks without punishing anyone.

**Adapt to the data**: Early Bitcoin blocks are tiny; modern blocks are megabytes. Timeouts and expectations should reflect this reality.

**Pull, don't push**: Fast peers naturally absorb more work when they request it. Coordinators shouldn't guess which peers are fast.

**Batch for efficiency, but not too much**: 8 blocks balances per-request overhead against head-of-line blocking risk.

### 11.3 The Path to Ossification

This IBD architecture, once proven through extended mainnet testing and security audit, becomes part of Bitcoin Echo's frozen artifact. The careful engineering documented here ensures that future nodes can bootstrap efficiently from genesis, validating every block in Bitcoin's history without trusting any external source.

*Build once. Build right. Stop.*

---

## Appendix A: Data Structures

*Note: These structs show key architectural fields. See source files for complete definitions including additional timing, stats, and rate-limiting fields.*

### A.1 Sync Manager State

Defined in `src/protocol/sync.c`. Coordinates headers-first sync and delegates block downloads to the download manager.

```c
struct sync_manager {
    /* Core state */
    chainstate_t *chainstate;
    sync_callbacks_t callbacks;
    sync_mode_t mode;                    // IDLE, HEADERS, BLOCKS, DONE, STALLED

    /* Peer tracking */
    peer_sync_state_t peers[SYNC_MAX_PEERS];  // 100 peers (matches libbitcoin-node)
    size_t peer_count;

    /* Best known header chain */
    block_index_t *best_header;          // Tip of header chain (may be ahead of blocks)

    /* Header sync (simple model with periodic probing) */
    bool have_header_peer;               // Active peer selected?
    size_t active_header_peer_idx;       // Index of active peer
    uint32_t header_batch_count;         // Batches received from active peer
    uint64_t last_header_response_ms;    // Response time of last batch
    size_t probe_peer_idx;               // Peer being probed (SIZE_MAX if none)
    uint64_t probe_sent_time;            // When probe was sent

    /* Block sync */
    download_mgr_t *download_mgr;        // PULL-based work distribution
    uint32_t download_window;            // How far ahead to queue (100K during IBD)

    /* Pending headers (deferred persistence) */
    pending_header_t *pending_headers;   // Queue during HEADERS mode
    size_t pending_headers_count;
    size_t pending_headers_capacity;

    /* Timing and metrics */
    uint64_t start_time;
    uint64_t block_sync_start_time;
    uint32_t block_sync_start_height;
    uint64_t last_progress_time;
    uint64_t stalling_timeout_ms;        // Adaptive timeout (2s → 64s max)

    /* Chase integration (libbitcoin-style event-driven validation) */
    chase_dispatcher_t *dispatcher;
    chase_subscription_t *subscription;
};
```

### A.2 Download Manager State

Defined in `src/protocol/download_mgr.c`. Implements PULL-based work distribution with batch assignments.

```c
struct download_mgr {
    download_callbacks_t callbacks;

    /* Work queue (doubly-linked list of batches) */
    batch_node_t *queue_head;            // Front of queue (oldest)
    batch_node_t *queue_tail;            // Back of queue (newest)
    size_t queue_count;

    /* Height tracking */
    uint32_t lowest_pending_height;      // Lowest height in queue/assigned
    uint32_t highest_queued_height;      // Highest height added

    /* Peer performance */
    peer_perf_t peers[DOWNLOAD_MAX_PEERS];  // 256 peers max
    size_t peer_count;

    /* Stall detection */
    uint32_t last_validated_height;      // Last reported validated height
    uint64_t last_progress_time;         // When validation last progressed

    /* Adaptive stall timeout (Bitcoin Core style backoff) */
    uint32_t stall_backoff_height;       // Height we're stuck at
    uint32_t stall_backoff_count;        // Times we've stolen at this height
};
```

---

## Appendix B: Message Sequences

### B.1 Header Sync Sequence

```
NODE                                PEER A                    PEER B
 │                                    │                         │
 │──── getheaders(locator) ──────────▶│                         │
 │──── getheaders(locator) ──────────────────────────────────▶  │
 │                                    │                         │
 │◀──── headers(2000) ────────────────│ (156ms)                 │
 │                                    │                         │
 │     [Peer A selected as active]    │                         │
 │                                    │                         │
 │◀──── headers(2000) ────────────────────────────────────────│ (312ms)
 │                                    │                         │
 │     [Both responses processed]     │                         │
 │                                    │                         │
 │──── getheaders(locator) ──────────▶│                         │
 │◀──── headers(2000) ────────────────│                         │
 │──── getheaders(locator) ──────────▶│                         │
 │◀──── headers(2000) ────────────────│                         │
 │                                    │                         │
 │     [Probe interval reached]       │                         │
 │                                    │                         │
 │──── getheaders(locator) ──────────▶│                         │
 │──── getheaders(locator) ──────────────────────────────────▶  │
 │                                    │                         │
 │◀──── headers(2000) ────────────────────────────────────────  │ (95ms) ✓
 │◀──── headers(2000) ────────────────│ (180ms)                 │
 │                                    │                         │
 │     [Peer B wins race, becomes active]                       │
 │                                    │                         │
```

### B.2 Block Download with Sticky Racing

```
NODE                     PEER A              PEER B              VALIDATION
 │                         │                   │                     │
 │── getdata([500-507]) ──▶│                   │                     │
 │── getdata([508-515]) ──────────────────────▶│                     │
 │                         │                   │                     │
 │◀──── block(508) ────────────────────────────│                     │
 │◀──── block(509) ────────────────────────────│                     │
 │◀──── block(510-515) ────────────────────────│                     │
 │                         │                   │                     │
 │     [Peer A slow, stall detected at 499]    │            tip=499 ◀│
 │                         │                   │                     │
 │     [Create STICKY batch [500-507]]         │                     │
 │                         │                   │                     │
 │── getdata([500-507]) ──────────────────────▶│  (sticky clone)     │
 │                         │                   │                     │
 │◀──── block(500) ────────────────────────────│ (100ms)             │
 │                         │                   │            tip=500 ◀│
 │                         │                   │                     │
 │     [Sticky resolved, remove from queue]    │                     │
 │                         │                   │                     │
 │◀──── block(500) ────────│ (finally!)        │                     │
 │                         │                   │                     │
 │     [Ignored - already validated]           │                     │
 │                         │                   │                     │
```

---

## Appendix C: References

Bitcoin Echo's IBD architecture draws inspiration from two foundational implementations:

### libbitcoin

The [libbitcoin](https://libbitcoin.info/) project pioneered the PULL-based work distribution model and chase event system that Echo adapts. Its elegant approach to peer management — where peers request work rather than being assigned it — fundamentally shapes our download engine design.

### Bitcoin Core

[Bitcoin Core](https://bitcoincore.org/) remains the reference implementation against which all Bitcoin software is measured. Its battle-tested IBD approach, timeout constants, and protocol handling inform Echo's design decisions, even where we diverge (such as batch sizes and stall recovery strategies).

---

*Created: December 31st, 2025*

*Bitcoin Echo Project*
