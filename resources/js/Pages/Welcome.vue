<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    events: {
        type: Array,
        default: () => [],
    },
})

const itemsPerPage = 6;
const currentPage = ref(1);

const totalPages = computed(() =>
    Math.ceil(props.events.length / itemsPerPage)
);

const paginatedEvents = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return props.events.slice(start, start + itemsPerPage);
});

function nextPage() {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
}

function prevPage() {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
}

</script>

<style>
    .main {
        padding: 20px;
    }

    .events-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 16px;
        margin-top: 20px;
    }

    .event-card {
        background: #ffffff;
        border-radius: 8px;
        padding: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e5e5e5;
    }

    .event-desc {
        font-style: italic;
    }

    .event-limit {
        font-style: normal;
        font-size: large;
    }

    .event-add {
        float: right;
        background: #717985;
        color: white;
        padding: 1px;
    }

    .pagination {
        margin-top: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .pagination button {
        padding: 8px 14px;
        border-radius: 6px;
        border: 1px solid #aaa;
        background: #f7f7f7;
        cursor: pointer;
    }

    .pagination button:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
</style>

<template>
    <div class="bg-gray-100">
        <header>
            <nav v-if="canLogin" class="flex overflow-y-auto">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] bg-gray-200 m-2"
                >
                    Kezelőpult
                </Link>

                <template v-else>
                    <Link
                        :href="route('login')"
                        class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] bg-gray-200 m-2"
                    >
                        Bejelentkezés
                    </Link>

                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] bg-gray-200 m-2"
                    >
                        Regisztráció
                    </Link>
                </template>
            </nav>

            <h1 class="text-center text-xl">Mini esemény regisztrációs rendszer</h1>
        </header>
        <main class="mt-6">
            <div class="main">
                <h3 class="text-center">Események listája</h3>
                <div class="events-grid">
                    <div
                        class="event-card"
                        v-for="event in events"
                        :key="event.id"
                    >
                        <h3>{{ event.name }}</h3>
                        <p class="event-desc">{{ event.desc }}</p>
                        <p class="event-date">{{ event.add_date }}</p>
                        <p class="event-limit">{{ event.limit }}</p>
                    </div>
                </div>

                <div class="pagination">
                    <button
                        @click="prevPage"
                        :disabled="currentPage === 1"
                    >
                        Előző
                    </button>

                    <span>Oldal: {{ currentPage }} / {{ totalPages }}</span>

                    <button
                        @click="nextPage"
                        :disabled="currentPage === totalPages"
                    >
                        Következő
                    </button>
                </div>
            </div>
        </main>
        <footer></footer>
    </div>
</template>
