# Bitcoin Echo

**A Faithful Implementation of the Bitcoin Protocol, Built for Permanence**

*Version 1.0 — Final*

---

## Abstract

We present Bitcoin Echo, a complete implementation of the Bitcoin protocol designed not for continued development, but for preservation. Where other implementations evolve, Bitcoin Echo ossifies. The system separates consensus-critical logic from platform-specific code, embeds all cryptographic primitives directly, eliminates external dependencies, and produces a codebase simple enough to be understood completely by a single competent programmer. This document serves as both specification and rationale. Upon completion and audit, Bitcoin Echo requires no further development—only maintenance of the platform abstraction layer as operating systems change beneath it. The consensus engine, once frozen, stays frozen. We are not building software to be improved. We are transcribing a protocol to be preserved.

---

## 1. Introduction

Bitcoin's consensus rules have remained remarkably stable since January 2009. The core validation logic—how transactions are verified, how blocks are accepted, how the chain with the most accumulated proof-of-work is selected—has changed only through carefully-deployed soft forks that tighten, rather than loosen, the rules. This stability is not incidental; it is essential. Any node that disagrees on consensus rules fractures the network.

Yet the software that implements these rules continues to evolve. Dependencies are updated, build systems change, APIs shift, and code is refactored. Each modification, however well-intentioned, introduces risk. A bug in a new optimization can cause consensus failure. A changed dependency can break builds. A refactored module can introduce subtle behavioral differences. The implementation drifts even as the protocol holds steady.

Bitcoin Echo takes a different approach. We implement the Bitcoin protocol once, correctly, and then stop. The result is not a living project with contributors and releases and roadmaps. It is an artifact—a crystallized expression of Satoshi's protocol that can be compiled and run decades from now by engineers who have never heard of us.

This is not a criticism of active development. Bitcoin Core and other implementations serve vital roles in the ecosystem. But there is value in a parallel track: a reference implementation that prioritizes permanence over progress, simplicity over optimization, and auditability over extensibility.

An echo does not editorialize. It does not improve upon the original. It faithfully reproduces what was said, and in doing so, preserves it.

---

## 2. Design Philosophy

Bitcoin Echo is guided by five principles, stated here so that all subsequent design decisions can be evaluated against them.

### 2.1 Minimize Dependencies

Every external dependency is a liability. Libraries change their APIs. Build systems evolve. Package managers alter their resolution algorithms. A project with thirty dependencies is not a project—it is thirty bets that independent maintainers will make compatible decisions indefinitely.

Bitcoin Echo has exactly one external dependency: a C compiler that targets the host platform and provides a standard C library. Everything else—cryptographic primitives, data structures, serialization logic—is embedded directly in the source tree and frozen.

### 2.2 Separate Consensus from Platform

The Bitcoin protocol is an abstract set of rules. It says nothing about sockets or threads or file systems. These are implementation details, and they are the details most likely to change over time.

Bitcoin Echo enforces a strict architectural boundary. The consensus engine is pure computation: bytes in, validity judgment out. It makes no system calls. It allocates no memory dynamically during validation. It can be compiled and tested in isolation. The consensus engine is the artifact we preserve.

Surrounding the consensus engine is a platform abstraction layer—a thin interface to the operating system for networking, storage, threading, and entropy. This layer is expected to require occasional updates as operating systems evolve. Such updates are maintenance, not development. They do not touch consensus.

### 2.3 Prefer Simplicity Over Optimization

A clever optimization that saves 10% CPU time is worthless if it introduces a subtle consensus bug, or if it cannot be understood by the maintainer who inherits the code in 2075.

Bitcoin Echo is not optimized. It validates blocks correctly, not quickly. It uses straightforward algorithms with clear invariants. It favors legibility over performance in every case where the two conflict.

Modern hardware is fast. A slow but correct implementation can validate the entire historical blockchain in acceptable time. There is no prize for finishing first.

### 2.4 Design for Auditability

The codebase must be small enough that a single skilled programmer can read and understand every line. We target approximately 15,000–25,000 lines of C for the complete implementation, heavily commented, with no clever tricks.

Every function should be obvious in purpose. Every branch should be justifiable by reference to the protocol specification. An auditor should be able to compare the code to the Bitcoin protocol rules and verify correspondence without heroic effort.

### 2.5 Close Doors

Most software projects seek contributions. They welcome feature requests, encourage extensions, and publish roadmaps of future work. This is appropriate for living projects.

Bitcoin Echo is not a living project. Upon completion, the correct number of future features is zero. The correct number of accepted pull requests is zero. The repository will be archived. The signing keys will be published. The implementation will be complete in the same way that a novel or a mathematical proof is complete—not because no improvement is possible, but because the work has boundaries, and we have reached them.

### 2.6 Why C

Bitcoin Core is written in C++. Why does Bitcoin Echo use C instead?

**Language stability.** C is finished. The C11 standard is essentially final, and C compilers have been stable for decades. C++, by contrast, evolves continuously—C++11, 14, 17, 20, 23—with each revision adding features, changing semantics, and creating pressure to "modernize." A frozen codebase requires a frozen language.

**No hidden behavior.** In C, what you read is what executes. A function call is a function call. In C++, a single line can trigger implicit constructors, destructors, operator overloads, exception handling, virtual dispatch, and template instantiation. An auditor reading C can trace exactly what the CPU will do. An auditor reading C++ must also understand what the compiler might generate invisibly.

**ABI stability.** C has a universal, stable application binary interface. C++ has name mangling (which varies by compiler), vtable layouts (implementation-defined), and exception handling mechanisms (platform-specific). Consensus code must behave identically everywhere. C's stable ABI makes this straightforward.

**Minimal standard library.** Bitcoin Echo uses only `<stdint.h>`, `<stddef.h>`, and `<string.h>` from the standard library. The C++ Standard Template Library is vast, varies across implementations, and changes between versions. Every container, algorithm, and utility in the STL is code we would have to trust but cannot audit. C lets us depend on almost nothing.

