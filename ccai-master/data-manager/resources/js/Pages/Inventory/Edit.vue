<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Inertia } from "@inertiajs/inertia";
import { Link, router } from "@inertiajs/vue3";

defineProps({
    inventory: Object,
});

function submit() {
    Inertia.put(route('transcriptions.update', inventory.id), form);
}

</script>

<template>
    <AppLayout title="Edit Generated Transcription">
        <template #header>
            <div class="block relative">
                <Link :href="route('inventory.index')" class="text-red-500">
                    Go Back
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Generated Transcription
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="$page.props.flash.success" class="alert alert-success mb-5">
                    {{ $page.props.flash.success }}
                </div>

                <!-- Audio player -->
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-bold text-black">Audio Player</h3>
                        <div class="mt-5 text-black">
                            <p>File Name: <code>{{ inventory.file_name }}</code></p>
                            <p>Disk Path: <code>{{ inventory.disk_path }}</code></p>
                            <br>
                            <audio controls :src="`/audio/play/${inventory.id}`" class="w-full"></audio>
                        </div>
                    </div>
                </div>

                <!-- Edit Transcript -->
                <div class="bg-white shadow sm:rounded-lg mt-8">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold text-black">Transcript(Edit here)</h3>
                            </div>
                        </div>
                        
                        <!-- Editable textarea for the transcription -->
                        <form @submit.prevent="submit">
                        <div class="mt-5 text-black">
                            <textarea v-model="inventory.vtt" rows="15" cols="168" class="form-control"></textarea>
                        </div>
                        <br>
                        <!-- Save Changes Button -->
                        <div class="flex justify-between items-center mt-5">
                            <!-- <Link class="btn btn-success whitespace-nowrap float-right" :href="route('transcriptions.update', inventory.id)"
                            v-if="!inventory.vttHtmlContent">
                                Save Changes
                            </Link> -->
                            <input type="submit" class="btn btn-success whitespace-nowrap float-right">
                            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Export
                            </button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
