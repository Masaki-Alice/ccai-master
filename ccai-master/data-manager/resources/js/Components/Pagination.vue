<script>
import { Link } from "@inertiajs/vue3";

export default {
    components: {
        Link,
    },
    props: {
        links: Array,
    },
};
</script>

<template>
    <div v-if="links.length > 3">
        <div class="flex flex-wrap -mb-1 text-black">
            <template v-for="(link, key) in links">
                <div v-if="link.url === null" :key="key"
                    class="mb-1 mr-1 px-4 py-3 text-gray-700 text-sm leading-4 border rounded bg-white"
                    v-html="link.label" />
                <Link v-else :key="`link-${key}`"
                    class="mb-1 mr-1 px-4 py-3 focus:text-blue-500 text-sm leading-4 hover:bg-white border focus:border-blue-500 rounded"
                    :class="{ 'bg-white': link.active }" :href="link.url" v-html="link.label" />
            </template>
        </div>
    </div>
</template>