**Universal availability.** Every platform has a C compiler. C code from 1990 still compiles and runs identically today. C++ code from even 2010 may require substantial modification to compile with modern toolchains due to deprecated features and changed defaults.

This is not a criticism of C++ as a language. It is recognition that C is the only mainstream language whose trajectory matches our own: implement once, correctly, and stop.

---

## 3. Architecture

Bitcoin Echo comprises four components arranged in strict layers. Lower layers know nothing of higher layers. Information flows downward as function calls and upward as return values.

```
┌─────────────────────────────────────────────────────────────────┐
│                        Application Layer                        │
│           (node operation, RPC interface, logging)              │
├─────────────────────────────────────────────────────────────────┤
│                        Protocol Layer                           │
│        (P2P message handling, peer management, mempool)         │
├─────────────────────────────────────────────────────────────────┤
│                       Consensus Engine                          │
│   (block validation, transaction validation, chain selection)   │
├─────────────────────────────────────────────────────────────────┤
│                   Platform Abstraction Layer                    │
│          (sockets, threads, files, time, entropy)               │
└─────────────────────────────────────────────────────────────────┘
```

### 3.1 Platform Abstraction Layer

The platform abstraction layer provides a minimal interface between Bitcoin Echo and the host operating system. It exposes approximately thirty functions across five categories:

**Networking.** Socket creation, connection, sending, receiving, and closure. DNS resolution. Non-blocking I/O primitives.

**Threading.** Thread creation and joining. Mutexes. Condition variables for signaling between threads.

**File System.** Reading, writing, and appending to files. Creating directories. Atomic rename for safe database updates.

**Time.** Current wall-clock time in milliseconds. Monotonic time for measuring intervals.

**Entropy.** Cryptographically secure random bytes from the operating system.

Two implementations of this interface exist: one for POSIX systems (Linux, macOS, FreeBSD, OpenBSD) and one for Windows. Each implementation is approximately 500–1000 lines of straightforward code using only facilities that have been stable for decades.

The platform abstraction layer is the only component expected to require maintenance over time. Such maintenance is explicitly out of scope for the consensus engine.

### 3.2 Consensus Engine

The consensus engine is the core of Bitcoin Echo and the component that must never change. It implements all rules necessary to determine whether a block or transaction is valid, and which chain represents the current state of the network.

The consensus engine is a pure function in the mathematical sense. Given a block and the current chain state, it returns a validity determination and (if valid) the new chain state. It performs no I/O. It makes no system calls. It does not allocate memory except from a pre-allocated arena passed in by the caller.

This purity is not merely aesthetic. It enables exhaustive testing. It permits formal analysis. It ensures that the consensus engine can be extracted, compiled independently, and verified against any other implementation or specification.

The consensus engine handles:

- Block header validation (proof-of-work, timestamp, difficulty, merkle root)
- Transaction validation (input/output balance, script execution, signature verification)
- Script interpretation (the complete Bitcoin Script language as frozen after historical soft forks)
- Chain selection (most accumulated proof-of-work among valid chains)
- UTXO set management (tracking unspent outputs, detecting double-spends)

The consensus engine explicitly does not handle:

- Network communication
- Peer selection
- Mempool policy
- Fee estimation
- Wallet functionality

These concerns belong to higher layers or to separate software entirely.

### 3.3 Protocol Layer

The protocol layer implements Bitcoin's peer-to-peer network protocol. It manages connections to other nodes, serializes and deserializes protocol messages, maintains the mempool of unconfirmed transactions, and coordinates block download and relay.

The protocol layer uses the platform abstraction layer for networking and threading, and calls into the consensus engine to validate received blocks and transactions.

Policy decisions reside here. Which transactions to accept into the mempool. Which peers to connect to. How to prioritize block download. These decisions do not affect consensus—a node with different policies will still agree on the valid chain—but they affect performance, resource usage, and resistance to denial-of-service attacks.

Policy may be adjusted through compile-time constants. There are no configuration files, no command-line flags that alter behavior, no runtime policy changes. The node behaves identically on every execution.

### 3.4 Application Layer

The application layer is the entry point and orchestration logic. It initializes the platform abstraction layer, loads or creates the chain database, establishes network connections, and runs the main event loop.

A minimal JSON-RPC interface provides external access for querying blockchain state and submitting transactions. This interface exposes only read operations and transaction broadcast. There are no administrative commands, no wallet operations, no runtime reconfiguration.

Logging is minimal and fixed-format, suitable for long-term archival and machine parsing.

---

## 4. Consensus Rules

The consensus rules implemented by Bitcoin Echo are those of the Bitcoin network as of the completion date of this implementation, including all deployed soft forks. We enumerate them here for completeness and to serve as a specification against which the code can be audited.

### 4.1 Block Structure

A valid block consists of:

1. A block header of exactly 80 bytes
2. A variable-length integer encoding the transaction count
3. One or more transactions, the first of which must be a coinbase transaction

### 4.2 Block Header Validation

A block header contains:

- Version (4 bytes): interpreted according to BIP-9 version bits semantics where applicable
- Previous block hash (32 bytes): must reference a known block in the active chain or a valid side chain
- Merkle root (32 bytes): must equal the merkle root computed from the block's transactions
- Timestamp (4 bytes): must be greater than the median of the previous 11 blocks, and no more than two hours in the future relative to network-adjusted time
- Difficulty target (4 bytes): compact representation of the target threshold; must match the expected difficulty as computed from the retargeting algorithm
- Nonce (4 bytes): arbitrary value adjusted by miners to find valid proof-of-work

The block header hash (SHA-256 applied twice) must be numerically less than or equal to the target threshold derived from the difficulty field.

### 4.3 Difficulty Adjustment

