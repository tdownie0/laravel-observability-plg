<script setup lang="ts">
import Button from './ui/button/Button.vue';

const causeError = (): void => {
    throw new Error('Error from button click!');
};
</script>

<template>
    <div>
        <Button @click="causeError" variant="destructive" size="lg" class="hover:cursor-pointer">Cause an error</Button>
    </div>
</template>
