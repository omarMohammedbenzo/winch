# Scaling Decision — High Write + Read Pressure

*Part 2 of the brief. ~360 words.*

## Start by measuring

Before changing anything I'd quantify the problem: writes/second at peak, read **p95/p99** on the active-orders query (and its `EXPLAIN` plan), current table size and growth rate, and the ratio of *active* to *total* orders. "Redis because it's faster" treats a symptom — the numbers tell us whether the real bottleneck is the query plan, lock contention, I/O, or sheer row volume. Most "slow screen" cases are a missing or wrong index, not a missing cache.

## (a) Index — first, almost always

The active-orders screen filters by status (and recency). A composite index on `(status, created_at)` turns a full scan of millions of rows into a range scan over the few thousand active ones — typically dropping the query from seconds to milliseconds. It's the cheapest change and needs no code.
**Trade-off:** every index slows writes, and we *already* have write pressure. So I'd index narrowly and exactly to the read path, never speculatively.

## (c) Separate active-orders table — next, if active ≪ total

The real tension is that reads and writes hit the same growing table. If active orders are a small fraction (say <10%) of the total, a dedicated hot table makes reads scan a tiny table while the archive grows independently; rows move to cold storage on completion/cancellation.
**Trade-off:** the data movement adds complexity and a consistency risk — it needs a transactional or event-driven move. But it addresses *both* pressures at once: reads get a small table, and the huge table stops being read-hot.

## (b) Redis cache — last, not first

Redis is excellent *once the DB layer is healthy*, but it doesn't solve write pressure (we still INSERT into the DB), it adds a failure point, and cache invalidation is genuinely hard — a stale "available" driver is a real dispatch bug. Caching a slow query masks a missing index instead of fixing it.

## What I'd actually do

Measure → add the right index (immediate win) → if writes still buckle, **buffer them** (queue → batched INSERT) and split active vs. archived orders. Redis as a read accelerator only after the DB is sound. Every step is reversible and measured — not a leap to the trendiest tool.
