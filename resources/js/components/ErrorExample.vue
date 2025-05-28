<script setup>
const causeError = () => {
    throw new Error('Error from button click!');
};
</script>

<template>
    <div>
        <button
            @click="causeError"
            class="btn btn-destructive"
        >
            Cause an error
        </button>
    </div>
</template>