Difficulty adjusts every 2016 blocks. The new target is calculated as:

```
new_target = old_target * (time for last 2016 blocks) / (2016 * 10 minutes)
```

The adjustment is clamped to a factor of 4 in either direction to prevent extreme changes. The minimum difficulty is the genesis block difficulty.

### 4.4 Transaction Structure

A valid transaction consists of:

- Version (4 bytes)
- Input count (variable-length integer)
- Inputs (variable length)
- Output count (variable-length integer)
- Outputs (variable length)
- Lock time (4 bytes)

For transactions with witness data (SegWit), the structure includes:

- Marker (1 byte, must be 0x00)
- Flag (1 byte, must be 0x01)
- Witness data for each input

### 4.5 Transaction Validation

A non-coinbase transaction is valid if and only if:

1. It is syntactically well-formed
2. All referenced inputs exist in the UTXO set
3. No input is referenced twice within the transaction or the same block
4. The sum of input values is greater than or equal to the sum of output values
5. Each input's unlocking script (scriptSig and witness) satisfies the corresponding output's locking script (scriptPubKey)
6. The transaction's lock time constraints are satisfied
7. The transaction size does not exceed consensus limits
8. For witness transactions, witness data is correctly structured and does not exceed limits

### 4.6 Coinbase Transaction

The first transaction in every block must be a coinbase transaction. A valid coinbase transaction:

1. Has exactly one input
2. That input references the null outpoint (32 zero bytes, index 0xFFFFFFFF)
3. The input's scriptSig begins with the block height encoded as required by BIP-34
4. Creates outputs whose total value does not exceed the block subsidy plus total fees
5. Outputs are not spendable until 100 blocks of maturity

### 4.7 Block Subsidy

The initial block subsidy is 50 BTC (5,000,000,000 satoshis). The subsidy halves every 210,000 blocks. The current subsidy at height h is:

```
subsidy = 5000000000 >> (h / 210000)
```

When the right shift reduces the value to zero, no further subsidy is awarded.

### 4.8 Script Execution

Bitcoin Script is a stack-based language with no loops. Bitcoin Echo implements the full script interpreter including:

- All original opcodes as defined in Bitcoin 0.1
- Disabled opcodes (OP_CAT, OP_SUBSTR, etc.) that render a transaction invalid
- OP_CHECKMULTISIG with the historical off-by-one bug preserved
- Pay-to-Script-Hash (P2SH) as specified in BIP-16
- Segregated Witness (SegWit) script execution as specified in BIP-141
- CHECKLOCKTIMEVERIFY as specified in BIP-65
- CHECKSEQUENCEVERIFY as specified in BIP-112
- Taproot script execution as specified in BIP-341 and BIP-342

Script execution is the most complex component of the consensus engine. Bitcoin Echo implements it as a standalone module with exhaustive test coverage derived from the Bitcoin Core test vectors.

### 4.9 Chain Selection

The valid chain with the most accumulated proof-of-work is the active chain. Accumulated proof-of-work is calculated as the sum of work for each block, where work is:

```
work = 2^256 / (target + 1)
```

In the event of a tie (equal accumulated work), the first-seen chain is preferred. This is the only consensus rule that depends on observation order rather than purely on block content.

---

## 5. Cryptographic Primitives

Bitcoin Echo embeds all cryptographic primitives directly in the source tree. There are no external cryptographic dependencies.

### 5.1 SHA-256

The SHA-256 implementation follows FIPS 180-4 exactly. It is optimized only to the extent of using compiler intrinsics for rotation where available. The implementation includes:

- Single-block hashing
- Streaming interface for multi-block messages
- Double-SHA256 (SHA256d) as used for block hashes and transaction IDs
- Midstate optimization for mining (computing the first 64 bytes once)

### 5.2 RIPEMD-160

RIPEMD-160 is used in Bitcoin addresses (HASH160 = RIPEMD160(SHA256(x))). The implementation follows the original 1996 specification.

### 5.3 secp256k1

Elliptic curve operations on the secp256k1 curve are required for ECDSA signature verification and for Schnorr signatures (Taproot). Bitcoin Echo embeds a constant-time implementation supporting:

- Point addition and doubling
- Scalar multiplication
- ECDSA signature verification (not signing—nodes verify, they do not sign)
- Schnorr signature verification as specified in BIP-340

The implementation is derived from widely-audited reference code, stripped of signing functionality (which is not needed for validation), and frozen.

### 5.4 Tagged Hashes

Taproot uses tagged hashes to domain-separate different uses of SHA-256. A tagged hash is:

```
TaggedHash(tag, msg) = SHA256(SHA256(tag) || SHA256(tag) || msg)
```

The tag prefixes for each Taproot context are precomputed constants.

### 5.5 Implementation Verification

All cryptographic implementations include test vectors from:

- NIST (SHA-256)
- The original RIPEMD-160 publication
- The libsecp256k1 test suite
- BIP-340 test vectors (Schnorr)
- Bitcoin Core's extensive script and signature test cases

Test vectors are embedded in the source tree and executed on every build.

---

## 6. Storage

Bitcoin Echo uses a simple, append-oriented storage model designed for durability and recoverability.

### 6.1 Block Storage

Blocks are stored in flat files, sequentially appended. Each block is prefixed with:

- A magic number (network identifier)
- The block size in bytes

This format is intentionally compatible with Bitcoin Core's blk*.dat files, allowing bootstrap from existing block data.

Block files are append-only. Once written, bytes are never modified. This enables simple backup strategies and reduces the surface for corruption.

### 6.2 UTXO Set

The Unspent Transaction Output set is stored using SQLite in WAL (Write-Ahead Logging) mode. SQLite is chosen for:

- Public domain dedication (no licensing concerns, ever)
- Documented commitment to long-term support
- Single-file database (no server, no configuration)
- Proven reliability over decades of deployment
- Built-in integrity checking and recovery

