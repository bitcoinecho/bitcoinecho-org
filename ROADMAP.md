# Bitcoin Echo — Implementation Roadmap

**A Living Document for Development Sessions**

*Last Updated: December 6, 2025*

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

### Not Yet Started
- [ ] Platform abstraction layer

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
| 4.1 Script Structures | Not Started | |
| 4.2 Stack Machine | Not Started | |
| 4.3 Arithmetic/Logic Opcodes | Not Started | |
| 4.4 Crypto Opcodes | Not Started | |
| 4.5 SegWit Scripts | Not Started | |
| 4.6 Taproot Scripts | Not Started | |
| 4.7 P2SH Evaluation | Not Started | |
| 4.8 Timelock Opcodes | Not Started | |
| 4.9 Script Test Vectors | Not Started | |
| 4.10 Transaction Validation | Not Started | |

### Phase 5: Consensus — Block Validation
| Session | Status | Notes |
|---------|--------|-------|
| 5.1 Header Validation | Not Started | |
| 5.2 Difficulty Adjustment | Not Started | |
| 5.3 Coinbase Validation | Not Started | |
| 5.4 Full Block Validation | Not Started | |

### Phase 6: Consensus — Chain Selection
| Session | Status | Notes |
|---------|--------|-------|
| 6.1 UTXO Structures | Not Started | |
| 6.2 Chain State | Not Started | |
| 6.3 Chain Selection | Not Started | |
| 6.4 Consensus Integration | Not Started | |

### Phase 7: Storage
| Session | Status | Notes |
|---------|--------|-------|
| 7.1 Block Files | Not Started | |
| 7.2 SQLite Integration | Not Started | |
| 7.3 UTXO Database | Not Started | |
| 7.4 Block Index | Not Started | |

### Phase 8: Protocol Layer
| Session | Status | Notes |
|---------|--------|-------|
| 8.1 Message Structures | Not Started | |
| 8.2 Serialization | Not Started | |
| 8.3 Peer Management | Not Started | |
| 8.4 Peer Discovery | Not Started | |
| 8.5 Inventory/Relay | Not Started | |
| 8.6 Headers-First Sync | Not Started | |
| 8.7 Mempool | Not Started | |

### Phase 9: Application Layer
| Session | Status | Notes |
|---------|--------|-------|
| 9.1 Node Initialization | Not Started | |
| 9.2 Event Loop | Not Started | |
| 9.3 RPC Interface | Not Started | |
| 9.4 Logging | Not Started | |

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

**Bitcoin Echo: The Last Implementation**
