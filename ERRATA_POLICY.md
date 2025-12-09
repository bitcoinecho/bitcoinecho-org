# Bitcoin Echo — Errata and Security Fix Policy

**Version 1.0 — Draft for Discussion**

---

## Philosophy

Bitcoin Echo is designed to freeze forever upon completion. However, "freeze" means "no new features, no behavioral changes, no evolution." It does not mean "ignore critical defects that make the software unusable or dangerous."

An echo must be accurate to be valuable. A bug that causes consensus failure or security vulnerabilities defeats the purpose of preservation.

---

## Types of Post-Completion Changes

### 1. **Critical Consensus Bugs** ✅ ALLOWED
**Definition:** Bugs that cause Bitcoin Echo to reject valid blocks/transactions or accept invalid ones, resulting in consensus failure.

**Process:**
- Issue identified and documented
- Fix is minimal: changes only the defective code path
- Fix is verified to restore correct consensus behavior
- All existing tests still pass
- New test added that fails on old version, passes on new
- Issued as Erratum (e.g., "Bitcoin Echo 1.0.1 — Erratum for Consensus Bug in Script Validation")
- Signed with original keys (if still available) or clearly documented new signing authority
- Erratum document references specific commit/tarball hash being fixed

**Example:** Script interpreter incorrectly rejects a valid P2SH redemption that Bitcoin Core accepts.

---

### 2. **Critical Security Vulnerabilities** ✅ ALLOWED
**Definition:** Memory safety issues, buffer overflows, or other vulnerabilities that could lead to remote code execution or data corruption when running Bitcoin Echo.

**Process:**
- Same as consensus bugs, with additional requirement:
- Must include security advisory documentation
- Coordinate disclosure if significant (responsible disclosure period)

**Example:** Buffer overflow in transaction parsing allows crafted transaction to crash node or execute code.

---

### 3. **Performance Issues** ❌ NOT ALLOWED
**Example:** "Initial sync is slow" — not a defect, this is expected behavior.

---

### 4. **Platform Compatibility Issues** ⚠️ CASE-BY-CASE
**Definition:** Changes needed to compile/run on new platforms or OS versions due to platform drift.

**Decision Criteria:**
- Does it change consensus behavior? → NO
- Does it add new features? → NO
- Does it only update platform abstraction layer? → MAYBE YES

**Process:**
- Document as "Platform Compatibility Update"
- Changes isolated to platform abstraction layer only
- Consensus engine remains unchanged
- All consensus tests still pass
- Versioned as patch (1.0.x)

**Example:** POSIX platform layer needs update for new macOS threading APIs that deprecated old calls.

---

### 5. **Documentation Fixes** ✅ ALLOWED
**Example:** Typo in README, incorrect API documentation, missing explanation.

---

### 6. **Protocol Changes** ❌ NOT ALLOWED
**Definition:** Any change that makes Bitcoin Echo validate differently than Bitcoin Core for the same protocol rules.

**Note:** If Bitcoin protocol evolves (e.g., quantum-resistant soft fork), create Bitcoin Echo-Q as a new project, don't modify Bitcoin Echo.

---

## Versioning

- **v1.0.0** — Initial frozen release
- **v1.0.1** — Critical consensus bug fix (Erratum)
- **v1.0.2** — Critical security fix (Erratum)
- **v1.1.0** — Platform compatibility update (if significant)
- **v2.0.0** — Never issued (would imply new features/behavior)

---

## Erratum Documentation Format

Each erratum release includes:

1. **Erratum Document** (e.g., `ERRATUM-1.0.1.md`):
   - Clear description of the defect
   - Impact assessment
   - Minimal diff showing exact changes
   - Test case that demonstrates fix
   - Verification steps

2. **Updated Tarball**:
   - New tarball with fixed code
   - New SHA-256 hash
   - Signed with original keys (or documented new authority)

3. **Erratum Registry**:
   - Single document listing all errata with brief summaries
   - Links to detailed erratum documents

---

## Signing Key Lifecycle

### Option A: Retain Keys (Recommended for Solo Maintainer)
- Keep signing keys secure but accessible
- Allows issuing errata with original authority
- Document key storage and succession plan
- Publish keys only if you're truly done (death, complete retirement)

### Option B: Publish Keys Immediately
- Publish keys at v1.0.0 release
- Future errata require establishing new signing authority
- Clear documentation of who has authority to issue errata

**Recommendation:** For a solo project, Option A is more practical. You can always publish keys later if you step away.

---

## The Pragmatic Balance

Bitcoin Echo's philosophy is about **not evolving**, not about **ignoring reality**.

- A consensus bug means the software doesn't do what it claims to do → must fix
- A security vulnerability makes the software dangerous to run → must fix
- A feature request or optimization → ignore forever

The key is **minimality**: every erratum must be the smallest possible change that fixes the defect. No refactoring. No "while we're here" improvements.

---

## Long-Term Maintenance

If you step away from the project:

1. Document who (if anyone) has authority to issue errata
2. Or, publish keys and declare no future errata will be issued
3. Community forks are always possible (MIT license)
4. New projects (Bitcoin Echo-Q, etc.) can succeed Bitcoin Echo

---

**Status:** This policy is a draft for discussion. Should be finalized before v1.0.0 release.

**Questions to Resolve:**
- Who can issue errata after you step away?
- What's the process for community-reported bugs?
- How to handle platform compatibility long-term?