The UTXO set schema is minimal:

```sql
CREATE TABLE utxo (
    outpoint    BLOB PRIMARY KEY,  -- 36 bytes: txid (32) + vout (4)
    value       INTEGER NOT NULL,   -- satoshis
    script      BLOB NOT NULL,      -- scriptPubKey
    height      INTEGER NOT NULL,   -- block height when created
    coinbase    INTEGER NOT NULL    -- 1 if from coinbase, else 0
);
```

Additional indexes support efficient query patterns. The schema is fixed and documented here; there are no migrations.

### 6.3 Block Index

A separate SQLite database indexes block headers:

```sql
CREATE TABLE blocks (
    hash        BLOB PRIMARY KEY,   -- 32 bytes
    height      INTEGER NOT NULL,
    header      BLOB NOT NULL,      -- 80 bytes
    chainwork   BLOB NOT NULL,      -- 32 bytes, big-endian
    status      INTEGER NOT NULL    -- validation status flags
);

CREATE INDEX idx_height ON blocks(height);
CREATE INDEX idx_chainwork ON blocks(chainwork);
```

This enables efficient chain traversal and reorganization handling.

### 6.4 Atomic Updates

All database updates for a single block (UTXO additions, UTXO removals, block index update) occur within a single SQLite transaction. Either the entire block is applied or none of it is. This guarantees consistency even if the process is terminated mid-operation.

---

## 7. Networking

Bitcoin Echo implements the Bitcoin peer-to-peer protocol as documented in the Bitcoin protocol specification and as implemented by the reference client.

### 7.1 Supported Message Types

Bitcoin Echo implements the following message types:

**Required for operation:**
- version, verack (handshake)
- ping, pong (keepalive)
- inv, getdata, block, tx (data propagation)
- getblocks, getheaders, headers (chain synchronization)
- addr, getaddr (peer discovery)
- reject (error reporting)

**Supported for compatibility:**
- sendheaders, sendcmpct (protocol optimizations)
- feefilter (mempool policy)
- wtxidrelay (witness transaction relay)

**Not supported:**
- BIP-157/158 compact block filters (optional service)
- Bloom filters (deprecated, privacy-harmful)

### 7.2 Peer Management

Bitcoin Echo maintains connections to a configurable number of peers (default: 8 outbound, up to 125 total with inbound). Peer selection uses hardcoded seed nodes for bootstrap, supplemented by addresses learned via the addr protocol.

Peers are evaluated for:

- Protocol compliance (malformed messages result in disconnection)
- Responsiveness (slow peers are deprioritized)
- Usefulness (peers that provide blocks and transactions we need)

There is no peer reputation persistence. Each node startup begins with fresh peer evaluation.

### 7.3 Initial Block Download

Initial synchronization uses headers-first download:

1. Request headers from peers using getheaders
2. Validate headers (proof-of-work, difficulty, linkage) without full block data
3. Identify the best chain by accumulated work
4. Request full blocks for the best chain in parallel from multiple peers
5. Validate and apply blocks to build the UTXO set

This approach minimizes wasted bandwidth on orphan chains.

### 7.4 Block and Transaction Relay

Once synchronized, Bitcoin Echo:

- Accepts new blocks via block messages or compact block protocol
- Validates and relays valid blocks to peers
- Accepts transactions into the mempool if they pass validation and policy
- Relays transactions to peers using inventory announcements

Relay policy is deliberately simple: relay everything that passes validation and basic policy checks. Sophisticated relay policies are a vector for fingerprinting and complexity.

---

## 8. Mining Interface

Bitcoin Echo includes a mining interface for producing new blocks. The mining interface is intentionally minimal—it provides work to external mining software but does not perform hashing itself.

### 8.1 Block Template Generation

The getblocktemplate RPC method returns:

- Previous block hash
- Block version
- Difficulty target
- Coinbase transaction template with configurable payout address
- List of transactions selected from the mempool
- Merkle branches for efficient hash computation

Transaction selection uses a simple fee-rate ordering. Sophisticated transaction selection (ancestor fee rate, package relay) is explicitly out of scope.

### 8.2 Work Protocol

External miners connect via a simplified Stratum-like protocol or the getblocktemplate RPC. The interface provides:

- Current block template
- Notification of new blocks (to begin work on the new tip)
- Submission of completed blocks

### 8.3 Mining Hardware Abstraction

Mining hardware evolves continuously. ASICs in 2025 will be obsolete in 2030, and unimaginable in 2075. Bitcoin Echo makes no assumptions about mining hardware.

The hashing work—computing SHA256d on candidate block headers—is delegated entirely to external software. Bitcoin Echo's role is limited to:

- Assembling valid block templates
- Validating submitted blocks
- Propagating valid blocks to the network

This separation ensures that mining hardware evolution does not require changes to Bitcoin Echo.

---

## 9. Build System

Bitcoin Echo builds using only POSIX Make and a C compiler. There is no autoconf, no cmake, no package managers, no configuration scripts.

### 9.1 POSIX Build

```makefile
CC      ?= cc
CFLAGS  ?= -std=c11 -Wall -Wextra -pedantic -O2
LDFLAGS ?= -lpthread

SRC     := $(wildcard src/*.c src/**/*.c)
OBJ     := $(SRC:.c=.o)

echo: $(OBJ)
	$(CC) $(LDFLAGS) -o $@ $^

%.o: %.c
	$(CC) $(CFLAGS) -c -o $@ $<

clean:
	rm -f echo $(OBJ)
```

Variations for debug builds, testing, and platform selection are handled by straightforward conditional variables. The complete Makefile fits on a single printed page.

### 9.2 Windows Build

A batch file invokes the Microsoft C compiler directly:

```batch
@echo off
cl /std:c11 /W4 /O2 src\*.c src\platform\win32.c /Fe:echo.exe
```

