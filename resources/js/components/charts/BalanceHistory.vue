<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { formatUsd } from '@/lib/wallet-data';
import type { PortfolioPoint } from '@/types/wallet';

const props = defineProps<{
    history: PortfolioPoint[];
}>();

const ranges = [
    { key: '7d', label: '7D', points: 7 },
    { key: '30d', label: '30D', points: 30 },
] as const;
const activeRange = ref<(typeof ranges)[number]['key']>('30d');

const visible = computed(() => {
    const count = ranges.find((r) => r.key === activeRange.value)?.points ?? 30;
    return props.history.slice(-count - 1);
});

const W = 640;
const H = 200;
const PAD = 8;

const minVal = computed(() => Math.min(...visible.value.map((p) => p.value)));
const maxVal = computed(() => Math.max(...visible.value.map((p) => p.value)));

const points = computed(() => {
    const range = maxVal.value - minVal.value || 1;
    return visible.value.map((p, i) => {
        const x = (i / (visible.value.length - 1)) * (W - PAD * 2) + PAD;
        const y = H - PAD - ((p.value - minVal.value) / range) * (H - PAD * 2);
        return { x, y, label: p.label, value: p.value };
    });
});

const linePath = computed(() =>
    points.value
        .map(
            (p, i) =>
                `${i === 0 ? 'M' : 'L'} ${p.x.toFixed(1)} ${p.y.toFixed(1)}`,
        )
        .join(' '),
);

const areaPath = computed(() => {
    if (points.value.length === 0) return '';
    const first = points.value[0];
    const last = points.value[points.value.length - 1];
    return `${linePath.value} L ${last.x.toFixed(1)} ${H} L ${first.x.toFixed(1)} ${H} Z`;
});

const pathEl = ref<SVGPathElement | null>(null);
const pathLength = ref(0);
const drawn = ref(false);

function measure() {
    if (pathEl.value) {
        pathLength.value = pathEl.value.getTotalLength();
        drawn.value = false;
        requestAnimationFrame(() =>
            requestAnimationFrame(() => (drawn.value = true)),
        );
    }
}

onMounted(measure);
watch(linePath, measure);

const first = computed(() => visible.value[0]?.value ?? 0);
const last = computed(
    () => visible.value[visible.value.length - 1]?.value ?? 0,
);
const changePct = computed(() =>
    first.value > 0 ? ((last.value - first.value) / first.value) * 100 : 0,
);

const hoverIndex = ref<number | null>(null);
function onMove(e: MouseEvent) {
    const svg = e.currentTarget as SVGSVGElement | null;
    if (!svg) return;
    const rect = svg.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * W;
    let closest = 0;
    let closestDist = Infinity;
    points.value.forEach((p, i) => {
        const d = Math.abs(p.x - x);
        if (d < closestDist) {
            closestDist = d;
            closest = i;
        }
    });
    hoverIndex.value = closest;
}
</script>

<template>
    <div
        class="border-border bg-card col-span-full rounded-2xl border p-6 lg:col-span-2"
    >
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-vault-ink-dim text-sm">Balance history</p>
                <p class="tnum text-foreground mt-1 text-2xl font-semibold">
                    {{
                        formatUsd(
                            hoverIndex !== null
                                ? points[hoverIndex].value
                                : last,
                            { maximumFractionDigits: 0 },
                        )
                    }}
                </p>
                <p
                    class="tnum text-xs"
                    :class="
                        changePct >= 0 ? 'text-vault-mint' : 'text-vault-rose'
                    "
                >
                    {{ changePct >= 0 ? '+' : '' }}{{ changePct.toFixed(2) }}%
                    this period
                </p>
            </div>

            <div
                class="border-border bg-secondary/60 inline-flex rounded-full border p-0.5"
            >
                <button
                    v-for="r in ranges"
                    :key="r.key"
                    type="button"
                    @click="activeRange = r.key"
                    :class="[
                        'rounded-full px-3 py-1 text-xs font-medium transition-colors',
                        activeRange === r.key
                            ? 'bg-card text-foreground shadow-sm'
                            : 'text-vault-ink-dim hover:text-foreground',
                    ]"
                >
                    {{ r.label }}
                </button>
            </div>
        </div>

        <svg
            :viewBox="`0 0 ${W} ${H}`"
            class="mt-4 h-44 w-full"
            preserveAspectRatio="none"
            @mousemove="onMove"
            @mouseleave="hoverIndex = null"
        >
            <defs>
                <linearGradient id="balanceFill" x1="0" y1="0" x2="0" y2="1">
                    <stop
                        offset="0%"
                        stop-color="var(--vault-mint)"
                        stop-opacity="0.35"
                    />
                    <stop
                        offset="100%"
                        stop-color="var(--vault-mint)"
                        stop-opacity="0"
                    />
                </linearGradient>
            </defs>

            <path :d="areaPath" fill="url(#balanceFill)" />

            <path
                ref="pathEl"
                :d="linePath"
                fill="none"
                stroke="var(--vault-mint)"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                :style="{
                    strokeDasharray: pathLength,
                    strokeDashoffset: drawn ? 0 : pathLength,
                    transition: 'stroke-dashoffset 900ms ease-out',
                }"
            />

            <g v-if="hoverIndex !== null && points[hoverIndex]">
                <line
                    :x1="points[hoverIndex].x"
                    :x2="points[hoverIndex].x"
                    y1="0"
                    :y2="H"
                    stroke="var(--color-border)"
                    stroke-width="1"
                />
                <circle
                    :cx="points[hoverIndex].x"
                    :cy="points[hoverIndex].y"
                    r="4"
                    fill="var(--vault-mint)"
                    stroke="var(--card)"
                    stroke-width="2"
                />
            </g>
        </svg>
    </div>
</template>
