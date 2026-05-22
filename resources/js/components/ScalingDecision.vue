<script setup>
// Part 2 of the brief, presented in the app. Mirrors docs/scaling-decision.md.
const options = [
    {
        tag: 'a',
        title: 'Index — first, almost always',
        body: 'The active-orders screen filters by status (and recency). A composite index on (status, created_at) turns a full scan of millions of rows into a range scan over the few thousand active ones — typically dropping the query from seconds to milliseconds. It’s the cheapest change and needs no code.',
        trade: 'Every index slows writes, and we already have write pressure. So I’d index narrowly and exactly to the read path, never speculatively.',
    },
    {
        tag: 'c',
        title: 'Separate active-orders table — next, if active ≪ total',
        body: 'The real tension is that reads and writes hit the same growing table. If active orders are a small fraction (say <10%) of the total, a dedicated hot table makes reads scan a tiny table while the archive grows independently; rows move to cold storage on completion/cancellation.',
        trade: 'The data movement adds complexity and a consistency risk — it needs a transactional or event-driven move. But it addresses both pressures at once: reads get a small table, and the huge table stops being read-hot.',
    },
    {
        tag: 'b',
        title: 'Redis cache — last, not first',
        body: 'Redis is excellent once the DB layer is healthy, but it doesn’t solve write pressure (we still INSERT into the DB), it adds a failure point, and cache invalidation is genuinely hard — a stale “available” driver is a real dispatch bug. Caching a slow query masks a missing index instead of fixing it.',
        trade: null,
    },
];

const steps = ['Measure', 'Add the right index', 'Buffer writes + split active / archived', 'Redis as a read accelerator (last)'];
</script>

<template>
    <div class="mx-auto max-w-3xl p-6">
        <!-- Title -->
        <div class="mb-6">
            <span class="text-xs font-semibold uppercase tracking-widest text-brand">Part 2 — Architecture</span>
            <h1 class="mt-1 border-l-4 border-brand pl-3 text-2xl font-bold uppercase tracking-wide text-ink">
                Scaling Decision
            </h1>
            <p class="mt-1 pl-4 text-sm text-gray-500">High write + read pressure · ~360 words</p>
        </div>

        <!-- Measure first -->
        <section class="mb-6 rounded-lg border border-gray-200 bg-white p-5">
            <h2 class="mb-2 text-sm font-bold uppercase tracking-wide text-ink">Start by measuring</h2>
            <p class="text-sm leading-relaxed text-gray-700">
                Before changing anything I’d quantify the problem: writes/second at peak, read
                <strong>p95/p99</strong> on the active-orders query (and its <code class="rounded bg-gray-100 px-1">EXPLAIN</code>
                plan), current table size and growth rate, and the ratio of <em>active</em> to <em>total</em> orders.
                “Redis because it’s faster” treats a symptom — the numbers tell us whether the real bottleneck is the
                query plan, lock contention, I/O, or sheer row volume. Most “slow screen” cases are a missing or wrong
                index, not a missing cache.
            </p>
        </section>

        <!-- Options -->
        <section class="mb-6 space-y-4">
            <div v-for="opt in options" :key="opt.tag" class="rounded-lg border border-gray-200 bg-white p-5">
                <div class="mb-2 flex items-center gap-3">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand text-sm font-bold text-ink">
                        {{ opt.tag }}
                    </span>
                    <h2 class="text-sm font-bold uppercase tracking-wide text-ink">{{ opt.title }}</h2>
                </div>
                <p class="text-sm leading-relaxed text-gray-700">{{ opt.body }}</p>
                <p v-if="opt.trade" class="mt-3 border-l-4 border-amber-400 bg-amber-50 px-3 py-2 text-sm text-gray-700">
                    <strong class="text-amber-800">Trade-off:</strong> {{ opt.trade }}
                </p>
            </div>
        </section>

        <!-- Conclusion flow -->
        <section class="rounded-lg border border-gray-200 bg-ink p-5 text-white">
            <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-brand">What I’d actually do</h2>
            <div class="flex flex-wrap items-center gap-2">
                <template v-for="(step, i) in steps" :key="i">
                    <span class="rounded-md bg-ink-soft px-3 py-1.5 text-xs font-medium text-gray-100">{{ step }}</span>
                    <span v-if="i < steps.length - 1" class="text-brand">→</span>
                </template>
            </div>
            <p class="mt-4 text-sm leading-relaxed text-gray-300">
                Every step is reversible and measured — not a leap to the trendiest tool.
            </p>
        </section>
    </div>
</template>