No Visual Studio project files. No NuGet packages. Just the compiler and source files.

### 9.3 Compiler Requirements

Bitcoin Echo requires a C11-conformant compiler with standard library. Tested compilers include:

- GCC 7.0 and later
- Clang 6.0 and later
- MSVC 2019 and later

No compiler-specific extensions are used. No assembly language. No intrinsics except for rotation operations behind a portability macro.

### 9.4 Reproducible Builds

Given identical source code and compiler version, Bitcoin Echo produces identical binaries. This is achieved through:

- No timestamps embedded in binaries
- Deterministic ordering in all build steps
- No reliance on system headers beyond C11 standard library

Reproducible builds enable independent verification that distributed binaries match the source code.

---

## 10. Testing

### 10.1 Consensus Test Vectors

Bitcoin Echo includes the complete Bitcoin Core consensus test suite, consisting of:

- Script evaluation tests (valid and invalid scripts)
- Transaction validation tests
- Block validation tests
- Signature verification tests
- Difficulty calculation tests

These tests are run on every build. Failure of any consensus test is a fatal build error.

### 10.2 Fuzz Testing

The consensus engine is structured for fuzz testing. Entry points accept arbitrary byte arrays and must handle all inputs without crashing, hanging, or exhibiting undefined behavior.

Recommended fuzz testing duration before deployment: 10,000 CPU-hours minimum on transaction parsing, script execution, and block validation.

### 10.3 Chain Synchronization Test

A full validation test synchronizes from genesis to a specified checkpoint block against known chain data. This verifies that Bitcoin Echo reaches consensus with the Bitcoin network through hundreds of thousands of historical blocks.

### 10.4 Regression Test Network

Bitcoin Echo supports regtest mode, a private network with trivial difficulty for testing block production, reorganizations, and edge cases without requiring proof-of-work computation.

---

## 11. Security Considerations

### 11.1 Memory Safety

C is not a memory-safe language. Buffer overflows, use-after-free, and related vulnerabilities are possible. Bitcoin Echo mitigates these risks through:

- Conservative buffer sizing with explicit bounds checking
- No dynamic memory allocation in consensus-critical paths
- Static analysis with multiple tools (clang-analyzer, coverity)
- Fuzz testing of all parsing code
- Careful code review with security focus

Memory safety would be more easily achieved in a different language. We choose C despite this risk because its longevity and portability outweigh the safety cost for our purposes, and because the codebase is small enough for exhaustive manual review.

### 11.2 Denial of Service

Bitcoin Echo includes basic protections against denial-of-service:

- Connection limits per IP address
- Rate limiting on protocol messages
- Banning peers that send malformed messages
- Resource limits on memory and CPU per peer

These protections are simple and conservative. Sophisticated DoS mitigation often introduces complexity that obscures consensus-critical code.

### 11.3 Cryptographic Assumptions

Bitcoin's security rests on the hardness of:

- The discrete logarithm problem on secp256k1
- SHA-256 preimage and collision resistance
- RIPEMD-160 preimage resistance (for address security, not consensus)

If any of these assumptions fail, Bitcoin as a protocol is compromised. This is not a Bitcoin Echo-specific risk. We note only that Bitcoin Echo makes no additional cryptographic assumptions beyond those inherent to Bitcoin.

### 11.4 Consensus Compatibility

Bitcoin Echo's consensus rules must match the Bitcoin network exactly. A consensus difference, however minor, would cause Bitcoin Echo to follow a different chain—potentially accepting invalid blocks or rejecting valid ones.

Compatibility is verified through:

- Full chain synchronization from genesis
- Execution of all Bitcoin Core consensus tests
- Manual review of every consensus rule against protocol documentation

No amount of testing can prove the absence of consensus bugs. We minimize this risk through simplicity, extensive testing, and inviting adversarial review.

---

## 12. Distribution and Verification

### 12.1 Source Distribution

Bitcoin Echo is distributed as a source tarball. The tarball contains:

- All source code
- All test vectors
- This specification document
- Build instructions

No binary distribution is provided. Users compile from source.

### 12.2 Cryptographic Signing

The source tarball is signed with:

- PGP signatures from at least two project custodians
- A SHA-256 hash published to the Bitcoin blockchain (embedded in an OP_RETURN output)

The blockchain timestamp provides evidence that the code existed at a specific time and has not been modified since.

### 12.3 Key Publication

Upon project completion and final audit:

- Signing keys are published with their private components
- The repository is archived and marked read-only
- No further signed releases will be made

This is not key compromise. It is intentional closure. By publishing private keys, we ensure no future release can falsely claim authority. Bitcoin Echo 1.0 is the final version. There is no 1.1.

---

## 13. What Bitcoin Echo Is Not

Clarity about scope requires stating what is excluded.

**Bitcoin Echo is not a wallet.** It does not generate addresses, manage keys, or sign transactions. Wallet software should connect to Bitcoin Echo via RPC, or users should use separate wallet implementations.

**Bitcoin Echo is not a block explorer.** It provides minimal query capabilities sufficient for validation and transaction broadcast. Rich queries (address history, balance lookups, transaction graphs) require separate indexing software.

**Bitcoin Echo is not optimized.** It validates the blockchain correctly, not quickly. Initial synchronization takes longer than optimized implementations. This is acceptable.

**Bitcoin Echo is not extensible.** There is no plugin architecture, no scripting interface, no hooks for custom behavior. If you need different behavior, use different software.

**Bitcoin Echo is not a reference for implementers.** Developers building new Bitcoin implementations should reference the protocol documentation and Bitcoin Core. Bitcoin Echo is a reference for validators—people who want to verify the chain without trusting anyone else's code, decades from now.

**Bitcoin Echo is not under development.** Upon completion of initial implementation and audit, development ceases. Bug fixes for clear defects may be issued, signed with the original keys, and documented as errata. Features will not be added. Behavior will not change. The software is finished.

