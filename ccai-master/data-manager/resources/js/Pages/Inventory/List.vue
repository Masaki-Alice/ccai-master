<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import { Link, router } from "@inertiajs/vue3";
import { onUnmounted, ref } from "vue";
import { createToaster } from "@meforma/vue-toaster";

defineProps({
    inventory: Object,
});


const toaster = createToaster({
    position: 'top',
});

const refresher = setInterval(() => {
    router.reload({
        preserveScroll: true,
        preserveState: true,
    });
}, 30000);

onUnmounted(() => {
    clearInterval(refresher);
});

const q = ref('');
function search() {
    if (q.value == '') {
        toaster.error('Please enter a search term')
        return
    }

    router.get(route('inventory.index'), {
        q: q.value
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            toaster.success('Search results ready')
        }
    })
}
</script>

<template>
    <AppLayout title="Data Inventory">
        <template #header>
            <div class="flex space-x-2 absolute right-0 top-4">
                <div class="flex-auto">
                    <input type="text" class="input bg-white float-right text-black" placeholder="Search..."
                        v-model="q">
                </div>
                <div class="flex-shrink">
                    <button class="btn btn-secondary" @click="search" @keyup.enter="search">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                NCBA Data Inventory -
                {{ parseInt(inventory.total).toLocaleString() }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="$page.props.flash.success" class="alert alert-success mb-5">
                    {{ $page.props.flash.success }}
                </div>

                <pagination :links="inventory?.links"></pagination>

                <div class="bg-white overflow-hidden shadow-lg rounded-xl border my-8">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th></th>
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                    File Name
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                    colspan="2">
                                    Disk Path
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="file in inventory?.data" :key="file.id" class="hover:bg-gray-50">
                                <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                    {{ file.id }}
                                </td>
                                <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                    <Link :href="route('transcriptions.show', file.id)" class="text-ellipsis">
                                    {{ file.file_name }}
                                    </Link>
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-500">
                                    <p class="text-ellipsis">
                                        {{ file.disk_path }}
                                    </p>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="btn-group btn-group-scrollable float-right">
                                        <Link :href="route('inventory.show', file.id)"
                                            class="btn btn-primary btn-sm whitespace-nowrap">
                                        Details
                                        </Link>

                                        <!-- Read transcript -->
                                        <!-- <Link :href="route(
                                            'transcriptions.show',
                                            file.id
                                        )
                                            " method="get" class="btn btn-success btn-sm whitespace-nowrap"
                                            v-if="file.transcription">
                                        Read Script
                                        </Link> -->
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <pagination :links="inventory?.links"></pagination>
            </div>
        </div>
    </AppLayout>
</template>
