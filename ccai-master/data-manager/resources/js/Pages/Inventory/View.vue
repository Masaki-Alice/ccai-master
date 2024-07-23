<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, reactive } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { onUnmounted, onMounted } from "vue";
import axios from 'axios'; // Ensure Axios is imported
import { createToaster } from "@meforma/vue-toaster";

defineProps({
    inventory: Object,
});

let slice = reactive({});
let ground_truth = ref("");
let busy = ref(false);
let mode = ref('VIEW');

const toaster = createToaster({
    position: 'top',
});

function refreshPage() {
    router.reload({
        preserveScroll: true,
        preserveState: true,
    });
}

// Refresh page contents every 2 minutes
const refresher = setInterval(() => refreshPage(), 120000);
onUnmounted(() => {
    clearInterval(refresher);
});

function selectAudio(inventory) {
    ground_truth.value = inventory.ground_truth ?? inventory.text;
    busy.value = false
    mode.value = 'EDIT'
}

async function saveTranscript(currentSlice) {
    router.post(route("transcriptions.ground-truth", currentSlice.id), {
        ground_truth: ground_truth.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            ground_truth.value = '';
            mode.value = 'VIEW'
            busy.value = false;
            toaster.success('Your ground truth file has been saved')
        },
        onBefore: () => {
            busy.value = true;
        }
    })
}

function suggestWithGemini(inventory) {
    router.get(route("inventory.suggest_gemini", inventory.id), {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            slice.val = {};
            ground_truth = '';
            toaster.success('Suggestion successfully retrieved');
            refreshPage();
        },
    });
}

async function redactPII(inventory) {
    router.get(route("redact", inventory.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            busy.value = false;
            toaster.success('PII redaction job has been queued')
        },
        onBefore: () => {
            busy.value = true;
        }
    })
}
</script>


<template>
    <AppLayout title="Audio Details">
        <template #header>
            <div class="block relative">
                <Link :href="route('inventory.index')" class="text-red-500"> Go Back </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <i class="fas fa-play-circle fa-fw"></i> Audio Details
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
                        <h3 class="text-2xl font-bold text-black mb-2">Audio File</h3>
                        <div class="mt-5 text-black">
                            <p>
                                File Name: <code>{{ inventory.file_name }}</code>
                            </p>
                            <p>
                                Disk Path: <code>{{ inventory.disk_path }}</code>
                            </p>
                            <br />
                            <audio controls controlslist="nodownload" preload="metadata"
                                :src="`/audio/play/${inventory.id}/inventory`" class="w-full"></audio>
                        </div>
                    </div>
                </div>

                <!-- Original Transcript -->
                <div class="bg-white shadow sm:rounded-lg mt-8">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="btn-group float-right">
                            <button type="button" @click="suggestWithGemini(inventory)"
                                class="btn btn-secondary btn-sm ">
                                Suggest with Gemini
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" @click="selectAudio(inventory)"
                                v-if="mode === 'VIEW'">
                                Edit
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" @click="mode = 'VIEW'"
                                v-if="mode === 'EDIT'">
                                Cancel
                            </button>
                        </div>
                        <h3 class="text-2xl font-bold text-black mb-3">Transcription</h3>
                        <hr>

                        <!-- Paragraph -->
                        <div class="grid grid-cols-2 gap-4 pt-4" v-if="mode == 'VIEW'">
                            <div class="border rounded-lg p-3 bg-yellow-50">
                                <h1 class="font-bold text-lg text-center">Original</h1>
                                <div class="mt-1 text-black text-lg text-justify h-96 overflow-auto p-2 rounded-lg"
                                    v-html="inventory.text || ground_truth"></div>
                            </div>
                            <div class=" border rounded-lg p-3 bg-green-50">
                                <h1 class="font-bold text-lg text-center">Ground Truth</h1>
                                <div class="mt-1 text-black text-lg text-justify h-96 overflow-auto p-2 rounded-lg"
                                    v-html="inventory.text || ground_truth">
                                </div>
                            </div>
                        </div>

                        <!-- Edit Transcript -->
                        <div class="block mt-4 pt-4" v-else>
                            <div class="grid grid-cols-1 gap-2">
                                <div>
                                    <textarea class="textarea textarea-block !w-full bg-white text-lg text-black"
                                        rows="10" v-model="ground_truth"></textarea>
                                </div>
                                <div>
                                    <button class="btn btn-success float-left" type="button"
                                        @click="saveTranscript(inventory)" v-if="!busy">
                                        <i class="fas fa-check-circle fa-fw"></i> &nbsp;
                                        Save Changes
                                    </button>
                                    <button class="btn btn-loading" disabled v-if="busy">
                                        Saving...
                                    </button>
                                    <br>
                                    <br>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PII Risk Assessment -->
                <div class="bg-white shadow sm:rounded-lg mt-8">
                    <div class="px-4 py-5 sm:p-6">
                        <button type="button" class="btn btn-primary float-right btn-sm" @click="redactPII(inventory)">
                            Redact
                        </button>
                        <h3 class="text-lg font-bold text-black">PII Risk Assessment</h3>
                        <table class="min-w-full divide-y divide-gray-300 mt-5 border"
                            v-if="inventory.dlp_risk_analysis?.length">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                        Excerpt
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                        Detected Info Type
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                        Likelihood
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                        Probability
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="risk in inventory?.dlp_risk_analysis" :key="risk.infoType"
                                    class="hover:bg-gray-50">
                                    <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                        {{ risk.excerpt }}
                                    </td>
                                    <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                        {{ risk.infoType }}
                                    </td>
                                    <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                        {{ risk.likelihood }}
                                    </td>
                                    <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                        {{ risk.probability }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Empty state -->
                        <div class="text-center py-6" v-else>
                            <h3 class="mt-2 text-2xl font-semibold text-gray-500">No risk data available</h3>
                        </div>

                    </div>
                </div>

                <!-- Redacted Transcript -->
                <div class="bg-white shadow sm:rounded-lg mt-8">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-bold text-black">Redacted Transcript</h3>
                        <div class="mt-5 text-black" v-html="inventory.redacted_transcript"></div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