---

## 14. Project Completion Criteria

Bitcoin Echo is complete when:

1. All consensus tests pass
2. Full chain synchronization succeeds from genesis to current tip
3. The codebase has undergone independent security audit
4. This specification document is finalized
5. The implementation matches this specification exactly

Upon meeting these criteria:

1. A final source tarball is created
2. The tarball hash is embedded in the Bitcoin blockchain
3. The tarball is signed by project custodians
4. Private signing keys are published
5. The repository is archived
6. The project is finished

---

## 15. Cryptographic Succession

### 15.1 The Quantum Boundary

Bitcoin's security against classical computers rests on the hardness of the elliptic curve discrete logarithm problem. Quantum computers running Shor's algorithm would break this assumption, enabling derivation of private keys from public keys in polynomial time.

This is not a Bitcoin Echo vulnerability. It is a Bitcoin vulnerability. Bitcoin Echo implements the Bitcoin protocol faithfully; it cannot be more secure than the protocol it implements.

SHA-256, used for proof-of-work and transaction hashing, is more resistant. Grover's algorithm provides only a quadratic speedup, reducing effective security from 256 bits to approximately 128 bits—still beyond feasible attack for the foreseeable future.

We do not know when—or whether—quantum computers capable of breaking elliptic curve cryptography will exist. Estimates range from fifteen years to never. But a project designed for century-scale operation must acknowledge the possibility.

### 15.2 Immutability and Change

There is a common misconception that immutability opposes change. It does not. Immutability means the past does not change. New values may accrete; old values remain valid.

Consider the Bitcoin blockchain itself. It is append-only. Block 100,000 is the same today as when it was mined. Yet the chain grows. New blocks accrete. The system evolves without modifying history.

Bitcoin Echo adopts this philosophy for software. Version 1.0 is complete and frozen. It validates the Bitcoin protocol as that protocol exists at completion. Should the protocol evolve—through a quantum-resistant soft fork or otherwise—Bitcoin Echo 1.0 does not change. It continues to validate what it was built to validate. A successor implementation accretes new capability.

This is not planned obsolescence. Bitcoin Echo 1.0 will validate all pre-succession blocks forever. Even after a hypothetical Bitcoin Echo-Q exists, the original remains correct for its domain. Two frozen implementations, each complete, each valid for its era.

### 15.3 The Succession Seam

To enable clean succession without compromising our ossification commitment, Bitcoin Echo isolates cryptographic signature verification behind a minimal internal interface. This is not a plugin system. There is no runtime extensibility, no configuration, no abstraction for its own sake. It is a documented seam—the single point where a successor implementation would diverge.

```c
/*
 * sig_verify.h — signature verification interface
 *
 * This interface exists to document the succession boundary.
 * All signature verification in the consensus engine flows
 * through this interface. A post-quantum successor would:
 *
 *   1. Fork Bitcoin Echo at its final frozen state
 *   2. Add new signature types to sig_type_t
 *   3. Implement verification for the new scheme
 *   4. Leave all other consensus logic untouched
 *
 * Estimated modification: <500 lines of code.
 */

typedef enum {
    SIG_ECDSA,          /* Pre-SegWit and SegWit v0 */
    SIG_SCHNORR         /* Taproot (SegWit v1) */
    /* Successor adds: SIG_DILITHIUM, SIG_SPHINCS, etc. */
} sig_type_t;

/*
 * Verify a signature against a message hash and public key.
 *
 * Parameters:
 *   type      - signature scheme identifier
 *   sig       - signature bytes
 *   sig_len   - signature length
 *   hash      - 32-byte message hash (sighash)
 *   pubkey    - public key bytes
 *   pubkey_len- public key length
 *
 * Returns:
 *   1 if signature is valid
 *   0 if signature is invalid
 *
 * This function is pure: no side effects, no I/O, deterministic.
 */
int sig_verify(
    sig_type_t      type,
    const uint8_t  *sig,      size_t sig_len,
    const uint8_t  *hash,
    const uint8_t  *pubkey,   size_t pubkey_len
);

/*
 * Check whether a signature type is known.
 * Unknown types in consensus-critical paths cause validation failure.
 */
int sig_type_known(sig_type_t type);
```

The consensus engine calls only these functions for signature verification. Script interpretation, transaction validation, block validation—all cryptographic verification flows through this seam. The rest of the consensus engine need not know or care which signature scheme is in use.

### 15.4 Succession, Not Modification

When the Bitcoin network adopts a quantum-resistant signature scheme, the response is not to modify Bitcoin Echo. The response is succession:

1. **Fork**: Copy Bitcoin Echo source at its final frozen state
2. **Extend**: Add the new signature type and verification logic
3. **Verify**: Pass all existing tests plus new signature scheme tests
4. **Audit**: Independent security review of the new cryptographic code
5. **Complete**: Publish, sign, embed hash in blockchain, publish keys
6. **Freeze**: The successor is now frozen, as Bitcoin Echo is frozen

The successor—call it Bitcoin Echo-Q—is a new complete artifact. It validates all historical blocks (using ECDSA and Schnorr for old transactions) and all new blocks (using the post-quantum scheme where applicable). Bitcoin Echo 1.0 continues to exist, continues to validate pre-quantum blocks correctly, and remains frozen.

This is accretion. The new does not replace the old; it extends the timeline. Bitcoin Echo 1.0 is not deprecated by Bitcoin Echo-Q. It is supplemented.

### 15.5 What We Owe the Future

We cannot implement post-quantum cryptography today. The schemes are immature, the Bitcoin soft fork does not exist, and premature implementation would violate our commitment to implement the actual protocol rather than a speculative future.

What we can do:

- **Acknowledge** the quantum boundary honestly in this document
- **Architect** the signature verification seam cleanly
- **Document** the succession process explicitly
- **Estimate** the scope of change (~500 lines, confined to sig_verify.c)
- **Test** that the seam works by verifying all signature logic flows through it

