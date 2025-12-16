# Policy vs. Consensus: Why Core and Knots Are Both Right (and Both Wrong)

**A Bitcoin Echo Position Paper**

---

## The Current Debate

In 2025, the Bitcoin implementation landscape faces a significant philosophical divide:

**Bitcoin Core v30** removed the 80-byte OP_RETURN limit, aligning node mempool policy with observed miner behavior and reducing UTXO set bloat by encouraging data storage in OP_RETURN outputs rather than unspendable UTXOs.

**Bitcoin Knots** filters what it considers "spam" transactions—including Ordinals inscriptions, arbitrary data embeddings, and certain token protocols—to preserve Bitcoin's focus as a monetary network.

Both implementations claim to be preserving Bitcoin. Both are making policy choices. Neither is being entirely honest about what they're doing.

---

## The Confusion

### What Bitcoin Core v30 Claims

"We're just removing an arbitrary policy limit that doesn't match consensus rules. Miners decide what goes in blocks anyway, so node policy that differs from miner behavior just creates confusion and resource waste."

### What Bitcoin Core v30 Actually Does

Sets a permissive default policy that treats all consensus-valid transactions as equally legitimate and worthy of relay. This is a **value judgment**: it assumes Bitcoin has no "purpose" beyond what consensus rules permit, and that financial transactions have no special status.

### What Bitcoin Knots Claims

"We're filtering spam and abuse to protect Bitcoin's purpose as sound money. These data-embedding schemes exploit vulnerabilities and were never part of Bitcoin's design."

### What Bitcoin Knots Actually Does

Sets a restrictive default policy that filters transactions based on a philosophical judgment about Bitcoin's "true purpose." This is also a **value judgment**: it assumes Bitcoin's legitimate use cases can be distinguished from illegitimate ones at the policy layer.

### The Truth

**Both are right** that their policy serves their values and understanding of Bitcoin.

**Both are wrong** to claim neutrality while making value-laden choices about what Bitcoin "should" be used for.

---

## The Architectural Mistake

The Core/Knots debate confuses two distinct layers:

### Consensus Layer

**What it does:** Defines which blocks and transactions are valid according to protocol rules. Determines which chain has the most accumulated proof-of-work. Settles disputes about transaction history.

**Who decides:** The protocol itself, as implemented identically by all consensus-compatible nodes. Changes require broad ecosystem coordination (soft forks or hard forks).

**Current state:** Remarkably stable since 2009. Soft forks like SegWit and Taproot have tightened rules without invalidating old blocks.

### Policy Layer

**What it does:** Determines which valid transactions a node will relay and temporarily store in its mempool. Affects network resource usage, DoS resistance, and user experience, but not consensus.

**Who decides:** Each node operator, based on their values, resources, and use case.

**Current state:** Falsely presented as having a "correct default" by both major implementations.

---

## Why This Matters

When policy and consensus are confused, the debate becomes tribal rather than technical:

