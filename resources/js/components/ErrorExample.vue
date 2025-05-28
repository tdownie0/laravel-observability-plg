<script setup lang="ts">
const causeError = (): void => {
    throw new Error('Error from button click!');
};
</script>

<template>
    <div>
        <button @click="causeError" class="btn btn-destructive">Cause an error</button>
    </div>
</template>
