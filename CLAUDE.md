# Bitcoin Echo — Claude Context

## Project Identity

**Bitcoin Echo** is a complete, ossified implementation of the Bitcoin protocol in pure C. Built for permanence, not continued development. Upon completion and audit, the codebase freezes forever.

*Build once. Build right. Stop.*

## Repository Structure

```
bitcoinecho-org/     ← You are here (landing page, docs, public materials)
bitcoin-echo/        ← Sibling folder (C implementation)
```

## Critical Constraints

**You must follow these constraints in all implementation work:**

1. **Pure C11** — No C++ features, no extensions beyond rotation intrinsics
2. **Zero external dependencies** — Only C compiler and standard library (+ SQLite, which is embedded)
3. **Consensus engine purity** — No I/O, no system calls, no dynamic allocation during validation
4. **Simplicity over optimization** — Correct and clear beats fast and clever
5. **15,000–25,000 lines target** — Every line must justify its existence
6. **Heavy commenting** — Code must be understandable by auditor in 2125
7. **No future features** — Implement what exists in Bitcoin today, nothing speculative

## Code Style

- Functions are obvious in purpose
- Every branch justifiable by protocol specification
- No clever tricks
- Bounds checking on all buffers
- Constants over magic numbers
- Descriptive names over abbreviations

## Architecture Layers

```
Application Layer     — Node operation, RPC, logging
Protocol Layer        — P2P messages, peers, mempool
Consensus Engine      — FROZEN CORE (block/tx validation, chain selection)
Platform Abstraction  — OS interface (sockets, threads, files, time, entropy)
```

Information flows down as function calls, up as return values. Lower layers know nothing of higher layers.

## Session Workflow

1. **Check ROADMAP.md** — Find current phase and next session
2. **Review session tasks** — Understand deliverables
3. **Reference whitepaper** — Each session cites relevant sections
4. **Implement** — Focus, atomic, no scope creep
5. **Test** — Every feature must have verification
6. **Update progress table** — Mark session complete in ROADMAP.md
7. **Commit** — Small, atomic commits with clear messages

## Current State

Check the **Progress Tracking** section in [ROADMAP.md](ROADMAP.md) for current implementation status.

**Next session:** 0.1 Repository Setup (create bitcoin-echo sibling folder)

## Key Files

| File | Purpose |
|------|---------|
| [ROADMAP.md](ROADMAP.md) | Exhaustive implementation plan with 52 session units |
| [bitcoin-echo-whitepaper.md](bitcoin-echo-whitepaper.md) | Complete technical specification |
| [bitcoin-echo-manifesto.md](bitcoin-echo-manifesto.md) | Philosophical foundation |
| [index.html](index.html) | Landing page (live at bitcoinecho.org) |

## Quick Reference

**Signature verification seam:** `sig_verify.h` / `sig_verify.c` — the quantum succession boundary

**Platform interface:** See whitepaper Appendix A for complete API

**Supported soft forks:** P2SH, BIP-66, CLTV, CSV, SegWit, Taproot

**Test vectors:** Embed Bitcoin Core's consensus test suite; 100% pass required

## What NOT To Do

- Don't add features beyond Bitcoin protocol as it exists today
- Don't optimize at the expense of clarity
- Don't use external libraries (embed everything)
- Don't make the consensus engine touch I/O
- Don't create abstractions for one-time operations
- Don't add configuration options or runtime flags
- Don't write wallet functionality

## Commit Messages

Follow conventional format:
```
feat: implement SHA-256 with NIST test vectors
fix: correct off-by-one in CHECKMULTISIG (historical bug preserved)
test: add BIP-340 Schnorr verification vectors
docs: update ROADMAP progress table
```

End significant commits with:
```
🤖 Generated with Claude Code
```

## Questions?

When uncertain, consult:
1. The whitepaper (authoritative specification)
2. Bitcoin Core source (reference implementation)
3. BIPs (protocol documentation)

When the whitepaper is silent, choose simplicity.
