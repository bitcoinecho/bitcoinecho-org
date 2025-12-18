# Bitcoin Echo — Implementation Roadmap

**A Living Document for Development Sessions**

*Last Updated: December 17, 2025*

---

## Quick Orientation

**What is Bitcoin Echo?**
A complete, ossified implementation of the Bitcoin protocol in pure C—designed for permanence, not continued development. Upon completion and audit, the codebase freezes forever. No version 2.0. No roadmap beyond completion.

**Philosophy in one sentence:**
*Build once, build right, stop.*

**Key constraints:**
- Pure C11, no external dependencies beyond a C compiler and standard library
- Target: 15,000–25,000 lines of heavily-commented code
- Consensus engine is pure computation (no I/O, no dynamic allocation during validation)
- Platform abstraction layer isolates all OS-specific code
- Simplicity over optimization in every trade-off

**Repository structure:**
```
bitcoinecho-org/          ← You are here (landing page, docs, whitepaper)
bitcoin-echo/             ← Sibling folder (C implementation, to be created)
```

**Core documents:**
- [Whitepaper](bitcoin-echo-whitepaper.md) — Complete technical specification
- [Manifesto](bitcoin-echo-manifesto.md) — Philosophical foundation
- [Landing Page](index.html) — Public face at bitcoinecho.org

---

## Current State

### Completed
- [x] Project conception and philosophy defined
- [x] Whitepaper v1.0 finalized
- [x] Manifesto written
- [x] Landing page live at https://bitcoinecho.org
- [x] GitHub organization established (bitcoinecho)
- [x] X presence established (@bitcoinechoorg)
- [x] Symbol/logo created and deployed
- [x] MIT License selected
- [x] C implementation repository created
- [x] Build system established
- [x] Initial source code written (main.c prints version)
- [x] Core types and headers defined (Phase 0 complete)

### In Progress
- [ ] Phase 9: Application Layer (Session 9.6 remaining — Full Node Integration, 7 sub-sessions: 9.6.1-9.6.7)

