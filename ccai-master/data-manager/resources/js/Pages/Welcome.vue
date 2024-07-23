<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById("screenshot-container")?.classList.add("!hidden");
    document.getElementById("docs-card")?.classList.add("!row-span-1");
    document.getElementById("docs-card-content")?.classList.add("!flex-row");
    document.getElementById("background")?.classList.add("!hidden");
}
</script>

<template>

    <Head title="Welcome" />

    <div class="relative isolate overflow-hidden bg-gray-900 px-6 py-24 sm:py-32 lg:px-8 h-screen">
        <img src="https://images.pexels.com/photos/51809/pexels-photo-51809.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1"
            alt="" class="absolute inset-0 -z-10 h-full w-full object-cover brightness-50 blur-[3px]" />
        <div class="absolute -top-52 left-1/2 -z-10 -translate-x-1/2 transform-gpu blur-3xl sm:top-[-28rem] sm:ml-16 sm:translate-x-0 sm:transform-gpu"
            aria-hidden="true">
            <div class="aspect-[1097/845] w-[68.5625rem] bg-gradient-to-tr from-[#ff4694] to-[#776fff] opacity-20"
                style="
                    clip-path: polygon(
                        74.1% 44.1%,
                        100% 61.6%,
                        97.5% 26.9%,
                        85.5% 0.1%,
                        80.7% 2%,
                        72.5% 32.5%,
                        60.2% 62.4%,
                        52.4% 68.1%,
                        47.5% 58.3%,
                        45.2% 34.5%,
                        27.5% 76.7%,
                        0.1% 64.9%,
                        17.9% 100%,
                        27.6% 76.8%,
                        76.1% 97.7%,
                        74.1% 44.1%
                    );
                "></div>
        </div>
        <div class="mx-auto max-w-2xl text-center pt-14">
            <h2 class="text-4xl font-bold tracking-tight text-white sm:text-6xl drop-shadow-xl">
                CCAI Data Manager
            </h2>
            <p class="mb-8 mt-12 text-lg leading-8 text-white drop-shadow-lg">
                What would you like to do?
            </p>
            <div class="grid grid-cols-2 space-x-4">
                <a :href="route('register')" class="btn btn-success w-full">
                    Register
                </a>

                <a :href="route('login')" class="btn btn-primary w-full">
                    Login
                </a>
            </div>
        </div>
    </div>
</template>