We do not build for extensibility. We build for clarity. A future developer reading this specification and examining the code should understand exactly where and how succession occurs. They should be able to accomplish it without understanding the rest of the system deeply, because the seam is narrow and well-defined.

This is our obligation to the future: not to solve their problems, but to avoid making their problems harder. A clean seam and clear documentation. The rest is theirs.

### 15.6 On Multiple Successions

The Bitcoin protocol may change more than once. There may be soft forks we cannot anticipate. The succession model handles this naturally:

```
Bitcoin Echo 1.0 (frozen)
    │
    ├── validates: Genesis through pre-quantum era
    │
    └─► Bitcoin Echo-Q (frozen)
            │
            ├── validates: Genesis through post-quantum era
            │
            └─► Bitcoin Echo-R (frozen)
                    │
                    └── validates: Genesis through unknown future era
```

Each implementation is complete. Each is frozen upon completion. Each validates all historical blocks, including those from predecessor eras. The chain of succession is linear; each successor subsumes the prior.

Should Bitcoin fracture into incompatible forks (a hard fork), that is beyond our scope. Bitcoin Echo implements Bitcoin. It does not implement Bitcoin Cash, Bitcoin SV, or other forks. Similarly, successors implement Bitcoin as it evolves through soft forks, not hypothetical alternative protocols.

### 15.7 Guidance for Successors

We address future developers directly:

You are holding a frozen artifact. Do not modify it. If you need different behavior, create a successor. Fork the repository, make your changes, go through the completion process, and freeze your version.

The succession seam is `sig_verify.c`. All signature verification flows through the interface defined in `sig_verify.h`. To add a post-quantum signature scheme:

1. Add a new value to `sig_type_t`
2. Implement the verification function for that scheme
3. Update `sig_type_known()` to recognize the new type
4. Add test vectors for the new scheme
5. Verify all existing tests still pass

Do not modify other files unless the Bitcoin protocol changes in ways that require it. If the soft fork introduces new script opcodes, those changes belong in `script.c`. If it changes transaction structure, those changes belong in `tx.c`. But signature verification is the most likely succession point, and it is the cleanest.

Your successor implementation should follow the same principles we followed:

- Minimal dependencies
- Pure consensus engine
- Platform abstraction for OS-specific code
- Comprehensive tests
- Independent audit
- Completion criteria
- Key publication upon freeze

You are not continuing our project. You are creating your own, using ours as foundation. This is how immutable systems evolve.

---

## 16. Conclusion

Bitcoin's value proposition rests on immutability—not just immutability of the ledger, but immutability of the rules that govern it. An unchanging protocol deserves an unchanging implementation.

Bitcoin Echo is that implementation. It is not the fastest, not the most feature-rich, not the most actively maintained. It is simple, correct, auditable, and permanent. It requires no trust in future developers because there are no future developers. It requires no trust in dependency maintainers because there are no dependencies. It requires only a C compiler and the confidence that such compilers will exist for as long as computers do.

When the last Bitcoin Echo custodian is gone, the software will remain. The source code, the specification, the signed hashes—these are artifacts that outlive their creators. Someone in 2125 can download the tarball, verify the signature against the blockchain-embedded hash, compile the source, and validate the Bitcoin chain.

They will get the same answer we get today. That is the point.

Immutability does not preclude change. The blockchain itself demonstrates this: append-only, ever-growing, yet every historical block unchanged. Bitcoin Echo follows the same philosophy. Version 1.0 is frozen, complete, final. Should the protocol evolve, successors may accrete. Each successor is itself frozen upon completion. Each validates all historical blocks. The past does not change; the future accretes.

This is how immutable systems evolve. Not through modification, but through succession. Not by changing what was, but by adding what comes next.

An echo does not change. It faithfully reproduces what was said. And when the sound travels further than expected, a new echo carries it forward—faithful to the last, as the last was faithful to the first.

We are not building something new. We are preserving something important. And we are leaving a clean seam for those who will preserve what comes after.

---

## Appendix A: Platform Abstraction Interface

The complete platform abstraction interface is provided here for reference.

