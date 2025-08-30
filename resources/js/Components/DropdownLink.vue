<template>
    <Link
        v-bind="$attrs"
        :class="classes"
    >
        <slot />
    </Link>
</template>

<script>
import { Link } from '@inertiajs/vue3'

export default {
    components: {
        Link,
    },

    computed: {
        classes() {
            return 'block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out'
        },
    },
}
</script>
