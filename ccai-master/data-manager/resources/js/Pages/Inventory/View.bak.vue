<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, reactive } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { onUnmounted } from "vue";
import axios from 'axios'; // Ensure Axios is imported
import { createToaster } from "@meforma/vue-toaster";

defineProps({
    inventory: Object,
});

let slice = reactive({});
let ground_truth = ref("");
let busy = ref(false);

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

function selectSlice(slice_) {
    slice.val = slice_;
    ground_truth = slice_.ground_truth ?? slice_.html;
    busy = false
}

function cancelEdit() {
    slice.val = {};
}

async function saveTranscript(currentSlice) {
    router.post(route("transcriptions.ground-truth", currentSlice.id), {
        ground_truth: ground_truth,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            slice.val = {};
            ground_truth = '';
            busy = false;
            toaster.success('Your ground truth file has been saved')
        },
        onBefore: () => {
            busy = true;
        }
    })
}

function suggestWithGemini(slice_) {
    router.post(route("transcriptions.suggest_gemini", slice_.id), {}, {
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
</script>
+

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
                        <h3 class="text-2xl font-bold text-black mb-2">Complete Source File</h3>
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

                        <div class="block gap-4 space-x-2 mt-4 text-center">
                            <div class="badge badge-outline-primary">Sampling Rate: {{ inventory.frame_rate }} Hz</div>
                            <div class="badge badge-outline-primary">Length: {{ Math.round(inventory.length_in_seconds)
                                }} sec
                            </div>
                            <div class="badge badge-outline-primary">{{ Math.round(inventory.channels) }} channels
                            </div>
                            <div class="badge badge-outline-primary">Volume: {{ Math.round(inventory.loudness) }} dB
                            </div>
                            <div class="badge badge-outline-primary">Slice Count: {{ Math.round(inventory.slices.length)
                                }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slices -->
                <div class="bg-white shadow sm:rounded-lg mt-8">
                    <div class="px-4 py-5 sm:p-6">
                        <!-- Slicing happening -->
                        <button class="btn btn-loading btn-outline-primary btn-sm float-right mr-2"
                            v-if="inventory.slicing_queued_at">
                            Slicing...
                        </button>

                        <!-- Slice Action -->
                        <Link class="btn btn-outline-primary btn-sm float-right mr-2" method="post" as="button"
                            :href="route('slice', inventory.id)" v-if="!inventory.slicing_queued_at">
                        Make Slices
                        </Link>

                        <h3 class="text-2xl font-bold text-black">Audio Slices</h3>

                        <!-- Slices list -->
                        <div class="border divide-y mt-5 rounded-lg text-black" v-if="inventory.slices.length">
                            <div class="py-2 px-3 hover:bg-gray-50 cursor-pointer" v-for="slice_ in inventory.slices"
                                :key="slice_.id">
                                <div class="block pb-3">
                                    <!--Action buttons-->
                                    <div class="gap-3">
                                        <div class="float-left border rounded-full px-2 py-0.5 text-sm mr-2" :class="{
                                            'border-yellow-700 text-yellow-700': !slice_.transcription,
                                            'border-green-700 text-green-700': slice_.transcription,
                                        }">
                                            {{ slice_.transcription ? "Transcribed" : "Not Transcribed" }}
                                        </div>

                                        <!-- Edit transcript -->
                                        <button v-if="slice?.val?.id !== slice_.id"
                                            class="border border-blue-500 text-blue-500 block rounded-full px-2 float-right !py-0"
                                            @click="selectSlice(slice_)">
                                            <i class="fas fa-edit fa-fw"></i> Edit
                                        </button>


                                        <button v-if="slice?.val?.id === slice_.id && !busy"
                                            class="border bg-red-500 text-white block rounded-full px-2 float-right !py-0"
                                            @click="cancelEdit()">
                                            <i class="fas fa-times fa-fw"></i> Cancel
                                        </button>

                                        <!-- Suggest with Gemini -->
                                        <button type="button" v-if="slice?.val?.id === slice_.id"
                                            class="badge badge-outline-secondary float-right mr-2"
                                            @click="suggestWithGemini(slice_)">
                                            <i class="fas fa-wand-sparkles fa-fw"></i> Suggest with Gemini
                                        </button>
                                    </div>
                                    <h3 class="text-lg"><i class="fa fa-file-audio fa-fw"></i> {{ slice_.file_name }}
                                    </h3>
                                </div>
                                <hr />
                                <br />
                                <audio controls controlslist="nodownload" preload="metadata"
                                    :src="`/audio/play/${slice_.id}/slice`" class="w-full"></audio>

                                <!-- Edit Transcript -->
                                <div v-if="slice?.val?.id == slice_.id" class="block mt-4" :class="{
                                    hidden: slice_.id !== slice.val.id,
                                    block: slice_.id === slice.val.id,
                                }">
                                    <div class="grid grid-cols-1 gap-2">
                                        <div>
                                            <textarea
                                                class="textarea textarea-block !w-full bg-white text-lg text-black"
                                                rows="7" v-model="ground_truth"></textarea>
                                        </div>
                                        <div>
                                            <button class="btn btn-success float-left" type="button"
                                                @click="saveTranscript(slice_)" v-if="!busy">
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

                                <!-- Existing transcript: -->
                                <p v-else v-html="slice_.ground_truth ?? slice_.html"
                                    class="py-4 px-2 border rounded-lg mt-4 bg-white text-justify text-lg"
                                    :class="{ 'text-green-600': slice_.ground_truth, 'text-yellow-600': !slice_.ground_truth }">
                                </p>

                                <!-- <pre>
                                    {{JSON.stringify(slice, null, 2)}}
                                </pre> -->
                            </div>

                        </div>

                        <!-- Empty state -->
                        <div v-if="!inventory.slices.length"
                            class="relative block w-full rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 mt-6">
                            <span class="mt-2 block text-sm font-semibold text-gray-900 my-6">No slices available</span>
                            <!-- Slicing happening -->
                            <button class="btn btn-loading btn-outline-primary btn-sm float-center mr-2"
                                v-if="inventory.slicing_queued_at">
                                Slicing...
                            </button>

                            <!-- Slice -->
                            <Link class="btn btn-outline-primary btn-sm float-center mr-2"
                                :href="route('slice', inventory.id)" v-if="!inventory.slicing_queued_at">
                            Make Slices
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Original Transcript -->
                <div class="bg-white shadow sm:rounded-lg mt-8">
                    <div class="px-4 py-5 sm:p-6">
                        <Link class="btn btn-outline-primary float-right" :href="`/redact/${inventory.id}`"
                            v-if="!inventory.redaction_requested_on">
                        Redact <span v-if="inventory.redacted_transcript">Again</span>
                        </Link>

                        <!-- Redaction happening -->
                        <button class="btn btn-loading btn-outline-primary float-right"
                            v-if="inventory.redaction_requested_on">
                            Redacting...
                        </button>

                        <h3 class="text-lg font-bold text-black">Full Transcript</h3>
                        <div class="mt-5 text-black overflow-auto h-64 text-lg text-justify" v-html="inventory.text">
                        </div>
                    </div>
                </div>

                <!-- PII Risk Assessment -->
                <div class="bg-white shadow sm:rounded-lg mt-8">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-bold text-black">PII Risk Assessment Report</h3>
                        <table class="min-w-full divide-y divide-gray-300 mt-5 border">
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