- "Core v30 will kill Bitcoin!" (Wrong—it's a policy change, not a consensus change)
- "Knots is censoring transactions!" (Wrong—filtering mempool != preventing inclusion in blocks)
- "There's one true Bitcoin implementation!" (Wrong—diversity at the policy layer is healthy; uniformity at the consensus layer is essential)

The confusion creates a false choice: pick a side in the implementation wars, or run software whose policy choices you don't actually agree with.

---

## Bitcoin Echo's Approach

Bitcoin Echo separates consensus from policy **architecturally**, not rhetorically.

### The Consensus Engine (Frozen)

The core of Bitcoin Echo implements block and transaction validation according to the protocol rules as of 2025, including all deployed soft forks. This component:

- Is pure computation (no I/O, no system calls)
- Can be tested exhaustively in isolation
- Will be frozen upon completion and audit
- Is identical for all Bitcoin Echo operators

**Result:** All Bitcoin Echo nodes agree on the valid chain, regardless of policy configuration.

### The Policy Layer (Configurable at Compile Time)

The protocol layer implements mempool policy, peer management, and relay decisions. Core and Knots handle these through runtime flags. Bitcoin Echo makes them compile-time choices.

**Core/Knots use runtime flags like:**
- `-datacarriersize=100000` (Core v30, effectively unlimited OP_RETURN)
- `-datacarriersize=83` (Core pre-v30, 80 bytes of OP_RETURN data)
- `-rejecttokens=1` (Knots, filter inscriptions/ordinals/Runes)
- `-permitbaremultisig=0` (disable bare multisig relay)

**Bitcoin Echo uses compile-time constants:**
- `MAX_DATACARRIER_BYTES` — Set to 80 (restrictive) or 100000 (permissive) before compilation
- `FILTER_INSCRIPTION_PATTERNS` — Enable/disable token and inscription filtering
- `PERMIT_BARE_MULTISIG` — Allow or reject bare multisig transactions

**Example policy presets:**

**Restrictive** (Knots philosophy)
- `MAX_DATACARRIER_BYTES=80`
- `FILTER_INSCRIPTION_PATTERNS=1`
- `PERMIT_BARE_MULTISIG=0`
- Prioritizes monetary transactions, filters data-carrying schemes

**Permissive** (Core v30 philosophy)
- `MAX_DATACARRIER_BYTES=100000`
- `FILTER_INSCRIPTION_PATTERNS=0`
- `PERMIT_BARE_MULTISIG=1`
- Accepts all consensus-valid transactions equally

**Custom** (Operator-defined)
- Define specific policy thresholds
- Optimize for particular use cases
- Balance resource constraints with relay preferences

### Why Compile-Time Configuration?

Because **policy is a value judgment**, and we refuse to pretend otherwise.

There is no "neutral default" for mempool policy. Accepting everything is a choice. Filtering data-carrying transactions is a choice. Both reflect beliefs about what Bitcoin should be used for.

By forcing policy configuration at compile time, Bitcoin Echo makes the philosophical choice **explicit and auditable**. You're not running "Bitcoin Echo with the default settings"—you're running "Bitcoin Echo compiled for strict financial policy" or "Bitcoin Echo compiled for permissive consensus-only policy."

This is honest.

---

## Precedent: The 2010 Bitcoin Development Philosophy

This separation isn't new. Early Bitcoin developers understood it clearly.

In discussions about transaction fees, relay policy, and micropayments circa 2010, developers like Gavin Andresen recognized that nodes could have **different fee policies** without fragmenting the network, because **consensus remained intact**.

The protocol defined what was valid. Policy defined what was relayed. These were understood as separate concerns.

Somewhere along the way, this distinction was lost. Implementations began claiming that their policy choices were "the right way" or "more faithful to Satoshi's vision," when in fact they were simply embedding their values into default configurations.

Bitcoin Echo returns to the original design philosophy: **tight consensus, diverse policy**.

---

## What This Means for the Current Debate

### For Bitcoin Core Supporters

You're right that mempool policy differing from miner behavior creates inefficiency. You're right that arbitrary restrictions on OP_RETURN size are just policy, not consensus.

But you're wrong to claim your permissive policy is "neutral." Treating all consensus-valid transactions as equally worthy of relay is a philosophical position—one that prioritizes protocol permissiveness over use-case filtering.

Bitcoin Echo lets you run that policy **explicitly**, without pretending it's the only legitimate choice.

### For Bitcoin Knots Supporters

You're right that Bitcoin was designed primarily as electronic cash, and that data-embedding schemes may create long-term costs for node operators. You're right to want policy that reflects Bitcoin's monetary purpose.

But you're wrong to claim that filtering mempool transactions "preserves Bitcoin" in a consensus sense. Miners can include filtered transactions in blocks, and your node will still validate those blocks. Your policy is a **preference**, not a protection.

Bitcoin Echo lets you run that policy **explicitly**, without pretending it prevents data from entering the blockchain.

### For Everyone Else

You don't have to pick a tribe. You can run the **same consensus engine** as everyone else while choosing policy that matches your values.

Want strict filtering? Compile with `POLICY_STRICT`.

Want maximum permissiveness? Compile with `POLICY_PERMISSIVE`.

Have a specific use case? Configure `POLICY_CUSTOM`.

All three configurations produce nodes that agree on the valid chain. The debate becomes what it should be: a technical discussion about tradeoffs, not a religious war about Bitcoin's soul.

---

## The Path Forward

The health of Bitcoin doesn't depend on everyone running the same implementation with the same defaults. It depends on everyone running **consensus-compatible implementations** while having the freedom to set policy according to their needs.

Bitcoin Echo demonstrates this architecturally:

1. **Consensus engine** — Frozen, auditable, identical for all operators
2. **Policy layer** — Configurable, explicit, operator-controlled
3. **Platform layer** — Thin, replaceable, OS-specific

This isn't compromise. This isn't "trying to please everyone." This is **correct software architecture** applied to the consensus/policy distinction that has always existed in Bitcoin.

---

## Show, Don't Tell: Our Implementation

Bitcoin Echo's configuration structure demonstrates architectural separation in code, not rhetoric.

### Consensus Layer (Frozen, Never Changes)

[`echo_consensus.h`](https://github.com/bitcoinecho/bitcoin-echo/blob/main/include/echo_consensus.h) — The consensus rules that all nodes must agree on:

```c
/* Block subsidy halving interval (blocks) */
#define CONSENSUS_HALVING_INTERVAL 210000

/* Maximum block weight (weight units, post-SegWit) */
#define CONSENSUS_MAX_BLOCK_WEIGHT 4000000

/* Maximum script size (bytes) */
#define CONSENSUS_MAX_SCRIPT_SIZE 10000

/* Coinbase maturity (blocks before spendable) */
#define CONSENSUS_COINBASE_MATURITY 100
```

**These values define what makes a block valid. They are FROZEN and identical for all Bitcoin Echo nodes.**

### Policy Layer (Configurable, Operator-Controlled)

[`echo_policy.h`](https://github.com/bitcoinecho/bitcoin-echo/blob/main/include/echo_policy.h) — The relay and mempool rules each operator chooses:

```c
/* Data carrier (OP_RETURN) policy.
 * Historical values: 40 bytes (2013), 80 bytes (2014-2024),
 * 100000 bytes (effectively unlimited, Core v30)
 *
 * Your choice reflects belief about Bitcoin's purpose:
 * - Low values: Prioritize monetary transactions
 * - High values: Treat all consensus-valid uses as legitimate
 */
#define POLICY_MAX_DATACARRIER_BYTES 80

/* Witness data filtering.
 * 0 = Accept all consensus-valid witness data
 * 1 = Filter transactions with arbitrary data patterns
 */
#define POLICY_FILTER_WITNESS_DATA 0

/* Bare multisig relay.
 * 0 = Reject bare multisig (reduce UTXO bloat)
 * 1 = Accept bare multisig (maximum compatibility)
 */
#define POLICY_PERMIT_BARE_MULTISIG 1
```

**These values control relay behavior. They are CONFIGURABLE at compile time. Nodes with different policies still agree on valid blocks.**

### Platform Layer (Pragmatic, May Evolve)

[`echo_platform_config.h`](https://github.com/bitcoinecho/bitcoin-echo/blob/main/include/echo_platform_config.h) — Operational settings:

```c
/* Maximum outbound connections */
#define PLATFORM_MAX_OUTBOUND_PEERS 8

/* Connection timeout (milliseconds) */
#define PLATFORM_CONNECT_TIMEOUT_MS 5000

/* Mempool size limit (megabytes) */
#define POLICY_MEMPOOL_MAX_SIZE_MB 300
```

**These values affect performance and resource usage, not consensus or policy philosophy.**

### Why This Matters

Bitcoin Core and Knots mix these concerns. Their configuration uses runtime flags like `-datacarriersize=100000` that appear neutral but encode philosophical choices.

Bitcoin Echo makes the choice **explicit and visible in the source code**. An operator compiling Bitcoin Echo must consciously set `POLICY_MAX_DATACARRIER_BYTES` to their preferred value. There is no hidden default.

**This is architectural honesty.** The code structure itself enforces the separation we're advocating.

---

## Conclusion

The Bitcoin Core v30 / Knots controversy is not about consensus. It's about values.

Should Bitcoin prioritize being useful for any purpose consensus permits? Or should it prioritize being useful specifically as money?

These are legitimate questions. They deserve honest debate. But they can't be resolved by pretending one policy choice is "more Bitcoin" than another.

Bitcoin Echo offers a different path: **acknowledge that policy requires choice, make that choice explicit and auditable, and preserve consensus uniformity while enabling policy diversity**.

If you believe in Bitcoin's decentralization, you should want multiple implementations with different policy philosophies—as long as they all implement consensus identically.

That's what we're building.

---

**Bitcoin Echo**

*One consensus engine. Many policy choices. Frozen upon completion.*

[bitcoinecho.org](https://bitcoinecho.org)

---

## Further Reading

- [Bitcoin Echo Manifesto](bitcoin-echo-manifesto.md) — Why we freeze
- [Bitcoin Echo Whitepaper](bitcoin-echo-whitepaper.md) — Technical specification
- [Section 3.3.1: Policy Configuration](bitcoin-echo-whitepaper.md#331-policy-configuration-at-compile-time) — Detailed policy options

---

*If you're a Bitcoin developer, node operator, or researcher interested in the consensus/policy separation, we want to hear from you. This is an architectural question with a technical answer—let's treat it as such.*
