<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    events: {
        type: [Array, Object],
        default: () => [],
    },
});

const itemsPerPage = 6;
const currentPage = ref(1);

const totalPages = computed(() =>
    Math.ceil(props.events.length / itemsPerPage)
);

const paginatedEvents = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return props.events.slice(start, start + itemsPerPage);
});

const showModal = ref(false);
const selectedEvent = ref(null);

const currentTime = new Date();

const form = ref({
    name: '',
    date: '',
    message: '',
    numbers: ''
});

function openEventModal(event) {
    selectedEvent.value = event
    form.value = { name: '', date: '', message: '', numbers: '' }
    showModal.value = true
};

function closeModal() {
    showModal.value = false
};

/* SUBMIT */
function submitForm() {
    router.post(route('event.store'), {
        ...form.value
    }, {
        onSuccess: () => {
            showModal.value = false
        }
    })
}

function nextPage() {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

function prevPage() {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

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

    .event-add-button {
        border: 1px solid #2563eb;
        border-radius: 5px;
        float: right;
        background: #2563eb;
        color: white;
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
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Események listája
            </h2>
            <button class="event-add-button" @click="openEventModal(event)">Esemény hozzáadása</button>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="main">
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
                            <p class="event-add"><button
                                class="add"
                                @click="">Jelentkezés
                                </button>
                            </p>
                        </div>
                    </div>

                    <!-- LAPOZO -->
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
                    <!-- /LAPOZO -->

                    <!-- EVENT_ADD_MODAL - TODO: komponens kiemelesi lehetoseg -->
                    <div
                        v-if="showModal"
                        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50"
                    >
                        <div class="bg-white p-6 rounded shadow-lg w-96">
                            <h2 class="text-xl font-semibold mb-4">
                                Esemény hozzáadása {{ selectedEvent?.title }}
                            </h2>

                            <form @submit.prevent="submitForm">
                                <div class="mb-4">
                                    <label class="block font-medium">Név</label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        minlength="5"
                                        class="w-full border rounded p-2"
                                        required
                                    />
                                </div>
                                <div class="mb-4">
                                    <label class="block font-medium">Dátum</label>
                                    <input
                                        v-model="form.date"
                                        type="datetime-local"
                                        min=""
                                        class="w-full border rounded p-2"
                                        required
                                    />
                                </div>

                                <div class="mb-4">
                                    <label class="block font-medium">Leírás</label>
                                    <textarea
                                        v-model="form.message"
                                        maxlength="5000"
                                        class="w-full border rounded p-2"
                                    ></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="block font-medium">Létszám</label>
                                    <input
                                        v-model="form.numbers"
                                        type="text"
                                        class="w-full border rounded p-2"
                                        required
                                    />
                                </div>

                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="px-4 py-2 border rounded"
                                        @click="closeModal"
                                    >
                                        Mégse
                                    </button>

                                    <button
                                        type="submit"
                                        class="px-4 py-2 bg-green-600 text-white rounded"
                                    >
                                        Mentés
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- /EVENT_MODAL -->
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