### Not Yet Started
- [ ] Phase 10: Mining Interface
- [ ] Phase 11: Testing & Hardening
- [ ] Phase 12: Completion

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        Application Layer                        │
│           (node operation, RPC interface, logging)              │
├─────────────────────────────────────────────────────────────────┤
│                        Protocol Layer                           │
│        (P2P message handling, peer management, mempool)         │
├─────────────────────────────────────────────────────────────────┤
│                       Consensus Engine                          │  ← FROZEN
│   (block validation, transaction validation, chain selection)   │     CORE
├─────────────────────────────────────────────────────────────────┤
│                   Platform Abstraction Layer                    │
│          (sockets, threads, files, time, entropy)               │
└─────────────────────────────────────────────────────────────────┘
```

**Layer dependencies flow downward only.** The consensus engine knows nothing of networking or files. The protocol layer knows nothing of RPC or logging policy.

---

## Implementation Phases

### Phase 0: Project Foundation
*Establish the development environment and build system*

### Phase 1: Platform Abstraction Layer
*Create the thin OS interface that consensus will never touch*

### Phase 2: Cryptographic Primitives
*Embed all crypto directly—SHA-256, RIPEMD-160, secp256k1*

### Phase 3: Consensus Engine — Data Structures
*Define the byte-level representations of Bitcoin's core types*

### Phase 4: Consensus Engine — Transaction Validation
*Implement script execution and transaction verification*

### Phase 5: Consensus Engine — Block Validation
*Implement header validation, merkle trees, difficulty adjustment*

### Phase 6: Consensus Engine — Chain Selection
*UTXO management, chain state, reorganization handling*

### Phase 7: Storage Layer
*Block files and SQLite-based UTXO/index databases*

### Phase 8: Protocol Layer
*P2P networking, message serialization, peer management*

### Phase 9: Application Layer
*Node orchestration, RPC interface, logging*

### Phase 10: Mining Interface
*Block template generation, work submission*

### Phase 11: Testing & Hardening
*Fuzz testing, full chain sync, security audit preparation*

### Phase 12: Completion
*Final audit, blockchain hash, key publication, archive*

---

## Session Work Units

Each unit is designed to be completable in a single focused chat session. Units within the same phase can often be parallelized across sessions. Dependencies are noted.

---

### Phase 0: Project Foundation

#### Session 0.1: Repository Setup
**Objective:** Create the bitcoin-echo repository with initial structure

**Tasks:**
- Create `bitcoin-echo/` sibling directory
- Initialize git repository
- Create directory structure:
  ```
  bitcoin-echo/
  ├── src/
  │   ├── platform/
  │   ├── crypto/
  │   ├── consensus/
  │   ├── protocol/
  │   └── app/
  ├── include/
  ├── test/
  │   ├── vectors/
  │   └── unit/
  ├── docs/
  ├── Makefile
  ├── build.bat
  ├── README.md
  └── LICENSE
  ```
- Copy LICENSE from bitcoinecho-org
- Create initial README.md with build instructions placeholder

**Deliverables:** Empty but structured repository

---

#### Session 0.2: Build System
**Objective:** Establish the minimal build system

**Tasks:**
- Write POSIX Makefile (as specified in whitepaper §9.1)
- Write Windows batch build script (as specified in whitepaper §9.2)
- Create a minimal `src/main.c` that prints version and exits
- Verify builds work on available platforms
- Add `make clean`, `make test` targets (test target initially no-op)

**Deliverables:** Working build that produces `echo` binary

---

#### Session 0.3: Core Types and Headers
**Objective:** Define fundamental types used throughout the codebase

**Tasks:**
- Create `include/echo_types.h`:
  - Fixed-width integer types (uint8_t, uint32_t, uint64_t, etc.)
  - Byte array types for hashes (hash256_t, hash160_t)
  - Boolean type
  - Result/error codes enum
  - Satoshi amount type (int64_t)
- Create `include/echo_config.h`:
  - Compile-time constants (network magic, default ports, etc.)
  - Mainnet/testnet/regtest configuration
- Create `include/echo_assert.h`:
  - Debug assertion macros

**Deliverables:** Type system foundation

---

### Phase 1: Platform Abstraction Layer

#### Session 1.1: Platform Interface Definition
**Objective:** Define the complete platform abstraction API

**Tasks:**
- Create `include/platform.h` exactly as specified in whitepaper Appendix A
- Document each function with expected behavior
- Define opaque types for platform-specific handles

**Deliverables:** Complete platform.h header

**Reference:** Whitepaper Appendix A (full interface provided)

---

#### Session 1.2: POSIX Platform — Networking
**Objective:** Implement networking functions for POSIX systems

**Tasks:**
- Create `src/platform/posix.c` (will be extended across sessions)
- Implement:
  - `plat_socket_create`
  - `plat_socket_connect`
  - `plat_socket_listen`
  - `plat_socket_accept`
  - `plat_socket_send`
  - `plat_socket_recv`
  - `plat_socket_close`
  - `plat_dns_resolve`
- Write unit tests for socket operations

**Deliverables:** Working POSIX networking

---

#### Session 1.3: POSIX Platform — Threading
**Objective:** Implement threading primitives for POSIX

**Tasks:**
- Extend `src/platform/posix.c`:
  - `plat_thread_create`
  - `plat_thread_join`
  - `plat_mutex_init` / `destroy` / `lock` / `unlock`
  - `plat_cond_init` / `destroy` / `wait` / `timedwait` / `signal` / `broadcast`
- Write unit tests for thread synchronization

**Deliverables:** Working POSIX threading

---

#### Session 1.4: POSIX Platform — Files, Time, Entropy
**Objective:** Complete POSIX platform implementation

**Tasks:**
- Extend `src/platform/posix.c`:
  - `plat_file_read` / `write` / `append` / `rename` / `delete` / `exists`
  - `plat_dir_create`
  - `plat_time_ms`
  - `plat_monotonic_ms`
  - `plat_sleep_ms`
  - `plat_random_bytes` (using /dev/urandom or getrandom())
- Write unit tests for file operations

**Deliverables:** Complete POSIX platform layer

---

#### Session 1.5: Windows Platform Implementation
**Objective:** Implement full platform layer for Windows

**Tasks:**
- Create `src/platform/win32.c`
- Implement all platform functions using Win32 API:
  - Winsock for networking
  - Windows threads and synchronization primitives
  - Win32 file operations
  - QueryPerformanceCounter for time
  - BCryptGenRandom for entropy
- Test on Windows (or document for later testing)

**Deliverables:** Complete Windows platform layer

---

### Phase 2: Cryptographic Primitives

#### Session 2.1: SHA-256 Implementation
**Objective:** Implement SHA-256 per FIPS 180-4

**Tasks:**
- Create `src/crypto/sha256.c` and `include/sha256.h`
- Implement:
  - Single-block hashing
  - Streaming/incremental interface (init/update/final)
  - SHA256d (double SHA-256)
  - Midstate computation (for mining optimization)
- Embed NIST test vectors in `test/vectors/sha256_vectors.c`
- Write test harness that runs vectors on every build

**Deliverables:** Verified SHA-256 implementation

**Reference:** FIPS 180-4, Bitcoin Core test vectors

---

#### Session 2.2: RIPEMD-160 Implementation
**Objective:** Implement RIPEMD-160 per original specification

**Tasks:**
- Create `src/crypto/ripemd160.c` and `include/ripemd160.h`
- Implement single-block and streaming interface
- Implement HASH160 helper: `RIPEMD160(SHA256(x))`
- Embed test vectors from original publication
- Add to test harness

**Deliverables:** Verified RIPEMD-160 implementation

**Reference:** Dobbertin, Bosselaers, Preneel (1996)

---

#### Session 2.3: secp256k1 Field Arithmetic
**Objective:** Implement finite field operations for secp256k1

**Tasks:**
- Create `src/crypto/secp256k1.c` and `include/secp256k1.h`
- Implement modular arithmetic over the secp256k1 prime field:
  - Field element representation (constant-time)
  - Addition, subtraction, multiplication, squaring
  - Inversion (using Fermat's little theorem or extended Euclidean)
  - Square root (for point decompression)
- Write unit tests with known field values

**Deliverables:** Field arithmetic foundation

**Reference:** libsecp256k1 for test vectors

---

#### Session 2.4: secp256k1 Group Operations
**Objective:** Implement elliptic curve point operations

**Tasks:**
- Extend secp256k1 module:
  - Point representation (Jacobian coordinates for efficiency)
  - Point addition and doubling
  - Point negation
  - Scalar multiplication (constant-time double-and-add)
  - Point serialization/deserialization (compressed and uncompressed)
- Verify against known points

**Deliverables:** Working EC group operations

---

#### Session 2.5: ECDSA Signature Verification
**Objective:** Implement ECDSA verification (not signing)

**Tasks:**
- Extend secp256k1 module:
  - DER signature parsing (with strict validation per BIP-66)
  - ECDSA verification algorithm
  - Public key parsing and validation
- Embed libsecp256k1 verification test vectors
- Verify against Bitcoin Core signature test cases

**Deliverables:** Working ECDSA verification

**Note:** Signing is not needed—nodes verify, they do not sign.

---

#### Session 2.6: Schnorr Signature Verification (BIP-340)
**Objective:** Implement Schnorr verification for Taproot

**Tasks:**
- Extend secp256k1 module:
  - x-only public key handling
  - BIP-340 tagged hash computation
  - Schnorr signature verification
- Embed BIP-340 test vectors
- Verify all test cases pass

**Deliverables:** Working Schnorr verification

**Reference:** BIP-340

---

#### Session 2.7: Signature Verification Interface
**Objective:** Create the unified signature verification seam

**Tasks:**
- Create `include/sig_verify.h` exactly as specified in whitepaper §15.3
- Create `src/consensus/sig_verify.c`:
  - Implement `sig_verify()` dispatching to ECDSA or Schnorr
  - Implement `sig_type_known()`
- Document the succession boundary clearly

**Deliverables:** Clean signature verification interface (the quantum succession seam)

---

### Phase 3: Consensus Engine — Data Structures

#### Session 3.1: Variable-Length Integer Encoding
**Objective:** Implement Bitcoin's CompactSize encoding

**Tasks:**
- Create `src/consensus/serialize.c` and `include/serialize.h`
- Implement:
  - `varint_read()` — parse varint from byte buffer
  - `varint_write()` — write varint to buffer
  - `varint_size()` — compute encoded size
- Test with edge cases (0, 252, 253, 65535, 65536, etc.)

**Deliverables:** Varint encoding/decoding

---

#### Session 3.2: Transaction Data Structures
**Objective:** Define transaction representation

**Tasks:**
- Create `include/tx.h` and `src/consensus/tx.c`
- Define structures:
  - `outpoint_t` (txid + vout)
  - `tx_input_t` (outpoint, scriptSig, sequence, witness)
  - `tx_output_t` (value, scriptPubKey)
  - `tx_t` (version, inputs, outputs, locktime, witness flag)
- Implement transaction parsing from raw bytes
- Implement transaction serialization (with and without witness)
- Implement txid and wtxid computation

**Deliverables:** Transaction data structures and parsing

---

#### Session 3.3: Block Data Structures
**Objective:** Define block representation

**Tasks:**
- Create `include/block.h` and `src/consensus/block.c`
- Define structures:
  - `block_header_t` (version, prev_hash, merkle_root, timestamp, bits, nonce)
  - `block_t` (header, transaction count, transactions)
- Implement block header parsing
- Implement block header hash computation
- Implement full block parsing

**Deliverables:** Block data structures and parsing

---

#### Session 3.4: Merkle Tree Computation
**Objective:** Implement Merkle root calculation

**Tasks:**
- Create `src/consensus/merkle.c` and `include/merkle.h`
- Implement:
  - `merkle_root()` — compute Merkle root from transaction list
  - Handle odd transaction counts (duplicate last element)
  - Witness commitment Merkle tree (for SegWit)
- Verify against known block merkle roots

**Deliverables:** Merkle tree computation

---

### Phase 4: Consensus Engine — Transaction Validation

#### Session 4.1: Script Data Structures
**Objective:** Define script representation

**Tasks:**
- Create `include/script.h`
- Define:
  - Opcode enumeration (all Bitcoin opcodes)
  - Script structure (raw bytes, length)
  - Script type enumeration (P2PKH, P2SH, P2WPKH, P2WSH, P2TR, etc.)
- Implement script type detection
- Implement opcode parsing

**Deliverables:** Script data structures

---

#### Session 4.2: Script Stack Machine
**Objective:** Implement the Bitcoin Script interpreter core

**Tasks:**
- Create `src/consensus/script.c`
- Implement:
  - Stack data structure (with size limits)
  - Stack operations (push, pop, peek, dup, swap, rot, etc.)
  - Basic type conversions (bytes to int, int to bytes)
- Define interpreter context structure

**Deliverables:** Script execution stack

---

#### Session 4.3: Script Opcodes — Arithmetic & Logic
**Objective:** Implement arithmetic and logic opcodes

**Tasks:**
- Implement in script.c:
  - OP_0, OP_1 through OP_16, OP_1NEGATE
  - OP_IF, OP_NOTIF, OP_ELSE, OP_ENDIF, OP_VERIFY
  - OP_RETURN (makes script invalid)
  - OP_TOALTSTACK, OP_FROMALTSTACK
  - OP_DUP, OP_DROP, OP_SWAP, OP_ROT, OP_PICK, OP_ROLL, etc.
  - OP_EQUAL, OP_EQUALVERIFY
  - OP_ADD, OP_SUB, OP_1ADD, OP_1SUB, OP_NEGATE, OP_ABS
  - OP_NOT, OP_0NOTEQUAL, OP_BOOLAND, OP_BOOLOR
  - OP_NUMEQUAL, OP_NUMEQUALVERIFY, OP_NUMNOTEQUAL
  - OP_LESSTHAN, OP_GREATERTHAN, OP_LESSTHANOREQUAL, OP_GREATERTHANOREQUAL
  - OP_MIN, OP_MAX, OP_WITHIN

**Deliverables:** Arithmetic and flow control opcodes

---

#### Session 4.4: Script Opcodes — Crypto
**Objective:** Implement cryptographic opcodes

**Tasks:**
- Implement in script.c:
  - OP_RIPEMD160, OP_SHA256, OP_HASH160, OP_HASH256
  - OP_CHECKSIG, OP_CHECKSIGVERIFY
  - OP_CHECKMULTISIG, OP_CHECKMULTISIGVERIFY (with off-by-one bug)
  - OP_CODESEPARATOR
- Implement signature hash computation (SIGHASH types)

**Deliverables:** Cryptographic opcodes

---

#### Session 4.5: SegWit Script Execution (BIP-141/143/147)
**Objective:** Implement witness script evaluation

**Tasks:**
- Implement witness program detection and parsing
- Implement BIP-143 signature hash for SegWit v0
- Implement P2WPKH evaluation
- Implement P2WSH evaluation
- Implement witness stack limits and validation

**Deliverables:** SegWit v0 script execution

---

#### Session 4.6: Taproot Script Execution (BIP-341/342)
**Objective:** Implement Taproot script evaluation

**Tasks:**
- Implement Taproot output detection (SegWit v1, 32-byte program)
- Implement key path spending (Schnorr signature verification)
- Implement script path spending:
  - Merkle proof validation
  - Leaf version handling
  - Tapscript execution
- Implement OP_CHECKSIGADD
- Implement signature hash for Taproot (BIP-341 annex, leaf version)

**Deliverables:** Taproot script execution

---

#### Session 4.7: P2SH Evaluation (BIP-16)
**Objective:** Implement Pay-to-Script-Hash

**Tasks:**
- Implement P2SH detection (OP_HASH160 <20 bytes> OP_EQUAL pattern)
- Implement P2SH evaluation:
  - Evaluate scriptSig
  - Deserialize redeem script from stack
  - Evaluate redeem script with remaining stack
- Handle nested P2SH-P2WPKH and P2SH-P2WSH

**Deliverables:** P2SH evaluation

---

#### Session 4.8: Timelock Opcodes (BIP-65, BIP-112)
**Objective:** Implement CHECKLOCKTIMEVERIFY and CHECKSEQUENCEVERIFY

**Tasks:**
- Implement OP_CHECKLOCKTIMEVERIFY:
  - Compare stack top to transaction locktime
  - Validate sequence number allows CLTV
- Implement OP_CHECKSEQUENCEVERIFY:
  - Parse relative locktime from stack
  - Validate against input sequence
  - Handle type mismatch (blocks vs time)

**Deliverables:** Timelock opcodes

---

#### Session 4.9: Script Test Vectors
**Objective:** Verify script implementation against Bitcoin Core vectors

**Tasks:**
- Embed Bitcoin Core script test vectors
- Create test harness for script execution
- Run all valid script tests (must pass)
- Run all invalid script tests (must fail)
- Debug and fix any discrepancies

**Deliverables:** Verified script interpreter

---

#### Session 4.10: Transaction Validation Logic
**Objective:** Implement complete transaction validation

**Tasks:**
- Create `src/consensus/tx_validate.c`
- Implement transaction validation:
  - Syntactic validation (well-formed)
  - Size limits
  - Input/output count limits
  - No duplicate inputs
  - Output value range (0 to 21M BTC)
  - Total output <= total input (for non-coinbase)
  - Each input script validates against corresponding output
  - Locktime and sequence validation
- Create validation result structure with detailed error info

**Deliverables:** Transaction validator

---

### Phase 5: Consensus Engine — Block Validation

#### Session 5.1: Block Header Validation
**Objective:** Implement header validation rules

**Tasks:**
- Create `src/consensus/block_validate.c`
- Implement header validation:
  - Proof-of-work check (hash <= target)
  - Target-to-difficulty conversion
  - Previous block reference validation
  - Timestamp validation (median time past, future limit)
  - Version interpretation (BIP-9 version bits if needed)

**Deliverables:** Block header validator

---

#### Session 5.2: Difficulty Adjustment
**Objective:** Implement difficulty retargeting algorithm

**Tasks:**
- Implement difficulty calculation:
  - Every 2016 blocks
  - Time span calculation (clamped to factor of 4)
  - New target computation
  - Minimum difficulty enforcement
- Verify against known difficulty transitions in Bitcoin history

**Deliverables:** Difficulty adjustment logic

---

#### Session 5.3: Coinbase Validation
**Objective:** Implement coinbase transaction rules

**Tasks:**
- Implement coinbase validation:
  - Exactly one input, null outpoint
  - Block height encoding (BIP-34)
  - Subsidy calculation (50 BTC, halving every 210,000 blocks)
  - Total output <= subsidy + fees
  - Witness commitment (for SegWit blocks)
  - 100-block maturity tracking

**Deliverables:** Coinbase validator

---

#### Session 5.4: Full Block Validation
**Objective:** Integrate all block validation rules

**Tasks:**
- Implement complete block validation:
  - Header validation
  - Transaction count limits
  - Block size/weight limits
  - First transaction is coinbase
  - All other transactions are non-coinbase
  - All transactions valid
  - No duplicate transactions
  - Merkle root verification
  - Witness commitment verification
- Create validation result with detailed failure info

**Deliverables:** Complete block validator

---

### Phase 6: Consensus Engine — Chain Selection

#### Session 6.1: UTXO Set Data Structures
**Objective:** Define UTXO set representation

**Tasks:**
- Create `include/utxo.h` and `src/consensus/utxo.c`
- Define UTXO entry structure:
  - Outpoint (txid, vout)
  - Value (satoshis)
  - scriptPubKey
  - Block height
  - Coinbase flag
- Define UTXO set interface:
  - Lookup
  - Insert
  - Remove
  - Batch operations for block apply/revert

**Deliverables:** UTXO set abstraction

---

#### Session 6.2: Chain State
**Objective:** Implement chain state tracking

**Tasks:**
- Create `include/chainstate.h` and `src/consensus/chainstate.c`
- Define chain state:
  - Current tip (block hash, height)
  - Total accumulated work
  - UTXO set reference
- Implement chain state transitions:
  - Apply block (add UTXOs, remove spent UTXOs)
  - Revert block (for reorganizations)

**Deliverables:** Chain state management

---

#### Session 6.3: Chain Selection Algorithm
**Objective:** Implement most-work chain selection

**Tasks:**
- Implement work calculation:
  - Work per block: 2^256 / (target + 1)
  - Cumulative work tracking
- Implement chain comparison:
  - Compare cumulative work
  - Tie-breaking by first-seen
- Implement reorganization logic:
  - Find common ancestor
  - Revert blocks from old chain
  - Apply blocks from new chain

**Deliverables:** Chain selection logic

---

#### Session 6.4: Consensus Engine Integration
**Objective:** Create the complete consensus engine interface

**Tasks:**
- Create `include/consensus.h` and `src/consensus/consensus.c`
- Define consensus engine interface:
  - `consensus_validate_block()` — pure function, bytes → validity
  - `consensus_apply_block()` — update chain state
  - `consensus_get_chain_tip()` — current best chain
- Ensure consensus engine makes no system calls
- Ensure all memory comes from caller-provided arena
- Document the consensus boundary clearly

**Deliverables:** Complete consensus engine API

---

### Phase 7: Storage Layer

#### Session 7.1: Block File Storage
**Objective:** Implement block file storage

**Tasks:**
- Create `src/storage/blocks.c` and `include/blocks_storage.h`
- Implement block file format:
  - Magic bytes + size prefix + block data
  - Sequential append-only files (blk*.dat pattern)
  - Compatible with Bitcoin Core block files
- Implement block reading by file position
- Implement block appending

**Deliverables:** Block file storage

---

#### Session 7.2: SQLite Integration
**Objective:** Integrate SQLite for UTXO and index storage

**Tasks:**
- Embed SQLite amalgamation in source tree (or document how to include it)
- Create `src/storage/db.c` and `include/db.h`
- Implement database initialization
- Implement WAL mode configuration
- Create wrapper for transactions and queries

**Note:** SQLite is public domain and effectively a "frozen" dependency that aligns with project philosophy.

**Deliverables:** SQLite integration layer

---

#### Session 7.3: UTXO Database
**Objective:** Implement persistent UTXO storage

**Tasks:**
- Create UTXO table schema (as specified in whitepaper §6.2)
- Implement:
  - UTXO lookup by outpoint
  - Batch insert (new outputs)
  - Batch delete (spent outputs)
  - Atomic block apply (single transaction)
- Verify performance is acceptable

**Deliverables:** Persistent UTXO storage

---

#### Session 7.4: Block Index Database
**Objective:** Implement block header index

**Tasks:**
- Create block index schema (as specified in whitepaper §6.3)
- Implement:
  - Block lookup by hash
  - Block lookup by height
  - Chain traversal (next/prev)
  - Best chain query (max chainwork)
- Ensure atomic updates with UTXO changes

**Deliverables:** Block index storage

---

### Phase 8: Protocol Layer

#### Session 8.1: P2P Message Structures
**Objective:** Define protocol message formats

**Tasks:**
- Create `include/protocol.h` and `src/protocol/messages.c`
- Implement message header (magic, command, length, checksum)
- Define message structures for all supported types:
  - version, verack
  - ping, pong
  - inv, getdata
  - block, tx
  - getblocks, getheaders, headers
  - addr, getaddr
  - reject
  - sendheaders, sendcmpct, feefilter, wtxidrelay

**Deliverables:** Protocol message definitions

---

#### Session 8.2: Message Serialization
**Objective:** Implement message encoding/decoding

**Tasks:**
- Implement serialization for each message type
- Implement deserialization with validation
- Implement checksum computation
- Test with captured Bitcoin network traffic or test vectors

**Deliverables:** Message serialization

---

#### Session 8.3: Peer Connection Management
**Objective:** Implement peer lifecycle management

**Tasks:**
- Create `src/protocol/peer.c` and `include/peer.h`
- Define peer structure (socket, state, version info, etc.)
- Implement connection establishment
- Implement handshake (version/verack exchange)
- Implement disconnection handling
- Implement per-peer message queuing

**Deliverables:** Peer connection handling

---

#### Session 8.4: Peer Discovery
**Objective:** Implement peer discovery mechanisms

**Tasks:**
- Hardcode seed nodes (DNS seeds and/or IP addresses)
- Implement DNS resolution for seed nodes
- Implement addr/getaddr message handling
- Implement peer address storage
- Implement outbound connection selection

**Deliverables:** Peer discovery

---

#### Session 8.5: Inventory and Data Relay
**Objective:** Implement inv/getdata protocol

**Tasks:**
- Implement inventory vector handling
- Implement getdata requests
- Implement block and transaction reception
- Implement relay to peers
- Implement DoS prevention (rate limiting, banning)

**Deliverables:** Data relay protocol

---

#### Session 8.6: Headers-First Sync
**Objective:** Implement initial block download

**Tasks:**
- Implement getheaders/headers message handling
- Implement header chain validation
- Implement block download prioritization
- Implement parallel block download from multiple peers
- Implement sync progress tracking

**Deliverables:** Initial sync capability

---

#### Session 8.7: Mempool
**Objective:** Implement transaction memory pool

**Tasks:**
- Create `src/protocol/mempool.c` and `include/mempool.h`
- Implement mempool structure
- Implement transaction acceptance policy
- Implement fee-based prioritization
- Implement mempool size limits
- Implement transaction relay policy

**Deliverables:** Transaction mempool

---

### Phase 9: Application Layer

#### Session 9.1: Node Initialization
**Objective:** Implement node startup sequence

**Tasks:**
- Create `src/app/node.c` and `include/node.h`
- Implement initialization sequence:
  - Platform layer init
  - Database loading
  - Chain state restoration
  - Network startup
- Implement shutdown sequence (graceful cleanup)

**Deliverables:** Node lifecycle management

---

#### Session 9.2: Main Event Loop
**Objective:** Implement the main processing loop

**Tasks:**
- Implement event-driven main loop
- Implement peer message processing
- Implement block validation and chain updates
- Implement timer-based maintenance (peer ping, etc.)
- Implement signal handling (SIGTERM, etc.)

**Deliverables:** Main event loop

---

#### Session 9.3: RPC Interface
**Objective:** Implement minimal JSON-RPC interface

**Tasks:**
- Create `src/app/rpc.c` and `include/rpc.h`
- Implement JSON parsing (minimal, embedded)
- Implement RPC server (HTTP on configurable port)
- Implement methods:
  - `getblockchaininfo`
  - `getblock`
  - `getblockhash`
  - `getrawtransaction`
  - `sendrawtransaction`
  - `getblocktemplate` (for mining)
  - `submitblock`

**Deliverables:** RPC interface

---

#### Session 9.4: Logging
**Objective:** Implement minimal logging system

**Tasks:**
- Create `src/app/log.c` and `include/log.h`
- Implement fixed-format logging:
  - Timestamp
  - Log level
  - Component
  - Message
- Implement log levels (error, warn, info, debug)
- Ensure machine-parseable format
- Minimize runtime overhead

**Deliverables:** Logging system

---

#### Session 9.5: Observer Mode
**Objective:** Connect to the live Bitcoin network and observe traffic without validation

**Background:** Full chain synchronization requires building the UTXO set from genesis, which takes significant time. Observer mode provides immediate visibility into network activity by connecting to real peers and displaying traffic without attempting validation. This enables:
- Immediate proof-of-life for the node
- GUI development against live data
- Educational observation of Bitcoin P2P protocol
- Testing peer connectivity before committing to full sync

**Tasks:**
- Add `--observe` command-line flag to main.c
- Implement observer-only node initialization:
  - Initialize platform layer
  - Initialize logging
  - Initialize peer discovery (DNS seeds)
  - Skip consensus engine, storage, UTXO database initialization
- Wire up main.c for real operation:
  - Parse command-line arguments (--datadir, --testnet, --regtest, --observe, --rpcport, --port)
  - Create node with parsed configuration
  - Register signal handlers (SIGINT, SIGTERM)
  - Create and start RPC server
  - Run event loop (peer processing + RPC processing)
  - Graceful shutdown sequence
- Implement passive peer message handling:
  - Connect to peers via DNS seeds
  - Complete version/verack handshake
  - Receive and log inv messages (new blocks, transactions)
  - Optionally request and receive block/tx data via getdata
  - Parse received data for display (do not validate)
  - Do not relay anything to other peers
- Add observer-specific RPC methods:
  - `getobserverstats` — message counts by type, peer count, recent activity
  - `getobservedblocks` — list of recently announced block hashes
  - `getobservedtxs` — list of recently announced transaction ids
- Implement activity logging:
  - Log all received inv announcements
  - Log peer connections/disconnections
  - Log received block headers (parsed but not validated)
- Test observer mode:
  - Node starts and connects to mainnet peers
  - RPC responds to getobserverstats
  - Live traffic visible in logs
  - Ctrl+C triggers graceful shutdown

**Deliverables:** A node that connects to mainnet and observes live traffic, with RPC for the GUI to display activity

**Note:** This session also completes the "Pinocchio moment"—wiring main.c to actually run. The --observe flag enables immediate demonstration while full sync capability (requiring all consensus/storage) is tested separately in Phase 11.

---

#### Session 9.6: Full Node Integration

**Overview:** This is the critical integration session where all components come together into a fully operational validating node. Due to its scope and importance, it is divided into sub-sessions (9.6.0 through 9.6.7) that build incrementally toward mainnet readiness.

---

##### Session 9.6.0: Storage Foundation & Chain Restoration
**Objective:** The node remembers who it is — state persists across restarts

**Tasks:**
- Initialize block storage on full node startup:
  - Create data directory structure if not exists
  - Open/create block files (blk*.dat)
  - Verify block file integrity on startup
- Initialize SQLite databases:
  - UTXO database (utxo.db) with schema from §6.2
  - Block index database (blocks.db) with schema from §6.3
  - Configure WAL mode for both
- Implement chain state restoration:
  - Load best chain tip from block index
  - Restore chain height, best block hash, cumulative chainwork
  - Verify UTXO set consistency with chain tip
- Connect consensus engine to persistent storage:
  - UTXO lookups query SQLite instead of in-memory set
  - Chain state queries use block index database
- Wire `getblockchaininfo` RPC to report real chain state
- Test resumable sync:
  - Start node, process some blocks, stop
  - Restart node, verify tip persists
  - Verify UTXO set intact

**Deliverables:** Stateful node that survives restarts with chain state intact

**Reference:** Whitepaper §6 (Storage)

---

##### Session 9.6.1: Block Processing Pipeline
**Objective:** Blocks flow from network through validation to storage

**Tasks:**
- Wire consensus `consensus_validate_block()` to incoming blocks:
  - Receive block from peer
  - Deserialize and parse
  - Validate via consensus engine
- Implement atomic block application:
  - Begin SQLite transaction
  - Update UTXO set (add new outputs, remove spent)
  - Update block index (add block entry, update best chain)
  - Write block to block file
  - Commit transaction (all or nothing)
- Handle validation failures gracefully:
  - Log detailed error information
  - Reject block, continue operation
  - Track invalid blocks to avoid re-download
- Implement block relay after validation:
  - Announce valid blocks to peers via inv
  - Respond to getdata requests
- Test block pipeline:
  - Inject manually-crafted valid block
  - Verify stored in block file
  - Verify UTXO set updated
  - Verify block index updated

**Deliverables:** Working block validation → storage pipeline

**Reference:** Whitepaper §3.2 (Consensus Engine), §6.4 (Atomic Updates)

---

##### Session 9.6.2: Pruning Support
**Objective:** Enable lite mode — full validation with minimal storage

**Background:** A key barrier to education and adoption is the ~600 GB storage requirement for a full archival node. Pruned nodes validate every block identically to archival nodes, but discard old block data after validation, keeping only the UTXO set and recent blocks. This enables the full "Don't trust. Verify." experience with ~10 GB storage instead of ~600 GB.

**Tasks:**
- Add `--prune=<MB>` CLI flag:
  - 0 = no pruning (archival mode, default)
  - Positive value = target size in MB for block storage
  - Minimum: 550 MB (must keep 550+ blocks for reorg safety)
- Implement block file pruning:
  - Track which blocks are in each block file
  - After validating block N, mark block (N - 550) as prunable
  - Delete block files when all their blocks are prunable
  - Update block index to mark blocks as pruned (data unavailable)
- Add pruning height tracking:
  - Store `pruned_height` in block index database
  - Track earliest block with available data
  - Enable queries for "do we have block X data?"
- Update service bit advertising:
  - When pruned: do NOT advertise `NODE_NETWORK` (service bit 1)
  - Pruned nodes cannot serve historical blocks to peers
  - Still advertise `NODE_WITNESS` if applicable
- Handle block requests gracefully:
  - If peer requests pruned block, send `notfound`
  - Log when unable to serve due to pruning
- Update `getblockchaininfo` RPC:
  - Add `pruned`: true/false
  - Add `pruneheight`: lowest block with data (if pruned)
  - Add `prune_target_size`: configured target (if pruned)
- Implement `pruneblockchain` RPC (optional, for manual pruning):
  - `pruneblockchain <height>` — prune up to specified height
  - Return actual pruned height
- Test pruning:
  - Start node with `--prune=1000`
  - Sync past 1000 blocks
  - Verify old block files deleted
  - Verify UTXO set intact and correct
  - Verify `getblockchaininfo` reports pruning status
  - Verify node rejects requests for pruned blocks

**Deliverables:** Working pruned node mode — same security, 1/60th the storage

**Note:** Pruning is a storage optimization only. Security is identical to archival mode because every block is fully validated before being discarded. The UTXO set (which is all that's needed to validate new blocks) is always complete.

---

##### Session 9.6.3: Transaction Processing Pipeline
**Objective:** Transactions flow from network through validation to mempool

**Tasks:**
- Wire transaction validation with UTXO context:
  - Receive transaction from peer or RPC
  - Look up input UTXOs from database
  - Validate via consensus engine with UTXO context
  - Check mempool policy (size limits, fee rate, etc.)
- Connect validated transactions to mempool:
  - Add to mempool if valid and policy-compliant
  - Track ancestor/descendant relationships
  - Handle conflicts with existing mempool txs
- Implement `sendrawtransaction` RPC with real validation:
  - Parse hex transaction
  - Validate against current UTXO set
  - Add to mempool
  - Return txid on success, error on failure
- Implement `getrawtransaction` RPC with full lookup:
  - Check mempool first
  - Check confirmed transactions (requires tx index or block scan)
  - Return hex or decoded transaction
- Implement transaction relay:
  - Announce new mempool txs to peers via inv
  - Respect relay policy
- Test transaction pipeline:
  - Submit valid raw transaction via RPC
  - Verify appears in mempool
  - Verify relayed to peers

**Deliverables:** Working transaction validation → mempool pipeline

**Reference:** Whitepaper §3.3 (Protocol Layer), §4.5 (Transaction Validation)

---

##### Session 9.6.4: Regtest Mining
**Objective:** We can create blocks in our sandbox (pulled forward from Phase 10)

**Background:** Testing the full node requires the ability to create blocks. Rather than wait for Phase 10, we implement minimal mining support now to enable regtest testing. This is not the full mining interface—just enough to test.

**Tasks:**
- Implement regtest network parameters:
  - Trivial difficulty target (can mine with CPU instantly)
  - Regtest genesis block
  - Regtest magic bytes
  - No minimum difficulty
- Make `getblocktemplate` functional:
  - Select transactions from mempool by fee rate
  - Construct coinbase transaction:
    - Correct subsidy for height
    - BIP-34 height encoding
    - Configurable output address/script
  - Build block header template
  - Compute merkle root
  - Return template with all necessary fields
- Make `submitblock` functional:
  - Parse submitted block hex
  - Validate block via consensus engine
  - If valid: apply to chain, update UTXO, store block
  - Return null on success, error string on failure
- Create simple Python mining script for testing:
  - Call getblocktemplate
  - Grind nonce until valid PoW (trivial on regtest)
  - Call submitblock
  - Repeat
- Test regtest mining:
  - Start node with --regtest
  - Run mining script
  - Verify chain grows
  - Verify coinbase outputs appear in UTXO set

**Deliverables:** Complete regtest mining capability

**Reference:** Whitepaper §8 (Mining Interface)

---

##### Session 9.6.5: Regtest & Pruning Integration Testing
**Objective:** Full workflow proof including pruning in sandbox environment

**Tasks:**
- End-to-end test workflow (archival mode):
  1. Start fresh regtest node
  2. Mine genesis + 100 blocks (coinbase maturity)
  3. Create transaction spending mature coinbase
  4. Submit transaction to mempool
  5. Mine block including transaction
  6. Verify UTXO set reflects spend
  7. Stop node
  8. Restart node
  9. Verify all state persisted correctly
- End-to-end test workflow (pruned mode):
  1. Start fresh regtest node with `--prune=10`
  2. Mine 1000+ blocks
  3. Verify old block files deleted
  4. Verify UTXO set correct
  5. Create and mine transactions
  6. Verify pruning continues as chain grows
  7. Stop and restart, verify state intact
- Test coinbase maturity:
  - Attempt to spend immature coinbase (must fail)
  - Wait 100 blocks, spend succeeds
- Test chain reorganization (both modes):
  - Mine competing chains
  - Verify reorg to most-work chain
  - Verify UTXO set reverts/applies correctly
  - Verify pruning handles reorgs safely (keeps 550+ blocks)
- Stress test:
  - Mine 1000 blocks rapidly
  - Create 100 transactions
  - Verify performance acceptable in both modes
- Document any issues found

**Deliverables:** Regtest confidence achieved — full node works in sandbox (archival and pruned modes)

---

##### Session 9.6.6: Headers-First Sync Integration
**Objective:** We can learn the chain from the network

**Tasks:**
- Wire sync manager to consensus engine:
  - Validate downloaded headers via `consensus_validate_header()`
  - Track header chain with accumulated work
  - Identify best chain among multiple peers
- Implement header chain storage:
  - Store validated headers in block index
  - Track validation status (header-only vs full block)
  - Enable efficient header chain traversal
- Wire block download to validation:
  - Queue blocks for download based on header chain
  - Download from multiple peers in parallel
  - Validate full blocks as they arrive
  - Handle out-of-order arrival
- Integrate pruning with sync:
  - If pruning enabled, delete old blocks during IBD
  - Maintain 550+ block buffer for safety
  - Log pruning progress alongside sync progress
- Update `getblockchaininfo` with sync progress:
  - `headers`: number of validated headers
  - `blocks`: number of fully validated blocks
  - `verificationprogress`: blocks / headers ratio
  - `initialblockdownload`: true if syncing
  - `pruned`, `pruneheight`: pruning status
- Test headers-first sync:
  - Connect to testnet
  - Verify headers download and validate
  - Verify sync progress reported correctly
  - Test with both archival and pruned modes

**Deliverables:** Headers-first sync operational (with pruning support)

**Reference:** Whitepaper §7.3 (Initial Block Download)

---

##### Session 9.6.7: Testnet & Mainnet Validation
**Objective:** Validate real Bitcoin blocks on testnet and mainnet

**Tasks:**
- Implement testnet network parameters:
  - Testnet genesis block
  - Testnet magic bytes
  - Testnet DNS seeds
  - Testnet difficulty rules (including reset rule)
- Enable full block validation on testnet:
  - Download blocks via headers-first sync
  - Validate each block through consensus engine
  - Apply to UTXO set
  - Store in block files (or prune if configured)
- Sync significant portion of testnet:
  - Target: 10,000+ blocks with full validation
  - Test both archival and pruned modes
  - Verify no consensus failures
  - Verify UTXO set grows correctly
- Verify mainnet parameters:
  - Mainnet genesis block (correct hash, correct coinbase)
  - Mainnet magic bytes (0xf9beb4d9)
  - Mainnet DNS seeds
  - Mainnet difficulty (no reset rule)
- Begin mainnet IBD:
  - Download initial headers
  - Begin block download and validation
  - Validate first 10,000+ blocks
  - Verify all pass consensus
  - Test with `--prune=10000` (10 GB) for practical demo
- Document performance:
  - Blocks per second rate
  - Memory usage
  - Disk usage (archival vs pruned)
  - Estimated full sync time for each mode
- Ensure graceful handling of mainnet scale:
  - Large blocks (up to 4MB weight)
  - High transaction counts
  - Complex scripts (Taproot, etc.)

**Deliverables:** Mainnet-capable node (archival and pruned modes), ready for Phase 11 full sync testing

**Note:** This session completes Phase 9. The node is now a fully operational validating node capable of syncing mainnet in either archival or pruned mode. Phase 11 will perform comprehensive testing.

---

### Phase 10: Mining Interface

#### Session 10.1: Block Template Generation
**Objective:** Implement getblocktemplate

**Tasks:**
- Implement transaction selection from mempool
- Implement fee-rate ordering
- Implement coinbase transaction construction
- Implement merkle branch computation
- Implement template response formatting

**Deliverables:** Block template generation

---

#### Session 10.2: Block Submission
**Objective:** Implement submitblock

**Tasks:**
- Implement block parsing from submission
- Implement full validation
- Implement chain update on valid block
- Implement relay to network
- Implement error responses

**Deliverables:** Block submission handling

---

### Phase 11: Testing & Hardening

#### Session 11.1: Consensus Test Suite
**Objective:** Embed and run all Bitcoin Core consensus tests

**Tasks:**
- Download Bitcoin Core's test vectors:
  - `script_tests.json`
  - `tx_valid.json` / `tx_invalid.json`
  - `sighash.json`
  - Block validation vectors
- Create test harness to run all vectors
- Ensure 100% pass rate
- Add to build process (make test)

**Deliverables:** Comprehensive test suite

---

#### Session 11.2: Fuzz Testing Infrastructure
**Objective:** Set up fuzz testing for consensus engine

**Tasks:**
- Create fuzz targets for:
  - Transaction parsing
  - Block parsing
  - Script execution
  - Signature verification
- Document fuzz testing procedure
- Run initial fuzz campaign
- Target: 10,000+ CPU-hours before release

**Deliverables:** Fuzz testing setup

---

#### Session 11.3: Chain Sync Test
**Objective:** Verify full chain synchronization

**Tasks:**
- Sync from genesis to current tip on mainnet
- Verify all blocks validate
- Verify UTXO set matches known checkpoints
- Document sync time and resource usage
- Test on regtest with rapid block generation

**Deliverables:** Verified chain sync capability

---

#### Session 11.4: Security Audit Preparation
**Objective:** Prepare codebase for external audit

**Tasks:**
- Code review checklist verification
- Static analysis (clang-analyzer, etc.)
- Memory safety review
- Ensure all consensus paths have test coverage
- Document audit scope and priority areas
- Prepare audit package

**Deliverables:** Audit-ready codebase

---

### Phase 12: Completion

#### Session 12.1: Documentation Finalization
**Objective:** Complete all documentation

**Tasks:**
- Final README with build/run instructions
- Code documentation review
- API documentation
- Verify whitepaper matches implementation
- Create CHANGELOG

**Deliverables:** Complete documentation

---

#### Session 12.2: Release Preparation
**Objective:** Prepare final release

**Tasks:**
- Final build verification (all platforms)
- Final test run (all tests pass)
- Create source tarball
- Compute SHA-256 hash
- Prepare signing keys

**Deliverables:** Release candidate

---

#### Session 12.3: Blockchain Embedding
**Objective:** Embed release hash in Bitcoin blockchain

**Tasks:**
- Create transaction with OP_RETURN containing tarball hash
- Broadcast transaction
- Wait for confirmation
- Document transaction ID and block

**Deliverables:** Immutable timestamp proof

---

#### Session 12.4: Final Signing and Archival
**Objective:** Complete project closure

**Tasks:**
- Sign tarball with PGP keys
- Publish signed release
- Publish private signing keys
- Archive repository (read-only)
- Update landing page with completion announcement
- Project complete

**Deliverables:** Frozen, complete implementation

---

## Progress Tracking

Use this section to track completion status. Update after each session.

### Phase 0: Foundation
| Session | Status | Notes |
|---------|--------|-------|
| 0.1 Repository Setup | Complete | Dec 2025 |
| 0.2 Build System | Complete | Dec 2025 |
| 0.3 Core Types | Complete | Dec 2025 |

### Phase 1: Platform Abstraction
| Session | Status | Notes |
|---------|--------|-------|
| 1.1 Interface Definition | Complete | Dec 2025 |
| 1.2 POSIX Networking | Complete | Dec 2025 |
| 1.3 POSIX Threading | Complete | Dec 2025 |
| 1.4 POSIX Files/Time/Entropy | Complete | Dec 2025 |
| 1.5 Windows Implementation | Not Started | |

### Phase 2: Cryptographic Primitives
| Session | Status | Notes |
|---------|--------|-------|
| 2.1 SHA-256 | Complete | Dec 2025 — 9/9 tests pass |
| 2.2 RIPEMD-160 | Complete | Dec 2025 — 17/17 tests pass |
| 2.3 secp256k1 Field | Complete | Dec 2025 — 19/19 tests pass |
| 2.4 secp256k1 Group | Complete | Dec 2025 — 15/15 tests pass |
| 2.5 ECDSA Verification | Complete | Dec 2025 — 17/17 tests pass |
| 2.6 Schnorr Verification | Complete | Dec 2025 — 20/20 tests pass |
| 2.7 Signature Interface | Complete | Dec 2025 — 13/13 tests pass |

### Phase 3: Consensus — Data Structures
| Session | Status | Notes |
|---------|--------|-------|
| 3.1 Varint Encoding | Complete | Dec 2025 — 50/50 tests pass |
| 3.2 Transaction Structures | Complete | Dec 2025 — 15/15 tests pass |
| 3.3 Block Structures | Complete | Dec 2025 — 14/14 tests pass |
| 3.4 Merkle Trees | Complete | Dec 2025 — 15/15 tests pass |

### Phase 4: Consensus — Transaction Validation
| Session | Status | Notes |
|---------|--------|-------|
| 4.1 Script Structures | Complete | Dec 2025 — 56/56 tests pass |
| 4.2 Stack Machine | Complete | Dec 2025 — 66/66 tests pass |
| 4.3 Arithmetic/Logic Opcodes | Complete | Dec 2025 — 70/70 tests pass |
| 4.4 Crypto Opcodes | Complete | Dec 2025 — 89/89 tests pass |
| 4.5 SegWit Scripts | Complete | Dec 2025 — BIP-143 sighash, P2WPKH, P2WSH verification |
| 4.6 Taproot Scripts | Complete | Dec 2025 — BIP-341 sighash, key/script path, OP_CHECKSIGADD |
| 4.7 P2SH Evaluation | Complete | Dec 2025 — P2SH verification, push-only check, P2SH-SegWit |
| 4.8 Timelock Opcodes | Complete | Dec 2025 — OP_CLTV, OP_CSV, 23/23 tests pass |
| 4.9 Script Test Vectors | Complete | Dec 2025 — Bitcoin Core vectors, 960/982 pass (100% non-witness), SHA1 impl, DER validation |
| 4.10 Transaction Validation | Complete | Dec 2025 — tx_validate.c, 30/30 tests pass, full validation with UTXO context |

### Phase 5: Consensus — Block Validation
| Session | Status | Notes |
|---------|--------|-------|
| 5.1 Header Validation | Complete | Dec 2025 — PoW check, MTP timestamp, version bits, 29/29 tests pass |
| 5.2 Difficulty Adjustment | Complete | Dec 2025 — Retargeting every 2016 blocks, factor-of-4 clamping, powlimit cap, 42/42 tests pass |
| 5.3 Coinbase Validation | Complete | Dec 2025 — Subsidy halving, BIP-34 height encoding, witness commitment, maturity check, 30/30 tests pass |
| 5.4 Full Block Validation | Complete | Dec 2025 — block_validate() with full validation pipeline, merkle root verification, duplicate txid detection, size/weight limits, 57/57 tests pass |

### Phase 6: Consensus — Chain Selection
| Session | Status | Notes |
|---------|--------|-------|
| 6.1 UTXO Structures | Complete | Dec 2025 — utxo.h/c, hash table implementation, 22/22 tests pass |
| 6.2 Chain State | Complete | Dec 2025 — chainstate.h/c, work256 arithmetic, block apply/revert, 28/28 tests pass |
| 6.3 Chain Selection | Complete | Dec 2025 — block index map, chain comparison, common ancestor, reorg planning, 48/48 tests pass |
| 6.4 Consensus Integration | Complete | Dec 2025 — consensus.h/c, unified API, pure validation functions, soft fork activation flags, 37/37 tests pass |

### Phase 7: Storage
| Session | Status | Notes |
|---------|--------|-------|
| 7.1 Block Files | Complete | Dec 2025 — blocks_storage.h/c, blk*.dat format, append-only files, 9/9 tests pass |
| 7.2 SQLite Integration | Complete | Dec 2025 — db.h/c, SQLite amalgamation wrapper, WAL mode, transactions, prepared statements, 21/21 tests pass |
| 7.3 UTXO Database | Complete | Dec 2025 — utxo_db.h/c, UTXO table schema per §6.2, atomic block apply, batch operations, 16/16 tests pass |
| 7.4 Block Index | Complete | Dec 2025 — block_index_db.h/c, block index schema per §6.3, chain queries, reorganization support, 16/16 tests pass |

### Phase 8: Protocol Layer
| Session | Status | Notes |
|---------|--------|-------|
| 8.1 Message Structures | Complete | Dec 2025 — protocol.h, messages.c, 25/25 tests pass |
| 8.2 Serialization | Complete | Dec 2025 — protocol_serialize.h/c, 17/17 tests pass, full wire format encoding/decoding |
| 8.3 Peer Management | Complete | Dec 2025 — peer.h/c, connection lifecycle, version/verack handshake, message queuing, 20/20 tests pass |
| 8.4 Peer Discovery | Complete | Dec 2025 — discovery.h/c, DNS seeds, hardcoded seeds, addr/getaddr handling, outbound selection, 20/20 tests pass |
| 8.5 Inventory/Relay | Complete | Dec 2025 — relay.h/c, inv/getdata protocol, block/tx relay, DoS prevention (rate limiting, banning), 15/15 tests pass |
| 8.6 Headers-First Sync | Complete | Dec 2025 — sync.h/c, headers-first IBD, block locator construction, header chain validation, block download queue, parallel downloads from multiple peers, sync progress tracking, 32/32 tests pass |
| 8.7 Mempool | Complete | Dec 2025 — mempool.h/c, transaction acceptance policy, fee-based prioritization, size limits, eviction, conflict detection, ancestor/descendant tracking, block handling, mining selection, 82/82 tests pass |

### Phase 9: Application Layer
| Session | Status | Notes |
|---------|--------|-------|
| 9.1 Node Initialization | Complete | Dec 2025 — node.h/c, node lifecycle management (create/start/stop/destroy), data directory setup, database initialization, consensus engine integration, mempool integration, peer discovery integration, component accessors, statistics, signal handling, 36/36 tests pass |
| 9.2 Event Loop | Complete | Dec 2025 — node_process_peers() peer message handling (ping/pong, addr, headers, blocks, tx, inv/getdata), node_process_blocks() for chain updates, node_maintenance() periodic tasks (peer ping, sync tick, outbound connections, cleanup), main event loop structure in main.c with signal handling, 13/13 tests pass |
| 9.3 RPC Interface | Complete | Dec 2025 — rpc.h/c with full JSON-RPC 1.0 server: minimal recursive-descent JSON parser, JSON response builder, HTTP/1.0 request handling, 7 RPC methods (getblockchaininfo, getblock, getblockhash, getrawtransaction, sendrawtransaction, getblocktemplate, submitblock), hash formatting with reversed byte order for display, hex encoding/decoding utilities, completed read_net_addr for addr message deserialization, 39/39 tests pass |
| 9.4 Logging | Complete | Dec 2025 — log.h/c, fixed-format machine-parseable logging, timestamp with milliseconds, log levels (ERROR/WARN/INFO/DEBUG), component-based filtering (MAIN/NET/P2P/CONS/SYNC/POOL/RPC/DB/STOR/CRYP), file output support, thread-safe with platform mutex, plat_mutex_alloc/free added to platform API, 28/28 tests pass |
| 9.5 Observer Mode | Complete | Dec 2025 — --observe CLI flag, argument parsing (--datadir, --port, --rpcport), peer discovery via DNS seeds, ring buffers for blocks (100) and transactions (1000), observer RPC methods (getobserverstats, getobservedblocks, getobservedtxs), non-blocking sockets, CORS preflight support, complete peer handshake, INV message parsing, graceful shutdown, connects to Bitcoin mainnet and observes live network traffic |
| **9.6 Full Node Integration** | | **8 sub-sessions for critical integration work** |
| 9.6.0 Storage Foundation | Complete | Dec 2025 — Chain state restoration from block_index_db on startup (node_restore_chain_state), block application with persistence (node_apply_block updates consensus engine + block files + block_index_db + utxo_db atomically), submitblock RPC uses node_apply_block, getblockchaininfo reports restored chain state, 4 new storage foundation tests (chain restoration across restarts, UTXO persistence, multiple restart cycles), 1023/1023 tests pass |
| 9.6.1 Block Pipeline | Complete | Dec 2025 — Sync manager initialization with block pipeline callbacks (node_init_sync), consensus validation wired to incoming blocks via sync_cb_validate_and_apply_block callback, invalid block tracking ring buffer (1000 blocks) to avoid re-download, block relay after validation (INV broadcast to peers), graceful validation failure handling with detailed logging (error type, failing tx/input index), public API for invalid block checks (node_is_block_invalid, node_get_invalid_block_count, node_process_received_block), 8 new block pipeline tests, 1031/1031 tests pass |
| 9.6.2 Pruning Support | Not Started | --prune flag, block file deletion, lite mode (~10 GB vs ~600 GB) |
| 9.6.3 Transaction Pipeline | Not Started | Transaction validation → mempool flow |
| 9.6.4 Regtest Mining | Not Started | getblocktemplate/submitblock for testing |
| 9.6.5 Regtest & Pruning Integration | Not Started | Full workflow proof in sandbox (archival + pruned modes) |
| 9.6.6 Headers-First Sync | Not Started | Sync manager + consensus integration (with pruning) |
| 9.6.7 Testnet & Mainnet Validation | Not Started | Real block validation on testnet/mainnet |

### Phase 10: Mining Interface
| Session | Status | Notes |
|---------|--------|-------|
| 10.1 Block Templates | Not Started | |
| 10.2 Block Submission | Not Started | |

### Phase 11: Testing & Hardening
| Session | Status | Notes |
|---------|--------|-------|
| 11.1 Consensus Tests | Not Started | |
| 11.2 Fuzz Testing | Not Started | |
| 11.3 Chain Sync Test | Not Started | |
| 11.4 Audit Prep | Not Started | |

### Phase 12: Completion
| Session | Status | Notes |
|---------|--------|-------|
| 12.1 Documentation | Not Started | |
| 12.2 Release Prep | Not Started | |
| 12.3 Blockchain Embedding | Not Started | |
| 12.4 Signing/Archival | Not Started | |

---

## Session Workflow

When starting a new session:

1. **Read this document** — Review current phase and next session
2. **Check progress table** — Understand what's done and what's next
3. **Review relevant whitepaper sections** — Each session references specific sections
4. **Execute the session tasks** — Focus on deliverables
5. **Update progress table** — Mark completed with date and notes
6. **Commit changes** — Small, atomic commits with clear messages

---

## Key Decisions

Record significant implementation decisions here for reference.

| Decision | Rationale | Date |
|----------|-----------|------|
| MIT License | Permissive, compatible with project philosophy | Dec 2024 |
| SQLite for UTXO | Public domain, proven stability, single-file | Per whitepaper |
| C11 standard | Widely supported, portable, long-term stability | Per whitepaper |
| No signing in secp256k1 | Nodes verify, they don't sign | Per whitepaper |

---

## Dependencies

The only external dependencies (per whitepaper):

1. **C11-conformant compiler** (GCC 7+, Clang 6+, MSVC 2019+)
2. **POSIX or Windows standard library**
3. **SQLite** (embedded, public domain)

Everything else is embedded in source tree.

---

## Risk Register

| Risk | Mitigation | Status |
|------|------------|--------|
| Consensus bug | Exhaustive testing against Bitcoin Core vectors | Ongoing |
| Memory safety | Static analysis, fuzz testing, code review | Planned |
| Platform drift | Minimal platform abstraction, stable APIs only | By design |
| Quantum computers | Signature verification seam documented | Documented |

---

## Companion Project: bitcoinecho-gui

**Status:** Planned (begins after Session 9.3)

A universal web application providing a visual interface to a running Bitcoin Echo node. Communicates exclusively via the JSON-RPC interface.

**Prerequisites:** Session 9.3 (RPC Interface) — once RPC endpoints exist, GUI development can proceed in parallel with remaining core work.

**Scope:**
- Sync progress and chain visualization
- Peer connectivity map
- Block and transaction explorer
- RPC console for advanced users

**Key principles:**
- Separate repository (`bitcoinecho-gui`)
- Not ossified — may evolve independently of the frozen core
- Technology stack to be determined in its own Phase 0
- Required for MVP before community launch

**Architecture:**
```
┌─────────────────────────┐
│    bitcoinecho-gui      │  ← Web app (can evolve)
│    (browser-based)      │
└───────────┬─────────────┘
            │ JSON-RPC
┌───────────▼─────────────┐
│     bitcoin-echo        │  ← Frozen daemon
└─────────────────────────┘
```

---

## References

- [Bitcoin Echo Whitepaper](bitcoin-echo-whitepaper.md)
- [Bitcoin Echo Manifesto](bitcoin-echo-manifesto.md)
- [Bitcoin Protocol Documentation](https://en.bitcoin.it/wiki/Protocol_documentation)
- [Bitcoin Improvement Proposals](https://github.com/bitcoin/bips)
- [libsecp256k1](https://github.com/bitcoin-core/secp256k1)
- [Bitcoin Core](https://github.com/bitcoin/bitcoin)

---

*This document is the keystone reference for Bitcoin Echo implementation. When in doubt, consult the whitepaper. When the whitepaper is silent, choose simplicity.*

*Build once. Build right. Stop.*
