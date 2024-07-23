<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import DashLinks from "@/Components/Dashboard/DashLinks.vue";

defineProps({
    menu: Array,
    stats: Array,
});
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div v-if="$page.props.flash.success" class="alert alert-success mb-5">
                    {{ $page.props.flash.success }}
                </div>

                <div class="overflow-hidden">
                    <h2 class="text-sm font-medium text-gray-500">
                        Quick Numbers
                    </h2>
                    <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                            <dt class="truncate text-sm font-medium text-gray-500">Full Audios</dt>
                            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
                                {{ stats.inventory_count.toLocaleString() }}
                            </dd>
                        </div>
                        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                            <dt class="truncate text-sm font-medium text-gray-500">Sliced Audios</dt>
                            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
                                {{ stats.slice_count.toLocaleString() }}
                            </dd>
                        </div>
                        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                            <dt class="truncate text-sm font-medium text-gray-500">Total Inventory</dt>
                            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
                                {{ (stats.inventory_count + stats.slice_count).toLocaleString() }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden">
                    <h2 class="text-sm font-medium text-gray-500">
                        Shortcuts
                    </h2>
                    <ul role="list" class="mt-3 grid grid-cols-1 gap-5 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">
                        <dash-links :item="item" v-for="item in menu"></dash-links>
                    </ul>
                </div>
            </div>
        </div>



    </AppLayout>
</template>