```c
/* platform.h — complete platform abstraction interface */

#ifndef ECHO_PLATFORM_H
#define ECHO_PLATFORM_H

#include <stddef.h>
#include <stdint.h>

/* Opaque types — defined per platform */
typedef struct plat_socket  plat_socket_t;
typedef struct plat_thread  plat_thread_t;
typedef struct plat_mutex   plat_mutex_t;
typedef struct plat_cond    plat_cond_t;
typedef struct plat_file    plat_file_t;

/* Error codes */
#define PLAT_OK           0
#define PLAT_ERR         -1
#define PLAT_ERR_TIMEOUT -2
#define PLAT_ERR_CLOSED  -3

/* ========== Networking ========== */

/* Create a TCP socket. Returns PLAT_OK on success. */
int plat_socket_create(plat_socket_t *sock);

/* Connect to host:port. Returns PLAT_OK on success. */
int plat_socket_connect(plat_socket_t *sock, const char *host, uint16_t port);

/* Bind and listen on port. Returns PLAT_OK on success. */
int plat_socket_listen(plat_socket_t *sock, uint16_t port, int backlog);

/* Accept incoming connection. Blocks until connection arrives. */
int plat_socket_accept(plat_socket_t *listener, plat_socket_t *client);

/* Send data. Returns bytes sent, or negative on error. */
int plat_socket_send(plat_socket_t *sock, const void *buf, size_t len);

/* Receive data. Returns bytes received, 0 on close, negative on error. */
int plat_socket_recv(plat_socket_t *sock, void *buf, size_t len);

/* Close socket. */
void plat_socket_close(plat_socket_t *sock);

/* Resolve hostname to IP address string. */
int plat_dns_resolve(const char *host, char *ip_out, size_t ip_len);

/* ========== Threading ========== */

/* Create and start a thread. */
int plat_thread_create(plat_thread_t *thread, void *(*fn)(void *), void *arg);

/* Wait for thread to finish. */
int plat_thread_join(plat_thread_t *thread);

/* Initialize a mutex. */
void plat_mutex_init(plat_mutex_t *mutex);

/* Destroy a mutex. */
void plat_mutex_destroy(plat_mutex_t *mutex);

/* Lock a mutex. Blocks until acquired. */
void plat_mutex_lock(plat_mutex_t *mutex);

/* Unlock a mutex. */
void plat_mutex_unlock(plat_mutex_t *mutex);

/* Initialize a condition variable. */
void plat_cond_init(plat_cond_t *cond);

/* Destroy a condition variable. */
void plat_cond_destroy(plat_cond_t *cond);

/* Wait on condition variable. Mutex must be held. */
void plat_cond_wait(plat_cond_t *cond, plat_mutex_t *mutex);

/* Wait on condition variable with timeout (milliseconds). */
int plat_cond_timedwait(plat_cond_t *cond, plat_mutex_t *mutex, uint32_t ms);

/* Signal one waiting thread. */
void plat_cond_signal(plat_cond_t *cond);

/* Signal all waiting threads. */
void plat_cond_broadcast(plat_cond_t *cond);

/* ========== File System ========== */

/* Read entire file into malloc'd buffer. Caller frees. */
int plat_file_read(const char *path, uint8_t **data, size_t *len);

/* Write buffer to file, replacing existing content. */
int plat_file_write(const char *path, const uint8_t *data, size_t len);

/* Append buffer to file. */
int plat_file_append(const char *path, const uint8_t *data, size_t len);

/* Atomically rename file. */
int plat_file_rename(const char *old_path, const char *new_path);

/* Delete file. */
int plat_file_delete(const char *path);

/* Check if file exists. Returns 1 if exists, 0 otherwise. */
int plat_file_exists(const char *path);

/* Create directory (and parents if needed). */
int plat_dir_create(const char *path);

/* ========== Time ========== */

/* Current wall-clock time in milliseconds since Unix epoch. */
uint64_t plat_time_ms(void);

/* Monotonic time in milliseconds (for measuring intervals). */
uint64_t plat_monotonic_ms(void);

/* Sleep for specified milliseconds. */
void plat_sleep_ms(uint32_t ms);

/* ========== Entropy ========== */

/* Fill buffer with cryptographically secure random bytes. */
int plat_random_bytes(uint8_t *buf, size_t len);

#endif /* ECHO_PLATFORM_H */
```

---

## Appendix B: Supported Soft Forks

Bitcoin Echo implements all consensus rules including these soft forks:

| Soft Fork | BIP | Activation | Description |
|-----------|-----|------------|-------------|
| Pay to Script Hash | BIP-16 | April 2012 | P2SH addresses |
| Strict DER Signatures | BIP-66 | July 2015 | DER signature encoding |
| OP_CHECKLOCKTIMEVERIFY | BIP-65 | December 2015 | Time-locked transactions |
| OP_CHECKSEQUENCEVERIFY | BIP-112 | July 2016 | Relative time locks |
| Segregated Witness | BIP-141/143/147 | August 2017 | Witness data separation |
| Taproot | BIP-340/341/342 | November 2021 | Schnorr signatures, MAST |

Bitcoin Echo does not implement soft forks that are proposed but not yet activated, nor does it include activation logic for future soft forks. Should a new soft fork activate on the Bitcoin network after Bitcoin Echo's completion, Bitcoin Echo will:

1. Continue validating pre-fork rules
2. Potentially reject post-fork blocks if the soft fork tightens rules
3. Not implement the new rules

This is an explicit design choice. An ossified implementation cannot track a changing protocol. Users requiring support for future soft forks should use an actively-developed implementation. Bitcoin Echo preserves the protocol as it exists at completion, indefinitely.

---

## Appendix C: Glossary

**Block**: A data structure containing a header and list of transactions, representing a batch of changes to the ledger.

**Coinbase Transaction**: The first transaction in a block, which creates new Bitcoin according to the subsidy schedule.

**Consensus Engine**: The component that determines validity of blocks and transactions according to protocol rules.

**Difficulty**: A measure of how hard it is to find a valid block hash. Adjusted every 2016 blocks.

**Mempool**: A node's collection of unconfirmed transactions awaiting inclusion in a block.

**Ossification**: The process of freezing software such that it no longer changes.

**Platform Abstraction Layer**: A thin interface isolating OS-specific code from portable logic.

**Proof-of-Work**: The mechanism by which miners demonstrate computational effort expended to create a block.

**Soft Fork**: A backwards-compatible tightening of consensus rules.

**UTXO**: Unspent Transaction Output. The set of all spendable coins.

---

## Appendix D: References

1. Nakamoto, S. (2008). Bitcoin: A Peer-to-Peer Electronic Cash System.

2. Bitcoin Improvement Proposals (BIPs). https://github.com/bitcoin/bips

3. Bitcoin Protocol Documentation. https://en.bitcoin.it/wiki/Protocol_documentation

4. Wuille, P. libsecp256k1. https://github.com/bitcoin-core/secp256k1

5. SQLite Long Term Support. https://www.sqlite.org/lts.html

6. FIPS 180-4: Secure Hash Standard. NIST, 2015.

7. Dobbertin, H., Bosselaers, A., Preneel, B. (1996). RIPEMD-160: A Strengthened Version of RIPEMD.

---

## Appendix E: Document History

| Version | Date | Description |
|---------|------|-------------|
| 1.0 | - | Initial and final release |

---

*Bitcoin Echo: A faithful echo of Satoshi's protocol, built to outlast its creators.*

**bitcoinecho.org**
