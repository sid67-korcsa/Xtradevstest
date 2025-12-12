<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

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
            </div>
        </main>

        <footer></footer>
    </div>
</template>
